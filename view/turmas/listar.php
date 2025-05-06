<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/Turma.php';
require_once __DIR__ . '/../includes/header.php'; 
require_once __DIR__ . '/../includes/_status_badge.php';

// Aqui NÃO precisa mais session_start() porque já foi chamado no header.php!

$turmaModel = new Turma($conn);
$turmas = $turmaModel->listarTodas();
?>


<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people-fill"></i> Turmas</h2>
    <div>
        <a href="/certificado/view/turmas/cadastrar.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nova Turma
        </a>
        <a href="/certificado/view/admin/cursos_disponiveis/listar.php" class="btn btn-secondary ms-2">
            <i class="bi bi-book"></i> Cursos Disponíveis
        </a>
    </div>
</div>


    <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
        <div class="alert alert-success"><?= $_SESSION['mensagem_sucesso']; unset($_SESSION['mensagem_sucesso']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensagem_erro'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['mensagem_erro']; unset($_SESSION['mensagem_erro']); ?></div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tabela-turmas">
                    <thead>
                        <tr>
                            <th>Turma</th>
                            <th>Curso</th>
                            <th>Período</th>
                            <th>Vagas</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($turmas as $turma): 
                            $status = '-';
                            if (!empty($turma['curso_data_inicio']) && !empty($turma['curso_data_termino'])) {
                                $status = getStatusTurma($turma['curso_data_inicio'], $turma['curso_data_termino']);
                            }

                            $statusClass = getStatusClass($status);
                            $vagas_ocupadas = $turma['capacidade_maxima'] - ($turma['vagas_disponiveis'] ?? 0);
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($turma['nome']) ?></td>
                                <td><?= htmlspecialchars($turma['curso_nome']) ?></td>
                                <td>
                                    <?= !empty($turma['curso_data_inicio']) ? date('d/m/Y', strtotime($turma['curso_data_inicio'])) : '-' ?>
                                    a
                                    <?= !empty($turma['curso_data_termino']) ? date('d/m/Y', strtotime($turma['curso_data_termino'])) : '-' ?>
                                </td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: <?= ($turma['capacidade_maxima'] > 0 ? ($vagas_ocupadas / $turma['capacidade_maxima']) * 100 : 0) ?>%"
                                            aria-valuenow="<?= $vagas_ocupadas ?>"
                                            aria-valuemin="0"
                                            aria-valuemax="<?= $turma['capacidade_maxima'] ?>">
                                            <?= $vagas_ocupadas ?>/<?= $turma['capacidade_maxima'] ?>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-<?= $statusClass ?>"><?= $status ?></span></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/certificado/view/turmas/visualizar.php?id=<?= $turma['id'] ?>" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                                        <a href="/certificado/index.php?page=turmas/editar&id=<?= $turma['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="/certificado/controllers/TurmaController.php?acao=excluir&id=<?= $turma['id'] ?>" 
   onclick="return confirm('Tem certeza que deseja excluir esta turma?')" 
   class="btn btn-sm btn-danger">
   <i class="bi bi-trash"></i>
</a>


                                    </div>
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

<script>
$(document).ready(function() {
    $('#tabela-turmas').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json' }
    });

    $('.excluir-turma').click(function() {
        const turmaId = $(this).data('id');
        if (confirm('Tem certeza que deseja excluir esta turma?')) {
            window.location.href = '/turmas/excluir/' + turmaId;
        }
    });
});



document.addEventListener('DOMContentLoaded', function() {
    const botoesExcluir = document.querySelectorAll('.excluir-turma');

    botoesExcluir.forEach(botao => {
        botao.addEventListener('click', function() {
            const id = this.getAttribute('data-id');

            if (confirm('Tem certeza que deseja excluir esta turma?')) {
                window.location.href = `index.php?page=turmas/excluir&id=${id}`;
            }
        });
    });
});


</script>
