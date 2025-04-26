<?php
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../conexao.php';

class CategoriaController {
    private $conn;
    private $categoriaModel;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->categoriaModel = new Categoria($conn);
    }

    public function cadastrar($nome) {
        if (!empty($nome)) {
            return $this->categoriaModel->cadastrar($nome);
        }
        return false;
    }

    public function listar() {
        return $this->categoriaModel->listarTodos();
    }

    public function editar($id, $nome) {
        if (!empty($id) && !empty($nome)) {
            return $this->categoriaModel->editar($id, $nome);
        }
        return false;
    }

    public function excluir($id) {
        if (!empty($id)) {
            return $this->categoriaModel->excluir($id);
        }
        return false;
    }
}
?>
