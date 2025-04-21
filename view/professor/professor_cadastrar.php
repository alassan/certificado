<?php
require_once __DIR__ . '/../../conexao.php';
session_start();
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['Admin', 'Funcionário'])) {
    header("Location: ../index.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $stmt = $conn->prepare("INSERT INTO professores (nome, email, telefone) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $email, $telefone]);
    header("Location: professor_listar.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8"><title>Novo Professor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light"><div class="container mt-5"><div class="card p-4">
  <h4 class="mb-3">Cadastrar Professor</h4>
  <form method="POST">
    <input type="text" name="nome" class="form-control mb-3" placeholder="Nome completo" required>
    <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
    <input type="text" name="telefone" class="form-control mb-3" placeholder="Telefone" required>
    <button type="submit" class="btn btn-success">Salvar</button>
    <a href="professor_listar.php" class="btn btn-secondary">Voltar</a>
  </form>
</div></div></body>
</html>
