<?php
require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../../models/CursoDisponivel.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /login/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$status = $_GET['status'] ?? 'ativo';

$model = new CursoDisponivel($conn);
$cursos = $model->buscarPorUsuarioEStatus($usuario_id, $status);

$tituloPagina = "Meus Cursos";
$tituloStatus = ucfirst($status);

include __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-book-reader me-2"></i><?= $tituloPagina ?>
                <span class="badge bg-light text-dark ms-2"> <?= $tituloStatus ?> </span>
            </h4>
        </div>
        <div class="card-body">
            <div class="mb-3 text-center">
                <a href="meus_cursos.php?status=ativo" class="btn btn-outline-primary <?= $status === 'ativo' ? 'active' : '' ?>">Ativos</a>
                <a href="meus_cursos.php?status=concluido" class="btn btn-outline-success <?= $status === 'concluido' ? 'active' : '' ?>">Concluídos</a>
                <a href="meus_cursos.php?status=cancelado" class="btn btn-outline-secondary <?= $status === 'cancelado' ? 'active' : '' ?>">Cancelados</a>
            </div>

            <?php if (count($cursos) > 0): ?>
                <div class="row">
                    <?php foreach ($cursos as $index => $curso): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card border-primary shadow-sm h-100">
                                <div class="card-header text-primary fw-bold">
                                    <?= htmlspecialchars($curso['curso_nome']) ?>
                                </div>
                                <div class="card-body">
                                    <p><strong>Período:</strong> <?= date('d/m/Y', strtotime($curso['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($curso['data_termino'])) ?></p>
                                    <p><strong>Professor:</strong> <?= htmlspecialchars($curso['professor_nome'] ?? 'Não informado') ?></p>
                                    <p><strong>Empresa:</strong> <?= htmlspecialchars($curso['empresa']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Nenhum curso encontrado com o status <strong><?= $tituloStatus ?></strong>.</div>
            <?php endif; ?>

            <div class="text-end">
                <a href="http://localhost/certificado/view/dashboard/painel.php" class="btn btn-dark">Voltar ao Painel</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
