<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/Turma.php';
require_once __DIR__ . '/../../models/CursoDisponivel.php';
require_once __DIR__ . '/../../models/Professor.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define BASE_URL se não estiver definido
if (!defined('BASE_URL')) {
    define('BASE_URL', '/certificado/');
}

require_once __DIR__ . '/../includes/header.php';

// Buscar cursos disponíveis ATIVOS
$cursoDisponivelModel = new CursoDisponivel($conn);
$cursosDisponiveis = $cursoDisponivelModel->listarAtivos();

// Buscar professores
$professorModel = new Professor($conn);
$professores = $professorModel->listarTodos();
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4><i class="bi bi-people-fill"></i> Cadastrar Nova Turma</h4>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>controllers/TurmaController.php?acao=salvar" method="POST">
                <?php if (isset($_SESSION['mensagem_erro'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4">
                        <?= $_SESSION['mensagem_erro'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['mensagem_erro']); ?>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nome da Turma *</label>
                        <input type="text" name="nome" class="form-control" 
                               value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" 
                               required placeholder="Ex: Turma A">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Curso Disponível *</label>
                        <select name="curso_disponivel_id" class="form-select" required>
                            <option value="">Selecione um curso...</option>
                            <?php foreach ($cursosDisponiveis as $curso): ?>
                                <option value="<?= htmlspecialchars($curso['id']) ?>"
                                    <?= (isset($_POST['curso_disponivel_id']) && $_POST['curso_disponivel_id'] == $curso['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($curso['nome']) ?> (<?= $curso['carga_horaria'] ?? 'N/A' ?>h)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Capacidade Máxima *</label>
                        <input type="number" name="capacidade_maxima" class="form-control" 
                               value="<?= htmlspecialchars($_POST['capacidade_maxima'] ?? '10') ?>" 
                               min="1" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Professor</label>
                        <select name="professor_id" class="form-select">
                            <option value="">Selecione um professor...</option>
                            <?php foreach ($professores as $professor): ?>
                                <option value="<?= htmlspecialchars($professor['id']) ?>"
                                    <?= (isset($_POST['professor_id']) && $_POST['professor_id'] == $professor['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($professor['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Local *</label>
                        <input type="text" name="local" class="form-control" 
                               value="<?= htmlspecialchars($_POST['local'] ?? '') ?>" 
                               required placeholder="Ex: Sala de Aula 1">
                    </div>

                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   name="alocar_automaticamente" id="alocar_automaticamente"
                                   <?= (isset($_POST['alocar_automaticamente']) && $_POST['alocar_automaticamente']) ? 'checked' : 'checked' ?>>
                            <label class="form-check-label" for="alocar_automaticamente">
                                Alocar alunos automaticamente da lista de espera (se houver)
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php?page=turmas/listar" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar Turma
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>