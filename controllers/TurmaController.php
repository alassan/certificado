<?php
require_once __DIR__ . '/../models/Turma.php';
require_once __DIR__ . '/../models/Empresa.php';
require_once __DIR__ . '/../models/Professor.php';

class TurmaController {
    private $turmaModel;
    private $empresaModel;
    private $professorModel;
    
    public function __construct($conn) {
        $this->turmaModel = new Turma($conn);
        $this->empresaModel = new Empresa($conn);
        $this->professorModel = new Professor($conn);
    }
    
    public function listar() {
        $turmas = $this->turmaModel->listar();
        require_once __DIR__ . '/../views/turmas/listar.php';
    }
    
    public function cadastrar() {
        $cursos = $this->turmaModel->listarCursos();
        $empresas = $this->turmaModel->listarEmpresas();
        $professores = $this->turmaModel->listarProfessores();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome'] ?? '';
            $data_inicio = $_POST['data_inicio'] ?? '';
            $data_fim = $_POST['data_fim'] ?? '';
            $curso_id = $_POST['curso_id'] ?? '';
            $empresa_id = $_POST['empresa_id'] ?? '';
            $local = $_POST['local'] ?? '';
            $professor_id = $_POST['professor_id'] ?? '';
            
            if ($this->turmaModel->cadastrar($nome, $data_inicio, $data_fim, $curso_id, $empresa_id, $local, $professor_id)) {
                $_SESSION['mensagem_sucesso'] = 'Turma cadastrada com sucesso!';
                header('Location: /turmas/listar');
                exit;
            } else {
                $_SESSION['mensagem_erro'] = 'Erro ao cadastrar turma.';
            }
        }
        
        require_once __DIR__ . '/../views/turmas/cadastrar.php';
    }
    
    public function editar($id) {
        $cursos = $this->turmaModel->listarCursos();
        $empresas = $this->turmaModel->listarEmpresas();
        $professores = $this->turmaModel->listarProfessores();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome'] ?? '';
            $data_inicio = $_POST['data_inicio'] ?? '';
            $data_fim = $_POST['data_fim'] ?? '';
            $curso_id = $_POST['curso_id'] ?? '';
            $empresa_id = $_POST['empresa_id'] ?? '';
            $local = $_POST['local'] ?? '';
            $professor_id = $_POST['professor_id'] ?? '';
            
            if ($this->turmaModel->atualizar($id, $nome, $data_inicio, $data_fim, $curso_id, $empresa_id, $local, $professor_id)) {
                $_SESSION['mensagem_sucesso'] = 'Turma atualizada com sucesso!';
                header('Location: /turmas/listar');
                exit;
            } else {
                $_SESSION['mensagem_erro'] = 'Erro ao atualizar turma.';
            }
        }
        
        $turma = $this->turmaModel->buscarPorId($id);
        if (!$turma) {
            $_SESSION['mensagem_erro'] = 'Turma não encontrada.';
            header('Location: /turmas/listar');
            exit;
        }
        
        require_once __DIR__ . '/../views/turmas/editar.php';
    }
    
    public function excluir($id) {
        if ($this->turmaModel->excluir($id)) {
            $_SESSION['mensagem_sucesso'] = 'Turma excluída com sucesso!';
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao excluir turma.';
        }
        header('Location: /turmas/listar');
        exit;
    }
    
    public function visualizar($id) {
        $turma = $this->turmaModel->buscarPorId($id);
        if (!$turma) {
            $_SESSION['mensagem_erro'] = 'Turma não encontrada.';
            header('Location: /turmas/listar');
            exit;
        }
        require_once __DIR__ . '/../views/turmas/visualizar.php';
    }
}
?>