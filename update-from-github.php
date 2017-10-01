<?php
if (isset($_POST)){
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
    $reponse = $db->prepare ('INSERT INTO platform_data (data_name, data_value) VALUES("version", :version) ON DUPLICATE KEY UPDATE data_name="version", data_value=:version');
    $reponse->execute([ 'version' => $version ]);
    $reponse->closeCursor();
    return "<pre>" . $output . "</pre>";
}