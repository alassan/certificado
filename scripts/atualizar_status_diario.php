<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/FichaInscricao.php';

$ficha = new FichaInscricao($conn);
$ficha->atualizarStatusAutomaticamente();

file_put_contents(__DIR__ . '/status_log.txt', "Status atualizado: " . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
