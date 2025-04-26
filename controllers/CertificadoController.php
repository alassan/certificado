<?php
// Inclui o model responsável pelas operações com certificados
require_once __DIR__ . '/../models/Certificado.php';

// Inicia a sessão para utilizar variáveis de sessão (ex: mensagens, dados)
session_start();

/**
 * Classe responsável pelo controle das ações relacionadas aos certificados.
 * Atua como intermediária entre o model (dados) e as views (interface).
 */
class CertificadoController {
    private $conn;

    /**
     * Construtor da classe. Recebe a conexão com o banco de dados via injeção de dependência.
     *
     * @param PDO $conn Conexão com o banco de dados
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Método que processa a busca de certificados com base no nome do curso.
     * Utilizado para exibir resultados na tela de busca.
     */
    public function buscarCurso() {
        // Verifica se a requisição foi feita via POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /view/certificado/form_busca.php");
            exit;
        }

        // Obtém e sanitiza o nome do curso
        $curso = trim($_POST['curso'] ?? '');

        // Valida se o campo foi preenchido
        if (empty($curso)) {
            $_SESSION['erro'] = "Informe o nome do curso.";
            header("Location: /view/certificado/form_busca.php");
            exit;
        }

        // Instancia o model e busca certificados de cursos concluídos com base no nome informado
        $certificadoModel = new Certificado($this->conn);
        $resultados = $certificadoModel->buscarPorCursoConcluido($curso);

        // Verifica se há resultados
        if (!$resultados) {
            $_SESSION['erro'] = "Nenhum certificado encontrado para o curso informado.";
            header("Location: /view/certificado/form_busca.php");
            exit;
        }

        // Armazena os resultados em sessão para exibição posterior
        $_SESSION['resultados_certificados'] = $resultados;
        header("Location: /view/certificado/listar_resultado.php");
        exit;
    }
}
