<?php
require_once __DIR__ . '/../../../config/conexao.php';
require_once __DIR__ . '/../../../models/CursoDisponivel.php';
require_once __DIR__ . '/../../../models/FichaInscricao.php';

$cursoDisponivelModel = new CursoDisponivel($conn);
$fichaInscricaoModel = new FichaInscricao($conn);
$cursos = $cursoDisponivelModel->listarTodos();
?>

<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">
            <i class="fas fa-calendar-alt me-2"></i> Cursos Disponíveis
        </h2>
        <div>
            <a href="/certificado/view/admin/cursos_disponiveis/cadastrar.php" class="btn btn-success shadow-sm">
                <i class="fas fa-plus me-1"></i> Novo Curso
            </a>
            <a href="/certificado/index.php?page=dashboard/painel" class="btn btn-outline-secondary shadow-sm ms-2">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php include __DIR__ . '/../../includes/mensagem.php'; ?>

            <div class="table-responsive rounded-3 overflow-hidden">
                <table class="table table-striped table-hover align-middle mb-0" id="tabela-cursos-disponiveis">
                    <thead class="table-primary">
                        <tr>
                            <th class="ps-4">Curso</th>
                            <th>Período</th>
                            <th>Inscrições</th>
                            <th>Empresa</th>
                            <th class="text-center pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($cursos)): ?>
                            <?php foreach ($cursos as $curso): 
                                $cursoId = $curso['id'];
                                $temInscricoes = $fichaInscricaoModel->verificarInscricoesCurso($cursoId);
                                $temTurmas = method_exists($cursoDisponivelModel, 'temTurmas') ? $cursoDisponivelModel->temTurmas($cursoId) : false;
                                $temTopicos = method_exists($cursoDisponivelModel, 'temTopicos') ? $cursoDisponivelModel->temTopicos($cursoId) : false;
                                $podeExcluir = !$temInscricoes && !$temTurmas && !$temTopicos;

                                $nomeCurso = $curso['curso_nome'] ?? '<span class="text-muted">Indefinido</span>';
                                $nomeEmpresa = $curso['empresa_nome'] ?? 'Não informado';
                            ?>
                            <tr class="border-bottom">
                                <td class="ps-4"><strong><?= htmlspecialchars($nomeCurso) ?></strong></td>
                                <td>
                                    <span class="d-block"><?= date('d/m/Y', strtotime($curso['data_inicio'])) ?></span>
                                    <small class="text-muted">até <?= date('d/m/Y', strtotime($curso['data_termino'])) ?></small>
                                </td>
                                <td>
                                    <span class="d-block"><?= date('d/m/Y', strtotime($curso['inicio_inscricao'])) ?></span>
                                    <small class="text-muted">até <?= date('d/m/Y', strtotime($curso['termino_inscricao'])) ?></small>
                                </td>
                                <td><?= htmlspecialchars($nomeEmpresa) ?></td>
                                <td class="text-center pe-4">
                                    <div class="btn-group btn-group-sm shadow-sm" role="group">
                                        <a href="editar.php?id=<?= $cursoId ?>" class="btn btn-outline-primary rounded-start" title="Editar" data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <a href="index.php?page=admin/cursos_disponiveis/visualizar&id=<?= $cursoId ?>" 
                                           class="btn btn-outline-success" 
                                           title="Detalhes"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <button type="button"
                                            class="btn btn-outline-danger rounded-end <?= !$podeExcluir ? 'disabled' : '' ?>"
                                            title="<?= !$podeExcluir ? 'Curso vinculado a inscrições, turmas ou tópicos' : 'Excluir' ?>"
                                            onclick="<?= $podeExcluir ? 'confirmarExclusao('.$cursoId.')' : '' ?>"
                                            data-bs-toggle="tooltip">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-calendar-times fa-3x text-muted mb-3 opacity-50"></i>
                                        <h5 class="text-muted mb-2">Nenhum curso disponível</h5>
                                        <p class="text-muted small">Cadastre um novo curso para começar</p>
                                    </div>
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
    $('#tabela-cursos-disponiveis').DataTable({
        language: { 
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' 
        },
        columnDefs: [ 
            { orderable: false, targets: [4] },
            { className: "text-nowrap", targets: [1, 2] }
        ],
        dom: '<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
        initComplete: () => { 
            $('[data-bs-toggle="tooltip"]').tooltip(); 
        }
    });
});

function confirmarExclusao(id) {
    Swal.fire({
        title: 'Tem certeza?',
        text: 'Você está prestes a excluir este curso permanentemente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar',
        backdrop: 'rgba(0,0,0,0.4)'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../../../controllers/CursoDisponivelController.php?acao=remover&id=' + id;
        }
    });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>