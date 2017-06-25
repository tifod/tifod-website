<?php
require_once __DIR__ . '/vendor/autoload.php';

session_start();

date_default_timezone_set('Europe/Paris');

// Create and configure Slim app
$app = new \Slim\App(['settings' => [ 'addContentLengthHeader' => false, "displayErrorDetails" => true]]);

// Get container
$container = $app->getContainer();

$container['dbinfos'] = [
    'connect' => 'mysql:host=localhost;dbname=tifod;charset=utf8',
    'user' => 'root',
    'password' => ''
];

function user_can_do ($action_name, $project_type) {
    $permissions = [
        'platform' => [
            'create_project' => ['anyone', 'creator', 'moderator']
        ],
        'open_public' => [
            'add_post' => ['anyone', 'creator', 'moderator'],
            'view_project' => ['visitor', 'anyone', 'creator', 'moderator'],
            'vote_post' => ['anyone', 'creator', 'moderator'],
            'reset_score_post' => [ null ],
            'delete_project' => ['creator'],
            'delete_post' => ['creator'],
            'pin_post' => ['creator', 'moderator'],
            'edit_post' => ['creator', 'moderator'],
        ],
        'closed_public' => [
            'add_post' => ['creator', 'moderator'],
            'view_project' => ['visitor', 'anyone', 'creator', 'moderator'],
            'vote_post' => ['anyone', 'creator', 'moderator'],
            'reset_score_post' => ['creator'],
            'delete_project' => ['creator'],
            'delete_post' => ['creator'],
            'pin_post' => ['creator', 'moderator'],
            'edit_post' => ['creator', 'moderator'],
        ],
        'closed_private' => [
            'add_post' => ['creator', 'moderator'],
            'view_project' => ['creator', 'moderator'],
            'vote_post' => ['creator', 'moderator'],
            'reset_score_post' => ['creator'],
            'delete_project' => ['creator'],
            'delete_post' => ['creator'],
            'pin_post' => ['creator', 'moderator'],
            'edit_post' => ['creator', 'moderator'],
        ]
    ];
    $default_project_type = 'platform';
    $default_platform_role = 'visitor';
    
    $current_project_type = empty($project_type) ? $default_project_type : $project_type;
    if (empty($permissions[$current_project_type][$action_name])){
        throw new Exception("Nom d'action inconnue ($action_name, $current_project_type)");
    }
    return in_array((empty($_SESSION['current_user']['current_project_role']) ? $default_platform_role : $_SESSION['current_user']['current_project_role']), $permissions[$current_project_type][$action_name]) or (!empty($_SESSION['current_user']) and $_SESSION['current_user']['platform_role'] == 'admin');
}

