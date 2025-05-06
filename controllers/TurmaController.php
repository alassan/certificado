<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/Turma.php';
require_once __DIR__ . '/../models/CursoDisponivel.php';
require_once __DIR__ . '/../models/FichaInscricao.php';
require_once __DIR__ . '/../models/GerenciadorTurmas.php'; // Novo

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    define('BASE_URL', '/certificado/');
}

// Verificar permissões
if ($_SESSION['usuario_nivel'] !== 'Admin') {
    $_SESSION['mensagem_erro'] = 'Você não tem permissão para acessar esta funcionalidade.';
    header('Location: ' . BASE_URL . 'view/dashboard/painel.php');
    exit;
}

$acao = $_GET['acao'] ?? '';

$turmaModel = new Turma($conn);
$cursoDisponivelModel = new CursoDisponivel($conn);
$fichaInscricaoModel = new FichaInscricao($conn);
$gerenciadorTurmas = new GerenciadorTurmas($conn); // Novo

if ($acao === 'salvar') {
    $id = $_POST['id'] ?? null;
    $nome = trim($_POST['nome'] ?? '');
    $cursoDisponivelId = $_POST['curso_disponivel_id'] ?? null;
    $capacidadeMaxima = (int)($_POST['capacidade_maxima'] ?? 10);
    $status = $_POST['status'] ?? 'aberta';
    $professorId = $_POST['professor_id'] ?? null;
    $local = trim($_POST['local'] ?? '');
    $alocarAutomaticamente = isset($_POST['alocar_automaticamente']) ? 1 : 0;

    // Validações
    if (empty($nome) || empty($cursoDisponivelId) || empty($local)) {
        $_SESSION['mensagem_erro'] = 'Preencha todos os campos obrigatórios.';
        header('Location: ' . BASE_URL . 'view/turmas/cadastrar.php');
        exit;
    }

    if ($capacidadeMaxima < 1 || $capacidadeMaxima > 50) {
        $_SESSION['mensagem_erro'] = 'Capacidade deve ser entre 1 e 50 alunos.';
        header('Location: ' . BASE_URL . 'view/turmas/cadastrar.php');
        exit;
    }

    $cursoDisponivel = $cursoDisponivelModel->buscarPorId($cursoDisponivelId);
    if (!$cursoDisponivel) {
        $_SESSION['mensagem_erro'] = 'Curso disponível não encontrado.';
        header('Location: ' . BASE_URL . 'view/turmas/cadastrar.php');
        exit;
    }

    $dadosTurma = [
        'nome' => $nome,
        'curso_disponivel_id' => $cursoDisponivelId,
        'capacidade_maxima' => $capacidadeMaxima,
        'status' => $status,
        'professor_id' => $professorId ?: null,
        'local' => $local,
        'alocar_automaticamente' => $alocarAutomaticamente
    ];

    try {
        if ($id) {
            // Atualização de turma existente
            $resultado = $turmaModel->atualizar($id, $dadosTurma);
            $msg = $resultado ? 'Turma atualizada com sucesso!' : 'Erro ao atualizar turma.';
            
            // Se aumentou a capacidade, tenta alocar alunos em espera
            if ($resultado) {
                $turmaAtual = $turmaModel->buscarPorId($id);
                if ($turmaAtual['vagas_disponiveis'] > 0) {
                    $gerenciadorTurmas->alocarAlunosEmEspera($id);
                }
            }
        } else {
            // Criação de nova turma
            $novaTurmaId = $turmaModel->cadastrar($dadosTurma);
            $resultado = $novaTurmaId ? true : false;
            $msg = $resultado ? 'Turma cadastrada com sucesso!' : 'Erro ao cadastrar turma.';

            // Alocar alunos automaticamente da lista de espera
            if ($resultado && $alocarAutomaticamente && $status === 'aberta') {
                $gerenciadorTurmas->alocarAlunosEmEspera($novaTurmaId);
            }
        }

        $_SESSION[$resultado ? 'mensagem_sucesso' : 'mensagem_erro'] = $msg;
    } catch (Exception $e) {
        $_SESSION['mensagem_erro'] = 'Erro no sistema: ' . $e->getMessage();
        error_log("Erro no TurmaController: " . $e->getMessage());
    }

    header('Location: ' . BASE_URL . 'view/turmas/listar.php');
    exit;
}

