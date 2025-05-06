<?php
// curso_excluir.php
session_start();
require_once __DIR__ . '/../../config/conexao.php';

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['Admin', 'Funcionário'])) {
    header("Location: index.php?page=login/login");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id && is_numeric($id)) {
    $stmt = $conn->prepare("SELECT id FROM cursos WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->fetch()) {
        $delete = $conn->prepare("DELETE FROM cursos WHERE id = ?");
        $delete->execute([$id]);

        header("Location: index.php?page=curso/curso_listar&msg=excluido");
        exit;
    }
}

header("Location: index.php?page=curso/curso_listar&msg=erro");
exit;
