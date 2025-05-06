<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/FichaInscricao.php';
$fichaModel = new FichaInscricao($conn);
$fichaModel->atualizarStatusAutomaticamente();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /certificado/view/login/login.php");
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
    $totalAtivos = $conn->query("SELECT COUNT(*) FROM cursos_disponiveis WHERE NOW() BETWEEN inicio_inscricao AND data_termino")->fetchColumn();
    $totalConcluidos = $conn->query("SELECT COUNT(*) FROM fichas_inscricao WHERE status_aluno = 'concluido'")->fetchColumn();

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
        SELECT c.nome, f.status_aluno, cd.data_inicio, cd.data_termino
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
    <!-- Cabeçalho com saudação personalizada -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold text-gradient">
                <i class="bi bi-house-door-fill me-2"></i>
                Olá, <?= $nome ?>!
            </h4>
            <p class="text-muted mb-0">
                <i class="bi bi-shield-lock me-1"></i>
                Nível: <span class="badge bg-gradient-primary text-capitalize"><?= $nivel ?></span>
            </p>
        </div>
        <?php if ($nivel === 'Aluno'): ?>
            <a href="index.php?page=aluno/ficha_inscricao" class="btn btn-primary btn-gradient">
                <i class="bi bi-pencil-square me-1"></i> Nova Inscrição
            </a>
        <?php endif; ?>
    </div>

    <?php if ($nivel !== 'Aluno'): ?>
        <!-- Cards de Métricas -->
        <div class="row g-3 mb-4">
            <?php
                $cards = [
                    ['label' => 'Cursos Cadastrados', 'icon' => 'mortarboard', 'value' => $totalCursos, 'color' => 'primary'],
                    ['label' => 'Total de Inscrições', 'icon' => 'person-plus', 'value' => $totalInscritos, 'color' => 'success'],
                    ['label' => 'Cursos Ativos', 'icon' => 'person-check', 'value' => $totalAtivos, 'color' => 'info'],
					['label' => 'Cursos Concluídos', 'icon' => 'award', 'value' => $totalConcluidos, 'color' => 'warning'],
                ];

                foreach ($cards as $card):
            ?>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="icon-rounded bg-soft-<?= $card['color'] ?> mb-3">
                            <i class="bi bi-<?= $card['icon'] ?> text-<?= $card['color'] ?> fs-4"></i>
                        </div>
                        <h6 class="text-muted mb-2"><?= $card['label'] ?></h6>
                        <h3 class="fw-bold text-<?= $card['color'] ?> mb-0"><?= $card['value'] ?></h3>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Gráfico e Atividades -->
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-3">
                            <i class="bi bi-bar-chart-line text-primary me-2"></i>
                            Inscrições por Curso (Top 5)
                        </h5>
                    </div>
                    <div class="card-body pt-0">
                        <canvas id="graficoInscricoes" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-3">
                            <i class="bi bi-clock-history text-primary me-2"></i>
                            Últimas Atividades
                        </h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-badge bg-success"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">Hoje, 10:45</small>
                                    <p class="mb-0">Curso "Informática Básica" cadastrado</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-badge bg-info"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">Ontem, 15:30</small>
                                    <p class="mb-0">5 novas inscrições registradas</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-badge bg-warning"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">Ontem, 11:20</small>
                                    <p class="mb-0">Certificado emitido para João Silva</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-sm btn-outline-primary rounded-pill px-3">Ver todas</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Painel do Aluno -->
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="bi bi-journal-bookmark text-primary me-2"></i> Meus Cursos Recentes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($meusCursos) > 0): ?>
                            <div class="row g-3">
                                <?php foreach ($meusCursos as $curso): ?>
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow-sm h-100 hover-lift">
                                            <div class="card-header bg-light border-0">
                                                <h6 class="mb-0 text-primary"><?= htmlspecialchars($curso['nome']) ?></h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-2">
                                                    <?php
$hoje = date('Y-m-d');
$status = $curso['status_aluno'];

if ($status === 'matriculado' && $curso['data_inicio'] <= $hoje) {
    $status = 'em andamento';
} elseif ($status === 'em andamento' && $curso['data_termino'] < $hoje) {
    $status = 'concluido';
}

$cor = match($status) {
    'matriculado' => 'primary',
    'em andamento' => 'warning',
    'concluido' => 'success',
    'cancelado' => 'danger',
    default => 'secondary'
};
?>
<span class="badge bg-<?= $cor ?> me-2"><?= ucfirst($status) ?></span>

                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar me-1"></i>
                                                        <?= date('d/m/Y', strtotime($curso['data_inicio'])) ?> - <?= date('d/m/Y', strtotime($curso['data_termino'])) ?>
                                                    </small>
                                                </div>
                                                <div class="progress mb-2" style="height: 6px;">
                                                    <?php
$largura = match($status) {
    'matriculado' => 25,
    'em andamento' => 75,
    'concluido' => 100,
    default => 10
};
?>
<div class="progress-bar bg-<?= $cor ?>" style="width: <?= $largura ?>%"></div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light border d-flex align-items-center">
                                <i class="bi bi-info-circle-fill text-primary me-3 fs-4"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Nenhum curso encontrado</h6>
                                    <p class="mb-0 small">Você ainda não está inscrito em nenhum curso.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white text-center border-0 pt-0">
                        <a href="index.php?page=curso/meus_cursos" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="bi bi-arrow-right me-1"></i> Ver todos os cursos
                        </a>
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
                backgroundColor: 'rgba(13, 110, 253, 0.7)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                borderRadius: 4,
                hoverBackgroundColor: 'rgba(13, 110, 253, 0.9)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#333',
                    titleFont: { size: 14 },
                    bodyFont: { size: 12 },
                    padding: 12,
                    cornerRadius: 4,
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' inscrições';
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    ticks: { 
                        stepSize: 1,
                        font: { size: 12 }
                    },
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 12 } }
                }
            }
        }
    });
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<style>
    .text-gradient {
        background: linear-gradient(90deg, #0d6efd, #20c997);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        display: inline-block;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #0d6efd, #6610f2);
    }
    
    .btn-gradient {
        background: linear-gradient(135deg, #0d6efd, #20c997);
        border: none;
        color: white;
    }
    
    .icon-rounded {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
    }
    
    .timeline {
        position: relative;
        padding-left: 1.5rem;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    
    .timeline-badge {
        position: absolute;
        left: -0.5rem;
        top: 0;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        z-index: 1;
    }
    
    .timeline-content {
        padding-left: 1rem;
    }
    
    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: -0.125rem;
        top: 1rem;
        bottom: 0;
        width: 2px;
        background: #f0f0f0;
    }
</style>