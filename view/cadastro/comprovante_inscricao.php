<?php
require_once __DIR__ . '/../../conexao.php';
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger text-center'>ID de inscrição inválido.</div>";
    exit;
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT f.*, c.nome AS curso_nome, e.* FROM fichas_inscricao f
    JOIN cursos_disponiveis cd ON cd.id = f.curso_disponivel_id
    JOIN cursos c ON c.id = cd.curso_id
    JOIN enderecos e ON e.id = f.endereco_id
    WHERE f.id = ? LIMIT 1");

$stmt->execute([$id]);
$ficha = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ficha) {
    echo "<div class='alert alert-warning text-center'>Ficha de inscrição não encontrada.</div>";
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Gerar QR Code com link de verificação
$link = "https://certificados.teresinadev.com.br/verificar_inscricao.php?id=" . $id;
$qrCode = QrCode::create($link)->setSize(150)->setMargin(10);
$writer = new PngWriter();
$result = $writer->write($qrCode);
$qrImage = base64_encode($result->getString());
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
    }
  </style>
</head>
<body>
<div class="container my-5">
  <div class="card shadow p-4">
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
      <dd class="col-sm-8"><?= date('d/m/Y', strtotime($ficha['data_inscricao'])) ?></dd>

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
      <dd class="col-sm-8 text-success fw-bold">Inscrição Ativa</dd>

      <dt class="col-sm-4">QR Code de Verificação:</dt>
      <dd class="col-sm-8 qrcode">
        <img src="data:image/png;base64,<?= $qrImage ?>" alt="QR Code">
      </dd>
    </dl>

    <div class="text-center mt-4 print-hidden">
      <button class="btn btn-secondary" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Imprimir
      </button>
      <a href="listar_fichas.php" class="btn btn-outline-primary ms-2">
        ← Voltar para lista de fichas
      </a>
    </div>
  </div>
</div>
</body>
</html>
