<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/Empresa.php';

session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

class EmpresaController {
    private $empresaModel;

    public function __construct($conn) {
        $this->empresaModel = new Empresa($conn);
    }

    public function listar() {
        try {
            $empresas = $this->empresaModel->listar();
            require_once __DIR__ . '/../view/empresas/empresa_listar.php';
        } catch (Exception $e) {
            $_SESSION['mensagem_erro'] = $e->getMessage();
            header("Location: /certificado/index.php?page=empresa/empresa_listar");
            exit;
        }
    }

    public function cadastrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $dados = $this->sanitizarDados($_POST);
                $dados['logo_path'] = $this->salvarLogoUpload();

                if ($this->empresaModel->cadastrar($dados)) {
                    $_SESSION['mensagem_sucesso'] = 'Empresa cadastrada com sucesso!';
                    header('Location: /certificado/index.php?page=empresa/empresa_listar');
                    exit;
                } else {
                    throw new Exception('Erro ao salvar empresa.');
                }
            } catch (Exception $e) {
                $_SESSION['mensagem_erro'] = $e->getMessage();
                $_SESSION['dados_formulario'] = $_POST;
                header('Location: /certificado/index.php?page=empresa/empresa_cadastrar');
                exit;
            }
        }

        require_once __DIR__ . '/../view/empresas/empresa_cadastrar.php';
    }

    public function editar($id) {
        try {
            $empresa = $this->empresaModel->buscarPorId($id);
            if (!$empresa) throw new Exception('Empresa não encontrada');

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $dados = $this->sanitizarDados($_POST);

                $novaLogo = $this->salvarLogoUpload();
                if ($novaLogo) {
                    $dados['logo_path'] = $novaLogo;
                } else {
                    $dados['logo_path'] = $empresa['logo_path']; // mantém a anterior
                }

                if ($this->empresaModel->atualizar($id, $dados)) {
                    $_SESSION['mensagem_sucesso'] = 'Empresa atualizada com sucesso!';
                    header('Location: /certificado/index.php?page=empresa/empresa_listar');
                    exit;
                } else {
                    throw new Exception('Nenhuma alteração foi realizada.');
                }
            }

            require_once __DIR__ . '/../view/empresas/empresa_editar.php';
        } catch (Exception $e) {
            $_SESSION['mensagem_erro'] = $e->getMessage();
            header('Location: /certificado/index.php?page=empresa/empresa_listar');
            exit;
        }
    }

    public function excluir($id) {
        try {
            if ($this->empresaModel->excluir($id)) {
                $_SESSION['mensagem_sucesso'] = 'Empresa excluída com sucesso!';
            } else {
                throw new Exception('Erro ao excluir empresa.');
            }
        } catch (Exception $e) {
            $_SESSION['mensagem_erro'] = $e->getMessage();
        }

        header('Location: /certificado/index.php?page=empresa/empresa_listar');
        exit;
    }

    public function visualizar($id) {
        try {
            $empresa = $this->empresaModel->buscarPorId($id);
            if (!$empresa) throw new Exception('Empresa não encontrada.');
            require_once __DIR__ . '/../view/empresas/empresa_visualizar.php';
        } catch (Exception $e) {
            $_SESSION['mensagem_erro'] = $e->getMessage();
            header('Location: /certificado/index.php?page=empresa/empresa_listar');
            exit;
        }
    }

    private function sanitizarDados($dados) {
        return [
            'nome'        => trim($dados['nome']),
            'cnpj'        => preg_replace('/[^0-9]/', '', $dados['cnpj']),
            'endereco'    => trim($dados['endereco']),
            'telefone'    => trim($dados['telefone']),
            'email'       => filter_var(trim($dados['email']), FILTER_SANITIZE_EMAIL),
            'responsavel' => trim($dados['responsavel'])
        ];
    }

    private function salvarLogoUpload(): ?string {
        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $nomeFinal = 'uploads/logos/' . uniqid('logo_') . '.' . $ext;

        $caminhoAbsoluto = __DIR__ . '/../' . $nomeFinal;
        if (!is_dir(dirname($caminhoAbsoluto))) {
            mkdir(dirname($caminhoAbsoluto), 0777, true);
        }

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $caminhoAbsoluto)) {
            return $nomeFinal;
        }

        return null;
    }
}

// Roteamento
$controller = new EmpresaController($conn);
$acao = $_GET['acao'] ?? '';

switch ($acao) {
    case 'listar':
        $controller->listar(); break;
    case 'cadastrar':
        $controller->cadastrar(); break;
    case 'editar':
        if (!empty($_GET['id'])) $controller->editar((int) $_GET['id']);
        break;
    case 'excluir':
        if (!empty($_GET['id'])) $controller->excluir((int) $_GET['id']);
        break;
    case 'visualizar':
        if (!empty($_GET['id'])) $controller->visualizar((int) $_GET['id']);
        break;
    default:
        echo "Ação não reconhecida."; break;
}
