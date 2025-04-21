<?php
class Conexao {
    public static function getConnection() {
        $host = 'localhost';
        $db = 'sistema_certificados';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            return new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            echo 'Erro de conexão: ' . $e->getMessage();
            exit;
        }
    }
}
