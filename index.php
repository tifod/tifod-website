<?php
require_once __DIR__ . '/vendor/autoload.php';

session_start();

// Create and configure Slim app
$app = new \Slim\App(['settings' => [ 'addContentLengthHeader' => false, "displayErrorDetails" => true]]);

// Get container
$container = $app->getContainer();

$container['dbinfos'] = [
    'connect' => 'mysql:host=localhost;dbname=tifod;charset=utf8',
    'user' => 'root',
    'password' => ''
];

// Register component on container
$container['view'] = function ($container) {
    $loader = new Twig_Loader_Filesystem(__DIR__ . '/src/templates');
    $twig = new Twig_Environment($loader, [
        // 'cache' => __DIR__ . '/templates/twig_cache'
        'cache' => false
    ]);
    
    $twig->addGlobal('current_user', (empty($_SESSION['current_user']) ? null : $_SESSION['current_user']));
    
    $twig->addGlobal("current_url", $_SERVER["REQUEST_URI"]);
    
    $filter = new Twig_SimpleFilter('timeago', function ($datetime) {
      $time = time() - strtotime($datetime); 

      $units = array (
        31536000 => 'an',
        2592000 => 'mois',
        604800 => 'semaine',
        86400 => 'jour',
        3600 => 'heure',
        60 => 'minute',
        1 => 'seconde'
      );

      foreach ($units as $unit => $val) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return ($unit == 1)? "à l'instant" : 
               'il y a '.$numberOfUnits.' '.$val.(($numberOfUnits>1 and $val != 'mois') ? 's' : '');
      }
    });
    $twig->addFilter($filter);
    
    return $twig;
};

//Override the default Not Found Handler
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['response']
            ->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write($c['view']->render('error.html', [ 'message' => '404 - Page inexistante' ]));
    };
};

// personnal functions
function createTree($children_list, $children){
    $tree = array();
    foreach ($children as $child){
        if(isset($children_list[$child['id']])){
            $child['children'] = createTree($children_list, $children_list[$child['id']]);
        }
        $tree[] = $child;
    }
    return $tree;
}

// Define app routes
$app->get('/p/{projectId}', function ($request, $response, $args) {
    $projectId = $args['projectId'];
    
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { die('Erreur avec la base de donnée : '.$e->getMessage()); }
    $reponse = $db->query ('select project_id from post where parent_id = 0');
    while ($donnees[] = $reponse->fetch());
    array_pop($donnees);
    $reponse->closeCursor();
    $projectsId = [];
    foreach ($donnees as $project){ $projectsId[] = $project['project_id']; }
    unset($reponse);
    unset($donnees);
    unset($project);
    
    if (in_array($projectId,$projectsId)){
        $reponse = $db->query ('select *, (select user_name from user u where u.user_id = p.author_id) author_name, (select user_name from user u where u.user_id = p.user_id_pin) user_pseudo_pin from post p where project_id = ' . $projectId . ' order by user_id_pin desc, score_percent desc');
        while ($donnees[] = $reponse->fetch());
        array_pop($donnees);
        $reponse->closeCursor();

        if (count($donnees) == 1){
            return $this->view->render('post/project-player.html', ['project' => $donnees, 'projectId' => $projectId]);
        }

        // creating a comprehensive list of the projet posts
        foreach ($donnees as $k => $post){
            $posts [] = [
                'id' => $post['id'],
                'content' => $post['content'],
                'parent_id' => $post['parent_id'],
                'path' => $post['path'],
                'score_result' => $post['score_result'],
                'vote_minus' => $post['vote_minus'],
                'vote_plus' => $post['vote_plus'],
                'score_percent' => $post['score_percent'],
                'user_id_pin' => $post['user_id_pin'],
                'user_pseudo_pin' => $post['user_pseudo_pin'],
                'posted_on' => date($post['posted_on']),
                'author_id' => $post['author_id'],
                'author_name' => $post['author_name'],
            ];
            if ($post['parent_id'] == 0) $topPostId = $k;
        }
        
        $new = [];
        foreach ($posts as $a){
            $new[$a['parent_id']][] = $a;
        }
        $project = createTree($new, array($posts[$topPostId]));

        $new = [];
        foreach ($posts as $a){
            $new[$a['parent_id']][] = [
                'innerHTML' => $this->view->render('post/tree-post.html', ['post' => $a]),
                'id' => $a['id']
            ];
        }
        $project_json = createTree($new, array($posts[$topPostId]));        
        
        return $this->view->render('post/project-player.html', ['project' => $project, 'projectId' => $projectId, 'project_json' => $project_json]);
    } else {
        echo "Hm. Désolé, ce projet n'existe pas, <a href='/'>revenez à la page d'accueil</a>";
    }
});

