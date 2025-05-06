<?php
require_once __DIR__ . '/../../config/conexao.php';
require 'fpdf/fpdf.php';
require 'phpqrcode/qrlib.php';

$codigo = $_GET['codigo'] ?? '';
if (!$codigo) {
    die('Código de verificação inválido.');
}

$stmt = $conn->prepare("SELECT fi.*, c.nome AS curso, c.carga_horaria, p.nome AS professor
    FROM fichas_inscricao fi
    JOIN cursos c ON fi.curso_id = c.id
    LEFT JOIN professores p ON fi.professor_id = p.id
");
$stmt->execute();
$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$aluno = null;
foreach ($alunos as $a) {
    $hash = md5($a['nome_aluno'] . $a['cpf'] . $a['curso_id'] . $a['data_inicio']);
    if ($hash === $codigo) {
        $aluno = $a;
        break;
    }
}

if (!$aluno) {
    die('Certificado não encontrado.');
}

// Dados do certificado
$nome = $aluno['nome_aluno'];
$cpf = $aluno['cpf'];
$curso = $aluno['curso'];
$carga_horaria = $aluno['carga_horaria'] . ' horas';
$data_inicio = $aluno['data_inicio'];
$data_termino = $aluno['data_termino'];
$empresa = 'Fundação Wall Ferraz'; // fixo

$baseUrl = ($_SERVER['HTTP_HOST'] === 'localhost')
    ? 'http://localhost/certificado'
    : 'https://verificar.certificado.com';

$qrcode_temp = 'temp_qrcode.png';
QRcode::png("$baseUrl/ver_certificado.php?codigo=$codigo", $qrcode_temp, QR_ECLEVEL_L, 3);

// Gerar PDF
class PDF extends FPDF {
    function Header() {
        if ($this->PageNo() == 1) {
            $this->Image('assets/img/template_certificado.jpg', 0, 0, 173.31, 122.60);
        } elseif ($this->PageNo() == 2) {
            $this->Image('assets/img/template_certificado_pg2.jpg', 0, 0, 173.31, 122.60);
        }
    }
}

$pdf = new PDF('L', 'mm', array(173.31, 122.60));
$pdf->AddPage();
$pdf->SetMargins(13, 10, 13);
$pdf->SetY(58);
$pdf->SetX(20);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);

$partes = [
    "Certificamos que ",
    ["texto" => $nome, "negrito" => true],
    " concluiu com êxito o curso de ",
    ["texto" => $curso, "negrito" => true],
    ", ministrado pela $empresa com carga horária de ",
    ["texto" => $carga_horaria, "negrito" => true],
    ", realizado no período de ",
    ["texto" => date('d/m/Y', strtotime($data_inicio)) . " a " . date('d/m/Y', strtotime($data_termino)), "negrito" => true],
    "."
];

foreach ($partes as $parte) {
    if (is_array($parte) && $parte['negrito']) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Write(6, utf8_decode($parte['texto']));
        $pdf->SetFont('Arial', '', 10);
    } else {
        $pdf->Write(6, utf8_decode($parte));
    }
}

$pdf->Image($qrcode_temp, 155, 97, 15, 15);
$pdf->AddPage();
$pdf->Output("I", "certificado_$nome.pdf");
unlink($qrcode_temp);
?>
