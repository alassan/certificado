<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/FichaInscricao.php';
require_once __DIR__ . '/../models/CursoDisponivel.php';

$fichaModel = new FichaInscricao($conn);
$cursoDisponivelModel = new CursoDisponivel($conn);

// Busca fichas que precisam ter status atualizado
$fichas = $fichaModel->buscarParaAtualizacaoStatus();

foreach ($fichas as $ficha) {
    $curso = $cursoDisponivelModel->buscarPorId($ficha['curso_disponivel_id']);
    
    $novoStatus = $ficha['status_aluno'];
    
    // Se matriculado e curso começou
    if ($ficha['status_aluno'] === 'matriculado' && 
        $curso && date('Y-m-d') >= $curso['data_inicio_curso']) {
        $novoStatus = 'em_andamento';
    }
    
    // Se em andamento e curso terminou
    if ($ficha['status_aluno'] === 'em_andamento' && 
        $curso && date('Y-m-d') > $curso['data_fim_curso']) {
        $novoStatus = 'concluido';
    }
    
    if ($novoStatus !== $ficha['status_aluno']) {
        $fichaModel->atualizarStatus($ficha['id'], $novoStatus);
    }
}

echo "Status atualizados com sucesso. " . count($fichas) . " fichas processadas.";