$app->get('/', function ($request, $response) {
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { die('Erreur avec la base de donnée : '.$e->getMessage()); }
    $reponse = $db->query ('select id, project_id, content from post where parent_id = 0');
    while ($donnees[] = $reponse->fetch());
    array_pop($donnees);
    $lastProjectId = count($donnees) > 0 ? $donnees[count($donnees) - 1]['project_id'] + 1 : 1;
    $reponse->closeCursor();
    return $this->view->render('homepage.html', ['projects' => $donnees, 'child' => ['id' => 0], 'projectId' => $lastProjectId]);
})->setName('homepage');

$app->post('/create-project', function ($request, $response) {
    if (!(empty($_POST['content']) or empty($_POST['project_id']))){
        try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
        } catch(Exception $e) { die('Erreur avec la base de donnée : '.$e->getMessage()); }
        
        $reponse = $db->prepare ("INSERT INTO post(content, parent_id, project_id, path, author_id) VALUES (:content, 0, :project_id, '/', :author_id); UPDATE post SET path = CONCAT(path,(SELECT LAST_INSERT_ID()),'/') WHERE id = (SELECT LAST_INSERT_ID())");
        $reponse->execute([
            'content' => $_POST['content'],
            'project_id' => $_POST['project_id'],
            'author_id' => $_SESSION['current_user']['user_id']
        ]);
        $reponse->closeCursor();
    }
    header('Location: /p/' . $_POST['project_id']); exit();
});

$app->post('/add-post', function ($request, $response) {
    if (empty($_SESSION['current_user'])){
        header('Location: /');
        exit();
    } else {
        if ((!empty($_POST['content']) or isset($_POST['image'])) and isset($_POST['parent_id']) and isset($_POST['project_id'])){
            try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
            } catch(Exception $e) { die('Erreur avec la base de donnée : '.$e->getMessage()); }
            
            $reponse = $db->prepare ("INSERT INTO post(content, parent_id, project_id, path, author_id) VALUES ('file', :parent_id, :project_id, (SELECT IF (:parent_id = 0,'/',(SELECT path FROM post AS p WHERE id = :parent_id))), :author_id); UPDATE post SET path = CONCAT(path,(SELECT LAST_INSERT_ID()),'/') WHERE id = (SELECT LAST_INSERT_ID())");
            $reponse->execute([
                'parent_id' => $_POST['parent_id'],
                'project_id' => $_POST['project_id'],
                'author_id' => $_SESSION['current_user']['user_id']
            ]);
            $reponse->closeCursor();
            $reponse = $db->query('SELECT LAST_INSERT_ID()');
            $postId = $reponse->fetch()[0];
            // need to get infos about the post
            $fileName = MyApp\Utility\Math::getARandomString(6) . '-' . $postId . '.png';
            $reponse->closeCursor();
            $reponse = $db->prepare("UPDATE post SET content = :fileName WHERE id = :postId");
            $reponse->execute(['fileName' => $fileName, 'postId' => $postId]);
            $reponse->closeCursor();
            
            $img = filter_input(INPUT_POST, 'image', FILTER_SANITIZE_URL);
            $img = str_replace(' ', '+', str_replace('data:image/png;base64,', '', $img));
            $data = base64_decode($img);
            file_put_contents(__DIR__ . '/public/img/post/' . $fileName, $data);
            
            header('Location: /p/'.$_POST['project_id']); exit();
        } else {
            throw new Exception ('Erreur : Vous avez oublié de remplir certains champs ("content" ou "image", "parent_id", "projectId")');
        }
    }
});

$app->get('/delete-post/{post-id}', function ($request, $response, $args) {
    if (!empty($_SESSION['current_user'])){
        try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
        } catch(Exception $e) { die('Erreur avec la base de donnée : '.$e->getMessage()); }
        $reponse = $db->prepare ("delete from post where path LIKE concat('%', (select * from (select path from post where id = :postId) p), '%')");
        $reponse->execute(['postId' => $args['post-id']]);
        $reponse->closeCursor();
    }
    header('Location: ' . (empty($_GET['redirect']) ? '/' : $_GET['redirect'])); exit();
});

