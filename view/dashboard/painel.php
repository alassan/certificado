<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../conexao.php';


require_once __DIR__ . '/../../conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit;
}

$nome = htmlspecialchars($_SESSION['usuario_nome']);
$nivel = htmlspecialchars($_SESSION['usuario_nivel']);
$usuario_id = $_SESSION['usuario_id'];

$totalCursos = $totalInscritos = $totalAtivos = $totalConcluidos = 0;
$dadosGrafico = [];

if ($nivel !== 'Aluno') {
    $totalCursos = $conn->query("SELECT COUNT(*) FROM cursos")->fetchColumn();
    $totalInscritos = $conn->query("SELECT COUNT(*) FROM fichas_inscricao")->fetchColumn();
    $totalAtivos = $conn->query("SELECT COUNT(*) FROM fichas_inscricao WHERE status = 'ativo'")->fetchColumn();
    $totalConcluidos = $conn->query("SELECT COUNT(*) FROM fichas_inscricao WHERE status = 'concluido'")->fetchColumn();

    $stmt = $conn->query("
        SELECT c.nome, COUNT(f.id) as total 
        FROM cursos c
        LEFT JOIN fichas_inscricao f ON f.curso_id = c.id
        GROUP BY c.nome
        ORDER BY total DESC
        LIMIT 5
    ");
    $dadosGrafico = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$meusCursos = [];
if ($nivel === 'Aluno') {
    $stmt = $conn->prepare("
        SELECT c.nome, f.status, cd.data_inicio, cd.data_termino
        FROM fichas_inscricao f
        JOIN cursos_disponiveis cd ON cd.id = f.curso_disponivel_id
        JOIN cursos c ON c.id = cd.curso_id
        WHERE f.usuario_id = ?
        ORDER BY cd.data_inicio DESC
        LIMIT 3
    ");
    $stmt->execute([$usuario_id]);
    $meusCursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/menu.php';
?>

<div class="main-content p-4" style="margin-left: 250px;">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold">
                <i class="bi bi-house-door-fill text-primary me-2"></i>
                Bem-vindo, <?= $nome ?>!
            </h4>
            <p class="text-muted mb-0">
                Nível de Acesso: 
                <span class="badge bg-primary text-capitalize"><?= $nivel ?></span>
            </p>
        </div>
        <?php if ($nivel === 'Aluno'): ?>
            <a href="../aluno/ficha_inscricao.php" class="btn btn-primary">
                <i class="bi bi-pencil-square me-1"></i> Nova Inscrição
            </a>
        <?php endif; ?>
    </div>

    <?php if ($nivel !== 'Aluno'): ?>
        <div class="row g-3 mb-4">
            <?php
                $cards = [
                    ['label' => 'Cursos Cadastrados', 'icon' => 'mortarboard-fill', 'value' => $totalCursos, 'color' => 'primary'],
                    ['label' => 'Total de Inscrições', 'icon' => 'person-plus-fill', 'value' => $totalInscritos, 'color' => 'success'],
                    ['label' => 'Cursos Ativos', 'icon' => 'person-check-fill', 'value' => $totalAtivos, 'color' => 'info'],
                    ['label' => 'Cursos Concluídos', 'icon' => 'award-fill', 'value' => $totalConcluidos, 'color' => 'warning'],
                ];

                foreach ($cards as $card):
            ?>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-<?= $card['color'] ?> shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-<?= $card['icon'] ?> fs-3 text-<?= $card['color'] ?>"></i>
                        <h6 class="mt-2"><?= $card['label'] ?></h6>
                        <p class="fs-4 fw-bold text-<?= $card['color'] ?> mb-0"><?= $card['value'] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-bar-chart-line text-secondary me-2"></i>
                            Inscrições por Curso (Top 5)
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoInscricoes" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history text-secondary me-2"></i>
                            Últimas Atividades
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush small">
                            <div class="list-group-item px-0 border-0">📌 Curso "Informática Básica" cadastrado.</div>
                            <div class="list-group-item px-0 border-0">🧑‍🎓 5 novas inscrições registradas.</div>
                            <div class="list-group-item px-0 border-0">🏅 Certificado emitido para João Silva.</div>
                        </div>
                        <div class="text-end mt-2">
                            <a href="#" class="btn btn-sm btn-outline-secondary">Ver todas</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Painel do Aluno -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="bi bi-journal-bookmark text-primary me-2"></i> Meus Cursos Recentes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($meusCursos) > 0): ?>
                            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
                                <?php foreach ($meusCursos as $curso): ?>
                                    <div class="col">
                                        <div class="card border shadow-sm h-100">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0 text-primary"><?= htmlspecialchars($curso['nome']) ?></h6>
                                            </div>
                                            <div class="card-body">
                                                <p class="mb-1"><strong>Status:</strong> 
                                                    <span class="badge bg-<?= 
                                                        $curso['status'] === 'concluido' ? 'success' : 
                                                        ($curso['status'] === 'ativo' ? 'primary' : 'secondary') 
                                                    ?>"><?= $curso['status'] ?></span>
                                                </p>
                                                <p class="mb-0"><strong>Período:</strong> <?= date('d/m/Y', strtotime($curso['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($curso['data_termino'])) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">Você ainda não está inscrito em nenhum curso.</div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <a href="../curso/meus_cursos.php" class="btn btn-sm btn-outline-primary">Ver todos os cursos</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($nivel !== 'Aluno'): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoInscricoes');
    const grafico = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($dadosGrafico, 'nome')) ?>,
            datasets: [{
                label: 'Inscrições',
                data: <?= json_encode(array_column($dadosGrafico, 'total')) ?>,
                backgroundColor: ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#6610f2'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' inscrições';
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
