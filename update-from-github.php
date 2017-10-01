<?php
if (isset($_POST['payload'])){
    $db = MyApp\Utility\Db::getPDO();
    $reponse = $db->query ('SELECT data_value FROM platform_data WHERE data_name = github_key');
    $github_key = $reponse->fetch()['data_value'];
    $reponse->closeCursor();
    
    $header = getallheaders();
    if (isset($header['X-Hub-Signature']) && $header['X-Hub-Signature'] === ('sha1=' . sha1($github_key)) ) {
        require_once __DIR__ . '/vendor/autoload.php';
        session_start();
        date_default_timezone_set('Europe/Paris');

        $result = [];
        $output = '';
        exec("git pull", $result);
        foreach ($result as $line) $output .= $line."\n";
        exec("rm -rf " . __DIR__ . "/src/templates/twig_cache/*");
        exec("touch " . __DIR__ . "/src/templates/twig_cache/.gitkeep");
        exec("php composer.phar update -o");
        $version = ($_SERVER["SERVER_NAME"] != 'tifod.com') ? json_decode($_POST['payload'])->after : json_decode($_POST['payload'])->release->tag_name;
        $db = MyApp\Utility\Db::getPDO();
        $reponse = $db->prepare ('UPDATE platform_data SET data_value = :version WHERE data_name =  version');
        if (!$reponse->execute([ 'version' => $version ])) echo "platform_data.version is missing in database, can't be updated!";
        $reponse->closeCursor();
        echo "<pre>" . $output . "</pre>";
    } else {
        echo "Signature not matching";
    }
} else {
    echo "Method to update not allowed";
}