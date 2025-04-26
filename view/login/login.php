<?php
require_once __DIR__ . '/../../conexao.php';
session_start();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE cpf = ? LIMIT 1");
    $stmt->execute([$cpf]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id']    = $usuario['id'];
        $_SESSION['usuario_nome']  = $usuario['nome'];
        $_SESSION['usuario_cpf']   = $usuario['cpf'];
        $_SESSION['usuario_nivel'] = $usuario['nivel_acesso'];
        header("Location: ../dashboard/painel.php");
        exit;
    } else {
        $erro = "CPF ou senha inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login - Sistema de Certificados</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen px-4">

  <!-- Bloco da Logo Centralizada -->
  <div class="mb-6 bg-white p-3 rounded shadow border border-blue-100 text-center">
    <img src="/certificado/assets/img/logo_fwf.png"
         alt="Logo Fundação Wall Ferraz e Prefeitura de Teresina"
         class="mx-auto max-h-20 object-contain mb-1">
    <p class="text-sm text-gray-500">Prefeitura Municipal de Teresina/PI</p>
  </div>

  <!-- Card de Login -->
  <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold text-center text-[#003c8f] mb-6">🔐 Acesso ao Sistema</h2>

    <form method="POST" autocomplete="off" novalidate class="space-y-4">
      <div>
        <label for="cpf" class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
        <input type="text" name="cpf" id="cpf"
               class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-[#003c8f]"
               placeholder="Digite seu CPF" required autofocus>
      </div>

      <div>
        <label for="senha" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
        <input type="password" name="senha" id="senha"
               class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-[#003c8f]"
               placeholder="Digite sua senha" required>
      </div>

      <button type="submit"
              class="w-full bg-[#003c8f] hover:bg-[#002c6f] text-white font-semibold py-2 px-4 rounded transition">
        Entrar
      </button>
    </form>

    <div class="text-center mt-6 text-sm text-gray-600">
      Ainda não tem conta? 
      <a href="../cadastro/cadastro.php" class="text-[#003c8f] hover:underline font-medium">Cadastrar-se</a>
    </div>

    <div class="mt-3 text-center">
      <a href="https://fwf.pmt.pi.gov.br/" class="text-gray-400 text-sm hover:underline">← Voltar para página inicial</a>
    </div>
  </div>

  <?php if (!empty($erro)): ?>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Erro no Login',
        text: '<?= addslashes($erro) ?>',
        confirmButtonColor: '#003c8f',
        confirmButtonText: 'OK'
      });
    </script>
  <?php endif; ?>

  <script>
  document.addEventListener("DOMContentLoaded", function () {
    const cpfInput = document.getElementById("cpf");
    if (cpfInput) {
      cpfInput.addEventListener("input", function (e) {
        let value = e.target.value.replace(/\D/g, "");
        if (value.length > 11) value = value.slice(0, 11);
        value = value.replace(/(\d{3})(\d)/, "$1.$2");
        value = value.replace(/(\d{3})(\d)/, "$1.$2");
        value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        e.target.value = value;
      });
    }
  });
  </script>
</body>
</html>
