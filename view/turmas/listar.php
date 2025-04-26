<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people-fill"></i> Turmas</h2>
        <a href="/turmas/cadastrar" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nova Turma
        </a>
    </div>

    <?php if (isset($_SESSION['mensagem_sucesso'])) : ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['mensagem_sucesso'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensagem_sucesso']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensagem_erro'])) : ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['mensagem_erro'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensagem_erro']); ?>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tabela-turmas">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Curso</th>
                            <th>Período</th>
                            <th>Empresa</th>
                            <th>Local</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($turmas as $turma) : 
                            $status = $this->getStatusTurma($turma['data_inicio'], $turma['data_fim']);
                            $statusClass = $this->getStatusClass($status);
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($turma['nome']) ?></td>
                                <td><?= htmlspecialchars($turma['curso_nome']) ?></td>
                                <td>
                                    <?= date('d/m/Y', strtotime($turma['data_inicio'])) ?> a 
                                    <?= date('d/m/Y', strtotime($turma['data_fim'])) ?>
                                </td>
                                <td><?= htmlspecialchars($turma['empresa_nome']) ?></td>
                                <td><?= htmlspecialchars($turma['local']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $status ?></span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/turmas/visualizar/<?= $turma['id'] ?>" class="btn btn-sm btn-info" title="Visualizar">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/turmas/editar/<?= $turma['id'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger excluir-turma" data-id="<?= $turma['id'] ?>" title="Excluir">
                                            <i class="bi bi-trash"></i>
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>