$app->get('/vote/{vote-sign}/{post-id}', function ($request, $response, $args) {
    if (!empty($_SESSION['current_user'])){
        // plus, minus
        if ($args['vote-sign'] == 'minus' or $args['vote-sign'] == 'plus'){
            try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
            } catch(Exception $e) { die('Erreur avec la base de donnée : '.$e->getMessage()); }
            $firstQuery = $args['vote-sign'] == 'minus' ? 'update post set vote_minus = vote_minus + 1, score_result = score_result - 1 where id = :postId ; ' : 'update post set vote_plus = vote_plus + 1, score_result = score_result + 1 where id = :postId ; ';
            $reponse = $db->prepare ($firstQuery . "update post set score_percent = (select * from (select ((vote_plus*100)/(vote_plus + vote_minus)) from post p where id = :postId) p) where id = :postId ;");
            $reponse->execute(['postId' => $args['post-id']]);
            $reponse = $db->prepare ('select score_percent, score_result, vote_minus, vote_plus from post where id = :postId');
            $reponse->execute(['postId' => $args['post-id']]);
            while ($donnees[] = $reponse->fetch());
            $reponse->closeCursor();
            die(json_encode($donnees));
        } else {
            die('Url invalide');
        }
    }
});

$app->get('/togglePin/{post-id}', function ($request, $response, $args) {
    if (!empty($_SESSION['current_user'])){
        try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
        } catch(Exception $e) { die('Erreur avec la base de donnée : '.$e->getMessage()); }
        $reponse = $db->prepare ('UPDATE post SET user_id_pin = (IF (user_id_pin = 0,:user_id_pin,0)) where id = :postId');
        $reponse->execute([
            'user_id_pin' => $_SESSION['current_user']['user_id'],
            'postId' => $args['post-id']
        ]);
        // $reponse = $db->prepare ('select user_id_pin from post where id = :postId');
        // $reponse->execute(['postId' => $args['post-id']]);
        // while ($donnees[] = $reponse->fetch());
        // die(json_encode($donnees));
        $reponse->closeCursor();
        header('Location: ' . (empty($_GET['redirect']) ? '/' : $_GET['redirect'])); exit();
    }
});

$app->get('/resetPostScore/{post-id}', function ($request, $response, $args) {
    if (!empty($_SESSION['current_user'])){
        try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
        } catch(Exception $e) { die('Erreur avec la base de donnée : '.$e->getMessage()); }
        $reponse = $db->prepare ('update post set score_percent = 0, score_result = 0, vote_minus = 0, vote_plus = 0 where id = :postId');
        $reponse->execute(['postId' => $args['post-id']]);
        $reponse->closeCursor();
        header('Location: ' . (empty($_GET['redirect']) ? '/' : $_GET['redirect'])); exit();
    }
});

$app->get('/logout', function ($request, $response, $args) {
    session_destroy(); header('Location: ' . (empty($_GET['redirect']) ? '/' : $_GET['redirect'])); exit();
});

$app->get('/login', function ($request, $response, $args) {
    if (empty($_SESSION['current_user']['pseudo'])){
        return $this->view->render('user/login.html', ['redirect_to' => (empty($_GET['redirect']) ? '/' : $_GET['redirect'])]);
    } else {
        header('Location: /'); exit();
    }
});

$app->post('/login', function ($request, $response, $args) {
    // password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 11]);
    
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { die('Erreur avec la base de donnée : '.$e->getMessage()); }
    $reponse = $db->prepare('select * from user where user_name = :login');
    $reponse->execute(['login' => $_POST['username']]);
    while ($donnees[] = $reponse->fetch());
    array_pop($donnees);
    $reponse->closeCursor();
    
    if (empty($donnees[0]['user_name'])){
        throw new Exception('Pseudo inexistant');
    } else {
        if(password_verify($_POST['password'], $donnees[0]['user_password'])) {
            $_SESSION['current_user'] = [
                'user_id' => $donnees[0]['user_id'],
                'pseudo' => $donnees[0]['user_name'],
                'avatar' => $donnees[0]['avatar']
            ];
        } else {
            throw new Exception('Erreur avec le mot de passe');
        }
    }
    
    header('Location: ' . (empty($_GET['redirect']) ? '/' : $_GET['redirect'])); exit();
});
// Run app
$app->run();