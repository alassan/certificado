<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$nome = $_SESSION['usuario_nome'];
$nivel = $_SESSION['usuario_nivel'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Perfil do Usuário</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', sans-serif;
    }
    .perfil-container {
      max-width: 500px;
      margin: 5% auto;
      background: #fff;
      padding: 2rem 2.5rem;
      border-radius: 1rem;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }
    .perfil-container h4 {
      font-weight: 600;
    }
  </style>
</head>
<body>

<div class="perfil-container">
  <h4 class="text-center mb-4"><i class="bi bi-person-circle me-2 text-primary"></i> Meu Perfil</h4>
  <p><strong>Nome:</strong> <?= htmlspecialchars($nome) ?></p>
  <p><strong>Nível de Acesso:</strong> <?= ucfirst(htmlspecialchars($nivel)) ?></p>

  <div class="text-center mt-4">
    <a href="index.php?page=dashboard/painel" class="btn btn-outline-primary">
      <i class="bi bi-arrow-left-circle me-1"></i> Voltar ao Painel
    </a>
  </div>
</div>

</body>
</html>
