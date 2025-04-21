<?php
// Inicia a sessão se não estiver ativa
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Verifica permissões de acesso
if (!isset($_SESSION['usuario_nivel']) || !in_array($_SESSION['usuario_nivel'], ['Admin', 'Funcionário'])) {
    header('Location: /certificado/dashboard/painel.php');
    exit;
}

// Define o caminho base
$base_path = '/certificado';

// Inclui arquivos necessários com caminho absoluto
require_once $_SERVER['DOCUMENT_ROOT'] . '/certificado/models/conexao.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/certificado/models/Curso.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/certificado/models/Professor.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/certificado/models/CursoDisponivel.php';

// CORREÇÃO DA LINHA 23 - Obtém a conexão corretamente
$conn = Conexao::getConnection(); // Alterado de getInstance() para getConnection()

// Instancia modelos
$cursoModel = new Curso($conn);
$professorModel = new Professor($conn);
$cursoDisponivelModel = new CursoDisponivel($conn);

// Obtém dados para os selects
$cursos = $cursoModel->listarTodos();
$professores = $professorModel->listarTodos();

// Restante do código permanece igual...


// Inclui o cabeçalho
include $_SERVER['DOCUMENT_ROOT'] . '/certificado/view/includes/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="<?= $base_path ?>/view/admin/cursos_disponiveis/listar.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Voltar
        </a>
        <h2 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Cadastrar Curso Disponível</h2>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <!-- Mensagens de feedback -->
            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_GET['erro']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    Curso lançado com sucesso!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Formulário -->
            <form id="form-curso-disponivel" action="<?= $base_path ?>/controllers/CursoDisponivelController.php?acao=salvar" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="curso_id" class="form-label">Curso *</label>
                        <select id="curso_id" name="curso_id" class="form-select" required>
                            <option value="">Selecione um curso</option>
                            <?php foreach ($cursos as $curso): ?>
                                <option value="<?= $curso['id'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="professor_id" class="form-label">Professor</label>
                        <select id="professor_id" name="professor_id" class="form-select">
                            <option value="">Selecione um professor (opcional)</option>
                            <?php foreach ($professores as $prof): ?>
                                <option value="<?= $prof['id'] ?>"><?= htmlspecialchars($prof['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="inicio_inscricao" class="form-label">Início das Inscrições *</label>
                        <input type="date" id="inicio_inscricao" name="inicio_inscricao" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="termino_inscricao" class="form-label">Término das Inscrições *</label>
                        <input type="date" id="termino_inscricao" name="termino_inscricao" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="data_inicio" class="form-label">Data de Início do Curso *</label>
                        <input type="date" id="data_inicio" name="data_inicio" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="data_termino" class="form-label">Data de Término do Curso *</label>
                        <input type="date" id="data_termino" name="data_termino" class="form-control" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="empresa" class="form-label">Empresa Parceira *</label>
                    <input type="text" id="empresa" name="empresa" class="form-control" placeholder="Nome da empresa responsável" required>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <button type="reset" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-times me-1"></i> Limpar
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Salvar Curso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hoje = new Date();
        const hojeISO = hoje.toISOString().split('T')[0];
        
        // Configura os valores mínimos para os campos de data
        document.getElementById('inicio_inscricao').min = hojeISO;
        document.getElementById('termino_inscricao').min = hojeISO;
        document.getElementById('data_inicio').min = hojeISO;
        document.getElementById('data_termino').min = hojeISO;

        // Formatação automática para campos de data
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.value) {
                    const parts = this.value.split('-');
                    this.value = `${parts[0]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`;
                }
            });
        });
    });

    document.getElementById('form-curso-disponivel').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Obtém os valores das datas
        const inicioInscricao = new Date(document.getElementById('inicio_inscricao').value);
        const terminoInscricao = new Date(document.getElementById('termino_inscricao').value);
        const dataInicio = new Date(document.getElementById('data_inicio').value);
        const dataTermino = new Date(document.getElementById('data_termino').value);
        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0);

        // Validações
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

        if (inicioInscricao < hoje) {
            Swal.fire({
                icon: 'warning',
                title: 'Data passada',
                text: 'A data de início das inscrições não pode ser anterior ao dia atual.',
                confirmButtonColor: '#0d6efd'
            });
            return false;
        }

        // Se todas as validações passarem, envia o formulário
        this.submit();
    });
</script>

<?php 
include $_SERVER['DOCUMENT_ROOT'] . '/certificado/view/includes/footer.php'; 
?>