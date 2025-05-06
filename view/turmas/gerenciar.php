<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/Turma.php';
require_once __DIR__ . '/../../models/CursoDisponivel.php';
require_once __DIR__ . '/../../models/FichaInscricao.php';
require_once __DIR__ . '/../../models/FichaInscricao.php';
$fichaModel = new FichaInscricao($conn);
$fichaModel->atualizarStatusAutomaticamente();
require_once __DIR__ . '/../../controllers/GerenciadorTurmas.php';
require_once __DIR__ . '/../includes/_status_badge.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Verificar permissões (exemplo: apenas administradores podem gerenciar)
if ($_SESSION['usuario_nivel'] !== 'Admin') {
    $_SESSION['mensagem_erro'] = 'Você não tem permissão para acessar esta página.';
    header('Location: index.php?page=turmas/listar');
    exit;
}

// Inicializar modelos
$turmaModel = new Turma($conn);
$cursoDisponivelModel = new CursoDisponivel($conn);
$fichaModel = new FichaInscricao($conn);
$gerenciador = new GerenciadorTurmas($conn);

// Processar ações
$acao = $_GET['acao'] ?? '';
$cursoId = $_GET['curso_id'] ?? null;

if ($acao === 'criar_turmas' && $cursoId) {
    $resultado = $gerenciador->criarTurmasAutomaticas($cursoId);
    if ($resultado !== false) {
        $_SESSION['mensagem_sucesso'] = 'Turmas criadas/atualizadas com sucesso!';
    } else {
        $_SESSION['mensagem_erro'] = 'Nenhuma inscrição pendente para alocação.';
    }
    header('Location: index.php?page=turmas/gerenciar');
    exit;
}

// Buscar dados para exibição
$cursosDisponiveis = $cursoDisponivelModel->listarAtivos();
$turmas = $turmaModel->listarTodas();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-gear-fill"></i> Gerenciar Turmas</h2>
        <div>
            <a href="index.php?page=turmas/cadastrar" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nova Turma Manual
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

    <?php if (isset($_SESSION['mensagem_erro'])) : ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['mensagem_erro'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensagem_erro']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5><i class="bi bi-lightning-charge"></i> Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="index.php">
                        <input type="hidden" name="page" value="turmas/gerenciar">
                        <div class="mb-3">
                            <label class="form-label">Criar Turmas Automáticas para:</label>
                            <select name="curso_id" class="form-select" required>
                                <option value="">Selecione um curso...</option>
                                <?php foreach ($cursosDisponiveis as $curso): ?>
                                    <option value="<?= $curso['id'] ?>">
                                        <?= htmlspecialchars($curso['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <input type="hidden" name="acao" value="criar_turmas">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-magic"></i> Criar/Alocar Automaticamente
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5><i class="bi bi-graph-up"></i> Estatísticas</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total de Turmas
                            <span class="badge bg-primary rounded-pill"><?= count($turmas) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Turmas Ativas
                            <span class="badge bg-success rounded-pill">
                                <?= count(array_filter($turmas, fn($t) => getStatusTurma($t['curso_data_inicio'], $t['curso_data_termino']) === 'Em andamento')) ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Turmas com Vagas
                            <span class="badge bg-info rounded-pill">
                                <?= count(array_filter($turmas, fn($t) => ($t['vagas_disponiveis'] ?? 0) > 0)) ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5><i class="bi bi-list-check"></i> Turmas Recentes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Turma</th>
                                    <th>Curso</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($turmas, 0, 5) as $turma): 
                                    $status = getStatusTurma($turma['curso_data_inicio'], $turma['curso_data_termino']);
                                    $statusClass = getStatusClass($status);
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($turma['nome']) ?></td>
                                        <td><?= htmlspecialchars($turma['curso_nome']) ?></td>
                                        <td><span class="badge bg-<?= $statusClass ?>"><?= $status ?></span></td>
                                        <td>
                                            <a href="index.php?page=turmas/visualizar&id=<?= $turma['id'] ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="index.php?page=turmas/listar" class="btn btn-outline-primary">Ver Todas as Turmas</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h5><i class="bi bi-people-fill"></i> Listas de Espera por Curso</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Alunos em Espera</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cursosDisponiveis as $curso): 
                            $espera = $fichaModel->contarListaEsperaPorCurso($curso['id']);
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($curso['nome']) ?></td>
                                <td><?= $espera ?></td>
                                <td>
                                    <?php if ($espera > 0): ?>
                                        <a href="index.php?page=turmas/gerenciar&acao=criar_turmas&curso_id=<?= $curso['id'] ?>" 
                                           class="btn btn-sm btn-success">
                                            <i class="bi bi-magic"></i> Alocar
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
	<div class="text-center mt-4">
      <a href="index.php?page=dashboard/painel" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar ao Painel
      </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>