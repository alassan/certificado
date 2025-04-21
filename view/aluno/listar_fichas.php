
<?php
require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../../utils/atualizar_status.php';
session_start();

$usuario_id = $_SESSION['usuario_id'] ?? null;
$nivel = $_SESSION['usuario_nivel'] ?? '';

if (!$usuario_id) {
    header('Location: ../login/login.php');
    exit;
}

// Filtros
$cursoFiltro = $_GET['curso'] ?? '';
$statusFiltro = $_GET['status'] ?? '';

// Lista de cursos para filtro
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

// Status disponíveis
$statusOptions = [
    'ativo' => 'Ativo',
    'andamento' => 'Em Andamento',
    'concluido' => 'Concluído',
    'cancelado' => 'Cancelado'
];

$params = [];
$condicoes = [];

$sql = "SELECT f.id, f.nome_aluno, f.cpf, f.contato, f.data_inscricao, f.status,
               c.nome AS curso_nome
        FROM fichas_inscricao f
        JOIN cursos c ON c.id = f.curso_id";

if ($nivel === 'Aluno') {
    $condicoes[] = "f.usuario_id = ?";
    $params[] = $usuario_id;
} else {
    if ($cursoFiltro && is_numeric($cursoFiltro)) {
        $condicoes[] = "f.curso_id = ?";
        $params[] = $cursoFiltro;
    }
}

if ($statusFiltro && array_key_exists($statusFiltro, $statusOptions)) {
    $condicoes[] = "f.status = ?";
    $params[] = $statusFiltro;
}

if (!empty($condicoes)) {
    $sql .= " WHERE " . implode(" AND ", $condicoes);
}

$sql .= " ORDER BY f.data_inscricao DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Inscrições</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body { background-color: #f8f9fa; }
    .card { border-radius: 1rem; }
    .table th, .table td { vertical-align: middle; }
    .badge { font-size: 0.85rem; }
  </style>
</head>
<body>
<div class="container my-5">
  <div class="card shadow p-4">
    <h3 class="mb-4">
      <i class="bi bi-list-task text-primary me-2"></i>
      <?= $nivel === 'Aluno' ? 'Minhas Inscrições' : 'Listagem de Inscrições' ?>
    </h3>

    <?php if ($nivel !== 'Aluno'): ?>
      <form method="get" class="row g-3 align-items-end mb-4">
        <div class="col-md-6">
          <label for="curso" class="form-label">Filtrar por Curso</label>
          <select name="curso" id="curso" class="form-select">
            <option value="">-- Todos os Cursos --</option>
            <?php foreach ($cursos as $curso): ?>
              <option value="<?= $curso['id'] ?>" <?= $curso['id'] == $cursoFiltro ? 'selected' : '' ?>>
                <?= htmlspecialchars($curso['nome']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label for="status" class="form-label">Filtrar por Status</label>
          <select name="status" id="status" class="form-select">
            <option value="">-- Todos os Status --</option>
            <?php foreach ($statusOptions as $key => $label): ?>
              <option value="<?= $key ?>" <?= $statusFiltro === $key ? 'selected' : '' ?>>
                <?= $label ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2 d-grid">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-filter-circle"></i> Filtrar
          </button>
        </div>
      </form>
    <?php endif; ?>

    <?php if (count($fichas) === 0): ?>
      <div class="alert alert-warning text-center">
        Nenhuma inscrição encontrada.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover table-striped align-middle">
          <thead class="table-light">
            <tr class="text-center">
              <?php if ($nivel !== 'Aluno'): ?>
                <th>Nome</th>
                <th>CPF</th>
                <th>Contato</th>
              <?php endif; ?>
              <th>Curso</th>
              <th>Data</th>
              <th>Status</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($fichas as $ficha): ?>
              <tr class="text-center">
                <?php if ($nivel !== 'Aluno'): ?>
                  <td class="text-start"><?= htmlspecialchars($ficha['nome_aluno']) ?></td>
                  <td><?= htmlspecialchars($ficha['cpf']) ?></td>
                  <td><?= htmlspecialchars($ficha['contato']) ?></td>
                <?php endif; ?>
                <td class="text-start"><?= htmlspecialchars($ficha['curso_nome']) ?></td>
                <td><?= date('d/m/Y', strtotime($ficha['data_inscricao'])) ?></td>
                <td>
                  <?php
                    $statusClass = match($ficha['status']) {
                      'concluido' => 'success',
                      'ativo' => 'primary',
                      'andamento' => 'warning',
                      'cancelado' => 'danger',
                      default => 'secondary'
                    };
                    $statusIcon = match($ficha['status']) {
                      'concluido' => 'check-circle',
                      'ativo' => 'hourglass-split',
                      'andamento' => 'play-circle',
                      'cancelado' => 'x-circle',
                      default => 'question-circle'
                    };
                  ?>
                  <span class="badge bg-<?= $statusClass ?>">
                    <i class="bi bi-<?= $statusIcon ?>"></i>
                    <?= ucfirst($ficha['status']) ?>
                  </span>
                </td>
                <td>
                  <a href="comprovante_inscricao.php?id=<?= $ficha['id'] ?>" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-file-earmark-text"></i>
                  </a>
                  <?php if ($ficha['status'] === 'concluido'): ?>
                    <a href="../certificados/gerar_certificado.php?ficha_id=<?= $ficha['id'] ?>" class="btn btn-sm btn-outline-success">
                      <i class="bi bi-award"></i>
                    </a>
                  <?php endif; ?>
                  <a href="ficha_editar.php?id=<?= $ficha['id'] ?>" class="btn btn-sm btn-warning">
                    <i class="bi bi-pencil"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <div class="text-center mt-4">
      <a href="../dashboard/painel.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar ao Painel
      </a>
    </div>
  </div>
</div>
</body>
</html>
