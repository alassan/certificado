<?php
session_start();
require_once __DIR__ . '/../../conexao.php';

// Verifica se há mensagens para exibir
$mensagemErro = $_SESSION['mensagem_erro'] ?? null;
$mensagemSucesso = $_SESSION['mensagem_sucesso'] ?? null;
unset($_SESSION['mensagem_erro']);
unset($_SESSION['mensagem_sucesso']);

$nomeUsuarioLogado  = $_SESSION['usuario_nome'] ?? '';
$emailUsuarioLogado = $_SESSION['usuario_email'] ?? '';
$usuario_id         = $_SESSION['usuario_id'] ?? null;

if (!$usuario_id) {
    header('Location: ../../login/login.php');
    exit;
}

$hoje = date('Y-m-d');

// Buscar cursos disponíveis que o aluno ainda NÃO está inscrito
$sql = "
    SELECT cd.id, c.nome, cd.inicio_inscricao, cd.termino_inscricao
    FROM cursos_disponiveis cd
    JOIN cursos c ON c.id = cd.curso_id
    WHERE cd.inicio_inscricao <= :hoje
      AND cd.termino_inscricao >= :hoje
      AND cd.id NOT IN (
          SELECT curso_disponivel_id 
          FROM fichas_inscricao 
          WHERE usuario_id = :usuario_id
      )
    ORDER BY c.nome
";

$stmt = $conn->prepare($sql);
$stmt->execute([
    'hoje' => $hoje,
    'usuario_id' => $usuario_id
]);
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
    .curso-option {
      display: flex;
      justify-content: space-between;
    }
    .curso-periodo {
      font-size: 0.85rem;
      color: #6c757d;
    }
  </style>
</head>
<body>
<div class="container my-5">
  <div class="form-container">
    <h3 class="text-center mb-4">
      <i class="bi bi-person-vcard-fill text-primary me-2"></i> Ficha de Inscrição
    </h3>

    <?php if ($mensagemErro): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= htmlspecialchars($mensagemErro) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if ($mensagemSucesso): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= htmlspecialchars($mensagemSucesso) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if (count($cursos) === 0): ?>
      <div class="alert alert-warning text-center">
        <i class="bi bi-info-circle-fill me-2"></i>
        Você já está inscrito em todos os cursos disponíveis no momento.
      </div>
    <?php else: ?>

    <form action="../../controllers/FichaInscricaoController.php" method="POST" onsubmit="return validarFormulario()">
      <div class="form-section-title">👤 Dados do Aluno</div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Nome do Aluno <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="nome_aluno" value="<?= htmlspecialchars($nomeUsuarioLogado) ?>" readonly>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">E-mail</label>
          <input type="email" class="form-control" name="email_aluno" value="<?= htmlspecialchars($emailUsuarioLogado) ?>" readonly>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">CPF <span class="text-danger">*</span></label>
          <input type="text" class="form-control cpf" name="cpf" required maxlength="14">
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Data de Nascimento <span class="text-danger">*</span></label>
          <input type="date" class="form-control" name="data_nascimento" required max="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Contato <span class="text-danger">*</span></label>
          <input type="text" class="form-control telefone" name="contato" required>
        </div>
        <div class="col-md-12 mb-3">
          <label class="form-label">Curso <span class="text-danger">*</span></label>
          <select name="curso_id" class="form-select" required>
            <option value="">Selecione um curso...</option>
            <?php foreach ($cursos as $curso): ?>
              <option value="<?= $curso['id'] ?>">
                <div class="curso-option">
                  <span><?= htmlspecialchars($curso['nome']) ?></span>
                  <span class="curso-periodo">
                    (Inscrições: <?= date('d/m/Y', strtotime($curso['inicio_inscricao'])) ?> 
                    a <?= date('d/m/Y', strtotime($curso['termino_inscricao'])) ?>)
                  </span>
                </div>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-section-title">🏠 Endereço</div>
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">CEP <span class="text-danger">*</span></label>
          <input type="text" id="cep" name="cep" class="form-control cep" required>
        </div>
        <div class="col-md-8 mb-3">
          <label class="form-label">Logradouro <span class="text-danger">*</span></label>
          <input type="text" id="logradouro" name="logradouro" class="form-control" readonly required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Bairro <span class="text-danger">*</span></label>
          <input type="text" id="bairro" name="bairro" class="form-control" readonly required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Cidade <span class="text-danger">*</span></label>
          <input type="text" id="cidade" name="cidade" class="form-control" readonly required>
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label">UF <span class="text-danger">*</span></label>
          <input type="text" id="uf" name="uf" class="form-control" readonly required>
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label">Número <span class="text-danger">*</span></label>
          <input type="text" name="numero" class="form-control" required>
        </div>
      </div>

      <div class="form-check mb-3">
        <input type="checkbox" name="pmt_funcionario" class="form-check-input" id="pmt_funcionario">
        <label class="form-check-label" for="pmt_funcionario">É funcionário da PMT?</label>
      </div>

      <div class="mb-3">
        <label class="form-label">Observações</label>
        <textarea name="observacoes" rows="3" class="form-control"></textarea>
      </div>

      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg">
          <i class="bi bi-save2"></i> Salvar Ficha
        </button>
        <a href="../dashboard/painel.php" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left"></i> Voltar
        </a>
      </div>
    </form>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
// Máscaras para os campos
$(document).ready(function(){
    $('.cpf').mask('000.000.000-00', {reverse: true});
    $('.cep').mask('00000-000');
    $('.telefone').mask('(00) 00000-0000');
});

// Busca CEP via API
document.getElementById('cep').addEventListener('blur', function() {
    let cep = this.value.replace(/\D/g, '');
    if (cep.length === 8) {
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById('logradouro').value = data.logradouro;
                    document.getElementById('bairro').value = data.bairro;
                    document.getElementById('cidade').value = data.localidade;
                    document.getElementById('uf').value = data.uf;
                } else {
                    alert("CEP não encontrado.");
                    // Libera os campos para edição manual
                    document.getElementById('logradouro').readOnly = false;
                    document.getElementById('bairro').readOnly = false;
                    document.getElementById('cidade').readOnly = false;
                    document.getElementById('uf').readOnly = false;
                }
            })
            .catch(error => {
                console.error("Erro ao buscar CEP:", error);
                alert("Erro ao buscar CEP. Preencha os campos manualmente.");
                // Libera os campos para edição manual
                document.getElementById('logradouro').readOnly = false;
                document.getElementById('bairro').readOnly = false;
                document.getElementById('cidade').readOnly = false;
                document.getElementById('uf').readOnly = false;
            });
    }
});

// Validação do formulário
function validarFormulario() {
    const cpf = document.querySelector('input[name="cpf"]').value.replace(/\D/g, '');
    if (cpf.length !== 11) {
        alert('CPF inválido. Digite os 11 números.');
        return false;
    }

    const dataNascimento = new Date(document.querySelector('input[name="data_nascimento"]').value);
    const hoje = new Date();
    if (dataNascimento >= hoje) {
        alert('Data de nascimento inválida. Deve ser anterior à data atual.');
        return false;
    }

    return true;
}
</script>
</body>
</html>