<?php
require_once __DIR__ . '/../../../models/CursoDisponivel.php';
require_once __DIR__ . '/../../../models/Curso.php';
require_once __DIR__ . '/../../../models/Professor.php';
require_once __DIR__ . '/../../../models/conexao.php';

session_start();

// Verifica autenticação e permissões
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['Admin', 'Funcionário'])) {
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
$cursoModel = new Curso($conn);
$professorModel = new Professor($conn);

// Busca o curso disponível
$cursoDisponivel = $cursoDisponivelModel->buscarPorId($id);

if (!$cursoDisponivel) {
    header('Location: /certificado/view/admin/cursos_disponiveis/listar.php?erro=Curso não encontrado');
    exit;
}

// Obtém dados para os selects
$cursos = $cursoModel->listarTodos();
$professores = $professorModel->listarTodos();

// Inclui o cabeçalho
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
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form id="form-curso-disponivel" action="/certificado/controllers/CursoDisponivelController.php?acao=atualizar" method="POST">
                <input type="hidden" name="id" value="<?= $cursoDisponivel['id'] ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="curso_id" class="form-label">Curso *</label>
                        <select id="curso_id" name="curso_id" class="form-select" required>
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
                        <select id="professor_id" name="professor_id" class="form-select">
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
                        <input type="date" id="inicio_inscricao" name="inicio_inscricao" class="form-control" 
                               value="<?= htmlspecialchars($cursoDisponivel['inicio_inscricao']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="termino_inscricao" class="form-label">Término das Inscrições *</label>
                        <input type="date" id="termino_inscricao" name="termino_inscricao" class="form-control" 
                               value="<?= htmlspecialchars($cursoDisponivel['termino_inscricao']) ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="data_inicio" class="form-label">Data de Início do Curso *</label>
                        <input type="date" id="data_inicio" name="data_inicio" class="form-control" 
                               value="<?= htmlspecialchars($cursoDisponivel['data_inicio']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="data_termino" class="form-label">Data de Término do Curso *</label>
                        <input type="date" id="data_termino" name="data_termino" class="form-control" 
                               value="<?= htmlspecialchars($cursoDisponivel['data_termino']) ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="empresa" class="form-label">Empresa Parceira *</label>
                    <input type="text" id="empresa" name="empresa" class="form-control" 
                           value="<?= htmlspecialchars($cursoDisponivel['empresa']) ?>" placeholder="Nome da empresa responsável" required>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-3">
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validação de datas (similar ao cadastrar.php)
    document.getElementById('form-curso-disponivel').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const inicioInscricao = new Date(document.getElementById('inicio_inscricao').value);
        const terminoInscricao = new Date(document.getElementById('termino_inscricao').value);
        const dataInicio = new Date(document.getElementById('data_inicio').value);
        const dataTermino = new Date(document.getElementById('data_termino').value);

        if (inicioInscricao > terminoInscricao) {
            Swal.fire({
                icon: 'error',
                title: 'Datas inválidas',
                text: 'O término das inscrições deve ser após a data de início.',
                confirmButtonColor: '#0d6efd'
            });
            return false;
        }

        if (dataInicio > dataTermino) {
            Swal.fire({
                icon: 'error',
                title: 'Datas inválidas',
                text: 'O término do curso deve ser após a data de início.',
                confirmButtonColor: '#0d6efd'
            });
            return false;
        }

        if (terminoInscricao > dataInicio) {
            Swal.fire({
                icon: 'error',
                title: 'Datas inválidas',
                text: 'O curso deve começar após o término do período de inscrições.',
                confirmButtonColor: '#0d6efd'
            });
            return false;
        }

        this.submit();
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>