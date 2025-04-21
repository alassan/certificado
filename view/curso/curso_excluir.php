<?php
require_once __DIR__ . '/../../conexao.php';
session_start();

// Verifica se o usuário tem permissão
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['admin', 'funcionario'])) {
    header("Location: ../index.php");
    exit;
}

// Verifica se o ID foi enviado
$id = $_GET['id'] ?? null;

if ($id && is_numeric($id)) {
    // Verifica se o curso existe
    $stmt = $conn->prepare("SELECT id FROM cursos WHERE id = ?");
    $stmt->execute([$id]);
    $curso = $stmt->fetch();

    if ($curso) {
        // Tenta excluir o curso
        $stmt = $conn->prepare("DELETE FROM cursos WHERE id = ?");
        $stmt->execute([$id]);

        // Redireciona com mensagem de sucesso
        header("Location: curso_listar.php?msg=excluido");
        exit;
    }
}

// Redireciona mesmo se o curso não for encontrado
header("Location: curso_listar.php?msg=erro");
exit;
