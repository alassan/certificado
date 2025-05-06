<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../../config/conexao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Apenas cursos com turmas já criadas
$cursos = $conn->query("
    SELECT DISTINCT cd.id, c.nome AS curso_nome
    FROM cursos_disponiveis cd
    INNER JOIN cursos c ON c.id = cd.curso_id
    INNER JOIN turma t ON t.curso_disponivel_id = cd.id
    WHERE t.id IS NOT NULL
    ORDER BY c.nome
")->fetchAll(PDO::FETCH_ASSOC);

// Mensagens de sessão
$mensagemSucesso = $_SESSION['mensagem_sucesso'] ?? null;
$mensagemErro = $_SESSION['mensagem_erro'] ?? null;
unset($_SESSION['mensagem_sucesso'], $_SESSION['mensagem_erro']);
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-book"></i> Novo Tópico de Conteúdo</h4>
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

            <form method="POST" action="/certificado/controllers/TopicoController.php?action=cadastrar">

                <div class="mb-3">
                    <label for="curso_disponivel_id" class="form-label">Curso Disponível *</label>
                    <select class="form-select" name="curso_disponivel_id" id="curso_disponivel_id" required>
                        <option value="">Selecione um curso</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?= $curso['id'] ?>"><?= htmlspecialchars($curso['curso_nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="conteudo" class="form-label">Conteúdo *</label>
                    <input type="text" class="form-control" id="conteudo" name="conteudo" required>
                </div>

                <div class="mb-3">
                    <label for="ch" class="form-label">Carga Horária (em minutos)</label>
                    <input type="number" class="form-control" id="ch" name="ch" min="15" required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/certificado/index.php?page=admin/conteudo_topico/listar_topicos" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar Tópico
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
