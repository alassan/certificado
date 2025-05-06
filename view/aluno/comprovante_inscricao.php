<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/FichaInscricao.php';
require_once __DIR__ . '/../includes/status_aluno.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['mensagem_erro'] = "ID de inscrição inválido.";
    header('Location: ../aluno/listar_fichas.php');
    exit;
}

$fichaModel = new FichaInscricao($conn);
$ficha = $fichaModel->getById($id);

if (!$ficha) {
    $_SESSION['mensagem_erro'] = "Ficha de inscrição não encontrada.";
    header('Location: ../aluno/listar_fichas.php');
    exit;
}

// Atualiza status automaticamente conforme datas
$novoStatus = StatusAluno::atualizar($ficha, [
    'data_inicio_curso' => $ficha['data_inicio'] ?? null,
    'data_fim_curso' => $ficha['data_termino'] ?? null
]);

if ($novoStatus && $novoStatus !== $ficha['status_aluno']) {
    $fichaModel->atualizarStatus($id, $novoStatus);
    $ficha['status_aluno'] = $novoStatus;
}

// Prepara mapeamento visual dos status
$statusClass = [
    'concluido' => ['success', 'bi-check-circle-fill', 'Concluído'],
    'matriculado' => ['primary', 'bi-hourglass-split', 'Matriculado'],
    'em_andamento' => ['warning', 'bi-hourglass-top', 'Em Andamento'],
    'cancelado' => ['secondary', 'bi-x-circle-fill', 'Cancelado'],
    'espera' => ['warning', 'bi-clock', 'Lista de Espera'],
    'indefinido' => ['dark', 'bi-question-circle', 'Indefinido']
];
$statusKey = strtolower(str_replace(' ', '_', $ficha['status_aluno'] ?? 'indefinido'));
$statusInfo = $statusClass[$statusKey] ?? $statusClass['indefinido'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Comprovante de Inscrição - <?= htmlspecialchars($ficha['nome_aluno']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body { background-color: #f8f9fa; }
    .card { max-width: 800px; margin: 0 auto; }
    .print-hidden { display: inline-block; }
    @media print {
      .print-hidden { display: none !important; }
      body { background-color: white; }
      .card { box-shadow: none; border: 1px solid #ddd; margin: 0; padding: 0; }
      .container { padding: 0; }
    }
    dt { font-weight: 500; }
  </style>
</head>
<body>
<div class="container my-5">
  <div class="card shadow p-4">
    <div class="text-center mb-4">
      <h3 class="text-primary"><i class="bi bi-receipt"></i> Comprovante de Inscrição</h3>
      <p class="text-muted small">Nº <?= $id ?> - Emitido em: <?= date('d/m/Y H:i') ?></p>
    </div>

    <dl class="row">
      <dt class="col-sm-4">Nome do Aluno:</dt>
      <dd class="col-sm-8"><?= htmlspecialchars($ficha['nome_aluno']) ?></dd>

      <dt class="col-sm-4">Curso:</dt>
      <dd class="col-sm-8"><?= htmlspecialchars($ficha['curso_nome']) ?></dd>

      <dt class="col-sm-4">CPF:</dt>
      <dd class="col-sm-8"><?= htmlspecialchars($ficha['cpf']) ?></dd>

      <dt class="col-sm-4">Data de Inscrição:</dt>
      <dd class="col-sm-8"><?= date('d/m/Y H:i', strtotime($ficha['data_inscricao'])) ?></dd>

      <dt class="col-sm-4">Contato:</dt>
      <dd class="col-sm-8"><?= htmlspecialchars($ficha['contato']) ?></dd>

      <dt class="col-sm-4">Endereço:</dt>
      <dd class="col-sm-8">
        <?= htmlspecialchars($ficha['logradouro']) ?>, Nº <?= htmlspecialchars($ficha['numero']) ?>,
        <?= htmlspecialchars($ficha['bairro']) ?>, <?= htmlspecialchars($ficha['cidade']) ?> - <?= htmlspecialchars($ficha['uf']) ?>,
        CEP: <?= htmlspecialchars($ficha['cep']) ?>
      </dd>

      <dt class="col-sm-4">Funcionário PMT:</dt>
      <dd class="col-sm-8"><?= $ficha['pmt_funcionario'] ? 'Sim' : 'Não' ?></dd>

      <dt class="col-sm-4">Situação:</dt>
      <dd class="col-sm-8 fw-semibold">
        <span class="badge bg-<?= $statusInfo[0] ?>">
          <i class="bi <?= $statusInfo[1] ?>"></i> <?= $statusInfo[2] ?>
        </span>
      </dd>
    </dl>

    <div class="text-center mt-4 print-hidden d-flex flex-wrap justify-content-center gap-2">
      <button class="btn btn-secondary" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Imprimir
      </button>

      <a href="../aluno/listar_fichas.php" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left"></i> Voltar à lista
      </a>

      <a href="../aluno/gerar_pdf_inscricao.php?id=<?= htmlspecialchars($ficha['id']) ?>" class="btn btn-outline-dark">
        <i class="bi bi-file-earmark-pdf"></i> Baixar PDF
      </a>

      <?php if ($statusKey === 'matriculado'): ?>
        <a href="../aluno/ficha_editar.php?id=<?= htmlspecialchars($ficha['id']) ?>" class="btn btn-warning">
          <i class="bi bi-pencil-square"></i> Editar Ficha
        </a>
      <?php endif; ?>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.querySelector('button[onclick="window.print()"]').addEventListener('click', () => {
    setTimeout(() => window.location.reload(), 1000);
  });
</script>
</body>
</html>
