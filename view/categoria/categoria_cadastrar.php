<?php
require_once __DIR__ . '/../../conexao.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST['nome']);
    if (!empty($nome)) {
        $sql = "INSERT INTO categorias (nome) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$nome]);
        header("Location: categoria_listar.php?sucesso=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastrar Categoria</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .form-container {
      max-width: 500px;
      margin: 60px auto;
      padding: 2rem;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
  </style>
</head>
<body>

<div class="container">
  <div class="form-container">
    <h4 class="mb-4 text-center text-primary"><i class="bi bi-folder-plus"></i> Cadastrar Categoria</h4>

    <form method="POST" class="needs-validation" novalidate>
      <div class="mb-3">
        <label for="nome" class="form-label">Nome da Categoria</label>
        <input type="text" name="nome" id="nome" class="form-control" placeholder="Ex: Tecnologia, Saúde, Gestão..." required>
        <div class="invalid-feedback">Informe o nome da categoria.</div>
      </div>

      <div class="d-grid">
        <button type="submit" class="btn btn-success">
          <i class="bi bi-check-circle me-1"></i> Salvar
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  // Bootstrap validation
  (function () {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  })();
</script>

</body>
</html>
