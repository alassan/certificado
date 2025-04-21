<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$nome = $_SESSION['usuario_nome'];
$nivel = $_SESSION['usuario_nivel'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Painel do Usuário</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .painel {
      max-width: 960px;
      margin: 50px auto;
      padding: 2rem;
      border-radius: 1rem;
      background: #fff;
      box-shadow: 0 0 10px rgba(0,0,0,0.08);
    }
    .card-indicador {
      margin-bottom: 1rem;
    }
  </style>
</head>
<body class="bg-light">
<div class="container">
  <div class="painel">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4>Bem-vindo, <?= htmlspecialchars($nome) ?>!</h4>
        <p class="text-muted mb-0">Nível de Acesso: <strong><?= ucfirst($nivel) ?></strong></p>
      </div>
      <div>
        <?php if ($nivel === 'Aluno'): ?>
          <a href="../cadastro/ficha_inscricao.php" class="btn btn-outline-primary">📝 Ficha de Inscrição</a>
        <?php endif; ?>
        <a href="../perfil/perfil.php" class="btn btn-outline-secondary">👤 Perfil</a>
        <a href="../logout/logout.php" class="btn btn-outline-danger">🚪 Sair</a>
      </div>
    </div>

    <?php if ($nivel !== 'Aluno'): ?>
    <div class="row text-center">
      <div class="col-md-3 card-indicador">
        <div class="card border-primary">
          <div class="card-body">
            <h5 class="card-title">Cursos</h5>
            <p class="card-text fs-4">0</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 card-indicador">
        <div class="card border-success">
          <div class="card-body">
            <h5 class="card-title">Inscritos</h5>
            <p class="card-text fs-4">0</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 card-indicador">
        <div class="card border-info">
          <div class="card-body">
            <h5 class="card-title">Ativos</h5>
            <p class="card-text fs-4">0</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 card-indicador">
        <div class="card border-dark">
          <div class="card-body">
            <h5 class="card-title">Concluídos</h5>
            <p class="card-text fs-4">0</p>
          </div>
        </div>
      </div>
    </div>

    <hr class="my-4">
    <h5 class="text-center mb-3">Inscrições por Curso</h5>
    <canvas id="grafico" height="100"></canvas>
    <?php endif; ?>
  </div>
</div>

<?php if ($nivel !== 'aluno'): ?>
<script>
  const ctx = document.getElementById('grafico').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Curso A', 'Curso B', 'Curso C'],
      datasets: [{
        label: 'Inscrições',
        data: [0, 0, 0],
        backgroundColor: ['#0d6efd', '#198754', '#0dcaf0']
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
</script>
<?php endif; ?>
</body>
</html>
