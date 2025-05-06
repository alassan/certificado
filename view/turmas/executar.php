<?php
require_once dirname(__DIR__, 2) . '/config/conexao.php';
require_once dirname(__DIR__, 2) . '/models/Turma.php';

session_start();

$turmaModel = new Turma($conn);
$totalCorrigidas = $turmaModel->corrigirTodasVagas();

$_SESSION['mensagem_sucesso'] = "$totalCorrigidas turmas atualizadas com sucesso!";
header('Location: listar.php');
exit;
