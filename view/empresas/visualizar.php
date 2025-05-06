<?php 
require_once __DIR__ . '/../../models/conexao.php';
require_once __DIR__ . '/../../models/Empresa.php';
require_once __DIR__ . '/../includes/header.php';

// Conexão e modelo
$conn = Conexao::getConnection();
$model = new Empresa($conn);

// Verifica se ID foi passado
$id = $_GET['id'] ?? null;

if ($id) {
    $empresa = $model->buscarPorId($id);
}

if (!isset($empresa) || !$empresa) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Empresa não encontrada.</div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-building"></i> Detalhes da Empresa</h4>
                        <a href="/certificado/public/index.php?page=empresas/listar" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h3><?= htmlspecialchars($empresa['nome']) ?></h3>
                            <span class="badge bg-<?= $empresa['ativo'] ? 'success' : 'danger' ?>">
                                <?= $empresa['ativo'] ? 'Ativo' : 'Inativo' ?>
                            </span>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="/certificado/public/index.php?page=empresas/editar&id=<?= $empresa['id'] ?>" class="btn btn-warning me-2">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <form method="POST" action="/certificado/controllers/EmpresaController.php?action=excluir&id=<?= $empresa['id'] ?>" class="d-inline">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta empresa?')">
                                    <i class="bi bi-trash"></i> Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informações Básicas</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>CNPJ:</strong> <?= htmlspecialchars($empresa['cnpj']) ?></li>
                                <li class="list-group-item"><strong>Responsável:</strong> <?= htmlspecialchars($empresa['responsavel']) ?></li>
                                <li class="list-group-item"><strong>Data de Cadastro:</strong> <?= date('d/m/Y H:i', strtotime($empresa['data_cadastro'])) ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Contato</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>Telefone:</strong> <?= htmlspecialchars($empresa['telefone']) ?></li>
                                <li class="list-group-item"><strong>E-mail:</strong> <?= htmlspecialchars($empresa['email']) ?></li>
                            </ul>
                        </div>
                        <div class="col-md-12 mt-3">
                            <h5>Endereço</h5>
                            <div class="card">
                                <div class="card-body">
                                    <?= nl2br(htmlspecialchars($empresa['endereco'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
