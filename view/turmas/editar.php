<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/Turma.php';
require_once __DIR__ . '/../../models/CursoDisponivel.php';
require_once __DIR__ . '/../../models/Professor.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$turmaModel = new Turma($conn);
$cursoDisponivelModel = new CursoDisponivel($conn);
$professorModel = new Professor($conn);

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);

    $dados = [
        'nome' => $_POST['nome'] ?? '',
        'curso_disponivel_id' => $_POST['curso_disponivel_id'] ?? null,
        'capacidade_maxima' => $_POST['capacidade_maxima'] ?? 0,
        'status' => $_POST['status'] ?? 'planejada',
        'professor_id' => $_POST['professor_id'] ?: null,
        'local' => $_POST['local'] ?? '',
        'alocar_automaticamente' => 0 // se necessário
    ];

    if ($turmaModel->atualizar($id, $dados)) {
        $_SESSION['mensagem_sucesso'] = 'Turma atualizada com sucesso!';
        header("Location: index.php?page=turmas/visualizar&id=$id");
        exit;
    } else {
        $_SESSION['mensagem_erro'] = 'Erro ao atualizar a turma.';
    }
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensagem_erro'] = 'ID da turma não informado.';
    header('Location: index.php?page=turmas/listar');
    exit;
}

$id = (int)$_GET['id'];
$turma = $turmaModel->buscarPorId($id);

if (!$turma) {
    $_SESSION['mensagem_erro'] = 'Turma não encontrada.';
    header('Location: index.php?page=turmas/listar');
    exit;
}

$curso = $cursoDisponivelModel->buscarPorId($turma['curso_disponivel_id']);
$data_inicio = $curso['data_inicio'] ?? '';
$data_fim = $curso['data_termino'] ?? '';
$cursosDisponiveis = $cursoDisponivelModel->listarTodos();
$professores = $professorModel->listarTodos();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4><i class="bi bi-pencil-square"></i> Editar Turma</h4>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['mensagem_erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4">
                    <?= $_SESSION['mensagem_erro'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['mensagem_erro']); ?>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($turma['id']) ?>">
                <input type="hidden" name="curso_disponivel_id" value="<?= $turma['curso_disponivel_id'] ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nome da Turma *</label>
                        <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($turma['nome']) ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Curso *</label>
                        <select class="form-select" disabled>
                            <?php foreach ($cursosDisponiveis as $cursoOpt): ?>
                                <option value="<?= $cursoOpt['id'] ?>" <?= ($cursoOpt['id'] == $turma['curso_disponivel_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cursoOpt['curso_nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Data de Início *</label>
                        <input type="date" class="form-control" value="<?= htmlspecialchars($data_inicio) ?>" readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Data de Término *</label>
                        <input type="date" class="form-control" value="<?= htmlspecialchars($data_fim) ?>" readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Capacidade Máxima *</label>
                        <input type="number" name="capacidade_maxima" class="form-control" value="<?= htmlspecialchars($turma['capacidade_maxima']) ?>" min="1" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="planejada" <?= $turma['status'] === 'planejada' ? 'selected' : '' ?>>Planejada</option>
                            <option value="em_andamento" <?= $turma['status'] === 'em_andamento' ? 'selected' : '' ?>>Em andamento</option>
                            <option value="concluida" <?= $turma['status'] === 'concluida' ? 'selected' : '' ?>>Concluída</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Professor</label>
                        <select name="professor_id" class="form-select">
                            <option value="">Selecione um professor...</option>
                            <?php foreach ($professores as $professor): ?>
                                <option value="<?= $professor['id'] ?>" <?= ($professor['id'] == $turma['professor_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($professor['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Local *</label>
                        <input type="text" name="local" class="form-control" value="<?= htmlspecialchars($turma['local']) ?>" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php?page=turmas/listar" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