// Register component on container
$container['view'] = function ($container) {
    $loader = new Twig_Loader_Filesystem(__DIR__ . '/src/templates');
    $twig = new Twig_Environment($loader, [
        // 'cache' => __DIR__ . '/templates/twig_cache'
        'cache' => false
    ]);
    
    $twig->addGlobal('current_user', (empty($_SESSION['current_user']) ? null : $_SESSION['current_user']));
    
    $twig->addGlobal("current_url", $_SERVER["REQUEST_URI"]);
    
    $twig->addGlobal("server_name", $_SERVER["SERVER_NAME"]);
    
    $twig->addGlobal("dev_mode", $container['settings']['displayErrorDetails']);
    
    $filter = new Twig_SimpleFilter('is_allowed_for', function ($action_name, $project_type) { return user_can_do($action_name, $project_type); });
    $twig->addFilter($filter);
    
    $filter = new Twig_SimpleFilter('markdown', function ($text) { return Michelf\Markdown::defaultTransform($text); });
    $twig->addFilter($filter);
        
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
            ->write($c['view']->render('error/404.html'));
    };
};
//Override the default php Error Handler
$container['phpErrorHandler'] = function ($c) {
    return function ($request, $response, $error) use ($c) {
        return $c['response']
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write($c['view']->render('error/error.html', ['title' => 'Erreur interne', 'error' => $error]));
    };
};
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $error) use ($c) {
        return $c['response']
            ->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write($c['view']->render('error/error.html', ['title' => 'Erreur interne', 'error' => $error]));
    };
};
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting, so ignore it
        return;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

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
$app->post('/update-from-github', function ($request, $response, $args) {
    $result = [];
	$output = '';
    exec("git pull", $result);
    foreach ($result as $line) $output .= $line."\n";
	return "<pre>$output</pre>";
});
$app->get('/p/{projectId}', function ($request, $response, $args) {
    $projectId = $args['projectId'];    
    
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { throw $e; }
    $reponse = $db->query ('select project_id from post where parent_id = 0');
    while ($donnees[] = $reponse->fetch());
    array_pop($donnees);
    $reponse->closeCursor();
    $projectsId = [];
    foreach ($donnees as $each_project){ $projectsId[] = $each_project['project_id']; }
    
    if (in_array($projectId,$projectsId)){
        $reponse = $db->prepare ('select project_type from project where project_id = :project_id');
        $reponse->execute([ 'project_id' => $projectId ]);
        $project_type = $reponse->fetch()['project_type'];
        
        if (!empty($_SESSION['current_user'])){
            $reponse = $db->prepare ('select project_role from project_role where project_id = :project_id and user_id = :user_id');
            $reponse->execute([
                'project_id' => $projectId,
                'user_id' => $_SESSION['current_user']['user_id']
            ]);
            $role = $reponse->fetch()['project_role'];
            $_SESSION['current_user']['current_project_role'] = empty($role) ? 'anyone' : $role;
        }
        
        if (user_can_do('view_project',$project_type)){
            $reponse = $db->query ('select *, (select user_name from user u where u.user_id = p.author_id) author_name, (select user_name from user u where u.user_id = p.user_id_pin) user_pseudo_pin, (select avatar from user u where u.user_id = p.author_id) author_avatar from post p where project_id = ' . $projectId . ' order by user_id_pin desc, score_percent desc');
            $donnees = [];
            while ($donnees[] = $reponse->fetch());
            array_pop($donnees);
            $reponse->closeCursor();

            if (count($donnees) == 1){
                return $this->view->render('post/project-player.html', ['project' => $donnees, 'project_type' => $project_type, 'projectId' => $projectId]);
            }

            // creating a comprehensive list of the projet posts
            foreach ($donnees as $k => $post){
                $posts [] = [
                    'id' => $post['id'],
                    'content' => $post['content'],
                    'content_type' => $post['content_type'],
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
                    'author_avatar' => $post['author_avatar']
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
            
            return $this->view->render('post/project-player.html', ['project' => $project, 'project_type' => $project_type, 'projectId' => $projectId, 'project_json' => $project_json]);
        } else {
            throw new Exception("Désolé, vous n'êtes pas autorisé à consulter ce projet");
        }
    } else {
		throw new Exception("Désolé, ce projet n'existe pas");
    }
});

$app->get('/', function ($request, $response) {
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { throw $e; }
    $reponse = $db->query ('select id, project_id, content from post where parent_id = 0');
    while ($donnees[] = $reponse->fetch());
    array_pop($donnees);
    $lastProjectId = count($donnees) > 0 ? $donnees[count($donnees) - 1]['project_id'] + 1 : 1;
    $reponse->closeCursor();
    return $this->view->render('homepage.html', ['projects' => $donnees, 'child' => ['id' => 0], 'projectId' => $lastProjectId]);
})->setName('homepage');

$app->post('/create-project', function ($request, $response) {
    $project_type = 'open_public';
    if (user_can_do('create_project','platform')){
        if (!(empty($_POST['content']) or empty($_POST['project_id']))){
            try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
            } catch(Exception $e) { throw $e; }
            
            $reponse = $db->prepare ("INSERT INTO post(content, content_type, parent_id, project_id, path, author_id) VALUES (:content, 'text', 0, :project_id, '/', :author_id); UPDATE post SET path = CONCAT(path,(SELECT LAST_INSERT_ID()),'/') WHERE id = (SELECT LAST_INSERT_ID()); INSERT INTO project_role (project_id, user_id, project_role) VALUES (:project_id,:author_id,'creator'); INSERT INTO project (project_id, project_type, project_root_post_id) VALUES (:project_id,:project_type,(SELECT LAST_INSERT_ID()));");
            $reponse->execute([
                'content' => $_POST['content'],
                'project_id' => $_POST['project_id'],
                'author_id' => $_SESSION['current_user']['user_id'],
                'project_type' => $project_type
            ]);
            $reponse->closeCursor();
        }
        header('Location: /p/' . $_POST['project_id']); exit();
    }
    header('Location: /login'); exit();
});

$app->post('/add-post', function ($request, $response) {
    if ((!empty($_POST['content']) or !empty($_POST['image'])) and isset($_POST['parent_id']) and isset($_POST['project_id'])){
        try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
        } catch(Exception $e) { throw $e; }
        $reponse = $db->prepare ('select project_type from project where project_id = :project_id');
        $reponse->execute(['project_id' => $_POST['project_id']]);
        $project_type = $reponse->fetch()['project_type'];
        if (user_can_do('add_post',$project_type)){
            $reponse = $db->prepare ("INSERT INTO post(content, content_type, parent_id, project_id, path, author_id) VALUES (:content, :content_type, :parent_id, :project_id, (SELECT IF (:parent_id = 0,'/',(SELECT path FROM post AS p WHERE id = :parent_id))), :author_id); UPDATE post SET path = CONCAT(path,(SELECT LAST_INSERT_ID()),'/') WHERE id = (SELECT LAST_INSERT_ID())");
            $reponse->execute([
                'content' => (empty($_POST['content'])?'file':$_POST['content']),
                'content_type' => (empty($_POST['content']) ? 'file' : 'text'),
                'parent_id' => $_POST['parent_id'],
                'project_id' => $_POST['project_id'],
                'author_id' => $_SESSION['current_user']['user_id']
            ]);
            $reponse->closeCursor();
            if (empty($_POST['content'])){
                $reponse = $db->query('SELECT LAST_INSERT_ID()');
                $post_id = $reponse->fetch()[0];
                // need to get infos about the post
                $fileName = MyApp\Utility\Math::getARandomString(6) . '-' . $post_id . '.png';
                $reponse->closeCursor();
                $reponse = $db->prepare("UPDATE post SET content = :fileName WHERE id = :post_id");
                $reponse->execute(['fileName' => $fileName, 'post_id' => $post_id]);
                $reponse->closeCursor();
                
                $img = filter_input(INPUT_POST, 'image', FILTER_SANITIZE_URL);
                $img = str_replace(' ', '+', str_replace('data:image/png;base64,', '', $img));
                $data = base64_decode($img);
                file_put_contents(__DIR__ . '/public/img/post/' . $fileName, $data);
                header('Location: /p/'.$_POST['project_id'].'#'.$post_id); exit();
            }
            header('Location: /p/'.$_POST['project_id']); exit();
        } else {
            throw new Exception ("Vous n'êtes pas autorisés à poster dans ce projet, veuillez contacter le créateur de ce projet, ou bien vous adressez à l'équipe tifod si vous pensez que c'est un bug");
        }
    } else {
        throw new Exception ('Vous avez oublié de remplir certains champs ("content" ou "image", "parent_id", "project_id")');
    }
});

