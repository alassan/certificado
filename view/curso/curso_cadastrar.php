<?php include __DIR__ . '/../includes/header-admin.php'; ?>

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
                        <input type="number" class="form-control" id="carga_horaria" name="carga_horaria" min="1" required>
                        <div class="invalid-feedback">Informe uma carga horária válida.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="nivel_academico" class="form-label">Nível Acadêmico</label>
                        <select class="form-select" id="nivel_academico" name="nivel_academico">
                            <option value="">Não especificado</option>
                            <option value="basico">Básico</option>
                            <option value="intermediario">Intermediário</option>
                            <option value="avancado">Avançado</option>
                            <option value="tecnico">Técnico</option>
                            <option value="superior">Superior</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="/admin/cursos" class="btn btn-secondary">
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

<script>
// Validação do formulário
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
});
</script>

<?php include __DIR__ . '/../includes/footer-admin.php'; ?>