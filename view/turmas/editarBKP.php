<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4><i class="bi bi-pencil-square"></i> Editar Turma</h4>
        </div>
        <div class="card-body">
            <form action="/turmas/editar/<?= $turma['id'] ?>" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nome da Turma *</label>
                        <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($turma['nome']) ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Curso *</label>
                        <select name="curso_id" class="form-select" required>
                            <option value="">Selecione um curso...</option>
                            <?php foreach ($cursos as $curso) : ?>
                                <option value="<?= $curso['id'] ?>" <?= $curso['id'] == $turma['curso_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($curso['nome']) ?> (<?= $curso['carga_horaria'] ?>h)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Data de Início *</label>
                        <input type="date" name="data_inicio" class="form-control" 
                               value="<?= $turma['data_inicio'] ?>" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Data de Término *</label>
                        <input type="date" name="data_fim" class="form-control" 
                               value="<?= $turma['data_fim'] ?>" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Capacidade Máxima *</label>
                        <input type="number" name="capacidade_maxima" class="form-control" 
                               min="1" value="<?= $turma['capacidade_maxima'] ?>" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="aberta" <?= $turma['status'] == 'aberta' ? 'selected' : '' ?>>Aberta</option>
                            <option value="fechada" <?= $turma['status'] == 'fechada' ? 'selected' : '' ?>>Fechada</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Professor</label>
                        <select name="professor_id" class="form-select">
                            <option value="">Selecione um professor...</option>
                            <?php foreach ($professores as $professor) : ?>
                                <option value="<?= $professor['id'] ?>" <?= $professor['id'] == $turma['professor_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($professor['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Local *</label>
                        <input type="text" name="local" class="form-control" 
                               value="<?= htmlspecialchars($turma['local']) ?>" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="/turmas/visualizar/<?= $turma['id'] ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>