<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/CursoDisponivel.php';
require_once __DIR__ . '/../includes/status_aluno.php';
require_once __DIR__ . '/../../models/FichaInscricao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /login/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$status = $_GET['status'] ?? 'matriculado';

$model = new CursoDisponivel($conn);
$fichaModel = new FichaInscricao($conn);
$fichas = $fichaModel->buscarTodasPorUsuarioEStatusAgrupado($usuario_id, $status);
$cursos = [];

foreach ($fichas as $ficha) {
    $curso = $model->buscarPorId($ficha['curso_disponivel_id']);
    if ($curso) {
        $curso['curso_disponivel_id'] = $ficha['curso_disponivel_id'];
        $curso['status_aluno'] = $ficha['status_calculado'];
        $curso['turma_nome'] = $ficha['turma_nome'] ?? null;
        $curso['professor_nome'] = $ficha['professor_nome'] ?? null;
        $curso['empresa_nome'] = $ficha['empresa_nome'] ?? null;
        $cursos[] = $curso;
    }
}

$tituloPagina = "Meus Cursos";
$tituloStatus = ucfirst(str_replace('_', ' ', $status));

include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-book-reader me-2"></i><?= $tituloPagina ?>
                <span class="badge bg-light text-dark ms-2"> <?= $tituloStatus ?> </span>
            </h4>
        </div>

        <div class="card-body">
            <div class="mb-3 text-center">
                <?php
                $statuses = ['matriculado', 'em_andamento', 'concluido', 'cancelado', 'espera'];
                foreach ($statuses as $item) {
                    $label = ucfirst(str_replace('_', ' ', $item));
                    $btnClass = $status === $item ? 'active' : '';
                    echo "<a href=\"index.php?page=curso/meus_cursos&status=$item\" class=\"btn btn-outline-" .
                         ($item === 'espera' ? 'warning' : ($item === 'concluido' ? 'success' : ($item === 'cancelado' ? 'secondary' : 'primary'))) .
                         " $btnClass me-1\">$label</a>";
                }
                ?>
            </div>

            <?php if (!empty($cursos)): ?>
                <div class="row">
                    <?php foreach ($cursos as $curso):
                        $isEspera = $curso['status_aluno'] === 'espera';
                    ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-<?= $isEspera ? 'warning' : 'primary' ?> shadow-sm">
                                <div class="card-header fw-bold text-<?= $isEspera ? 'dark' : 'primary' ?>">
                                    <?= htmlspecialchars($curso['curso_nome']) ?>
                                    <?php if ($isEspera): ?>
                                        <span class="badge bg-warning float-end"><i class="bi bi-clock"></i> Espera</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <p><strong>Período:</strong>
                                        <?= date('d/m/Y', strtotime($curso['data_inicio'])) ?> a
                                        <?= date('d/m/Y', strtotime($curso['data_termino'])) ?>
                                    </p>
                                    <p><strong>Professor:</strong>
                                        <?= htmlspecialchars($curso['professor_nome'] ?? 'Não informado') ?>
                                    </p>
                                    <p><strong>Turma:</strong>
                                        <?= htmlspecialchars($curso['turma_nome'] ?? 'Não informada') ?>
                                        <?php if ($isEspera): ?>
                                            <span class="badge bg-warning">Aguardando vaga</span>
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>Empresa:</strong>
                                        <?= htmlspecialchars($curso['empresa_nome'] ?? 'Não informada') ?>
                                    </p>
                                    <p><strong>Situação:</strong>
                                        <span class="badge bg-<?= StatusAluno::getBadgeClass($curso['status_aluno']) ?>">
                                            <i class="bi bi-<?= $isEspera ? 'clock' : 'check-circle' ?>"></i>
                                            <?= ucfirst($curso['status_aluno']) ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    Nenhum curso encontrado com o status <strong><?= $tituloStatus ?></strong>.
                </div>
            <?php endif; ?>

            <div class="text-end mt-3">
                <a href="index.php?page=dashboard/painel" class="btn btn-dark">
                    <i class="bi bi-arrow-left"></i> Voltar ao Painel
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
