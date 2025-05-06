<?php
require_once __DIR__ . '/../../../config/conexao.php';
require_once __DIR__ . '/../../../models/ConteudoTopico.php';
require_once __DIR__ . '/../../../models/CursoDisponivel.php';
require_once __DIR__ . '/../../includes/header.php';

$topicoModel = new ConteudoTopico($conn);
$cursoModel = new CursoDisponivel($conn);
$topicosPorCurso = $topicoModel->listarAgrupadoPorCurso();

function formatarMinutosParaHorasMinutos($minutos) {
    $h = floor($minutos / 60);
    $m = $minutos % 60;
    return "{$h}h" . ($m > 0 ? " {$m}min" : "");
}
?>

<div class="container mt-5">
    <h4><i class="bi bi-journal-text"></i> Tópicos de Conteúdo por Curso</h4>

    <?php if (empty($topicosPorCurso)): ?>
        <div class="alert alert-warning mt-4">Nenhum tópico cadastrado ainda.</div>
    <?php else: ?>
        <?php foreach ($topicosPorCurso as $cursoId => $grupo): ?>
            <?php
                // Buscar curso_disponivel e depois o curso real
                $cursoDisponivel = $cursoModel->buscarPorId($cursoId);
                $curso = $conn->prepare("SELECT * FROM cursos WHERE id = ?");
                $curso->execute([$cursoDisponivel['curso_id']]);
                $cursoInfo = $curso->fetch(PDO::FETCH_ASSOC);

                $chCurso = intval($cursoInfo['carga_horaria'] ?? 0); // em horas
                $chTopicosMin = intval($grupo['total_ch']);          // em minutos
                $chCursoMin = $chCurso * 60;
                $chRestanteMin = max(0, $chCursoMin - $chTopicosMin);
            ?>
            <div class="card shadow mt-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <strong><?= htmlspecialchars($grupo['curso_nome']) ?></strong>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary">
                            Tópicos: <?= formatarMinutosParaHorasMinutos($chTopicosMin) ?> / <?= $chCurso ?>h
                        </span>
                        <span class="badge bg-<?= $chRestanteMin > 0 ? 'warning' : 'success' ?>">
                            CH Restante: <?= formatarMinutosParaHorasMinutos($chRestanteMin) ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($grupo['topicos'] as $topico): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($topico['conteudo']) ?>
                                <div>
                                    <span class="badge bg-secondary"><?= $topico['ch'] ?> min</span>
                                    <a href="/certificado/controllers/TopicoController.php?acao=excluir&id=<?= $topico['id'] ?>&curso_id=<?= $cursoId ?>" 
                                       class="btn btn-sm btn-outline-danger ms-2"
                                       onclick="return confirm('Deseja excluir este tópico?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="index.php?page=dashboard/painel" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar ao Painel
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