elseif ($acao === 'visualizar') {
    $id = $_GET['id'] ?? null;

    if (!$id || !is_numeric($id)) {
        $_SESSION['mensagem_erro'] = 'ID da turma inválido.';
        header('Location: ' . BASE_URL . 'view/turmas/listar.php');
        exit;
    }

    $turma = $turmaModel->buscarPorId($id);
    if (!$turma) {
        $_SESSION['mensagem_erro'] = 'Turma não encontrada.';
        header('Location: ' . BASE_URL . 'view/turmas/listar.php');
        exit;
    }

    // Busca alunos matriculados e em espera específicos desta turma
    $alunosMatriculados = $turmaModel->listarAlunosTurma($id);
    $listaEspera = $turmaModel->listarListaEsperaTurma($id);
    $statusTurma = $turma['status'];

    require __DIR__ . '/../view/turmas/visualizar.php';
    exit;
}

elseif ($acao === 'editar') {
    $id = $_GET['id'] ?? null;

    if (!$id || !is_numeric($id)) {
        $_SESSION['mensagem_erro'] = 'ID inválido para edição.';
        header('Location: ' . BASE_URL . 'view/turmas/listar.php');
        exit;
    }

    $turma = $turmaModel->buscarPorId($id);
    if (!$turma) {
        $_SESSION['mensagem_erro'] = 'Turma não encontrada.';
        header('Location: ' . BASE_URL . 'view/turmas/listar.php');
        exit;
    }

    // Busca cursos disponíveis para o select
    $cursosDisponiveis = $cursoDisponivelModel->listarAtivos();

    require __DIR__ . '/../view/turmas/editar.php';
    exit;
}

elseif ($acao === 'excluir') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        try {
            // Verifica se há alunos matriculados antes de excluir
            $alunosCount = $turmaModel->contarAlunosMatriculados($id);
            
            if ($alunosCount > 0) {
                $_SESSION['mensagem_erro'] = 'Não é possível excluir turma com alunos matriculados.';
            } else {
                $resultado = $turmaModel->excluir($id);
                $_SESSION[$resultado ? 'mensagem_sucesso' : 'mensagem_erro'] = $resultado
                    ? 'Turma excluída com sucesso!'
                    : 'Erro ao excluir turma.';
            }
        } catch (Exception $e) {
            $_SESSION['mensagem_erro'] = 'Erro ao excluir turma: ' . $e->getMessage();
            error_log("Erro ao excluir turma: " . $e->getMessage());
        }
    } else {
        $_SESSION['mensagem_erro'] = 'ID inválido para exclusão.';
    }

    header('Location: ' . BASE_URL . 'view/turmas/listar.php');
    exit;
}

elseif ($acao === 'alocar_espera') {
    $turmaId = $_GET['id'] ?? null;
    $fichaId = $_GET['ficha_id'] ?? null;

    if (!$turmaId || !$fichaId) {
        $_SESSION['mensagem_erro'] = 'Parâmetros inválidos para alocação.';
        header('Location: ' . BASE_URL . 'view/turmas/listar.php');
        exit;
    }

    try {
        // Verifica se ainda há vagas
        $turma = $turmaModel->buscarPorId($turmaId);
        if ($turma['vagas_disponiveis'] <= 0) {
            $_SESSION['mensagem_erro'] = 'Não há vagas disponíveis nesta turma.';
            header('Location: ' . BASE_URL . 'view/turmas/visualizar.php?id=' . $turmaId);
            exit;
        }

        // Aloca o aluno e atualiza status
        $resultado = $turmaModel->alocarAlunoNaTurma($turmaId, $fichaId);
        if ($resultado) {
            $fichaInscricaoModel->atualizarStatus($fichaId, 'matriculado');
            $_SESSION['mensagem_sucesso'] = 'Aluno alocado com sucesso!';
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao alocar aluno.';
        }
    } catch (Exception $e) {
        $_SESSION['mensagem_erro'] = 'Erro no sistema: ' . $e->getMessage();
        error_log("Erro ao alocar aluno: " . $e->getMessage());
    }

    header('Location: ' . BASE_URL . 'view/turmas/visualizar.php?id=' . $turmaId);
    exit;
}

else {
    header('Location: ' . BASE_URL . 'view/dashboard/painel.php');
    exit;
}