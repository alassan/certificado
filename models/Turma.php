<?php
class Turma {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function cadastrar($nome, $data_inicio, $data_fim, $curso_id, $empresa_id, $local, $professor_id) {
        $sql = "INSERT INTO turma (nome, data_inicio, data_fim, curso_id, empresa_id, local, professor_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nome, $data_inicio, $data_fim, $curso_id, $empresa_id, $local, $professor_id]);
    }
    
    public function listar() {
        $sql = "SELECT t.*, c.nome as curso_nome, e.nome as empresa_nome, p.nome as professor_nome 
                FROM turma t
                LEFT JOIN cursos c ON c.id = t.curso_id
                LEFT JOIN empresas e ON e.id = t.empresa_id
                LEFT JOIN professores p ON p.id = t.professor_id
                ORDER BY t.data_inicio DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT t.*, c.nome as curso_nome, e.nome as empresa_nome, p.nome as professor_nome 
                FROM turma t
                LEFT JOIN cursos c ON c.id = t.curso_id
                LEFT JOIN empresas e ON e.id = t.empresa_id
                LEFT JOIN professores p ON p.id = t.professor_id
                WHERE t.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function atualizar($id, $nome, $data_inicio, $data_fim, $curso_id, $empresa_id, $local, $professor_id) {
        $sql = "UPDATE turma SET nome = ?, data_inicio = ?, data_fim = ?, curso_id = ?, 
                empresa_id = ?, local = ?, professor_id = ? 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nome, $data_inicio, $data_fim, $curso_id, $empresa_id, $local, $professor_id, $id]);
    }
    
    public function excluir($id) {
        $sql = "DELETE FROM turma WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function listarCursos() {
        $sql = "SELECT id, nome FROM cursos ORDER BY nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function listarEmpresas() {
        $sql = "SELECT id, nome FROM empresas WHERE ativo = 1 ORDER BY nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function listarProfessores() {
        $sql = "SELECT id, nome FROM professores ORDER BY nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>