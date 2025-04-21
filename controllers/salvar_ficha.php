<?php
// arquivo: salvar_ficha.php
require 'conexao.php';

$nome_aluno = $_POST['nome_aluno'] ?? '';
$cpf = $_POST['cpf'] ?? '';
$data_nascimento = $_POST['data_nascimento'] ?? '';
$contato = $_POST['contato'] ?? '';
$curso_id = $_POST['curso_id'] ?? '';
$endereco_id = $_POST['endereco_id'] ?? '';
$pmt_funcionario = isset($_POST['pmt_funcionario']) ? 1 : 0;
$observacoes = $_POST['observacoes'] ?? '';
$data_inscricao = date('Y-m-d');

try {
  $sql = "INSERT INTO fichas_inscricao (nome_aluno, cpf, data_nascimento, contato, curso_id, endereco_id, pmt_funcionario, data_inscricao, observacoes)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->execute([
    $nome_aluno,
    $cpf,
    $data_nascimento,
    $contato,
    $curso_id,
    $endereco_id,
    $pmt_funcionario,
    $data_inscricao,
    $observacoes
  ]);

  header("Location: listar_fichas.php?sucesso=1");
  exit;
} catch (Exception $e) {
  echo "<p class='text-danger text-center'>Erro ao salvar ficha: " . $e->getMessage() . "</p>";
}
