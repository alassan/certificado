<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../models/Turma.php';
require_once __DIR__ . '/../models/CursoDisponivel.php';
require_once __DIR__ . '/../models/FichaInscricao.php'; // ADICIONADO para trabalhar com fichas
session_start();

$acao = $_GET['acao'] ?? '';

$turmaModel = new Turma($conn);
$cursoDisponivelModel = new CursoDisponivel($conn);
$fichaInscricaoModel = new FichaInscricao($conn); // ADICIONADO

if ($acao === 'salvar') {
    $nome = trim($_POST['nome'] ?? '');
    $curso_disponivel_id = $_POST['curso_disponivel_id'] ?? null;
    $capacidade_maxima = $_POST['capacidade_maxima'] ?? 10;
    $status = $_POST['status'] ?? 'aberta';
    $professor_id = $_POST['professor_id'] ?? null;
    $local = trim($_POST['local'] ?? '');
    $alocar_automaticamente = isset($_POST['alocar_automaticamente']) ? 1 : 0;

    // Validação básica
    if (empty($nome) || empty($curso_disponivel_id) || empty($local)) {
        $_SESSION['mensagem_erro'] = 'Preencha todos os campos obrigatórios.';
        header('Location: /certificado/view/turmas/cadastrar.php');
        exit;
    }

    // Verifica se curso disponível existe
    $curso_disponivel = $cursoDisponivelModel->buscarPorId($curso_disponivel_id);
    if (!$curso_disponivel) {
        $_SESSION['mensagem_erro'] = 'Curso disponível não encontrado.';
        header('Location: /certificado/view/turmas/cadastrar.php');
        exit;
    }

    // Dados para cadastro
    $dados = [
        'nome' => $nome,
        'curso_disponivel_id' => $curso_disponivel_id,
        'capacidade_maxima' => $capacidade_maxima,
        'status' => $status,
        'professor_id' => $professor_id ?: null,
        'local' => $local,
        'alocar_automaticamente' => $alocar_automaticamente
    ];

    $resultado = $turmaModel->cadastrar($dados);

    if ($resultado) {
        $_SESSION['mensagem_sucesso'] = 'Turma cadastrada com sucesso!';
        header('Location: /certificado/view/turmas/listar.php');
    } else {
        $_SESSION['mensagem_erro'] = 'Erro ao cadastrar turma. Tente novamente.';
        header('Location: /certificado/view/turmas/cadastrar.php');
    }
    exit;
} elseif ($acao === 'visualizar') {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        $_SESSION['mensagem_erro'] = 'ID da turma não informado.';
        header('Location: /certificado/view/turmas/listar.php');
        exit;
    }

    // Buscar dados da turma
    $turma = $turmaModel->buscarPorId($id);

    if (!$turma) {
        $_SESSION['mensagem_erro'] = 'Turma não encontrada.';
        header('Location: /certificado/view/turmas/listar.php');
        exit;
    }

    // Buscar alunos matriculados (fichas com status 'Matriculado')
    $fichas_matriculadas = $fichaInscricaoModel->buscarFichasMatriculadasPorCursoDisponivel($turma['curso_disponivel_id']);

    // Buscar alunos na lista de espera (fichas com status 'Lista de Espera')
    $lista_espera = $fichaInscricaoModel->buscarFichasListaEsperaPorCursoDisponivel($turma['curso_disponivel_id']);

    // Status da turma
    $status = $turma['status'];

    // Chamar a view
    require __DIR__ . '/../view/turmas/visualizar.php';
} else {
    header('Location: /certificado/view/dashboard/painel.php');
    exit;
}