$app->get('/delete-post/{post-id}', function ($request, $response, $args) {
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { throw $e; }
    $reponse = $db->prepare ('select project_type from project where project_id = (SELECT project_id FROM post WHERE id = :post_id)');
    $reponse->execute(['post_id' => $args['post-id']]);
    $project_type = $reponse->fetch()['project_type'];
    if (user_can_do('delete_post',$project_type)){
        $reponse = $db->prepare("SELECT content, content_type FROM post WHERE id = :post_id");
        $reponse->execute([ 'post_id' => $args['post-id'] ]);
        while ($donnees [] = $reponse->fetch());
        if ($donnees[0]['content_type'] == 'file' and file_exists(__DIR__ . '/public/img/post/'.$donnees[0]['content'])) unlink(__DIR__ . '/public/img/post/'.$donnees[0]['content']);
        
        $reponse = $db->prepare ("DELETE FROM post_vote WHERE post_vote.post_id IN (SELECT id FROM post WHERE path LIKE concat('%', (select * from (select path from post where id = :post_id) p), '%')); DELETE FROM post_vote WHERE post_id = :post_id; DELETE FROM post WHERE path LIKE concat('%', (select * from (select path from post where id = :post_id) p), '%')");
        $reponse->execute([ 'post_id' => $args['post-id'] ]);
        $reponse->closeCursor();
    }
    header('Location: ' . (empty($_GET['redirect']) ? '/' : $_GET['redirect'])); exit();
});

