<?php
require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../../phpqrcode/qrlib.php';
require_once __DIR__ . '/../../models/FichaInscricao.php';
session_start();

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    $_SESSION['mensagem_erro'] = "ID de inscrição inválido.";
    header('Location: listar_fichas.php');
    exit;
}

$fichaModel = new FichaInscricao($conn);
$ficha = $fichaModel->getById($id);

if (!$ficha) {
    $_SESSION['mensagem_erro'] = "Ficha de inscrição não encontrada.";
    header('Location: listar_fichas.php');
    exit;
}

// Gerar QR Code de verificação
$tempDir = __DIR__ . '/temp/';
if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

$qrFile = $tempDir . 'qrcode_' . $id . '.png';
$link = "https://certificados.teresinadev.com.br/verificar_inscricao.php?id=" . $id;
QRcode::png($link, $qrFile, QR_ECLEVEL_L, 4);
$qrImage = base64_encode(file_get_contents($qrFile));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Comprovante de Inscrição</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body { background-color: #f8f9fa; }
    .card { max-width: 800px; margin: 0 auto; }
    .qrcode img { max-width: 150px; }
    .print-hidden { display: inline-block; }
    @media print {
      .print-hidden { display: none !important; }
      body { background-color: white; }
      .card { box-shadow: none; border: 1px solid #ddd; }
    }
  </style>
</head>
<body>
<div class="container my-5">
  <div class="card shadow p-4">

    <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
      <div class="alert alert-success text-center d-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <?= htmlspecialchars($_SESSION['mensagem_sucesso']) ?>
      </div>
      <?php unset($_SESSION['mensagem_sucesso']); ?>
    <?php endif; ?>

    <h3 class="text-center mb-4 text-primary">
      <i class="bi bi-receipt"></i> Comprovante de Inscrição
    </h3>

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
        <?= htmlspecialchars($ficha['logradouro']) ?>, Nº <?= $ficha['numero'] ?>,
        <?= htmlspecialchars($ficha['bairro']) ?>, <?= $ficha['cidade'] ?> - <?= $ficha['uf'] ?>,
        CEP: <?= $ficha['cep'] ?>
      </dd>

      <dt class="col-sm-4">Funcionário PMT:</dt>
      <dd class="col-sm-8"><?= $ficha['pmt_funcionario'] ? 'Sim' : 'Não' ?></dd>

      <dt class="col-sm-4">Situação:</dt>
      <dd class="col-sm-8 fw-semibold">
        <?php if ($ficha['status'] === 'concluido'): ?>
          <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Concluído</span>
        <?php elseif ($ficha['status'] === 'ativo'): ?>
          <span class="badge bg-primary"><i class="bi bi-hourglass-split"></i> Ativo</span>
        <?php else: ?>
          <span class="badge bg-secondary"><i class="bi bi-x-circle-fill"></i> Cancelado</span>
        <?php endif; ?>
      </dd>

      <dt class="col-sm-4">QR Code de Verificação:</dt>
      <dd class="col-sm-8 qrcode">
        <img src="data:image/png;base64,<?= $qrImage ?>" alt="QR Code">
        <div class="mt-2 small text-muted">Escaneie para verificar a autenticidade</div>
      </dd>
    </dl>

    <div class="text-center mt-4 print-hidden d-flex flex-wrap justify-content-center gap-2">
      <button class="btn btn-secondary" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Imprimir
      </button>

      <a href="listar_fichas.php" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left"></i> Voltar à lista
      </a>

      <a href="gerar_pdf_inscricao.php?id=<?= $ficha['id'] ?>" class="btn btn-outline-dark">
        <i class="bi bi-file-earmark-pdf"></i> Baixar PDF
      </a>

      <?php if ($ficha['status'] === 'ativo'): ?>
        <a href="ficha_editar.php?id=<?= $ficha['id'] ?>" class="btn btn-warning">
          <i class="bi bi-pencil-square"></i> Editar Ficha
        </a>
      <?php endif; ?>
    </div>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>