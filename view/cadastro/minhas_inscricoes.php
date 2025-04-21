<?php
require_once __DIR__ . '/../../conexao.php';
session_start();

$usuario_id = $_SESSION['usuario_id'] ?? null;

if (!$usuario_id) {
    header('Location: ../login/login.php');
    exit;
}

$stmt = $conn->prepare("SELECT f.id, f.nome_aluno, f.cpf, f.contato, f.data_inscricao, f.status,
    c.nome AS curso_nome
    FROM fichas_inscricao f
    JOIN cursos_disponiveis cd ON cd.id = f.curso_id
    JOIN cursos c ON c.id = cd.curso_id
    WHERE f.usuario_id = ?
    ORDER BY f.data_inscricao DESC");
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
</head>
<body>
<div class="container my-5">
  <div class="card shadow p-4">
    <h3 class="mb-4"><i class="bi bi-list-task text-primary me-2"></i> Minhas Inscrições</h3>

    <?php if (count($fichas) === 0): ?>
      <div class="alert alert-warning text-center">
        Você ainda não realizou nenhuma inscrição.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Curso</th>
              <th>Data</th>
              <th>Status</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($fichas as $ficha): ?>
              <tr>
                <td><?= htmlspecialchars($ficha['curso_nome']) ?></td>
                <td><?= date('d/m/Y', strtotime($ficha['data_inscricao'])) ?></td>
                <td>
                  <?php if ($ficha['status'] === 'concluido'): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Concluído</span>
                  <?php elseif ($ficha['status'] === 'ativo'): ?>
                    <span class="badge bg-primary"><i class="bi bi-hourglass-split"></i> Ativo</span>
                  <?php else: ?>
                    <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Cancelado</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="comprovante_inscricao.php?id=<?= $ficha['id'] ?>" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-file-earmark-text"></i> Comprovante
                  </a>
                  <?php if ($ficha['status'] === 'concluido'): ?>
                    <a href="../certificados/gerar_certificado.php?ficha_id=<?= $ficha['id'] ?>" class="btn btn-sm btn-outline-success">
                      <i class="bi bi-award"></i> Certificado
                    </a>
                  <?php endif; ?>
                  <a href="ficha_editar.php?id=<?= $ficha['id'] ?>" class="btn btn-sm btn-warning">
                    <i class="bi bi-pencil"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <div class="text-center mt-4">
      <a href="../dashboard/painel.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar ao Painel
      </a>
    </div>
  </div>
</div>
</body>
</html>
