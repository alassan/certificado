<?php
require_once __DIR__ . '/../../config/conexao.php';
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Listar Categorias</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .container-box {
      max-width: 800px;
      margin: 60px auto;
      background: #fff;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
  </style>
</head>
<body>

<div class="container container-box">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-primary mb-0"><i class="bi bi-folder2-open"></i> Categorias</h4>
    <a href="categoria_cadastrar.php" class="btn btn-success shadow-sm">
      <i class="bi bi-plus-circle me-1"></i> Nova Categoria
    </a>
  </div>

  <?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success">Categoria cadastrada com sucesso!</div>
  <?php endif; ?>

  <?php if (count($categorias) === 0): ?>
    <p class="text-muted">Nenhuma categoria cadastrada ainda.</p>
  <?php else: ?>
    <table class="table table-striped table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Nome</th>
          <th style="width: 100px;">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categorias as $cat): ?>
        <tr>
          <td><?= $cat['id'] ?></td>
          <td><?= htmlspecialchars($cat['nome']) ?></td>
          <td class="text-center">
            <a href="categoria_editar.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-pencil"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  <div class="text-center mt-4">
      <a href="index.php?page=dashboard/painel" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar ao Painel
      </a>
    </div>
</div>

</body>
</html>
