<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/conexao.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar'])) {
    $nome = trim($_POST['nome'] ?? '');
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

    // Validações
    if (empty($nome)) {
        $erro = "Por favor, informe seu nome completo.";
    } elseif (!validarCPF($cpf)) {
        $erro = "CPF inválido. Por favor, verifique o número digitado.";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter no mínimo 6 caracteres.";
    } elseif ($senha !== $confirmar) {
        $erro = "As senhas não coincidem. Por favor, digite novamente.";
    } else {
        // Verifica se CPF já existe
        $verificar = $conn->prepare("SELECT id FROM usuarios WHERE cpf = ?");
        $verificar->execute([$cpf]);
        if ($verificar->rowCount() > 0) {
            $erro = "Este CPF já está cadastrado em nosso sistema.";
        } else {
            // Cadastra o usuário
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, cpf, senha, nivel_acesso) VALUES (?, ?, ?, 'Aluno')");
            if ($stmt->execute([$nome, $cpf, $hash])) {
                $_SESSION['cadastro_sucesso'] = true;
                header("Location: ../login/login.php");
                exit;
            } else {
                $erro = "Ocorreu um erro ao cadastrar. Por favor, tente novamente.";
            }
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
    
    .register-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    
    .register-card:hover {
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
    
    .password-strength {
      height: 4px;
      transition: all 0.3s ease;
    }
    
    .password-container:focus-within .password-strength {
      height: 6px;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4">
  <!-- Container Principal -->
  <div class="w-full max-w-md mx-auto">
    <!-- Logo -->
    <div class="bg-white rounded-xl p-4 mb-6 text-center shadow-md transition hover:shadow-lg">
      <img src="/certificado/assets/img/logo_fwf.png" alt="Fundação Wall Ferraz" class="h-16 mx-auto mb-2">
      <h1 class="text-lg font-semibold text-gray-700">Sistema de Gestão de Cursos</h1>
      <p class="text-sm text-gray-500 mt-1">Prefeitura Municipal de Teresina/PI</p>
    </div>

    <!-- Card de Cadastro -->
    <div class="register-card rounded-xl p-8">
      <div class="text-center mb-6">
        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="bi bi-person-plus text-2xl text-blue-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Crie sua conta</h2>
        <p class="text-gray-500 mt-2">Preencha os dados abaixo para se cadastrar</p>
      </div>

      <form method="POST" autocomplete="off" novalidate class="space-y-5">
        <input type="hidden" name="registrar" value="1">

        <!-- Campo Nome -->
        <div>
          <label for="nome" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
            <i class="bi bi-person-fill mr-2 text-blue-500"></i>
            Nome Completo
          </label>
          <input type="text" name="nome" id="nome" required
                class="input-field w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Digite seu nome completo">
        </div>

        <!-- Campo CPF -->
        <div>
          <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
            <i class="bi bi-person-badge mr-2 text-blue-500"></i>
            CPF
          </label>
          <input type="text" name="cpf" id="cpf" required
                class="input-field w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="000.000.000-00">
        </div>

        <!-- Campo Senha -->
        <div class="password-container">
          <label for="senha" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
            <i class="bi bi-lock-fill mr-2 text-blue-500"></i>
            Senha (mínimo 6 caracteres)
          </label>
          <input type="password" name="senha" id="senha" required minlength="6"
                class="input-field w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Crie uma senha segura">
          <div class="password-strength mt-1 bg-gray-200 rounded-full overflow-hidden">
            <div id="password-strength-bar" class="h-full bg-transparent rounded-full transition-all duration-300"></div>
          </div>
        </div>

        <!-- Campo Confirmar Senha -->
        <div>
          <label for="confirmar" class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
            <i class="bi bi-lock-fill mr-2 text-blue-500"></i>
            Confirmar Senha
          </label>
          <input type="password" name="confirmar" id="confirmar" required minlength="6"
                class="input-field w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Digite a senha novamente">
        </div>

        <!-- Termos e Condições -->
        <div class="flex items-start">
          <div class="flex items-center h-5">
            <input id="termos" name="termos" type="checkbox" required
                  class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
          </div>
          <div class="ml-3 text-sm">
            <label for="termos" class="font-medium text-gray-700">
              Concordo com os <a href="#" class="text-blue-600 hover:underline">Termos de Serviço</a> e 
              <a href="#" class="text-blue-600 hover:underline">Política de Privacidade</a>
            </label>
          </div>
        </div>

        <!-- Botão de Cadastro -->
        <button type="submit"
                class="btn-primary w-full text-white font-semibold py-3 px-4 rounded-lg">
          <i class="bi bi-person-plus-fill mr-2"></i> Criar Conta
        </button>
      </form>

      <!-- Link para Login -->
      <div class="mt-6 pt-6 border-t border-gray-100 text-center">
        <p class="text-muted text-center mt-4">
    Já possui uma conta?
    <a href="login/login.php" class="text-primary fw-semibold text-decoration-none">Faça login aqui</a>
</p>

      </div>
    </div>
  </div>

  <?php if (!empty($erro)): ?>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Erro no Cadastro',
        html: `<div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                  <i class="bi bi-exclamation-triangle-fill text-2xl text-red-500"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-800 mb-2"><?= addslashes($erro) ?></h3>
                <p class="text-sm text-gray-600">Por favor, verifique os dados e tente novamente.</p>
              </div>`,
        confirmButtonColor: '#0d6efd',
        confirmButtonText: 'Entendido',
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
    
    // Validação de força da senha
    const senhaInput = document.getElementById("senha");
    const strengthBar = document.getElementById("password-strength-bar");
    
    if (senhaInput && strengthBar) {
      senhaInput.addEventListener("input", function() {
        const strength = calculatePasswordStrength(this.value);
        updateStrengthBar(strength);
      });
    }
    
    function calculatePasswordStrength(password) {
      let strength = 0;
      
      // Contém letras minúsculas e maiúsculas
      if (password.match(/[a-z]/)) strength += 1;
      if (password.match(/[A-Z]/)) strength += 1;
      
      // Contém números
      if (password.match(/[0-9]/)) strength += 1;
      
      // Contém caracteres especiais
      if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
      
      // Tamanho da senha
      if (password.length > 7) strength += 1;
      if (password.length > 10) strength += 1;
      
      return Math.min(strength, 5); // Máximo de 5
    }
    
    function updateStrengthBar(strength) {
      const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
      const width = (strength / 5) * 100;
      
      // Remove todas as classes de cor primeiro
      strengthBar.classList.remove('bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500');
      
      // Adiciona a classe de cor apropriada
      if (strength > 0) {
        strengthBar.classList.add(colors[strength - 1]);
      }
      
      strengthBar.style.width = `${width}%`;
    }
    
    // Foco nos campos
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