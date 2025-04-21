<?php
require_once __DIR__ . '/../../phpqrcode/qrlib.php'; // Correto agora

// Coleta de dados via GET
$nome = $_GET['nome'] ?? 'Nome Desconhecido';
$cpf = $_GET['cpf'] ?? '000.000.000-00';
$curso = $_GET['curso'] ?? 'Curso';
$empresa = $_GET['empresa'] ?? 'Empresa';
$carga = $_GET['carga_horaria'] ?? '00 horas';
$data_inicio = $_GET['data_inicio'] ?? '01/01/2025';
$data_fim = $_GET['data_termino'] ?? '31/01/2025';

// Gera código único de verificação
$codigo_verificacao = md5($nome . $cpf . $curso . $data_inicio);

// Define URL de verificação
$url_qr = "http://localhost/certificado/view/certificados/ver_certificado.php?codigo={$codigo_verificacao}";

// Caminho e nome do arquivo QR Code
$qr_path = "../../temp_qrcodes";
$qr_file = "{$qr_path}/{$codigo_verificacao}.png";

// Cria diretório, se não existir
if (!is_dir($qr_path)) {
    mkdir($qr_path, 0777, true);
}

// Gera o QR Code apenas se não existir
if (!file_exists($qr_file)) {
    QRcode::png($url_qr, $qr_file, QR_ECLEVEL_L, 4);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificado Encontrado</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      padding-top: 60px;
      font-family: 'Segoe UI', sans-serif;
    }
    .certificado-box {
      background: #ffffff;
      border-radius: 1rem;
      padding: 2.5rem;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.04);
    }
    .certificado-box h3 {
      color: #198754;
      font-weight: 600;
    }
    .qr-area img {
      width: 140px;
      height: auto;
    }
    .info-label {
      font-weight: 500;
    }
    .btn-success {
      padding: 0.6rem 1.2rem;
      font-weight: 500;
      font-size: 1rem;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="certificado-box">
        <h3 class="text-center mb-4"><i class="bi bi-patch-check-fill me-2"></i>Certificado Encontrado</h3>

        <div class="mb-3"><span class="info-label">👤 Nome:</span> <?= htmlspecialchars($nome) ?></div>
        <div class="mb-3"><span class="info-label">🎓 Curso:</span> <?= htmlspecialchars($curso) ?></div>
        <div class="mb-3"><span class="info-label">🏢 Empresa:</span> <?= htmlspecialchars($empresa) ?></div>
        <div class="mb-3"><span class="info-label">⏱ Carga Horária:</span> <?= htmlspecialchars($carga) ?></div>
        <div class="mb-3"><span class="info-label">🗓 Período:</span> <?= htmlspecialchars($data_inicio) ?> a <?= htmlspecialchars($data_fim) ?></div>

        <div class="qr-area text-center my-4">
          <img src="<?= $qr_file ?>" alt="QR Code" class="img-fluid">
          <p class="mt-3"><strong>🔒 Código de Verificação:</strong> <code><?= $codigo_verificacao ?></code></p>
        </div>

        <div class="text-center mt-4">
          <a href="gerar_certificado.php?nome=<?= urlencode($nome) ?>&cpf=<?= urlencode($cpf) ?>&curso=<?= urlencode($curso) ?>&empresa=<?= urlencode($empresa) ?>&carga_horaria=<?= urlencode($carga) ?>&data_inicio=<?= urlencode($data_inicio) ?>&data_termino=<?= urlencode($data_fim) ?>" class="btn btn-success">
            <i class="bi bi-eye"></i> Visualizar Certificado
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
