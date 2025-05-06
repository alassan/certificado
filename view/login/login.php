<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    if (!isset($_GET['page']) || $_GET['page'] !== 'dashboard/painel') {
        header("Location: /certificado/index.php?page=dashboard/painel");
        exit;
    }
}

require_once __DIR__ . '/../../config/conexao.php';

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
        
        // Redirecionamento corrigido - caminho relativo
        header("Location: /certificado/index.php?page=dashboard/painel");
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
  <title>Login - Sistema de Gestão de Cursos da Fundação Wall Ferraz</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root {
      --primary: #0d6efd;
      --primary-dark: #0b5ed7;
      --secondary: #6c757d;
    }
    
    body {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    
    .login-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    
    .login-card:hover {
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }
    
    .input-field {
      transition: all 0.3s ease;
      border: 1px solid #dee2e6;
    }
    
    .input-field:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
    }
    
    .logo-container {
      background: white;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
    }
    
    .logo-container:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
    }
  </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4">
  <!-- Container Principal -->
  <div class="w-full max-w-md mx-auto">
    <!-- Logo -->
    <div class="logo-container rounded-xl p-4 mb-6 text-center">
      <img src="/certificado/assets/img/logo_fwf.png" alt="Fundação Wall Ferraz" class="h-16 mx-auto mb-2">
      <h1 class="text-lg font-semibold text-gray-700">Sistema de Gestão de Cursos</h1>
      <p class="text-sm text-gray-500 mt-1">Prefeitura Municipal de Teresina/PI</p>
    </div>

    <!-- Card de Login -->
    <div class="login-card rounded-xl p-8">
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="bi bi-shield-lock text-2xl text-blue-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Acesso Restrito</h2>
        <p class="text-gray-500 mt-2">Informe suas credenciais para continuar</p>
      </div>

      <form method="POST" autocomplete="off" novalidate class="space-y-5">
        <!-- Campo CPF -->
        <div>
          <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
            <i class="bi bi-person-badge mr-2 text-blue-500"></i>
            CPF
          </label>
          <input type="text" name="cpf" id="cpf"
                class="input-field w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="000.000.000-00" required autofocus>
        </div>

        <!-- Campo Senha -->
        <div>
          <label for="senha" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
            <i class="bi bi-key-fill mr-2 text-blue-500"></i>
            Senha
          </label>
          <input type="password" name="senha" id="senha"
                class="input-field w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Digite sua senha" required>
          <div class="mt-2 text-right">
            <a href="#" class="text-sm text-blue-600 hover:underline">Esqueceu a senha?</a>
          </div>
        </div>

        <!-- Botão de Login -->
        <button type="submit"
                class="btn-primary w-full text-white font-semibold py-3 px-4 rounded-lg">
          <i class="bi bi-box-arrow-in-right mr-2"></i> Entrar
        </button>
      </form>

      <!-- Links Adicionais -->
      <div class="mt-6 pt-6 border-t border-gray-100 text-center">
        <p class="text-sm text-gray-600">
  Não possui uma conta? 
  <a href="index.php?page=cadastro/cadastro" class="text-blue-600 font-medium hover:underline">Cadastre-se aqui</a>
</p>


        <div class="mt-4">
          <a href="https://fwf.pmt.pi.gov.br/" class="text-sm text-gray-500 hover:text-gray-700 hover:underline flex items-center justify-center">
            <i class="bi bi-arrow-left mr-1"></i> Voltar para o site principal
          </a>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($erro)): ?>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Falha no Login',
        html: `<div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                  <i class="bi bi-exclamation-triangle-fill text-2xl text-red-500"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-800"><?= addslashes($erro) ?></h3>
              </div>`,
        confirmButtonColor: '#0d6efd',
        confirmButtonText: 'Tentar novamente',
        customClass: {
          popup: 'rounded-xl'
        }
      });
    </script>
  <?php endif; ?>

  <script>
  document.addEventListener("DOMContentLoaded", function () {
    // Formatação do CPF
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
    
    // Efeito de foco nos campos
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
      input.addEventListener('focus', () => {
        input.parentElement.querySelector('label').classList.add('text-blue-600');
      });
      input.addEventListener('blur', () => {
        input.parentElement.querySelector('label').classList.remove('text-blue-600');
      });
    });
  });
  </script>
</body>
</html>