<?php
require_once __DIR__ . '/../conexao.php';

class Curso 
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function listarTodos()
    {
        $stmt = $this->conn->query("
            SELECT c.*, cat.nome AS categoria_nome 
            FROM cursos c
            JOIN categorias cat ON c.categoria_id = cat.id
            ORDER BY c.nome
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id)
    {
        $stmt = $this->conn->prepare("
            SELECT c.*, cat.nome AS categoria_nome 
            FROM cursos c
            JOIN categorias cat ON c.categoria_id = cat.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cadastrar($dados)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO cursos 
            (nome, descricao, categoria_id, carga_horaria, nivel_academico) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $dados['nome'],
            $dados['descricao'],
            $dados['categoria_id'],
            $dados['carga_horaria'],
            $dados['nivel_academico'] ?? null
        ]);
    }

    public function atualizar($id, $dados)
    {
        $stmt = $this->conn->prepare("
            UPDATE cursos SET 
            nome = ?, 
            descricao = ?, 
            categoria_id = ?, 
            carga_horaria = ?,
            nivel_academico = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $dados['nome'],
            $dados['descricao'],
            $dados['categoria_id'],
            $dados['carga_horaria'],
            $dados['nivel_academico'] ?? null,
            $id
        ]);
    }

    public function excluir($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM cursos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function all()
    {
        $stmt = $this->conn->prepare("SELECT * FROM cursos ORDER BY nome");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
	
}
