<?php

namespace MyApp\Utility;

class Db {
    public static function getPDO ($length = 18, $keyspace = ''){
		$db_name = ($_SERVER["SERVER_NAME"] == 'tifod.com') ? 'tifod' : 'beta_tifod';
        $infos = [
			'connect' => 'mysql:host=localhost;dbname=' . $db_name . ';charset=utf8mb4',
			'user' => 'root',
			'password' => ''
		];
		
		try {
            $db = new \PDO ($infos['connect'],$infos['user'],$infos['password']);
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		} catch(Exception $e) { throw $e; }
		return $db;
    }
}