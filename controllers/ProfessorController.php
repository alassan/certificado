<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/Professor.php';

session_start();

// Verificar autenticação
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['Admin', 'Funcionário'])) {
    header("Location: ../index.php");
    exit;
}

$professorModel = new Professor($conn);
$acao = $_GET['acao'] ?? '';

// Função para salvar a imagem de assinatura
function salvarAssinatura(string $inputName = 'assinatura'): ?string {
    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
        $nomeFinal = 'uploads/assinaturas/' . uniqid('assin_') . '.' . $ext;

        $caminhoCompleto = __DIR__ . '/../' . $nomeFinal;
        $pasta = dirname($caminhoCompleto);

        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }

        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $caminhoCompleto)) {
            return $nomeFinal;
        }
    }
    return null;
}

if ($acao === 'cadastrar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $assinaturaPath = salvarAssinatura();

    if ($professorModel->verificarDuplicado($nome)) {
        $_SESSION['msg_erro'] = 'Já existe um professor com esse nome!';
        header("Location: ../view/professor/professor_cadastrar.php");
        exit;
    }

    if ($professorModel->cadastrar($nome, $email, $telefone, $assinaturaPath)) {
        $_SESSION['msg_successo'] = 'Professor cadastrado com sucesso!';
        header("Location: ../view/professor/professor_listar.php");
        exit;
    } else {
        $_SESSION['msg_erro'] = 'Erro ao cadastrar professor.';
        header("Location: ../view/professor/professor_cadastrar.php");
        exit;
    }
}

if ($acao === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    // Pega a assinatura atual do banco
    $professor = $professorModel->buscarPorId($id);
    $assinaturaAtual = $professor['assinatura'] ?? null;

    // Se houver nova assinatura, salva; senão, mantém a atual
    $novaAssinatura = salvarAssinatura();
    $assinaturaPath = $novaAssinatura ?: $assinaturaAtual;

    if ($professorModel->atualizar($id, $nome, $email, $telefone, $assinaturaPath)) {
        $_SESSION['msg_successo'] = 'Professor atualizado com sucesso!';
    } else {
        $_SESSION['msg_erro'] = 'Erro ao atualizar professor.';
    }
    header("Location: ../view/professor/professor_listar.php");
    exit;
}

if ($acao === 'excluir') {
    $id = (int)($_GET['id'] ?? 0);

    if ($professorModel->remover($id)) {
        $_SESSION['msg_successo'] = 'Professor removido com sucesso!';
    } else {
        $_SESSION['msg_erro'] = 'Erro ao remover professor.';
    }
    header("Location: ../view/professor/professor_listar.php");
    exit;
}
