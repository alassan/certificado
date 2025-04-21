<?php
session_start();
require 'conexao.php';
require 'phpqrcode/qrlib.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf = $_POST['cpf'] ?? '';
    $data_nascimento = $_POST['data_nascimento'] ?? '';

    if (!$cpf || !$data_nascimento) {
        $_SESSION['erro'] = "Dados incompletos.";
        header("Location: index.php");
        exit;
    }

    $sql = "SELECT * FROM certificados WHERE cpf = ? AND data_nascimento = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$cpf, $data_nascimento]);
    $certificado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$certificado) {
        $_SESSION['erro'] = "Certificado não encontrado.";
        header("Location: index.php");
        exit;
    }

    $_SESSION['certificado'] = $certificado;
    header("Location: ver_certificado.php");
    exit;
}

if (!isset($_SESSION['certificado'])) {
    $_SESSION['erro'] = "Sessão expirada. Faça a consulta novamente.";
    header("Location: index.php");
    exit;
}

$certificado = $_SESSION['certificado'];
unset($_SESSION['certificado']);
session_write_close();

$nome = $certificado['nome'];
$cpf = $certificado['cpf'];
$curso = $certificado['curso'];
$empresa = $certificado['empresa'];
$carga = $certificado['carga_horaria'] . ' horas';
$data_inicio = date('d/m/Y', strtotime($certificado['data_inicio']));
$data_fim = date('d/m/Y', strtotime($certificado['data_termino']));
$codigo_verificacao = md5($nome . $cpf . $curso . $certificado['data_inicio']);
$qrcode_file = "temp_qrcodes/{$codigo_verificacao}.png";

if (!file_exists($qrcode_file)) {
    QRcode::png("http://localhost/certificado/$codigo_verificacao", $qrcode_file, QR_ECLEVEL_L, 4);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Certificado Encontrado</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8fafc;
      padding-top: 60px;
    }
    .certificado-box {
      background: #fff;
      border-radius: 1rem;
      padding: 2rem;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      max-width: 700px;
      margin: auto;
    }
    .qr-area {
      text-align: center;
    }
  </style>
</head>
<body>

<div class="certificado-box">
  <h3 class="text-center mb-4">Certificado Encontrado</h3>
  <p><strong>Nome:</strong> <?= htmlspecialchars($nome) ?></p>
  <p><strong>Curso:</strong> <?= htmlspecialchars($curso) ?></p>
  <p><strong>Empresa:</strong> <?= htmlspecialchars($empresa) ?></p>
  <p><strong>Carga Horária:</strong> <?= htmlspecialchars($carga) ?></p>
  <p><strong>Período:</strong> <?= htmlspecialchars($data_inicio) ?> a <?= htmlspecialchars($data_fim) ?></p>

  <div class="qr-area mt-3">
    <img src="<?= $qrcode_file ?>" alt="QR Code">
    <p class="mt-2"><strong>Código de Verificação:</strong> <?= $codigo_verificacao ?></p>
  </div>

  <div class="text-center mt-4">
    <a href="gerar_certificado.php?nome=<?= urlencode($nome) ?>&cpf=<?= urlencode($cpf) ?>&curso=<?= urlencode($curso) ?>&empresa=<?= urlencode($empresa) ?>&carga_horaria=<?= urlencode($certificado['carga_horaria']) ?>&data_inicio=<?= urlencode($certificado['data_inicio']) ?>&data_termino=<?= urlencode($certificado['data_termino']) ?>" class="btn btn-success">Visualizar Certificado</a>
  </div>
</div>

</body>
</html>