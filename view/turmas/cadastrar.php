<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-people-fill"></i> Cadastrar Nova Turma</h4>
                </div>
                <div class="card-body">
                    <form action="/turmas/cadastrar" method="POST" id="form-turma">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome da Turma *</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="curso_id" class="form-label">Curso *</label>
                                <select class="form-select" id="curso_id" name="curso_id" required>
                                    <option value="">Selecione um curso...</option>
                                    <?php foreach ($cursos as $curso) : ?>
                                        <option value="<?= $curso['id'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="data_inicio" class="form-label">Data de Início *</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="data_fim" class="form-label">Data de Término *</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="professor_id" class="form-label">Professor</label>
                                <select class="form-select" id="professor_id" name="professor_id">
                                    <option value="">Selecione um professor...</option>
                                    <?php foreach ($professores as $professor) : ?>
                                        <option value="<?= $professor['id'] ?>"><?= htmlspecialchars($professor['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="empresa_id" class="form-label">Empresa Parceira</label>
                                <select class="form-select" id="empresa_id" name="empresa_id">
                                    <option value="">Selecione uma empresa...</option>
                                    <?php foreach ($empresas as $empresa) : ?>
                                        <option value="<?= $empresa['id'] ?>"><?= htmlspecialchars($empresa['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="local" class="form-label">Local *</label>
                                <input type="text" class="form-control" id="local" name="local" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <a href="/turmas/listar" class="btn btn-secondary me-md-2">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Salvar Turma
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>