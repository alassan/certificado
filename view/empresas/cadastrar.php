<?php 
require_once __DIR__ . '/../includes/header.php';

// Recupera mensagens e dados do formulário
$mensagemSucesso = $_SESSION['mensagem_sucesso'] ?? null;
$mensagemErro = $_SESSION['mensagem_erro'] ?? null;
$dadosFormulario = $_SESSION['dados_formulario'] ?? [];

// Limpa as mensagens da sessão
unset($_SESSION['mensagem_sucesso']);
unset($_SESSION['mensagem_erro']);
unset($_SESSION['dados_formulario']);
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-building"></i> Cadastrar Nova Empresa</h4>
                </div>
                <div class="card-body">
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
                    
                    <form action="../../controllers/EmpresaController.php?action=cadastrar" method="POST">

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="nome" class="form-label">Nome da Empresa *</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?= htmlspecialchars($dadosFormulario['nome'] ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="cnpj" class="form-label">CNPJ *</label>
                                <input type="text" class="form-control cnpj" id="cnpj" name="cnpj" 
                                       value="<?= htmlspecialchars($dadosFormulario['cnpj'] ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="responsavel" class="form-label">Responsável *</label>
                                <input type="text" class="form-control" id="responsavel" name="responsavel" 
                                       value="<?= htmlspecialchars($dadosFormulario['responsavel'] ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label">Telefone *</label>
                                <input type="text" class="form-control telefone" id="telefone" name="telefone" 
                                       value="<?= htmlspecialchars($dadosFormulario['telefone'] ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">E-mail *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($dadosFormulario['email'] ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="endereco" class="form-label">Endereço</label>
                                <textarea class="form-control" id="endereco" name="endereco" rows="2"><?= htmlspecialchars($dadosFormulario['endereco'] ?? '') ?></textarea>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <a href="../../controllers/EmpresaController.php?action=listar" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Empresa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Máscaras para os campos
$(document).ready(function() {
    $('.cnpj').mask('00.000.000/0000-00');
    $('.telefone').mask('(00) 00000-0000');
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>