<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../../models/Certificado.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/menu.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
$nivel = $_SESSION['usuario_nivel'] ?? null;
$model = new Certificado($conn);
$resultados = [];
$termo = $_GET['termo'] ?? '';

if ($nivel === 'Aluno') {
    $cursos = $model->buscarCursosConcluidosDoAluno($usuario_id);
    if (!empty($_GET['curso'])) {
        $curso = $_GET['curso'];
        $resultados = $model->buscarCertificadoAlunoPorCurso($usuario_id, $curso);
    }
} else {
    if (!empty($termo)) {
        $resultados = $model->buscarPorCursoOuCpfOuNome($termo);
    }
}
?>

<div class="container mt-4 with-sidebar">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Buscar Certificado</h4>
        </div>
        <div class="card-body">
            <?php if ($nivel === 'Aluno'): ?>
                <?php if (count($cursos) > 0): ?>
                    <form method="get" class="row g-2 mb-3">
                        <div class="col-md-10">
                            <select name="curso" class="form-select" required>
                                <option value="">Selecione o curso</option>
                                <?php foreach ($cursos as $c): ?>
                                    <option value="<?= htmlspecialchars($c['curso_nome']) ?>" <?= (isset($_GET['curso']) && $_GET['curso'] === $c['curso_nome']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['curso_nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Buscar</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning text-center">
                        Você ainda não concluiu nenhum curso.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <form method="get" class="row g-2 mb-3">
                    <div class="col-md-10">
                        <input type="text" name="termo" class="form-control" placeholder="Informe nome ou CPF" value="<?= htmlspecialchars($termo) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Pesquisar</button>
                    </div>
                </form>
            <?php endif; ?>

            <?php if (count($resultados) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Curso</th>
                                <th>CPF</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['nome_aluno']) ?></td>
                                    <td><?= htmlspecialchars($r['curso_nome']) ?></td>
                                    <td><?= htmlspecialchars($r['cpf']) ?></td>
                                    <td>
                                        <a href="ver_certificado.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-eye"></i> Visualizar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (!empty($_GET)): ?>
                <div class="alert alert-info text-center">Nenhum certificado encontrado.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<style>
@media (min-width: 768px) {
    .with-sidebar {
        margin-left: 270px;
    }
}
</style>
