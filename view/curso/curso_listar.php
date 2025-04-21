<?php
require_once __DIR__ . '/../../conexao.php';
$cursos = $conn->query("
    SELECT cursos.*, categorias.nome as categoria 
    FROM cursos 
    JOIN categorias ON cursos.categoria_id = categorias.id
")->fetchAll(PDO::FETCH_ASSOC);
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="bi bi-journal-bookmark-fill me-2 text-success"></i>Lista de Cursos</h3>
    <a href="curso_cadastrar.php" class="btn btn-success shadow-sm">
      <i class="bi bi-plus-circle me-1"></i> Novo Curso
    </a>
  </div>

  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'excluido'): ?>
    <div class="alert alert-success">Curso excluído com sucesso!</div>
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'erro'): ?>
    <div class="alert alert-danger">Erro ao tentar excluir o curso.</div>
  <?php endif; ?>

  <div class="table-responsive">
    <table id="tabela-cursos" class="table table-striped table-bordered align-middle">
      <thead class="table-light text-center">
        <tr>
          <th>Nome</th>
          <th>Descrição</th>
          <th>Categoria</th>
          <th>Carga Horária</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cursos as $curso): ?>
          <tr>
            <td><?= htmlspecialchars($curso['nome']) ?></td>
            <td><?= htmlspecialchars($curso['descricao']) ?></td>
            <td class="text-center"><?= htmlspecialchars($curso['categoria']) ?></td>
            <td class="text-center"><span class="badge bg-primary"><?= $curso['carga_horaria'] ?>h</span></td>
            <td class="text-center">
              <a href="curso_editar.php?id=<?= $curso['id'] ?>" class="btn btn-sm btn-outline-secondary me-1" title="Editar">
                <i class="bi bi-pencil-square"></i>
              </a>
              <a href="curso_excluir.php?id=<?= $curso['id'] ?>" onclick="return confirm('Deseja excluir este curso?')" class="btn btn-sm btn-outline-danger" title="Excluir">
                <i class="bi bi-trash3"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

<!-- ✅ SCRIPTS para DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    $('#tabela-cursos').DataTable({
      pageLength: 10,
      lengthChange: false,
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
      }
    });
  });
</script>
