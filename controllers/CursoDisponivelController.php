<?php
require_once __DIR__ . '/../models/CursoDisponivel.php';
require_once __DIR__ . '/../models/Curso.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/FichaInscricao.php';
require_once __DIR__ . '/../config/conexao.php';
session_start();

// Verifica autenticação
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['Admin', 'Funcionário'])) {
    header('Location: ../view/dashboard/painel.php');
    exit;
}

$acao = $_GET['acao'] ?? '';
$cursoDisponivelModel = new CursoDisponivel($conn);
$cursoModel = new Curso($conn);
$empresaModel = new Empresa($conn);
$fichaModel = new FichaInscricao($conn);

switch ($acao) {
    case 'salvar':
        $curso_id = $_POST['curso_id'] ?? null;
        $empresa_id = $_POST['empresa_id'] ?? null;
        $data_inicio = $_POST['data_inicio'] ?? null;
        $data_termino = $_POST['data_termino'] ?? null;
        $inicio_inscricao = $_POST['inicio_inscricao'] ?? null;
        $termino_inscricao = $_POST['termino_inscricao'] ?? null;

        // Validações
        if (!$curso_id || !$empresa_id || !$data_inicio || !$data_termino || !$inicio_inscricao || !$termino_inscricao) {
            header("Location: ../view/admin/cursos_disponiveis/cadastrar.php?erro=Preencha todos os campos obrigatórios.");
            exit;
        }

        if (strtotime($inicio_inscricao) > strtotime($termino_inscricao)) {
            header("Location: ../view/admin/cursos_disponiveis/cadastrar.php?erro=O término das inscrições deve ser após o início.");
            exit;
        }

        if (strtotime($data_inicio) > strtotime($data_termino)) {
            header("Location: ../view/admin/cursos_disponiveis/cadastrar.php?erro=O término do curso deve ser após o início.");
            exit;
        }

        if (strtotime($termino_inscricao) > strtotime($data_inicio)) {
            header("Location: ../view/admin/cursos_disponiveis/cadastrar.php?erro=O curso deve começar após o término das inscrições.");
            exit;
        }

        if ($cursoDisponivelModel->verificarDuplicidade($curso_id, $data_inicio, $data_termino)) {
            header("Location: ../view/admin/cursos_disponiveis/cadastrar.php?erro=Já existe um curso lançado com essas datas.");
            exit;
        }

        $dados = [
            'curso_id' => $curso_id,
            'empresa_id' => $empresa_id,
            'data_inicio' => $data_inicio,
            'data_termino' => $data_termino,
            'inicio_inscricao' => $inicio_inscricao,
            'termino_inscricao' => $termino_inscricao
        ];

        $sucesso = $cursoDisponivelModel->cadastrar($dados);

        if ($sucesso) {
            header("Location: ../view/admin/cursos_disponiveis/cadastrar.php?sucesso=1");
        } else {
            header("Location: ../view/admin/cursos_disponiveis/cadastrar.php?erro=Erro ao salvar. Tente novamente.");
        }
        exit;

    case 'remover':
        $id = $_GET['id'] ?? null;

        if (!$id || !is_numeric($id)) {
            header("Location: ../view/admin/cursos_disponiveis/listar.php?erro=ID inválido");
            exit;
        }

        // Verifica se pode excluir
        $temInscricoes = $fichaModel->verificarInscricoesCurso($id);
        $temTurmas = $cursoDisponivelModel->temTurmas($id);
        $temTopicos = $cursoDisponivelModel->temTopicos($id);

        if ($temInscricoes || $temTurmas || $temTopicos) {
            header("Location: ../view/admin/cursos_disponiveis/listar.php?erro=Curso possui vínculos (alunos, turmas ou tópicos). Não é possível excluir.");
            exit;
        }

        $removido = $cursoDisponivelModel->remover($id);
        if ($removido) {
            header("Location: ../view/admin/cursos_disponiveis/listar.php?sucesso=1");
        } else {
            header("Location: ../view/admin/cursos_disponiveis/listar.php?erro=Erro ao remover. Tente novamente.");
        }
        exit;

    default:
        header("Location: ../view/dashboard/painel.php");
        exit;
}
