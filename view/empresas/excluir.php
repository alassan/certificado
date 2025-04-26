<?php
require_once __DIR__ . '/../../controllers/EmpresaController.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $controller = new EmpresaController($conn);
    $controller->excluir($id);
    header("Location: empresa_listar.php");
    exit;
}
?>