$app->get('/vote/{vote-sign}/{post-id}', function ($request, $response, $args) {
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { throw $e; }
    $reponse = $db->prepare ('select project_type from project where project_id = (SELECT project_id FROM post WHERE id = :post_id)');
    $reponse->execute(['post_id' => $args['post-id']]);
    $project_type = $reponse->fetch()['project_type'];
    if (user_can_do('vote_post', $project_type)){
        // plus, minus
        if ($args['vote-sign'] == 'minus' or $args['vote-sign'] == 'plus'){
            try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
            } catch(Exception $e) { throw $e; }
            $reponse = $db->prepare ('select is_upvote from post_vote where user_id = :user_id and post_id = :post_id');
            $reponse->execute([
                'user_id' => $_SESSION['current_user']['user_id'],
                'post_id' => $args['post-id']
            ]);
            while ($donnees[] = $reponse->fetch());
            
            if (!($donnees[0]['is_upvote'] === '1' and $args['vote-sign'] == 'plus') and !($donnees[0]['is_upvote'] === '0' and $args['vote-sign'] == 'minus')){
                if ($donnees[0] === false){
                    $t = ($args['vote-sign'] == 'minus') ? ['vote_minus', '-', '0'] : ['vote_plus', '+', '1'];
                    $query = 'update post set ' . $t[0] . ' = ' . $t[0] . ' + 1, score_result = score_result ' . $t[1] . ' 1 where id = :post_id; INSERT INTO post_vote (post_id, user_id, is_upvote) VALUES (:post_id, :user_id, ' . $t[2] . ');';
                } else if (($donnees[0]['is_upvote'] === '1' and $args['vote-sign'] == 'minus') or ($donnees[0]['is_upvote'] === '0' and $args['vote-sign'] == 'plus')) {
                    $t = ($args['vote-sign'] == 'minus') ? ['vote_plus', '-'] : ['vote_minus', '+'];
                    $query = 'delete from post_vote where post_id = :post_id and user_id = :user_id; update post set ' . $t[0] . ' = ' . $t[0] . ' - 1, score_result = score_result ' . $t[1] . ' 1 where id = :post_id;';
                }
                $reponse = $db->prepare ($query . ' update post set score_percent = (select * from (select ((vote_plus*100)/(vote_plus + vote_minus)) from post p where id = :post_id) p) where id = :post_id ;');
                $reponse->execute([
                    'post_id' => $args['post-id'],
                    'user_id' => $_SESSION['current_user']['user_id']
                ]);
            }
            
            $reponse = $db->prepare ('select score_percent, score_result, vote_minus, vote_plus from post where id = :post_id');
            $reponse->execute(['post_id' => $args['post-id']]);
            $donnees = [];
            while ($donnees[] = $reponse->fetch());
            $reponse->closeCursor();
            die(json_encode($donnees));
        } else {
            die('Url invalide');
        }
    }
});

