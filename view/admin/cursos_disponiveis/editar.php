<?php
require_once __DIR__ . '/../../../config/conexao.php';
require_once __DIR__ . '/../../../models/CursoDisponivel.php';
require_once __DIR__ . '/../../../models/Curso.php';
require_once __DIR__ . '/../../../models/Professor.php';
require_once __DIR__ . '/../../../models/Empresa.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['Admin', 'Funcionário'])) {
    header('Location: /certificado/view/dashboard/painel.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /certificado/view/admin/cursos_disponiveis/listar.php');
    exit;
}

$id = (int)$_GET['id'];

$cursoDisponivelModel = new CursoDisponivel($conn);
$cursoModel = new Curso($conn);
$professorModel = new Professor($conn);
$empresaModel = new Empresa($conn);

$cursoDisponivel = $cursoDisponivelModel->buscarPorId($id);

if (!$cursoDisponivel) {
    header('Location: /certificado/view/admin/cursos_disponiveis/listar.php?erro=Curso não encontrado');
    exit;
}

$cursos = $cursoModel->listarTodos();
$professores = $professorModel->listarTodos();
$empresas = $empresaModel->listarAtivas();

include __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="/certificado/view/admin/cursos_disponiveis/listar.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Voltar
        </a>
        <h2 class="mb-0"><i class="fas fa-edit me-2"></i> Editar Curso Disponível</h2>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_GET['erro']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form id="form-curso-disponivel" action="/certificado/controllers/CursoDisponivelController.php?acao=atualizar" method="POST">
                <input type="hidden" name="id" value="<?= $cursoDisponivel['id'] ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="curso_id" class="form-label">Curso *</label>
                        <select name="curso_id" id="curso_id" class="form-select" required>
                            <option value="">Selecione um curso</option>
                            <?php foreach ($cursos as $curso): ?>
                                <option value="<?= $curso['id'] ?>" <?= $curso['id'] == $cursoDisponivel['curso_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($curso['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="professor_id" class="form-label">Professor</label>
                        <select name="professor_id" id="professor_id" class="form-select">
                            <option value="">Selecione um professor (opcional)</option>
                            <?php foreach ($professores as $prof): ?>
                                <option value="<?= $prof['id'] ?>" <?= $prof['id'] == $cursoDisponivel['professor_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prof['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="inicio_inscricao" class="form-label">Início das Inscrições *</label>
                        <input type="date" name="inicio_inscricao" id="inicio_inscricao" class="form-control"
                            value="<?= htmlspecialchars($cursoDisponivel['inicio_inscricao']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="termino_inscricao" class="form-label">Término das Inscrições *</label>
                        <input type="date" name="termino_inscricao" id="termino_inscricao" class="form-control"
                            value="<?= htmlspecialchars($cursoDisponivel['termino_inscricao']) ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="data_inicio" class="form-label">Data de Início do Curso *</label>
                        <input type="date" name="data_inicio" id="data_inicio" class="form-control"
                            value="<?= htmlspecialchars($cursoDisponivel['data_inicio']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="data_termino" class="form-label">Data de Término do Curso *</label>
                        <input type="date" name="data_termino" id="data_termino" class="form-control"
                            value="<?= htmlspecialchars($cursoDisponivel['data_termino']) ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="empresa_id" class="form-label">Empresa Parceira *</label>
                    <select name="empresa_id" id="empresa_id" class="form-select" required>
                        <option value="">Selecione uma empresa</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>" <?= $empresa['id'] == $cursoDisponivel['empresa_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($empresa['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-between border-top pt-3">
                    <button type="reset" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-times me-1"></i> Limpar
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Atualizar Curso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('form-curso-disponivel').addEventListener('submit', function (e) {
    const inicio = new Date(document.getElementById('inicio_inscricao').value);
    const termino = new Date(document.getElementById('termino_inscricao').value);
    const dataIni = new Date(document.getElementById('data_inicio').value);
    const dataFim = new Date(document.getElementById('data_termino').value);

    if (inicio > termino || termino > dataIni || dataIni > dataFim) {
        e.preventDefault();
        alert("Verifique as datas: o curso deve começar após o término da inscrição e terminar depois que começar.");
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
