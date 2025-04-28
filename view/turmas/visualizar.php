<?php
require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../../models/Turma.php';
require_once __DIR__ . '/../includes/_status_badge.php';

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

$status = getStatusTurma($turma['curso_data_inicio'], $turma['curso_data_termino']);
$statusClass = getStatusClass($status);

$alunos = $turmaModel->listarAlunosTurma($id);
$lista_espera = $turmaModel->listarListaEsperaTurma($id);
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-people-fill"></i> Turma: <?= htmlspecialchars($turma['nome']) ?>
            <span class="badge bg-<?= $statusClass ?> ms-2"> <?= $status ?> </span>
        </h2>
        <div>
            <a href="editar.php?id=<?= $turma['id'] ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="listar.php" class="btn btn-secondary ms-2">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['mensagem_sucesso'])) : ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['mensagem_sucesso'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensagem_sucesso']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5><i class="bi bi-info-circle"></i> Informações da Turma</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Curso:</strong> <?= htmlspecialchars($turma['curso_nome']) ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Período:</strong> 
                            <?php if (!empty($turma['curso_data_inicio']) && !empty($turma['curso_data_termino'])): ?>
                                <?= date('d/m/Y', strtotime($turma['curso_data_inicio'])) ?> a <?= date('d/m/Y', strtotime($turma['curso_data_termino'])) ?>
                            <?php else: ?>
                                Não informado
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Local:</strong> <?= htmlspecialchars($turma['local']) ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Professor:</strong> 
                            <?= $turma['professor_nome'] ? htmlspecialchars($turma['professor_nome']) : 'Não definido' ?>
                        </li>
                        <li class="list-group-item">
                          <strong>Empresa Parceira:</strong> 
                          <?= $turma['empresa_nome'] ? htmlspecialchars($turma['empresa_nome']) : 'Não definida' ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5><i class="bi bi-graph-up"></i> Vagas</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <strong>Capacidade:</strong> <?= $turma['capacidade_maxima'] ?>
                        </div>
                        <div>
                            <strong>Vagas disponíveis:</strong> <?= $turma['vagas_disponiveis'] ?>
                        </div>
                    </div>
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: <?= (($turma['capacidade_maxima'] - $turma['vagas_disponiveis']) / $turma['capacidade_maxima']) * 100 ?>%" 
                             aria-valuenow="<?= $turma['capacidade_maxima'] - $turma['vagas_disponiveis'] ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="<?= $turma['capacidade_maxima'] ?>">
                            <?= $turma['capacidade_maxima'] - $turma['vagas_disponiveis'] ?> alunos matriculados
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