$app->get('/togglePin/{post-id}', function ($request, $response, $args) {
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { throw $e; }
    $reponse = $db->prepare ('select project_type from project where project_id = (SELECT project_id FROM post WHERE id = :post_id)');
    $reponse->execute(['post_id' => $args['post-id']]);
    $project_type = $reponse->fetch()['project_type'];
    if (user_can_do('pin_post',$project_type)){
        $reponse = $db->prepare ('UPDATE post SET user_id_pin = (IF (user_id_pin = 0,:user_id_pin,0)) where id = :post_id');
        $reponse->execute([
            'user_id_pin' => $_SESSION['current_user']['user_id'],
            'post_id' => $args['post-id']
        ]);
        // $reponse = $db->prepare ('select user_id_pin from post where id = :post_id');
        // $reponse->execute(['post_id' => $args['post-id']]);
        // while ($donnees[] = $reponse->fetch());
        // die(json_encode($donnees));
        $reponse->closeCursor();
        header('Location: ' . (empty($_GET['redirect']) ? '/' : $_GET['redirect'].'#'.$args['post-id'])); exit();
    }
});

$app->get('/resetPostScore/{post-id}', function ($request, $response, $args) {
    if (!empty($_SESSION['current_user']) and $_SESSION['current_user']['platform_role'] == 'admin'){
        try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
        } catch(Exception $e) { throw $e; }
        $reponse = $db->prepare ('update post set score_percent = 0, score_result = 0, vote_minus = 0, vote_plus = 0 where id = :post_id; delete from post_vote where post_id = :post_id;');
        $reponse->execute(['post_id' => $args['post-id']]);
        $reponse->closeCursor();
        header('Location: ' . (empty($_GET['redirect']) ? '/' : $_GET['redirect'])); exit();
    }
});

$app->get('/logout', function ($request, $response, $args) {
    session_destroy(); header('Location: ' . (empty($_GET['redirect']) ? '/' : $_GET['redirect'])); exit();
});

$app->get('/login', function ($request, $response, $args) {
    if (empty($_SESSION['current_user'])){
        return $this->view->render('connexion/login.html', ['email' => (empty($_GET['email'])? false : $_GET['email']), 'redirect_to' => (empty($_GET['redirect']) ? '/' : $_GET['redirect'])]);
    } else {
        header('Location: /'); exit();
    }
});

$app->post('/login', function ($request, $response, $args) {
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { throw $e; }
    $reponse = $db->prepare('select * from user where email = :email');
    $reponse->execute(['email' => $_POST['email']]);
    while ($donnees[] = $reponse->fetch());
    $reponse->closeCursor();
    
    if (empty($donnees[0]['email'])){
        throw new Exception("Email incorrect");
    } else {
        if(password_verify($_POST['password'], $donnees[0]['user_password'])) {
            $_SESSION['current_user'] = [
                'user_id' => $donnees[0]['user_id'],
                'pseudo' => $donnees[0]['user_name'],
                'email' => $donnees[0]['email'],
                'avatar' => $donnees[0]['avatar'],
                'description' => $donnees[0]['description'],
                'platform_role' => $donnees[0]['platform_role']
            ];
        } else {
            throw new Exception('Mot de passe incorrect');
        }
    }
    
    header('Location: ' . (empty($_GET['redirect']) ? '/' : $_GET['redirect'])); exit();
});

$app->get('/u', function ($request, $response, $args) {
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { throw $e; }
    $reponse = $db->query("SELECT user_name, avatar, platform_role, user_id, (SELECT COUNT(id) FROM post AS p WHERE p.author_id = u.user_id) post_amount FROM user AS u ORDER BY user_id");
    while ($donnees[] = $reponse->fetch());
    array_pop($donnees);
    $reponse->closeCursor();
    return $this->view->render('user/user_list.html', ['users' => $donnees]);
});
$app->get('/u/{user_id}', function ($request, $response, $args) {
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { throw $e; }
    $reponse = $db->prepare("SELECT * FROM user WHERE user_id = :user_id");
    $reponse->execute(['user_id' => $args['user_id']]);
    while ($donnees[] = $reponse->fetch());
    $reponse = $db->prepare("SELECT * FROM post WHERE author_id = :user_id ORDER BY user_id_pin DESC, score_percent DESC");
    $reponse->execute(['user_id' => $args['user_id']]);
    while ($posts[] = $reponse->fetch());
    array_pop($posts);
    $reponse->closeCursor();
    
    if (empty($donnees[0])){
        throw new Exception("Utilisateur inconnu");
    } else {
        $user = [
            'user_id' => $args['user_id'],
            'pseudo' => $donnees[0]['user_name'],
            'avatar' => $donnees[0]['avatar'],
            'platform_role' => $donnees[0]['platform_role'],
            'description' => $donnees[0]['description'],
            'posts' => $posts
        ];
        return $this->view->render('user/profile.html', ['user' => $user]);
    }
});

