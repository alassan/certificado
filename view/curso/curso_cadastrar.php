<?php
require_once __DIR__ . '/../../models/conexao.php';
require_once __DIR__ . '/../../models/Categoria.php';
require_once __DIR__ . '/../../models/Curso.php';

$categoriaModel = new Categoria($conn);
$categorias = $categoriaModel->listarTodos();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cursoModel = new Curso($conn);

    $dados = [
        'nome' => $_POST['nome'] ?? '',
        'descricao' => $_POST['descricao'] ?? '',
        'categoria_id' => $_POST['categoria_id'] ?? '',
        'carga_horaria' => $_POST['carga_horaria'] ?? '',
        'nivel_academico' => $_POST['nivel_academico'] ?? 'Não especificado',
    ];

    $cursoModel->cadastrar($dados);

    header("Location: cursos_listar.php");
    exit;
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-plus-circle me-2"></i>Cadastrar Novo Curso
            </h4>
        </div>

        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="nome" class="form-label">Nome do Curso *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                        <div class="invalid-feedback">Por favor, informe o nome do curso.</div>
                    </div>

                    <div class="col-md-12">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="categoria_id" class="form-label">Categoria *</label>
                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>">
                                    <?= htmlspecialchars($categoria['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Por favor, selecione uma categoria.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="carga_horaria" class="form-label">Carga Horária (horas) *</label>
                        <input type="number" class="form-control" id="carga_horaria" name="carga_horaria" required>
                        <div class="invalid-feedback">Informe a carga horária do curso.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="nivel_academico" class="form-label">Nível Acadêmico</label>
                        <select class="form-select" id="nivel_academico" name="nivel_academico">
                            <option value="Não especificado">Não especificado</option>
                            <option value="Fundamental">Fundamental</option>
                            <option value="Médio">Médio</option>
                            <option value="Superior">Superior</option>
                            <option value="Pós-graduação">Pós-graduação</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Voltar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Salvar Curso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
