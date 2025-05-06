<?php
require_once __DIR__ . '/../../conexao.php';
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'Aluno') {
    header("Location: ../login/login.php");
    exit;
}

$ficha_id = $_GET['id'] ?? null;

if (!$ficha_id || !is_numeric($ficha_id)) {
    echo "<div class='alert alert-danger text-center mt-4'>ID de inscrição inválido.</div>";
    exit;
}

// Verifica se a ficha pertence ao aluno logado
$stmt = $conn->prepare("SELECT nome_aluno, curso_id FROM fichas_inscricao WHERE id = ? AND usuario_id = ?");
$stmt->execute([$ficha_id, $_SESSION['usuario_id']]);
$ficha = $stmt->fetch();

if (!$ficha) {
    echo "<div class='alert alert-danger text-center mt-4'>Ficha não encontrada ou você não tem permissão.</div>";
    exit;
}

// Se confirmação via POST, exclui
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("DELETE FROM fichas_inscricao WHERE id = ?");
    $stmt->execute([$ficha_id]);

    header("Location: listar_fichas.php?msg=excluido");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Remover Inscrição</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card border-danger">
        <div class="card-header bg-danger text-white">
            Confirmar Remoção
        </div>
        <div class="card-body">
            <h5 class="card-title">Deseja realmente excluir esta inscrição?</h5>
            <p class="card-text">
                <strong>Aluno:</strong> <?= htmlspecialchars($ficha['nome_aluno']) ?><br>
                <strong>ID da Ficha:</strong> <?= $ficha_id ?>
            </p>

            <form method="POST" class="d-flex gap-2">
                <button type="submit" class="btn btn-danger">Sim, excluir</button>
                <a href="listar_fichas.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
