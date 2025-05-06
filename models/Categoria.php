<?php
require_once __DIR__ . '/../config/conexao.php';

class Categoria {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function cadastrar($nome) {
        $sql = "INSERT INTO categorias (nome) VALUES (:nome)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        return $stmt->execute();
    }

    public function excluir($id) {
        $sql = "DELETE FROM categorias WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function listarTodos() {
        $sql = "SELECT id, nome FROM categorias ORDER BY nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function editar($id, $nome) {
        $sql = "UPDATE categorias SET nome = :nome WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nome', $nome);
        return $stmt->execute();
    }
}
?>
