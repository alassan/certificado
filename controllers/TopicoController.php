<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/ConteudoTopico.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$model = new ConteudoTopico($conn);
$action = $_GET['action'] ?? null;

switch ($action) {
    case 'cadastrar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cursoId = $_POST['curso_disponivel_id'] ?? null;
            $conteudo = trim($_POST['conteudo'] ?? '');
            $ch = intval($_POST['ch'] ?? 0);

            if (!$cursoId || !$conteudo || $ch <= 0) {
                $_SESSION['mensagem_erro'] = 'Preencha todos os campos corretamente.';
                header("Location: ../index.php?page=admin/conteudo_topico/form_topico&curso_id={$cursoId}");
                exit;
            }

            // Busca carga horária do curso base a partir do curso_disponivel
            $stmt = $conn->prepare("
                SELECT c.carga_horaria
                FROM cursos_disponiveis cd
                JOIN cursos c ON c.id = cd.curso_id
                WHERE cd.id = ?
            ");
            $stmt->execute([$cursoId]);
            $curso = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$curso) {
                $_SESSION['mensagem_erro'] = 'Curso não encontrado.';
                header("Location: ../index.php?page=admin/conteudo_topico/form_topico&curso_id={$cursoId}");
                exit;
            }

            $chCursoMin = intval($curso['carga_horaria']) * 60; // convertendo para minutos
            $chUtilizada = $model->somarCargaHoraria($cursoId); // soma atual dos tópicos
            $chRestante = $chCursoMin - $chUtilizada;

            if ($ch > $chRestante) {
                $_SESSION['mensagem_erro'] = "Carga horária excede o limite restante ({$chRestante} minutos).";
                header("Location: ../index.php?page=admin/conteudo_topico/form_topico&curso_id={$cursoId}");
                exit;
            }

            $dados = [
                'curso_disponivel_id' => $cursoId,
                'conteudo' => $conteudo,
                'ch' => $ch
            ];

            if ($model->cadastrar($dados)) {
                $_SESSION['mensagem_sucesso'] = 'Tópico cadastrado com sucesso.';
            } else {
                $_SESSION['mensagem_erro'] = 'Erro ao cadastrar o tópico.';
            }

            header("Location: ../index.php?page=admin/conteudo_topico/form_topico&curso_id={$cursoId}");
            exit;
        }
        break;

    case 'excluir':
        $id = $_GET['id'] ?? null;
        $cursoId = $_GET['curso_id'] ?? null;

        if ($id && $model->excluir($id)) {
            $_SESSION['mensagem_sucesso'] = 'Tópico excluído com sucesso.';
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao excluir o tópico.';
        }

        header("Location: ../index.php?page=admin/conteudo_topico/form_topico&curso_id={$cursoId}");
        exit;

    default:
        $_SESSION['mensagem_erro'] = 'Ação inválida.';
        header("Location: ../index.php?page=admin/conteudo_topico/form_topico");
        exit;
}
