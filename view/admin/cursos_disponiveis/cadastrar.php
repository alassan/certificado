<?php
// Inicia sessão, se necessário
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Acesso restrito a Admin e Funcionário
if (!isset($_SESSION['usuario_nivel']) || !in_array($_SESSION['usuario_nivel'], ['Admin', 'Funcionário'])) {
    header('Location: /certificado/dashboard/painel.php');
    exit;
}

// Configurações e conexões
$base_path = '/certificado';
require_once $_SERVER['DOCUMENT_ROOT'] . "$base_path/models/conexao.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "$base_path/models/Curso.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "$base_path/models/CursoDisponivel.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "$base_path/models/Empresa.php";

$conn = Conexao::getConnection();
$cursoModel = new Curso($conn);
$cursoDisponivelModel = new CursoDisponivel($conn);
$empresaModel = new Empresa($conn);

$cursos = $cursoModel->listarTodos();
$empresas = $empresaModel->listar();

include $_SERVER['DOCUMENT_ROOT'] . "$base_path/view/includes/header.php";
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="<?= $base_path ?>/view/admin/cursos_disponiveis/listar.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Voltar
        </a>
        <h2><i class="fas fa-plus-circle me-2"></i> Cadastrar Curso Disponível</h2>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_GET['erro']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    Curso lançado com sucesso!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form id="form-curso-disponivel" action="<?= $base_path ?>/controllers/CursoDisponivelController.php?acao=salvar" method="POST" novalidate>
                <div class="mb-3">
                    <label for="curso_id" class="form-label">Curso *</label>
                    <select id="curso_id" name="curso_id" class="form-select" required>
                        <option value="">Selecione um curso</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?= $curso['id'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="empresa_id" class="form-label">Empresa Parceira *</label>
                    <select id="empresa_id" name="empresa_id" class="form-select" required>
                        <option value="">Selecione uma empresa</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>"><?= htmlspecialchars($empresa['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="inicio_inscricao" class="form-label">Início das Inscrições *</label>
                        <input type="date" id="inicio_inscricao" name="inicio_inscricao" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="termino_inscricao" class="form-label">Término das Inscrições *</label>
                        <input type="date" id="termino_inscricao" name="termino_inscricao" class="form-control" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="data_inicio" class="form-label">Data de Início do Curso *</label>
                        <input type="date" id="data_inicio" name="data_inicio" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="data_termino" class="form-label">Data de Término do Curso *</label>
                        <input type="date" id="data_termino" name="data_termino" class="form-control" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between border-top pt-3">
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Limpar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Salvar Curso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS de validação -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const hoje = new Date().toISOString().split('T')[0];
    ['inicio_inscricao', 'termino_inscricao', 'data_inicio', 'data_termino'].forEach(id => {
        document.getElementById(id).min = hoje;
    });

    document.getElementById('form-curso-disponivel').addEventListener('submit', function(e) {
        const inicioInscricao = new Date(this.inicio_inscricao.value);
        const terminoInscricao = new Date(this.termino_inscricao.value);
        const dataInicio = new Date(this.data_inicio.value);
        const dataTermino = new Date(this.data_termino.value);
        const hoje = new Date(); hoje.setHours(0, 0, 0, 0);

        if (inicioInscricao > terminoInscricao) {
            e.preventDefault();
            return alerta('O término das inscrições deve ser após o início.');
        }
        if (dataInicio > dataTermino) {
            e.preventDefault();
            return alerta('O término do curso deve ser após o início.');
        }
        if (terminoInscricao > dataInicio) {
            e.preventDefault();
            return alerta('O curso deve iniciar após o término das inscrições.');
        }
        if (inicioInscricao < hoje) {
            e.preventDefault();
            return alerta('A inscrição não pode começar no passado.');
        }

        function alerta(msg) {
            Swal.fire({
                icon: 'error',
                title: 'Datas inválidas',
                text: msg,
                confirmButtonColor: '#0d6efd'
            });
        }
    });
});
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . "$base_path/view/includes/footer.php"; ?>
