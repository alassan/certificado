<?php
require_once __DIR__ . '/../../models/conexao.php';
require_once __DIR__ . '/../../models/Empresa.php';
require_once __DIR__ . '/../includes/header.php';

$conn = Conexao::getConnection();
$model = new Empresa($conn);
$empresas = $model->listar();

$mensagemSucesso = $_SESSION['mensagem_sucesso'] ?? null;
$mensagemErro = $_SESSION['mensagem_erro'] ?? null;

unset($_SESSION['mensagem_sucesso']);
unset($_SESSION['mensagem_erro']);
?>

<div class="container px-4 px-md-5 mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-primary">
      <i class="bi bi-building-fill me-2"></i> Empresas Parceiras
    </h2>
    <a href="index.php?page=empresas/cadastrar" class="btn btn-outline-primary shadow-sm rounded-pill px-4 py-2">
      <i class="bi bi-plus-circle me-1"></i> Nova Empresa
    </a>
  </div>

  <?php if ($mensagemSucesso): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm">
      <?= htmlspecialchars($mensagemSucesso) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($mensagemErro): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm">
      <?= htmlspecialchars($mensagemErro) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="card shadow-lg border-0 rounded-4">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover table-sm align-middle" id="tabela-empresas">
          <thead class="table-light">
            <tr>
              <th>Nome</th>
              <th>CNPJ</th>
              <th>Responsável</th>
              <th>Contato</th>
              <th>Status</th>
              <th class="text-center">Ações</th>
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
                  <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                      <a href="index.php?page=empresas/visualizar&id=<?= $empresa['id'] ?>" class="btn btn-outline-info" title="Visualizar">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="index.php?page=empresas/editar&id=<?= $empresa['id'] ?>" class="btn btn-outline-warning" title="Editar">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <a href="controllers/EmpresaController.php?acao=excluir&id=<?= $empresa['id'] ?>"
                         class="btn btn-outline-danger"
                         onclick="return confirm('Deseja excluir esta empresa?')" title="Excluir">
                        <i class="bi bi-trash3"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center text-muted">Nenhuma empresa cadastrada.</td>
              </tr>
            <?php endif; ?>
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

<!-- Scripts para DataTables e máscaras -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

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
