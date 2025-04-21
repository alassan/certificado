<?php
require_once __DIR__ . '/../../conexao.php';
session_start();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id']    = $usuario['id'];
        $_SESSION['usuario_nome']  = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email']; // ✅ Adicionado
        $_SESSION['usuario_nivel'] = $usuario['nivel_acesso'];
        header("Location: ../dashboard/painel.php");
        exit;
    } else {
        $erro = "E-mail ou senha inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login - Sistema de Certificados</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f0f2f5;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .login-box {
      background: #ffffff;
      padding: 2.5rem;
      border-radius: 1rem;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.06);
      width: 100%;
      max-width: 400px;
    }
    .btn-green {
      background-color: #198754;
      color: #fff;
    }
    .btn-green:hover {
      background-color: #157347;
    }
  </style>
</head>
<body>

<div class="login-box">
  <h3 class="text-center mb-4">🔐 Acesso ao Sistema</h3>

  <?php if (!empty($erro)): ?>
    <div class="alert alert-danger text-center"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <form method="POST" autocomplete="off" novalidate>
    <div class="mb-3">
      <label for="email" class="form-label">E-mail</label>
      <input type="email" name="email" id="email" class="form-control" placeholder="Digite seu e-mail" required autofocus>
    </div>

    <div class="mb-3">
      <label for="senha" class="form-label">Senha</label>
      <input type="password" name="senha" id="senha" class="form-control" placeholder="Digite sua senha" required>
    </div>

    <button type="submit" class="btn btn-green w-100">Entrar</button>
  </form>

  <div class="text-center mt-4">
    <small>
      Ainda não tem conta?
      <a href="../cadastro/cadastro.php" class="text-decoration-none">Cadastrar-se</a>
    </small>
    <div class="mt-2">
      <a href="https://fwf.pmt.pi.gov.br/" class="text-muted small d-block">← Voltar para página inicial</a>
    </div>
  </div>
</div>

</body>
</html>
