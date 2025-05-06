<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/Turma.php';
require_once __DIR__ . '/../includes/_status_badge.php';
require_once __DIR__ . '/../../models/FichaInscricao.php';
require_once __DIR__ . '/../includes/status_aluno.php';

$fichaModel = new FichaInscricao($conn);
$fichaModel->atualizarStatusAutomaticamente();
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_GET['id'])) {
    $_SESSION['mensagem_erro'] = 'Turma não encontrada.';
    header('Location: listar.php');
    exit;
}

$id = (int) $_GET['id'];

$turmaModel = new Turma($conn);
$turma = $turmaModel->buscarPorId($id);

if (!$turma) {
    $_SESSION['mensagem_erro'] = 'Turma não encontrada.';
    header('Location: listar.php');
    exit;
}

// Cálculo real de situação
$status = getStatusTurma($turma['curso_data_inicio'], $turma['curso_data_termino']);
$statusClass = getStatusClass($status);

$alunos = $turmaModel->listarAlunosTurma($id);
$lista_espera = $turmaModel->listarListaEsperaTurma($id);

// Informações de vagas e progresso
$matriculados = (int) $turma['alunos_matriculados'];
$capacidade = (int) $turma['capacidade_maxima'];
$vagasDisponiveis = (int) $turma['vagas_disponiveis'];
$percentual = ($capacidade > 0) ? ($matriculados / $capacidade) * 100 : 0;
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-1">
                <i class="bi bi-people-fill me-2"></i><?= htmlspecialchars($turma['nome']) ?>
            </h2>
            <div class="d-flex align-items-center">
                <span class="badge bg-<?= $statusClass ?> rounded-pill fs-6 px-3 py-2 shadow-sm">
                    <i class="bi <?= $status === 'Em andamento' ? 'bi-activity' : ($status === 'Concluída' ? 'bi-check-circle' : 'bi-calendar-event') ?> me-1"></i>
                    <?= $status ?>
                </span>
            </div>
        </div>
        <div>
            <a href="index.php?page=turmas/editar&id=<?= $turma['id'] ?>" class="btn btn-warning btn-lg rounded-pill px-4 shadow-sm">
    <i class="bi bi-pencil-fill me-2"></i>Editar
</a>
            <a href="index.php?page=turmas/listar" class="btn btn-outline-primary btn-lg rounded-pill px-4 ms-2 shadow-sm">
    <i class="bi bi-arrow-left-circle-fill me-2"></i>Voltar
