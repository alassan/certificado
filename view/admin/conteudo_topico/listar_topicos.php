<?php
require_once __DIR__ . '/../../../config/conexao.php';
require_once __DIR__ . '/../../../models/ConteudoTopico.php';
require_once __DIR__ . '/../../../models/CursoDisponivel.php';
require_once __DIR__ . '/../../includes/header.php';

$topicoModel = new ConteudoTopico($conn);
$cursoModel = new CursoDisponivel($conn);
$topicosPorCurso = $topicoModel->listarAgrupadoPorCurso();

function formatarMinutosParaHorasMinutos($minutos) {
    if ($minutos < 60) return "{$minutos}min";
    $h = floor($minutos / 60);
    $m = $minutos % 60;
    return $m > 0 ? "{$h}h{$m}min" : "{$h}h";
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-semibold text-primary">
            <i class="bi bi-journal-text me-2"></i> Tópicos por Curso
        </h4>
        <a href="index.php?page=dashboard/painel" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>

    <?php if (empty($topicosPorCurso)): ?>
        <div class="alert alert-warning text-center py-3">
            <i class="bi bi-info-circle me-2"></i> Nenhum tópico cadastrado
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($topicosPorCurso as $cursoId => $grupo): ?>
                <?php
                    $cursoDisponivel = $cursoModel->buscarPorId($cursoId);
                    $curso = $conn->prepare("SELECT * FROM cursos WHERE id = ?");
                    $curso->execute([$cursoDisponivel['curso_id']]);
                    $cursoInfo = $curso->fetch(PDO::FETCH_ASSOC);

                    $chCurso = intval($cursoInfo['carga_horaria'] ?? 0);
                    $chTopicosMin = intval($grupo['total_ch']);
                    $chCursoMin = $chCurso * 60;
                    $chRestanteMin = max(0, $chCursoMin - $chTopicosMin);
                    $progresso = $chCursoMin > 0 ? min(100, ($chTopicosMin / $chCursoMin) * 100) : 0;
                ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <h5 class="mb-0 fw-semibold"><?= htmlspecialchars($grupo['curso_nome']) ?></h5>
                            <div class="d-flex gap-2">
                                <span class="badge bg-primary rounded-pill">
                                    <i class="bi bi-clock me-1"></i> <?= formatarMinutosParaHorasMinutos($chTopicosMin) ?>/<?= $chCurso ?>h
                                </span>
                                <span class="badge bg-<?= $chRestanteMin > 0 ? 'warning' : 'success' ?> rounded-pill">
                                    <i class="bi bi-hourglass-<?= $chRestanteMin > 0 ? 'bottom' : 'top' ?> me-1"></i> <?= formatarMinutosParaHorasMinutos($chRestanteMin) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body p-0">
                            <div class="progress rounded-0" style="height: 4px;">
                                <div class="progress-bar bg-<?= $progresso >= 100 ? 'success' : 'primary' ?>" 
                                     role="progressbar" 
                                     style="width: <?= $progresso ?>%">
                                </div>
                            </div>
                            
                            <ul class="list-group list-group-flush">
                                <?php foreach ($grupo['topicos'] as $topico): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                        <span><?= htmlspecialchars($topico['conteudo']) ?></span>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-light text-dark border border-secondary rounded-pill me-2">
                                                <?= $topico['ch'] ?> min
                                            </span>
                                            <button class="btn btn-sm btn-outline-danger rounded-circle p-1"
                                                onclick="confirmarExclusao(<?= $topico['id'] ?>, <?= $cursoId ?>)"
                                                title="Excluir tópico">
                                                <i class="bi bi-trash" style="font-size: 0.8rem;"></i>
                                            </button>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmarExclusao(topicoId, cursoId) {
    if (confirm('Tem certeza que deseja excluir este tópico?')) {
        window.location.href = `/certificado/controllers/TopicoController.php?acao=excluir&id=${topicoId}&curso_id=${cursoId}`;
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>