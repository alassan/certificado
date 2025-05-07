<?php
require_once __DIR__ . '/../../models/conexao.php';
require_once __DIR__ . '/../../models/Empresa.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = Conexao::getConnection();
$model = new Empresa($conn);

// Verifica se ID foi passado e busca a empresa
$id = $_GET['id'] ?? null;
if ($id) {
    $empresa = $model->buscarPorId($id);
}

if (!$empresa) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Empresa não encontrada.</div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Se formulário enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'nome'       => $_POST['nome'] ?? '',
        'cnpj'       => $_POST['cnpj'] ?? '',
        'endereco'   => $_POST['endereco'] ?? '',
        'telefone'   => $_POST['telefone'] ?? '',
        'email'      => $_POST['email'] ?? '',
        'responsavel'=> $_POST['responsavel'] ?? '',
        'ativo'      => $_POST['ativo'] ?? 1,
    ];

    // Upload da logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $nomeFinal = 'uploads/logos/' . uniqid('logo_') . '.' . strtolower($ext);

        if (!is_dir(__DIR__ . '/../../uploads/logos')) {
            mkdir(__DIR__ . '/../../uploads/logos', 0777, true);
        }

        if (move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ . '/../../' . $nomeFinal)) {
            $dados['logo_path'] = $nomeFinal;
        }
    }

    if ($model->atualizar($id, $dados)) {
        $_SESSION['mensagem_sucesso'] = 'Empresa atualizada com sucesso!';
    } else {
        $_SESSION['mensagem_erro'] = 'Erro ao atualizar a empresa.';
    }

    header('Location: index.php?page=empresas/listar');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

$mensagemSucesso = $_SESSION['mensagem_sucesso'] ?? null;
$mensagemErro = $_SESSION['mensagem_erro'] ?? null;
unset($_SESSION['mensagem_sucesso'], $_SESSION['mensagem_erro']);
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-building"></i> Editar Empresa</h4>
                </div>
                <div class="card-body">
                    <?php if ($mensagemSucesso): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($mensagemSucesso) ?></div>
                    <?php endif; ?>
                    <?php if ($mensagemErro): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($mensagemErro) ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Nome da Empresa *</label>
                                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($empresa['nome']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">CNPJ *</label>
                                <input type="text" name="cnpj" class="form-control cnpj" value="<?= htmlspecialchars($empresa['cnpj']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Responsável *</label>
                                <input type="text" name="responsavel" class="form-control" value="<?= htmlspecialchars($empresa['responsavel']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telefone *</label>
                                <input type="text" name="telefone" class="form-control telefone" value="<?= htmlspecialchars($empresa['telefone']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">E-mail *</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($empresa['email']) ?>" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Endereço</label>
                                <textarea name="endereco" class="form-control"><?= htmlspecialchars($empresa['endereco']) ?></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="ativo" class="form-select">
                                    <option value="1" <?= $empresa['ativo'] ? 'selected' : '' ?>>Ativo</option>
                                    <option value="0" <?= !$empresa['ativo'] ? 'selected' : '' ?>>Inativo</option>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Logo da Empresa (PNG, JPG)</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                            </div>

                            <?php if (!empty($empresa['logo_path']) && file_exists(__DIR__ . '/../../' . $empresa['logo_path'])): ?>
                                <div class="mb-3">
                                    <label class="form-label d-block">Logo atual:</label>
                                    <img src="<?= '/' . $empresa['logo_path'] ?>" alt="Logo" height="80">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="index.php?page=empresas/listar" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.cnpj').mask('00.000.000/0000-00');
    $('.telefone').mask('(00) 00000-0000');
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
