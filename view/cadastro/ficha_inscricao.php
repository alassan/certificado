<?php
session_start();
require_once __DIR__ . '/../../config/conexao.php';

$nomeUsuarioLogado = $_SESSION['usuario_nome'] ?? '';
$emailUsuarioLogado = $_SESSION['usuario_email'] ?? '';

$hoje = date('Y-m-d');
$sql = "
    SELECT cd.id, c.nome
    FROM cursos_disponiveis cd
    JOIN cursos c ON c.id = cd.curso_id
    WHERE cd.inicio_inscricao <= ? AND cd.termino_inscricao >= ?
    ORDER BY c.nome
";
$stmt = $conn->prepare($sql);
$stmt->execute([$hoje, $hoje]);
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Ficha de Inscrição</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .form-section-title {
      margin-top: 1.5rem;
      margin-bottom: 1rem;
      border-bottom: 1px solid #dee2e6;
      padding-bottom: 0.5rem;
      font-size: 1.25rem;
      font-weight: 500;
    }
    .form-container {
      background-color: #fff;
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 0 12px rgba(0,0,0,0.05);
    }
  </style>
</head>
<body>
<div class="container my-5">
  <div class="form-container">
    <h3 class="text-center mb-4">
      <i class="bi bi-file-earmark-person-fill text-primary me-2"></i>
      Ficha de Inscrição
    </h3>

    <?php if (count($cursos) === 0): ?>
      <div class="alert alert-warning text-center">
        Nenhum curso disponível para inscrição no momento.
      </div>
    <?php else: ?>

    <form action="../../controllers/FichaInscricaoController.php" method="POST">
      <div class="form-section-title">👤 Dados do Aluno</div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="nome_aluno" class="form-label">Nome do Aluno</label>
          <input type="text" name="nome_aluno" id="nome_aluno" class="form-control" value="<?= htmlspecialchars($nomeUsuarioLogado) ?>" readonly>
        </div>
        <div class="col-md-6 mb-3">
          <label for="email_aluno" class="form-label">E-mail</label>
          <input type="email" name="email_aluno" id="email_aluno" class="form-control" value="<?= htmlspecialchars($emailUsuarioLogado) ?>" readonly>
        </div>
        <div class="col-md-4 mb-3">
          <label for="cpf" class="form-label">CPF</label>
          <input type="text" name="cpf" id="cpf" class="form-control" required>
        </div>
        <div class="col-md-4 mb-3">
          <label for="data_nascimento" class="form-label">Data de Nascimento</label>
          <input type="date" name="data_nascimento" id="data_nascimento" class="form-control" required>
        </div>
        <div class="col-md-4 mb-3">
          <label for="contato" class="form-label">Contato</label>
          <input type="text" name="contato" id="contato" class="form-control" required>
        </div>
        <div class="col-md-12 mb-3">
          <label for="curso_id" class="form-label">Curso</label>
          <select name="curso_id" id="curso_id" class="form-select" required>
            <option value="">Selecione...</option>
            <?php foreach($cursos as $curso): ?>
              <option value="<?= $curso['id'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-section-title">🏠 Endereço</div>
      <div class="row">
        <div class="col-md-4 mb-3">
          <label for="cep" class="form-label">CEP</label>
          <input type="text" id="cep" name="cep" class="form-control" maxlength="9" required>
        </div>
        <div class="col-md-8 mb-3">
          <label for="logradouro" class="form-label">Logradouro</label>
          <input type="text" id="logradouro" name="logradouro" class="form-control" readonly>
        </div>
        <div class="col-md-4 mb-3">
          <label for="bairro" class="form-label">Bairro</label>
          <input type="text" id="bairro" name="bairro" class="form-control" readonly>
        </div>
        <div class="col-md-4 mb-3">
          <label for="cidade" class="form-label">Cidade</label>
          <input type="text" id="cidade" name="cidade" class="form-control" readonly>
        </div>
        <div class="col-md-2 mb-3">
          <label for="uf" class="form-label">UF</label>
          <input type="text" id="uf" name="uf" class="form-control" readonly>
        </div>
        <div class="col-md-2 mb-3">
          <label for="numero" class="form-label">Número</label>
          <input type="text" id="numero" name="numero" class="form-control" required>
        </div>
      </div>

      <div class="form-check mb-3">
        <input type="checkbox" name="pmt_funcionario" value="1" class="form-check-input" id="pmt_funcionario">
        <label class="form-check-label" for="pmt_funcionario">É funcionário da PMT?</label>
      </div>

      <div class="mb-4">
        <label for="observacoes" class="form-label">Observações</label>
        <textarea name="observacoes" id="observacoes" rows="3" class="form-control"></textarea>
      </div>

      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-save me-1"></i> Salvar Ficha
      </button>
    </form>
    <?php endif; ?>
  </div>
</div>

<script>
document.getElementById('cep').addEventListener('blur', function () {
  let cep = this.value.replace(/\D/g, '');
  if (cep.length === 8) {
    fetch(`https://viacep.com.br/ws/${cep}/json/`)
      .then(res => res.json())
      .then(data => {
        if (!data.erro) {
          document.getElementById('logradouro').value = data.logradouro;
          document.getElementById('bairro').value = data.bairro;
          document.getElementById('cidade').value = data.localidade;
          document.getElementById('uf').value = data.uf;
        } else {
          alert('CEP não encontrado.');
        }
      });
  }
});
</script>
</body>
</html>
