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
        // 'cache' => __DIR__ . '/templates/cache'
        'cache' => false
    ]);
    
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
      
      /* En anglais
      $units = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
      );

      foreach ($units as $unit => $val) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return ($val == 'second')? 'a few seconds ago' : 
               (($numberOfUnits>1) ? $numberOfUnits : 'a')
               .' '.$val.(($numberOfUnits>1) ? 's' : '').' ago';
      }
      
      */
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
        $reponse = $db->query ('select *, (select user_name from user u where u.user_id = p.author_id) author_name from post p where project_id = ' . $projectId . ' order by has_pin desc, score_percent desc');
        while ($donnees[] = $reponse->fetch());
        array_pop($donnees);
        $reponse->closeCursor();

        if (count($donnees) == 1) return $this->view->render('post/project-player.html', ['project' => $donnees, 'projectId' => $projectId]);

        // creating a comprehensive list of the projet posts
        foreach ($donnees as $k => $post){
            $posts [] = [
                'id' => $post['id'],
                'content' => $post['content'],
                'content_type' => $post['content_type'],
                'parent_id' => $post['parent_id'],
                'path' => $post['path'],
                'score_result' => $post['score_result'],
                'score_percent' => $post['score_percent'],
                'has_pin' => $post['has_pin'],
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
            $has_pin = $a['has_pin'] ? ' | <i class="fa fa-thumb-tack" aria-hidden="true"></i>' : '';
            // $new[$a['parent_id']][] = ['innerHTML' => '<p class="tree-post" data-path="' . $a['path'] . '" data-id="' . $a['id'] . '">' . htmlspecialchars($a['content']) . '</p><hr><p>' . $a['score_result'] . ' points | ' . $a['score_percent'] . '%' . $has_pin . '</p>', 'id' => $a['id']];
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
    $reponse->closeCursor();
    return $this->view->render('homepage.html', ['projects' => $donnees, 'child' => ['id' => 0], 'projectId' => ($donnees[count($donnees) - 1]['project_id'] + 1)]);
})->setName('homepage');

$app->post('/add-post', function ($request, $response) {
    if ((!empty($_POST['content']) or isset($_POST['image'])) and isset($_POST['parent_id']) and isset($_POST['project_id']) and isset($_POST['content_type'])){
        try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
        } catch(Exception $e) { die('Erreur avec la base de donnée : '.$e->getMessage()); }
        
        $content = ($_POST['content_type'] == 'file') ? 'file' : $_POST['content'] ;
        $reponse = $db->prepare ("INSERT INTO post(content, content_type, parent_id, project_id, path) VALUES (:content, :content_type, :parent_id, :project_id, (SELECT IF (:parent_id = 0,'/',(SELECT path FROM post AS p WHERE id = :parent_id)))); UPDATE post SET path = CONCAT(path,(SELECT LAST_INSERT_ID()),'/') WHERE id = (SELECT LAST_INSERT_ID())");
        $reponse->execute([
            'content' => $content,
            'content_type' => $_POST['content_type'],
            'parent_id' => $_POST['parent_id'],
            'project_id' => $_POST['project_id']
        ]);
        $reponse->closeCursor();
        if ($_POST['content_type'] == 'file'){
            $reponse = $db->query('SELECT LAST_INSERT_ID()');
            $postId = $reponse->fetch()[0];
            $fileName = MyApp\Utility\Math::getARandomString(6) . '-' . $postId . '.png';
            $reponse->closeCursor();
            $reponse = $db->prepare("UPDATE post SET content = :fileName WHERE id = :postId");
            $reponse->execute(['fileName' => $fileName, 'postId' => $postId]);
            $reponse->closeCursor();
            
            $img = filter_input(INPUT_POST, 'image', FILTER_SANITIZE_URL);
            $img = str_replace(' ', '+', str_replace('data:image/png;base64,', '', $img));
            $data = base64_decode($img);
            file_put_contents(__DIR__ . '/public/img/post/' . $fileName, $data);
        }
        header('Location: /p/'.$_POST['project_id']); exit();
    } else {
        $msg = 'Erreur : Vous avez oublié de remplir certains champs ("content" ou "image", "parent_id", "projectId", "content_type")';
        error_log($msg);
        throw new Exception ($msg);
    }
});

$app->get('/delete-post/{post-id}', function ($request, $response, $args) {
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { die('Erreur avec la base de donnée : '.$e->getMessage()); }
    $reponse = $db->prepare ("delete from post where path LIKE concat('%', (select * from (select path from post where id = :postId) p), '%')");
    $reponse->execute(['postId' => $args['post-id']]);
    $reponse->closeCursor();
    if (!empty($_GET['redirect'])){
        header('Location: ' . $_GET['redirect']); exit();
    } else {
        header('Location: /'); exit();
    }
});

$app->get('/vote/{vote-sign}/{post-id}', function ($request, $response, $args) {
    // plus, minus, reset
    if ($args['vote-sign'] == 'minus' or $args['vote-sign'] == 'plus'){
        try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
        } catch(Exception $e) { die('Erreur avec la base de donnée : '.$e->getMessage()); }
        $firstQuery = $args['vote-sign'] == 'minus' ? 'update post set vote_minus = vote_minus + 1, score_result = score_result - 1 where id = :postId ; ' : 'update post set vote_plus = vote_plus + 1, score_result = score_result + 1 where id = :postId ; ';
        $reponse = $db->prepare ($firstQuery . "update post set score_percent = (select * from (select ((vote_plus*100)/(vote_plus + vote_minus)) from post p where id = :postId) p) where id = :postId ;");
        $reponse->execute(['postId' => $args['post-id']]);
        $reponse = $db->prepare ('select score_percent, score_result from post where id = :postId');
        $reponse->execute(['postId' => $args['post-id']]);
        while ($donnees[] = $reponse->fetch());
        $reponse->closeCursor();
        die(json_encode($donnees));
    } else {
        die('Url invalide');
    }
});

$app->get('/togglePin/{post-id}', function ($request, $response, $args) {
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { die('Erreur avec la base de donnée : '.$e->getMessage()); }
    $reponse = $db->prepare ('update post set has_pin = not has_pin where id = :postId');
    $reponse->execute(['postId' => $args['post-id']]);
    $reponse = $db->prepare ('select has_pin from post where id = :postId');
    $reponse->execute(['postId' => $args['post-id']]);
    while ($donnees[] = $reponse->fetch());
    $reponse->closeCursor();
    die(json_encode($donnees));
});

$app->get('/resetPostScore/{post-id}', function ($request, $response, $args) {
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { die('Erreur avec la base de donnée : '.$e->getMessage()); }
    $reponse = $db->prepare ('update post set score_percent = 0, score_result = 0, vote_minus = 0, vote_plus = 0 where id = :postId');
    $reponse->execute(['postId' => $args['post-id']]);
    $reponse->closeCursor();
    if (!empty($_GET['redirect'])){
        header('Location: ' . $_GET['redirect']); exit();
    } else {
        header('Location: /'); exit();
    }
});

// Run app
$app->run();