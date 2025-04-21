<?php
if (!isset($cursos)) {
    require_once __DIR__ . '/../../../conexao.php';
    require_once __DIR__ . '/../../../models/CursoDisponivel.php';
    require_once __DIR__ . '/../../../models/FichaInscricao.php';

    $cursoDisponivelModel = new CursoDisponivel($conn);
    $fichaInscricaoModel = new FichaInscricao($conn);
    $cursos = $cursoDisponivelModel->listarTodos();
}
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="container container-fluid mt-4">
    <!-- Botão Voltar -->
    <div class="mb-3">
        <a href="/certificado/view/dashboard/painel.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Voltar para o Painel
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>Cursos Disponíveis
                </h4>
                <a href="/certificado/view/admin/cursos_disponiveis/cadastrar.php" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i> Novo Curso
                </a>
            </div>
        </div>

        <div class="card-body">
            <?php include __DIR__ . '/../../includes/mensagem.php'; ?>

            <div class="table-responsive">
                <table class="table table-hover" id="tabela-cursos-disponiveis">
                    <thead class="table-light">
                        <tr>
                            <th>Curso</th>
                            <th>Período</th>
                            <th>Inscrições</th>
                            <th>Empresa</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($cursos)): ?>
                            <?php foreach ($cursos as $curso): 
                                $temInscricoes = $fichaInscricaoModel->verificarInscricoesCurso($curso['id']);
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($curso['curso_nome']) ?></strong>
                                    <?php if (!empty($curso['professor_nome'])): ?>
                                        <p class="text-muted small mb-0">Professor: <?= htmlspecialchars($curso['professor_nome']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($curso['data_inicio'])) ?> a 
                                    <?= date('d/m/Y', strtotime($curso['data_termino'])) ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($curso['inicio_inscricao'])) ?> a 
                                    <?= date('d/m/Y', strtotime($curso['termino_inscricao'])) ?>
                                </td>
                                <td><?= htmlspecialchars($curso['empresa']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/certificado/view/admin/cursos_disponiveis/editar.php?id=<?= $curso['id'] ?>" 
                                           class="btn btn-outline-primary" 
                                           title="Editar"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-danger <?= $temInscricoes ? 'disabled' : '' ?>" 
                                                title="<?= $temInscricoes ? 'Curso com inscrições - não pode ser excluído' : 'Excluir' ?>"
                                                data-bs-toggle="tooltip"
                                                onclick="<?= !$temInscricoes ? 'confirmarExclusao('.$curso['id'].')' : '' ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <a href="/certificado/view/admin/cursos_disponiveis/visualizar.php?id=<?= $curso['id'] ?>" 
                                           class="btn btn-outline-success" 
                                           title="Detalhes do Curso"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-certificate"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-calendar-times fa-2x mb-3"></i>
                                    <p class="mb-0">Nenhum curso disponível encontrado.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa DataTable
    $('#tabela-cursos-disponiveis').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
        },
        columnDefs: [
            { orderable: false, targets: [4] }
        ],
        dom: '<"top"<"row"<"col-md-6"l><"col-md-6"f>>>rt<"bottom"<"row"<"col-md-6"i><"col-md-6"p>>>',
        initComplete: function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    // Ativa tooltips do Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function confirmarExclusao(id) {
    Swal.fire({
        title: 'Confirmar Exclusão',
        text: 'Tem certeza que deseja excluir este curso disponível?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../../../controllers/CursoDisponivelController.php?acao=remover&id=' + id;
        }
    });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>