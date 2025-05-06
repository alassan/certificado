<?php
require_once __DIR__ . '/../../config/conexao.php';
session_start();

$nivel = $_SESSION['usuario_nivel'] ?? '';
$cpfLogado = $_SESSION['usuario_cpf'] ?? '';

if ($nivel === 'Aluno') {
    $sql = "SELECT fi.*, c.nome AS curso_nome, e.cidade, e.uf
            FROM fichas_inscricao fi
            JOIN cursos c ON fi.curso_id = c.id
            JOIN enderecos e ON fi.endereco_id = e.id
            WHERE fi.cpf = ?
            ORDER BY fi.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$cpfLogado]);
} else {
    $sql = "SELECT fi.*, c.nome AS curso_nome, e.cidade, e.uf
            FROM fichas_inscricao fi
            JOIN cursos c ON fi.curso_id = c.id
            JOIN enderecos e ON fi.endereco_id = e.id
            ORDER BY fi.created_at DESC";
    $stmt = $conn->query($sql);
}

$fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Listagem de Fichas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .table-container {
      background: #fff;
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
    .badge {
      font-size: 0.85rem;
    }
  </style>
</head>
<body>

<div class="container my-5">
  <div class="table-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4><i class="bi bi-list-check text-primary me-2"></i>Fichas de Inscrição</h4>
      <?php if ($nivel === 'Admin' || $nivel === 'Aluno'): ?>
        <a href="ficha_inscricao.php" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-pencil-square me-1"></i>Nova Ficha
        </a>
      <?php endif; ?>
    </div>

    <?php if (isset($_GET['sucesso'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>Ficha salva com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle shadow-sm">
        <thead class="table-light">
          <tr class="text-center">
            <th>Nome</th>
            <th>CPF</th>
            <th>Curso</th>
            <th>Contato</th>
            <th>Funcionário PMT</th>
            <th>Inscrição</th>
            <th>Endereço</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($fichas as $ficha): ?>
            <tr class="text-center">
              <td class="text-start"><?= htmlspecialchars($ficha['nome_aluno']) ?></td>
              <td><?= htmlspecialchars($ficha['cpf']) ?></td>
              <td><?= htmlspecialchars($ficha['curso_nome']) ?></td>
              <td><?= htmlspecialchars($ficha['contato']) ?></td>
              <td>
                <span class="badge <?= $ficha['pmt_funcionario'] ? 'bg-success' : 'bg-secondary' ?>">
                  <?= $ficha['pmt_funcionario'] ? 'Sim' : 'Não' ?>
                </span>
              </td>
              <td><?= date('d/m/Y', strtotime($ficha['data_inscricao'])) ?></td>
              <td><?= htmlspecialchars($ficha['cidade'] . ' - ' . $ficha['uf']) ?></td>
              <td>
                <a href="visualizar_ficha.php?id=<?= $ficha['id'] ?>" class="btn btn-sm btn-info me-1"><i class="bi bi-eye"></i></a>
                <?php if ($nivel === 'Admin' || $nivel === 'Aluno'): ?>
                  <a href="ficha_editar.php?id=<?= $ficha['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
