<?php
// Mostra erros no navegador (temporário para debug)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/conexao.php';
session_start();

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['admin', 'funcionario', 'Admin', 'Funcionário'])) {
    header("Location: /certificado/public/index.php");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $conn->prepare("DELETE FROM professores WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        echo "Erro ao excluir: " . $e->getMessage();
        exit;
    }
}

// Redireciona para listagem
header("Location: /certificado/public/index.php?page=professor/professor_listar");
exit;
?>
