<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../models/Curso.php';
require_once __DIR__ . '/../../models/Professor.php';

session_start();
if (!isset($_SESSION['usuario_nivel']) || !in_array($_SESSION['usuario_nivel'], ['Admin', 'Funcionário'])) {
    header('Location: ../dashboard/painel.php');
    exit;
}

$cursoModel = new Curso($conn);
$professorModel = new Professor($conn);

$cursos = $cursoModel->all($conn);
$professores = $professorModel->all();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $curso_id           = $_POST['curso_id'] ?? null;
    $professor_id       = $_POST['professor_id'] ?? null;
    $empresa            = trim($_POST['empresa'] ?? '');
    $data_inicio        = $_POST['data_inicio'] ?? null;
    $data_termino       = $_POST['data_termino'] ?? null;
    $inicio_inscricao   = $_POST['inicio_inscricao'] ?? null;
    $termino_inscricao  = $_POST['termino_inscricao'] ?? null;

    if (strtotime($inicio_inscricao) > strtotime($termino_inscricao)) {
        $erro = "A data de término da inscrição não pode ser anterior à data de início.";
    } elseif (strtotime($data_inicio) > strtotime($data_termino)) {
        $erro = "A data de término do curso não pode ser anterior à data de início.";
    } elseif ($curso_id && $data_inicio && $data_termino && $inicio_inscricao && $termino_inscricao && $empresa !== '') {
        $stmt = $conn->prepare("INSERT INTO cursos_disponiveis (curso_id, professor_id, empresa, data_inicio, data_termino, inicio_inscricao, termino_inscricao) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$curso_id, $professor_id, $empresa, $data_inicio, $data_termino, $inicio_inscricao, $termino_inscricao]);

        header("Location: listar_curso_disponivel.php?sucesso=1");
        exit;
    } else {
        $erro = "Todos os campos obrigatórios devem ser preenchidos corretamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gerenciar Cursos Disponíveis</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function validarFormulario() {
      const inicioInscricao = new Date(document.querySelector('[name="inicio_inscricao"]').value);
      const terminoInscricao = new Date(document.querySelector('[name="termino_inscricao"]').value);
      const dataInicio = new Date(document.querySelector('[name="data_inicio"]').value);
      const dataTermino = new Date(document.querySelector('[name="data_termino"]').value);

      if (inicioInscricao > terminoInscricao) {
        Swal.fire({ icon: 'warning', title: 'Atenção', text: 'A data de término da inscrição não pode ser anterior à data de início.' });
        return false;
      }
      if (dataInicio > dataTermino) {
        Swal.fire({ icon: 'warning', title: 'Atenção', text: 'A data de término do curso não pode ser anterior à data de início.' });
        return false;
      }
      return true;
    }
  </script>
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i> Gerenciar Cursos Disponíveis</h4>
    </div>
    <div class="card-body">
      <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
      <?php endif; ?>

      <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success">Curso lançado com sucesso!</div>
      <?php endif; ?>

      <form method="POST" onsubmit="return validarFormulario()">
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Curso</label>
            <select name="curso_id" class="form-select" required>
              <option value="">Selecione</option>
              <?php foreach ($cursos as $curso): ?>
                <option value="<?= $curso['id'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Professor</label>
            <select name="professor_id" class="form-select">
              <option value="">Selecione</option>
              <?php foreach ($professores as $prof): ?>
                <option value="<?= $prof['id'] ?>"><?= htmlspecialchars($prof['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Início da Inscrição</label>
            <input type="date" name="inicio_inscricao" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Término da Inscrição</label>
            <input type="date" name="termino_inscricao" class="form-control" required>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Data de Início do Curso</label>
            <input type="date" name="data_inicio" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Data de Término do Curso</label>
            <input type="date" name="data_termino" class="form-control" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Empresa</label>
          <input type="text" name="empresa" class="form-control" placeholder="Nome da empresa" required>
        </div>

        <div class="d-flex justify-content-between">
          <a href="../dashboard/painel.php" class="btn btn-outline-secondary">Voltar</a>
          <button type="submit" class="btn btn-primary">Lançar Curso</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
