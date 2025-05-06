<?php

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php?page=login/login");
    exit;
}

require_once __DIR__ . '/../../config/conexao.php';

$id = $_GET['id'] ?? null;
$stmt = $conn->prepare("SELECT * FROM professores WHERE id = ?");
$stmt->execute([$id]);
$professor = $stmt->fetch(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $stmt = $conn->prepare("UPDATE professores SET nome = ?, email = ?, telefone = ? WHERE id = ?");
    $stmt->execute([$nome, $email, $telefone, $id]);
    header("Location: index.php?page=professor/professor_listar");

    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8"><title>Editar Professor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light"><div class="container mt-5"><div class="card p-4">
  <h4 class="mb-3">Editar Professor</h4>
  <form method="POST" action="index.php?page=professor/professor_editar&id=<?= $professor['id'] ?>">
    <input type="text" name="nome" value="<?= htmlspecialchars($professor['nome']) ?>" class="form-control mb-3" required>
    <input type="email" name="email" value="<?= htmlspecialchars($professor['email']) ?>" class="form-control mb-3" required>
    <input type="text" name="telefone" value="<?= htmlspecialchars($professor['telefone']) ?>" class="form-control mb-3" required>
    <button type="submit" class="btn btn-primary">Atualizar</button>
    <a href="index.php?page=professor/professor_listar" class="btn btn-secondary">Cancelar</a>
</form>

</div></div></body>
</html>
