<?php
require_once __DIR__ . '/vendor/autoload.php';
session_start();
date_default_timezone_set('Europe/Paris');

echo __LINE__ . ' ';
if (isset($_POST['payload']) or true){
    echo __LINE__ . ' ';
    $db = MyApp\Utility\Db::getPDO();
    try {
        $reponse = $db->query ('SELECT data_value FROM platform_data WHERE data_name = "github_key"');
        $github_key = $reponse->fetch()['data_value'];
        $reponse->closeCursor();
    } catch (Throwable $e){
        die ("platform_data.github_key is missing from the database, platform can't be updated");
    }
    
    echo __LINE__ . ' ';
    $header = getallheaders();
    $hash = sha1($github_key);
    echo __LINE__ . ' ';
    if (isset($header['X-Hub-Signature']) && $header['X-Hub-Signature'] === ('sha1=' . $hash)) {
        echo __LINE__ . ' ';
        
        $result = [];
        $output = '';
        exec("git pull", $result);
        foreach ($result as $line) $output .= $line."\n";
        exec("rm -rf " . __DIR__ . "/src/templates/twig_cache/*");
        exec("touch " . __DIR__ . "/src/templates/twig_cache/.gitkeep");
        exec("php composer.phar update -o");
        $version = ($_SERVER["SERVER_NAME"] != 'tifod.com') ? json_decode($_POST['payload'])->after : json_decode($_POST['payload'])->release->tag_name;
        $reponse = $db->prepare ('UPDATE platform_data SET data_value = :version WHERE data_name =  "version"');
        try {
            $reponse->execute([ 'version' => $version ]);
            $reponse->closeCursor();
        } catch (Throwable $e){
            die ("platform_data.version is missing in database, can't be updated!");
        }
        echo "<pre>" . $output . "</pre>";
    } else {
        echo __LINE__ . ' ';
        echo "Signature not matching";
    }
} else {
    echo "Method to update not allowed";
}