$app->get('/signup', function ($request, $response, $args) {
    if (empty($_SESSION['current_user'])){
        if (!empty($_GET['email'])){
            try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
            } catch(Exception $e) { throw $e; }
            // check if user already exists
            $reponse = $db->prepare("SELECT user_id FROM user WHERE email = :email");
            $reponse->execute(['email' => $_GET['email']]);
            if ($reponse->fetch() !== false){ header('Location: /login?email='.$_GET['email']); exit(); }
            
            $token_key = md5(MyApp\Utility\Math::getARandomString().$_GET['email']);
            // insert in DB and delete all expired entries
            $reponse = $db->prepare("DELETE FROM token WHERE email = :email; INSERT INTO token (action, token_key, expiration_date, email) VALUES ('signup', :token_key, :expiration_date, :email); DELETE FROM token WHERE expiration_date < NOW();");
            $reponse->execute([
                'token_key' => $token_key,
                'expiration_date' => date("Y-m-d H:i:s",strtotime('+1 day')),
                'email' => $_GET['email']
            ]);
            $reponse->closeCursor();
            
            // send email with the token
            mail($_GET['email'], 'Valider la création de votre compte Tifod', $this->view->render('email/signup.html',['token_key' => $token_key]), "MIME-Version: 1.0\r\nContent-type: text/html; charset=utf-8\r\nFrom: Tifod <contact@tifod.com>\r\nReply-To: Tifod <contact@tifod.com>");
            
            return $this->view->render('connexion/signup.html',['token_sent' => true, 'email' => $_GET['email']]);
        } else {
            return $this->view->render('connexion/signup.html');
        }
    } else {
        header('Location: /'); exit();
    }
});

$app->get('/password_reset', function ($request, $response, $args) {
    if (empty($_SESSION['current_user'])){
        if (!empty($_GET['email'])){
            // create token
            $token_key = md5(MyApp\Utility\Math::getARandomString().$_GET['email']);
            
            try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
            } catch(Exception $e) { throw $e; }
            // check if user already exists
            $reponse = $db->prepare("SELECT user_id FROM user WHERE email = :email");
            $reponse->execute(['email' => $_GET['email']]);
            if ($reponse->fetch() === false){ header('Location: /signup?email='.$_GET['email']); exit(); }
            
            // insert token in DB and delete all expired entries
            $reponse = $db->prepare("DELETE FROM token WHERE email = :email; INSERT INTO token (action, token_key, expiration_date, email) VALUES ('password_reset', :token_key, :expiration_date, :email); DELETE FROM token WHERE expiration_date < NOW();");
            $reponse->execute([
                'token_key' => $token_key,
                'expiration_date' => date("Y-m-d H:i:s",strtotime('+1 hour')),
                'email' => $_GET['email']
            ]);
            $reponse->closeCursor();
            
            // send email with the token
            mail($_GET['email'], 'Réinitialiser votre mot de passe sur Tifod', $this->view->render('email/password_reset.html',['token_key' => $token_key]), "MIME-Version: 1.0\r\nContent-type: text/html; charset=utf-8\r\nFrom: Tifod <contact@tifod.com>\r\nReply-To: Tifod <contact@tifod.com>");
            
            return $this->view->render('connexion/password_reset.html',['token_sent' => true, 'email' => $_GET['email']]);
        } else {
            return $this->view->render('connexion/password_reset.html');
        }
    } else {
        header('Location: /'); exit();
    }
});

