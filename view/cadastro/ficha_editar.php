<?php
// arquivo: ficha_editar.php
require_once __DIR__ . '/../../conexao.php';
session_start();

if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['admin', 'Aluno'])) {
    echo "<p class='text-danger text-center mt-4'>Acesso negado.</p>";
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
  echo "<p class='text-danger text-center mt-4'>ID da ficha não informado.</p>";
  exit;
}

$sql = "SELECT fi.*, e.* FROM fichas_inscricao fi
        JOIN enderecos e ON fi.endereco_id = e.id
        WHERE fi.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$ficha = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ficha) {
  echo "<p class='text-danger text-center mt-4'>Ficha não encontrada.</p>";
  exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Ficha</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
  <div class="card p-4 shadow-sm">
    <h4 class="mb-4">✏️ Editar Ficha</h4>
    <form method="POST" action="../../controllers/FichaInscricaoController.php">
      <input type="hidden" name="id" value="<?= $ficha['id'] ?>">
      <div class="mb-3">
        <label for="nome_aluno" class="form-label">Nome do Aluno</label>
        <input type="text" name="nome_aluno" id="nome_aluno" class="form-control" value="<?= htmlspecialchars($ficha['nome_aluno']) ?>" required>
      </div>
      <div class="mb-3">
        <label for="cpf" class="form-label">CPF</label>
        <input type="text" name="cpf" id="cpf" class="form-control" value="<?= htmlspecialchars($ficha['cpf']) ?>" required>
      </div>
      <div class="mb-3">
        <label for="data_nascimento" class="form-label">Data de Nascimento</label>
        <input type="date" name="data_nascimento" id="data_nascimento" class="form-control" value="<?= $ficha['data_nascimento'] ?>" required>
      </div>
      <div class="mb-3">
        <label for="contato" class="form-label">Contato</label>
        <input type="text" name="contato" id="contato" class="form-control" value="<?= htmlspecialchars($ficha['contato']) ?>" required>
      </div>
      <div class="mb-3">
        <label for="observacoes" class="form-label">Observações</label>
        <textarea name="observacoes" id="observacoes" class="form-control" rows="3"><?= htmlspecialchars($ficha['observacoes']) ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Salvar</button>
      <a href="listar_fichas.php" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</div>
</body>
</html>
