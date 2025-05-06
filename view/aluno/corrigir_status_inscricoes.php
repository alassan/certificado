<?php
require_once __DIR__ . '/../../config/conexao.php';

require_once __DIR__ . '/../includes/status_aluno.php';


$sql = "
    SELECT f.id, f.status, cd.data_inicio, cd.data_termino
    FROM fichas_inscricao f
    JOIN cursos_disponiveis cd ON f.curso_id = cd.id
";

$stmt = $conn->query($sql);
$fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$atualizadas = 0;

foreach ($fichas as $ficha) {
    $id = $ficha['id'];
    $statusAtual = $ficha['status'];
    $dataInicio = $ficha['data_inicio'];
    $dataTermino = $ficha['data_termino'];

    $novoStatus = atualizarStatusInscricao($dataInicio, $dataTermino, $statusAtual);

    if ($novoStatus !== $statusAtual) {
        $update = $conn->prepare("UPDATE fichas_inscricao SET status = ? WHERE id = ?");
        $update->execute([$novoStatus, $id]);
        $atualizadas++;
    }
}

echo "Total de inscrições atualizadas: " . $atualizadas;
