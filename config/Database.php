<?php

namespace config;
use PDO;
use PDOException;

class Database
{
    public static function connect(): PDO
    {
        $config = include './config/config.php';

        $host = $config['host'];
        $port = $config['port'];
        $dbname = $config['dbname'];
        $username = $config['username'];
        $password = $config['password'];

        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;";
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("error while connecting database: " . $e->getMessage());
        }
    }
}