<?php
require_once __DIR__ . '/../../conexao.php';
session_start();
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['admin', 'funcionario'])) {
    header("Location: ../index.php");
    exit;
}
$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $conn->prepare("DELETE FROM professores WHERE id = ?");
    $stmt->execute([$id]);
}
header("Location: professor_listar.php");
exit;
?>
