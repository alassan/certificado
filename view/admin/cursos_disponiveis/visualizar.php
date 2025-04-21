<?php
require_once __DIR__ . '/../../../models/CursoDisponivel.php';
require_once __DIR__ . '/../../../models/FichaInscricao.php';
require_once __DIR__ . '/../../../models/conexao.php';

session_start();

// Verifica autenticação
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /certificado/view/dashboard/painel.php');
    exit;
}

// Verifica se o ID foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /certificado/view/admin/cursos_disponiveis/listar.php');
    exit;
}

$id = (int)$_GET['id'];

// Instancia modelos
$conn = Conexao::getConnection();
$cursoDisponivelModel = new CursoDisponivel($conn);
$fichaInscricaoModel = new FichaInscricao($conn);

// Busca o curso disponível com detalhes
$curso = $cursoDisponivelModel->buscarPorId($id);

if (!$curso) {
    header('Location: /certificado/view/admin/cursos_disponiveis/listar.php?erro=Curso não encontrado');
    exit;
}

// Busca inscrições para este curso
$inscricoes = $fichaInscricaoModel->listarPorCursoDisponivel($id);

// Inclui o cabeçalho
include __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="/certificado/view/admin/cursos_disponiveis/listar.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Voltar
        </a>
        <h2 class="mb-0"><i class="fas fa-certificate me-2"></i> Detalhes do Curso</h2>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h4><?= htmlspecialchars($curso['curso_nome']) ?></h4>
                    <?php if (!empty($curso['professor_nome'])): ?>
                        <p class="text-muted">Professor: <?= htmlspecialchars($curso['professor_nome']) ?></p>
                    <?php endif; ?>
                    <p>Empresa Parceira: <?= htmlspecialchars($curso['empresa']) ?></p>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Período do Curso</h5>
                            <p class="card-text">
                                <?= date('d/m/Y', strtotime($curso['data_inicio'])) ?> a 
                                <?= date('d/m/Y', strtotime($curso['data_termino'])) ?>
                            </p>
                            <h5 class="card-title">Período de Inscrições</h5>
                            <p class="card-text">
                                <?= date('d/m/Y', strtotime($curso['inicio_inscricao'])) ?> a 
                                <?= date('d/m/Y', strtotime($curso['termino_inscricao'])) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="mb-3">Inscrições (<?= count($inscricoes) ?>)</h5>
            <?php if (!empty($inscricoes)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Status</th>
                                <th>Data Inscrição</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscricoes as $inscricao): ?>
                            <tr>
                                <td><?= htmlspecialchars($inscricao['nome_aluno']) ?></td>
                                <td><?= htmlspecialchars($inscricao['cpf']) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $inscricao['status'] == 'Aprovado' ? 'success' : 
                                        ($inscricao['status'] == 'Pendente' ? 'warning' : 'danger') 
                                    ?>">
                                        <?= htmlspecialchars($inscricao['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($inscricao['data_inscricao'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Nenhuma inscrição encontrada para este curso.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>