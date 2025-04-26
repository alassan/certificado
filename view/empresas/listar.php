<?php
require_once __DIR__ . '/../../models/conexao.php';
require_once __DIR__ . '/../../models/Empresa.php';
require_once __DIR__ . '/../includes/header.php';

// Instancia o model e busca empresas
$conn = Conexao::getConnection();
$model = new Empresa($conn);
$empresas = $model->listar();

// Recupera mensagens
$mensagemSucesso = $_SESSION['mensagem_sucesso'] ?? null;
$mensagemErro = $_SESSION['mensagem_erro'] ?? null;

// Limpa as mensagens da sessão
unset($_SESSION['mensagem_sucesso']);
unset($_SESSION['mensagem_erro']);
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-building"></i> Empresas Parceiras</h2>
        <a href="../../controllers/EmpresaController.php?action=cadastrar" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nova Empresa
        </a>
    </div>

    <?php if ($mensagemSucesso): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($mensagemSucesso) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($mensagemErro): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($mensagemErro) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tabela-empresas">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>Responsável</th>
                            <th>Contato</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($empresas)): ?>
                            <?php foreach ($empresas as $empresa): ?>
                                <tr>
                                    <td><?= htmlspecialchars($empresa['nome']) ?></td>
                                    <td class="cnpj"><?= htmlspecialchars($empresa['cnpj']) ?></td>
                                    <td><?= htmlspecialchars($empresa['responsavel']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($empresa['telefone']) ?><br>
                                        <?= htmlspecialchars($empresa['email']) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $empresa['ativo'] ? 'success' : 'danger' ?>">
                                            <?= $empresa['ativo'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="../../controllers/EmpresaController.php?action=visualizar&id=<?= $empresa['id'] ?>" class="btn btn-sm btn-info" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="../../controllers/EmpresaController.php?action=editar&id=<?= $empresa['id'] ?>" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="../../controllers/EmpresaController.php?action=excluir&id=<?= $empresa['id'] ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta empresa?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Nenhuma empresa cadastrada.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.cnpj').mask('00.000.000/0000-00');
    $('#tabela-empresas').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
        },
        columnDefs: [
            { orderable: false, targets: [5] }
        ]
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
