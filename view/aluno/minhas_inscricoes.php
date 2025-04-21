<?php
require_once __DIR__ . '/../../conexao.php';
session_start();

$usuario_id = $_SESSION['usuario_id'] ?? null;

if (!$usuario_id) {
    header("Location: ../login/login.php");
    exit;
}

$sql = "SELECT fi.id, c.nome AS curso, fi.data_inscricao, fi.status
        FROM fichas_inscricao fi
        JOIN cursos_disponiveis cd ON cd.id = fi.curso_id
        JOIN cursos c ON c.id = cd.curso_id
        WHERE fi.usuario_id = ?
        ORDER BY fi.data_inscricao DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$usuario_id]);
$fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Minhas Inscrições</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .card-inscricoes {
      border-radius: 1rem;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
    .badge-status {
      font-size: 0.85rem;
      padding: 0.4em 0.7em;
    }
  </style>
</head>
<body>

<div class="container my-5">
  <div class="card p-4 card-inscricoes">
    <h4 class="text-center mb-4">
      <i class="bi bi-list-task text-primary me-2"></i>
      Minhas Inscrições
    </h4>

    <?php if (empty($fichas)): ?>
      <div class="alert alert-warning text-center">Você ainda não realizou nenhuma inscrição.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr class="text-center">
            <th>Curso</th>
            <th>Data de Inscrição</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($fichas as $ficha): ?>
            <tr class="text-center">
              <td class="text-start"><?= htmlspecialchars($ficha['curso']) ?></td>
              <td><?= date('d/m/Y', strtotime($ficha['data_inscricao'])) ?></td>
              <td>
                <?php
                  $status = $ficha['status'];
                  $cor = match($status) {
                    'Concluído' => 'success',
                    'Ativo'     => 'primary',
                    default     => 'secondary'
                  };
                ?>
                <span class="badge bg-<?= $cor ?> badge-status"><?= $status ?></span>
              </td>
              <td>
                <a href="visualizar_ficha.php?id=<?= $ficha['id'] ?>" class="btn btn-outline-info btn-sm" title="Visualizar">
                  <i class="bi bi-eye"></i>
                </a>
                <?php if ($status === 'Concluído'): ?>
                  <a href="gerar_certificado.php?id=<?= $ficha['id'] ?>" class="btn btn-outline-success btn-sm" title="Certificado">
                    <i class="bi bi-patch-check"></i>
                  </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <div class="text-center mt-4">
      <a href="../dashboard/painel.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
      </a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
