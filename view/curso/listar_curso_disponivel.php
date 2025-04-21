<?php include __DIR__ . '/../includes/header-admin.php'; ?>

<div class="container container-fluid mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-graduation-cap me-2"></i>Gerenciar Cursos
                </h4>
                <a href="/admin/cursos/cadastrar" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i> Novo Curso
                </a>
            </div>
        </div>

        <div class="card-body">
            <?php include __DIR__ . '/../includes/mensagem.php'; ?>

            <div class="table-responsive">
                <table class="table table-hover" id="tabela-cursos">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th class="text-center">Categoria</th>
                            <th class="text-center">Carga Horária</th>
                            <th class="text-center">Nível</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cursos as $curso): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($curso['nome']) ?></strong>
                                <?php if ($curso['descricao']): ?>
                                    <p class="text-muted small mb-0"><?= htmlspecialchars($curso['descricao']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($curso['categoria_nome']) ?></td>
                            <td class="text-center"><?= $curso['carga_horaria'] ?>h</td>
                            <td class="text-center">
                                <?php if ($curso['nivel_academico']): ?>
                                    <span class="badge bg-info"><?= ucfirst($curso['nivel_academico']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Não definido</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="/admin/cursos/editar/<?= $curso['id'] ?>" 
                                       class="btn btn-outline-primary" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            title="Excluir"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalExcluir"
                                            data-id="<?= $curso['id'] ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este curso? Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="btnConfirmarExclusao" class="btn btn-danger">Excluir</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configura DataTables
    $('#tabela-cursos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
        },
        columnDefs: [
            { orderable: false, targets: [4] }
        ]
    });

    // Configura modal de exclusão
    $('#modalExcluir').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');
        const modal = $(this);
        modal.find('#btnConfirmarExclusao').attr('href', '/admin/cursos/excluir/' + id);
    });
});
</script>

<?php include __DIR__ . '/../includes/footer-admin.php'; ?>