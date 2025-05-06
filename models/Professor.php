<?php

class Professor
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ✅ LISTAR TODOS OS PROFESSORES
    public function listarTodos()
    {
        $sql = "SELECT id, nome, email, telefone FROM professores ORDER BY nome ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ BUSCAR PROFESSOR POR ID
    public function buscarPorId($id)
    {
        $sql = "SELECT * FROM professores WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ CADASTRAR NOVO PROFESSOR
    public function cadastrar($nome, $email, $telefone)
    {
        $sql = "INSERT INTO professores (nome, email, telefone) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nome, $email, $telefone]);
    }

    // ✅ ATUALIZAR DADOS DO PROFESSOR
    public function atualizar($id, $nome, $email, $telefone)
    {
        $sql = "UPDATE professores SET nome = ?, email = ?, telefone = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nome, $email, $telefone, $id]);
    }

    // ✅ EXCLUIR PROFESSOR
    public function remover($id)
    {
        $sql = "DELETE FROM professores WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // ✅ VERIFICAR SE O PROFESSOR JÁ EXISTE (por nome)
    public function verificarDuplicado($nome)
    {
        $sql = "SELECT COUNT(*) FROM professores WHERE nome = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nome]);
        return $stmt->fetchColumn() > 0;
    }
}
