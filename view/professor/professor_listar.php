<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/Professor.php';
require_once __DIR__ . '/../includes/header.php';

$professorModel = new Professor($conn);
$professores = $professorModel->listarTodos();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Professores</h3>
        <a href="index.php?page=professor/professor_cadastrar" class="btn btn-primary">Novo Professor</a>
    </div>

    <?php if (!empty($_SESSION['msg_successo'])): ?>
        <div class="alert alert-success"><?= $_SESSION['msg_successo']; unset($_SESSION['msg_successo']); ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['msg_erro'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['msg_erro']; unset($_SESSION['msg_erro']); ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($professores): ?>
                <?php foreach ($professores as $professor): ?>
                    <tr>
                        <td><?= htmlspecialchars($professor['nome']) ?></td>
                        <td><?= htmlspecialchars($professor['email']) ?></td>
                        <td><?= htmlspecialchars($professor['telefone']) ?></td>
                        <td class="text-center">
    <a href="index.php?page=professor/professor_editar&id=<?= $professor['id'] ?>" class="btn btn-sm btn-primary">
    <i class="bi bi-pencil-square"></i> Editar
</a>

<a href="/certificado/controllers/ProfessorController.php?action=excluir&id=<?= $professor['id'] ?>" 
   class="btn btn-sm btn-danger" 
   onclick="return confirm('Tem certeza que deseja excluir?')">
    <i class="bi bi-trash"></i> Excluir
</a>


</td>

                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">Nenhum professor encontrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
	<div class="text-center mt-4">
      <a href="index.php?page=dashboard/painel" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar ao Painel
      </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
