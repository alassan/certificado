<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../includes/status_aluno.php';
require_once __DIR__ . '/../../models/FichaInscricao.php';
$fichaModel = new FichaInscricao($conn);
$fichaModel->atualizarStatusAutomaticamente();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_id = $_SESSION['usuario_id'] ?? null;
$nivel = $_SESSION['usuario_nivel'] ?? '';

if (!$usuario_id) {
    header('Location: ../login/login.php');
    exit;
}

// Filtros
$cursoFiltro = $_GET['curso'] ?? '';
$statusFiltro = $_GET['status'] ?? '';

// Lista de cursos para filtro (exceto para Aluno)
$cursos = [];
if ($nivel !== 'Aluno') {
    $sqlCursosDisponiveis = "
        SELECT DISTINCT c.id, c.nome 
        FROM cursos_disponiveis cd
        JOIN cursos c ON c.id = cd.curso_id
        ORDER BY c.nome
    ";
    $cursos = $conn->query($sqlCursosDisponiveis)->fetchAll(PDO::FETCH_ASSOC);
}

// Lista de status disponíveis para filtro
$statusOptions = [
    'matriculado' => 'Matriculado',
    'em_andamento'   => 'Em Andamento',
    'concluido'   => 'Concluído',
    'cancelado'   => 'Cancelado'
];

// Monta a consulta
$params = [];
$condicoes = [];

$sql = "SELECT f.id, f.nome_aluno, f.cpf, f.contato, f.data_inscricao,
               f.status_aluno, cd.data_inicio AS data_inicio_curso, cd.data_termino AS data_fim_curso,
               c.nome AS curso_nome
        FROM fichas_inscricao f
        JOIN cursos_disponiveis cd ON cd.id = f.curso_disponivel_id
        JOIN cursos c ON c.id = cd.curso_id";

if ($nivel === 'Aluno') {
    $condicoes[] = "f.usuario_id = ?";
    $params[] = $usuario_id;
} else {
    if ($cursoFiltro && is_numeric($cursoFiltro)) {
        $condicoes[] = "c.id = ?";
        $params[] = $cursoFiltro;
    }
}

if ($statusFiltro && array_key_exists($statusFiltro, $statusOptions)) {
    $condicoes[] = "f.status_aluno = ?";
    $params[] = $statusFiltro;
}

if (!empty($condicoes)) {
    $sql .= " WHERE " . implode(" AND ", $condicoes);
}

$sql .= " ORDER BY f.data_inscricao DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Atualiza status dinamicamente conforme datas
foreach ($fichas as &$ficha) {
    $novoStatus = StatusAluno::atualizar($ficha, [
        'data_inicio_curso' => $ficha['data_inicio_curso'],
        'data_fim_curso'    => $ficha['data_fim_curso']
    ]);

    if ($novoStatus !== $ficha['status_aluno']) {
        $stmtUpdate = $conn->prepare("UPDATE fichas_inscricao SET status_aluno = ? WHERE id = ?");
        $stmtUpdate->execute([$novoStatus, $ficha['id']]);
        $ficha['status_aluno'] = $novoStatus;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Inscrições</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
<div class="container my-5">
  <div class="card shadow p-4">
    <h3 class="mb-4">
      <i class="bi bi-list-task text-primary me-2"></i>
      <?= $nivel === 'Aluno' ? 'Minhas Inscrições' : 'Listagem de Inscrições' ?>
    </h3>

    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle">
        <thead class="table-light">
          <tr class="text-center">
            <th>Curso</th>
            <th>Data</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($fichas as &$ficha):
            $statusClass = match($ficha['status_aluno']) {
                'concluido'   => 'success',
                'matriculado' => 'primary',
                'em_andamento'   => 'info',
                'cancelado'   => 'danger',
                default       => 'secondary'
            };

            $statusIcon = match($ficha['status_aluno']) {
                'concluido'   => 'check-circle',
                'matriculado' => 'hourglass-split',
                'em_andamento'   => 'play-circle',
                'cancelado'   => 'x-circle',
                default       => 'question-circle'
            };
        ?>
          <tr class="text-center">
            <td class="text-start"><?= htmlspecialchars($ficha['curso_nome']) ?></td>
            <td><?= date('d/m/Y', strtotime($ficha['data_inscricao'])) ?></td>
            <td>
              <span class="badge bg-<?= $statusClass ?>">
                <i class="bi bi-<?= $statusIcon ?>"></i>
                <?= ucfirst($ficha['status_aluno']) ?>
              </span>
            </td>
            <td>
              <a href="index.php?page=aluno/comprovante_inscricao&id=<?= $ficha['id'] ?>" class="btn btn-sm btn-outline-info">
                <i class="bi bi-file-earmark-text"></i>
              </a>
              <?php if ($ficha['status_aluno'] === 'concluido'): ?>
                <a href="index.php?page=certificados/gerar_certificado&ficha_id=<?= $ficha['id'] ?>" class="btn btn-sm btn-outline-success">
                  <i class="bi bi-award"></i>
                </a>
              <?php endif; ?>
              <a href="index.php?page=aluno/ficha_editar&id=<?= $ficha['id'] ?>" class="btn btn-sm btn-warning">
                <i class="bi bi-pencil"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="text-center mt-4">
      <a href="index.php?page=dashboard/painel" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar ao Painel
      </a>
    </div>
  </div>
</div>
</body>
</html>
