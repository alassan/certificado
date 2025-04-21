<?php
require_once __DIR__ . '/../../conexao.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar'])) {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($senha !== $confirmar) {
        $erro = "As senhas não coincidem.";
    } else {
        $verificar = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $verificar->execute([$email]);
        if ($verificar->rowCount() > 0) {
            $erro = "E-mail já cadastrado.";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $email, $hash]);

            // Redirecionamento corrigido para o caminho completo
            header("Location: http://localhost/certificado/view/login/login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f1f5f9;
      font-family: 'Segoe UI', sans-serif;
    }
    .register-box {
      max-width: 800px;
      margin: 50px auto;
      background: #fff;
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }
    .btn-green {
      background-color: #198754;
      color: #fff;
    }
    .btn-green:hover {
      background-color: #157347;
    }
    .toggle-password {
      cursor: pointer;
      position: absolute;
      right: 15px;
      top: 38px;
      color: #888;
    }
    .position-relative {
      position: relative;
    }
  </style>
</head>
<body>

<div class="register-box">
  <h3 class="text-center mb-4">📝 Criar Conta</h3>

  <?php if (!empty($erro)): ?>
    <div class="alert alert-danger text-center"><?= htmlspecialchars($erro) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="nome" class="form-label">Nome completo</label>
        <input type="text" name="nome" id="nome" class="form-control" placeholder="Seu nome" required>
      </div>
      <div class="col-md-6 mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input type="email" name="email" id="email" class="form-control" placeholder="seu@email.com" required>
      </div>
      <div class="col-md-6 mb-3 position-relative">
        <label for="senha" class="form-label">Senha</label>
        <input type="password" name="senha" id="senha" class="form-control" placeholder="Senha" required>
        <span class="toggle-password" onclick="togglePassword('senha', this)">👁️</span>
      </div>
      <div class="col-md-6 mb-3 position-relative">
        <label for="confirmar" class="form-label">Confirmar Senha</label>
        <input type="password" name="confirmar" id="confirmar" class="form-control" placeholder="Confirme a senha" required>
        <span class="toggle-password" onclick="togglePassword('confirmar', this)">👁️</span>
      </div>
    </div>
    <button type="submit" name="registrar" class="btn btn-green w-100">Cadastrar</button>
  </form>

  <p class="text-center mt-3">
    Já tem conta? <a href="http://localhost/certificado/view/login/login.php" class="text-decoration-none">Entrar</a>
  </p>
  <p class="text-center">
    <a href="../index.php" class="text-muted small">← Voltar para página inicial</a>
  </p>
</div>

<script>
function togglePassword(fieldId, el) {
  const field = document.getElementById(fieldId);
  if (field.type === "password") {
    field.type = "text";
    el.textContent = "🙈";
  } else {
    field.type = "password";
    el.textContent = "👁️";
  }
}
</script>

</body>
</html>