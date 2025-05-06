<?php
// Definições da conexão com o banco de dados
$host = 'localhost';
$db   = 'sistema_certificados';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Constantes globais do sistema
define('NOME_ORGAO', 'Fundação Wall Ferraz');
define('BASE_URL', 'http://localhost/certificado/'); // ajuste conforme seu ambiente real

// DSN e opções PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // mais seguro com prepared statements reais
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo '<h2>Erro de conexão com o banco de dados</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}
