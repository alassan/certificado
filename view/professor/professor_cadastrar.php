<?php
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <div class="card p-4">
        <h4 class="mb-3">Cadastrar Professor</h4>

        <?php if (!empty($_SESSION['msg_erro'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['msg_erro']; unset($_SESSION['msg_erro']); ?></div>
        <?php endif; ?>

        <form action="/certificado/controllers/ProfessorController.php?acao=cadastrar" method="POST">
            <input type="text" name="nome" class="form-control mb-3" placeholder="Nome completo" required>
            <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
            <input type="text" name="telefone" class="form-control mb-3" placeholder="Telefone" required>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-success">Salvar</button>
                <a href="index.php?page=professor/professor_listar" class="btn btn-secondary">Voltar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
