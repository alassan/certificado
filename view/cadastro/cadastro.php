<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../conexao.php';
session_start();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar'])) {
    $nome = $_POST['nome'] ?? '';
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    function validarCPF($cpf) {
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) $d += $cpf[$c] * (($t + 1) - $c);
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }

    if (!validarCPF($cpf)) {
        $erro = "CPF inválido.";
    } elseif ($senha !== $confirmar) {
        $erro = "As senhas não coincidem.";
    } else {
        $verificar = $conn->prepare("SELECT id FROM usuarios WHERE cpf = ?");
        $verificar->execute([$cpf]);
        if ($verificar->rowCount() > 0) {
            $erro = "CPF já cadastrado.";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, cpf, senha) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $cpf, $hash]);
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
  <title>Cadastro - Sistema de Certificados</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen px-4">
  <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold text-center text-[#003c8f] mb-6">📝 Cadastro de Usuário</h2>

    <form method="POST" class="space-y-4" autocomplete="off">
      <input type="hidden" name="registrar" value="1">

      <div>
        <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
        <input type="text" name="nome" id="nome" required
               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#003c8f]">
      </div>

      <div>
        <label for="cpf" class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
        <input type="text" name="cpf" id="cpf" required
               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#003c8f]">
      </div>

      <div>
        <label for="senha" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
        <input type="password" name="senha" id="senha" required
               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#003c8f]">
      </div>

      <div>
        <label for="confirmar" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha</label>
        <input type="password" name="confirmar" id="confirmar" required
               class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#003c8f]">
      </div>

      <button type="submit"
              class="w-full bg-[#003c8f] hover:bg-[#002c6f] text-white font-semibold py-2 px-4 rounded transition">
        Cadastrar
      </button>
    </form>

    <div class="text-center mt-4 text-sm text-gray-600">
      Já tem conta? <a href="../login/login.php" class="text-[#003c8f] hover:underline font-medium">Entrar</a>
    </div>
  </div>

  <?php if (!empty($erro)): ?>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Erro no Cadastro',
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