</a>
        </div>
    </div>

    <?php if (isset($_SESSION['mensagem_sucesso'])) : ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                <div><?= $_SESSION['mensagem_sucesso'] ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensagem_sucesso']); ?>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Card Informações da Turma -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-primary text-white rounded-top-3 py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-info-circle-fill me-2"></i>Informações da Turma</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center py-3">
                            <i class="bi bi-bookmark-star-fill text-primary me-3 fs-5"></i>
                            <div>
                                <small class="text-muted d-block">Curso</small>
                                <span class="fw-semibold"><?= htmlspecialchars($turma['curso_nome']) ?></span>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center py-3">
                            <i class="bi bi-calendar-range-fill text-primary me-3 fs-5"></i>
                            <div>
                                <small class="text-muted d-block">Período</small>
                                <span class="fw-semibold">
                                    <?php if (!empty($turma['curso_data_inicio']) && !empty($turma['curso_data_termino'])): ?>
                                        <?= date('d/m/Y', strtotime($turma['curso_data_inicio'])) ?> a <?= date('d/m/Y', strtotime($turma['curso_data_termino'])) ?>
                                    <?php else: ?>
                                        Não informado
                                    <?php endif; ?>
                                </span>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center py-3">
                            <i class="bi bi-geo-alt-fill text-primary me-3 fs-5"></i>
                            <div>
                                <small class="text-muted d-block">Local</small>
                                <span class="fw-semibold"><?= htmlspecialchars($turma['local']) ?></span>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center py-3">
                            <i class="bi bi-person-badge-fill text-primary me-3 fs-5"></i>
                            <div>
                                <small class="text-muted d-block">Professor</small>
                                <span class="fw-semibold"><?= $turma['professor_nome'] ? htmlspecialchars($turma['professor_nome']) : 'Não definido' ?></span>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center py-3">
                            <i class="bi bi-building-fill text-primary me-3 fs-5"></i>
                            <div>
                                <small class="text-muted d-block">Empresa Parceira</small>
                                <span class="fw-semibold"><?= $turma['empresa_nome'] ? htmlspecialchars($turma['empresa_nome']) : 'Não definida' ?></span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Card Vagas -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-primary text-white rounded-top-3 py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-graph-up-arrow me-2"></i>Ocupação da Turma</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 shadow-sm">
                                <small class="text-muted d-block">Capacidade</small>
                                <h2 class="fw-bold text-primary mb-0"><?= $capacidade ?></h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 shadow-sm">
                                <small class="text-muted d-block">Matriculados</small>
                                <h2 class="fw-bold text-success mb-0"><?= $matriculados ?></h2>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 shadow-sm">
                                <small class="text-muted d-block">Disponíveis</small>
                                <h2 class="fw-bold <?= $vagasDisponiveis <= 0 ? 'text-danger' : 'text-success' ?> mb-0">
                                    <?= $vagasDisponiveis ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Progresso de ocupação</small>
                            <small class="fw-semibold"><?= number_format($percentual, 1) ?>%</small>
                        </div>
                        <div class="progress rounded-pill" style="height: 20px;">
                            <div class="progress-bar bg-gradient-success progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: <?= $percentual ?>%" 
                                 aria-valuenow="<?= $percentual ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Alunos Matriculados -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-primary text-white rounded-top-3 py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-person-check-fill me-2"></i>Alunos Matriculados</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($alunos)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nome</th>
                                        <th>CPF</th>
                                        <th>Status</th>
                                        <th class="text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alunos as $aluno): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar bg-light-primary rounded-circle me-3">
                                                        <i class="bi bi-person-fill fs-5"></i>
                                                    </div>
                                                    <?= htmlspecialchars($aluno['nome_aluno']) ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($aluno['cpf']) ?></td>
                                            <td>
    <?php 
        $label = StatusAluno::getLabel($aluno['status']);
        $classe = StatusAluno::getBadgeClass($aluno['status']);
    ?>
    <span class="badge bg-<?= $classe ?> rounded-pill">
        <?= $label ?>
    </span>
</td>

                                            <td class="text-end">
                                                <div class="btn-group" role="group">
                                                    <a href="#" class="btn btn-sm btn-outline-primary rounded-start-pill">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-outline-danger rounded-end-pill">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info rounded-3 shadow-sm mb-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                                <div>Nenhum aluno matriculado nesta turma.</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card Lista de Espera -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-warning text-white rounded-top-3 py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2"></i>Lista de Espera</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($lista_espera)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nome</th>
                                        <th>CPF</th>
                                        <th>Inscrição</th>
                                        <th class="text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lista_espera as $aluno): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar bg-light-warning rounded-circle me-3">
                                                        <i class="bi bi-hourglass-top fs-5"></i>
                                                    </div>
                                                    <?= htmlspecialchars($aluno['nome_aluno']) ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($aluno['cpf']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($aluno['data_inscricao'])) ?></td>
                                            <td class="text-end">
                                                <?php if ($vagasDisponiveis > 0): ?>
                                                    <form method="post" action="alocar_aluno.php" class="d-inline">
                                                        <input type="hidden" name="turma_id" value="<?= $id ?>">
                                                        <input type="hidden" name="ficha_id" value="<?= $aluno['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                                            <i class="bi bi-person-plus-fill me-1"></i> Matricular
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary rounded-pill">Aguardando vaga</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info rounded-3 shadow-sm mb-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                                <div>Nenhum aluno na lista de espera para esta turma.</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-gradient-success {
        background: linear-gradient(90deg, #2b8a3e, #51cf66);
    }
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
    .progress-bar-animated {
        animation: progress-bar-stripes 1s linear infinite;
    }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>