$app->get('/token/{token_key}', function ($request, $response, $args) {
    // delete expired token_keys, then get token_key data
    try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
    } catch(Exception $e) { throw $e; }
    $db->query('DELETE FROM token WHERE expiration_date < NOW();');
    $reponse = $db->prepare("SELECT email, action FROM token WHERE token_key = :token_key");
    $reponse->execute(['token_key' => $args['token_key']]);
    $donnees = $reponse->fetch();
    if ($donnees !== false){
        $email = $donnees['email'];
        $action = $donnees['action'];
        if ($action == 'signup'){
            // query to test
            $reponse = $db->prepare("INSERT INTO user (email) VALUES (:email); UPDATE user SET user_name = CONCAT('user-',(SELECT LAST_INSERT_ID())) WHERE email = :email;");
            $reponse->execute(['email' => $email]);
        }
        $reponse = $db->prepare("DELETE FROM token WHERE email = :email");
        $reponse->execute(['email' => $email]);
        $reponse = $db->prepare('SELECT * FROM user WHERE email = :email');
        $reponse->execute(['email' => $email]);
        $donnees = [];
        while ($donnees[] = $reponse->fetch());
        $_SESSION['current_user'] = [
            'user_id' => $donnees[0]['user_id'],
            'pseudo' => $donnees[0]['user_name'],
            'email' => $donnees[0]['email'],
            'avatar' => $donnees[0]['avatar'],
            'platform_role' => $donnees[0]['platform_role']
        ];
        $reponse->closeCursor();
        header('Location: /settings/new_password'); exit();
    } else {
        $reponse->closeCursor();
        throw new Exception ("Ce lien a expiré et n'est plus utilisable");
    }
});

$app->get('/settings', function ($request, $response, $args) {
    if (empty($_SESSION['current_user'])){
        header('Location: /login?redirect=/settings');
        exit();
    } else {
        return $this->view->render('user/settings.html');
    }
});
$app->get('/settings/new_password', function ($request, $response, $args) {
    return $this->view->render('user/new_password.html');
});
$app->post('/settings', function ($request, $response, $args) {
    if (empty($_POST['action'])){
        throw new Exception("Vous ne pouvez pas envoyer un formulaire vide");
    } else {
        if ($_POST['action'] == 'new_password'){
            $action = 'user_password';
            $new_value = password_hash($_POST['new_value'], PASSWORD_BCRYPT, ['cost' => 12]);
        } elseif ($_POST['action'] == 'new_user_name'){
            if (empty($_POST['new_value'])) throw new Exception ("Vous ne pouvez pas avoir de pseudo vide");
            $action = 'user_name';
            $new_value = $_POST['new_value'];
            $_SESSION['current_user']['pseudo'] = $new_value;
        } elseif ($_POST['action'] == 'new_avatar'){
            $action = 'avatar';
            $file_name = $_SESSION['current_user']['user_id'] . '-' . $_FILES["new_value"]["name"];
            if (!move_uploaded_file($_FILES["new_value"]["tmp_name"], __DIR__ . '/public/img/user/' . basename($file_name))) throw new Exception("Erreur d'upload du fichier!");
            
            $new_value = $file_name;
            if ($_SESSION['current_user']['avatar'] != 'default.png' and file_exists(__DIR__ . '/public/img/user/' . $_SESSION['current_user']['avatar'])) unlink(__DIR__ . '/public/img/user/' . $_SESSION['current_user']['avatar']);
            $_SESSION['current_user']['avatar'] = $file_name;
        } elseif ($_POST['action'] == 'new_description'){
            $action = 'description';
            $new_value = $_POST['new_value'];
            $_SESSION['current_user']['description'] = $new_value;
        }
        try { $db = new PDO ($this->dbinfos['connect'],$this->dbinfos['user'],$this->dbinfos['password']);
        } catch(Exception $e) { throw $e; }
        $reponse = $db->prepare("UPDATE user SET $action = :new_value WHERE user_id = :current_user_id");
        $reponse->execute([
            'new_value' => $new_value,
            'current_user_id' => $_SESSION['current_user']['user_id']
        ]);
        $reponse->closeCursor();
        header ('Location: /settings'); exit();
    }
});

$app->get('/preview_template/{template}', function ($request, $response, $args) {
    return $this->view->render('user/new_password.html');
});
// Run app
$app->run();