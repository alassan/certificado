<?php
require_once 'model/Certificado.php';

public function buscarCurso()
{
    session_start();
    require_once __DIR__ . '/../models/Certificado.php';
    $certificadoModel = new Certificado($this->conn);

    $curso = trim($_POST['curso'] ?? '');

    if (empty($curso)) {
        $_SESSION['erro'] = "Informe o nome do curso.";
        header("Location: /view/certificado/form_busca.php");
        exit;
    }

    // Busca certificados apenas de cursos concluídos
    $resultados = $certificadoModel->buscarPorCursoConcluido($curso);

    if (!$resultados || count($resultados) === 0) {
        $_SESSION['erro'] = "Nenhum certificado encontrado para o curso informado.";
        header("Location: /view/certificado/form_busca.php");
        exit;
    }

    $_SESSION['resultados_certificados'] = $resultados;
    header("Location: /view/certificado/listar_resultado.php");
    exit;
}

?>