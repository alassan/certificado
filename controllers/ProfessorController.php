<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/Professor.php';
session_start();

// Verificar se o usuário está autenticado e autorizado
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['Admin', 'Funcionário'])) {
    header("Location: ../index.php");
    exit;
}

$professorModel = new Professor($conn);
$acao = $_GET['acao'] ?? '';

if ($acao === 'cadastrar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    if ($professorModel->verificarDuplicado($nome)) {
        $_SESSION['msg_erro'] = 'Já existe um professor com esse nome!';
        header("Location: ../view/professor/professor_cadastrar.php");
        exit;
    }

    if ($professorModel->cadastrar($nome, $email, $telefone)) {
        $_SESSION['msg_successo'] = 'Professor cadastrado com sucesso!';
        header("Location: ../view/professor/professor_listar.php");
        exit;
    } else {
        $_SESSION['msg_erro'] = 'Erro ao cadastrar professor.';
        header("Location: ../view/professor/professor_cadastrar.php");
        exit;
    }
}

if ($acao === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    if ($professorModel->atualizar($id, $nome, $email, $telefone)) {
        $_SESSION['msg_successo'] = 'Professor atualizado com sucesso!';
    } else {
        $_SESSION['msg_erro'] = 'Erro ao atualizar professor.';
    }
    header("Location: ../view/professor/professor_listar.php");
    exit;
}

if ($acao === 'excluir') {
    $id = (int)($_GET['id'] ?? 0);

    if ($professorModel->remover($id)) {
        $_SESSION['msg_successo'] = 'Professor removido com sucesso!';
    } else {
        $_SESSION['msg_erro'] = 'Erro ao remover professor.';
    }
    header("Location: ../view/professor/professor_listar.php");
    exit;
}
