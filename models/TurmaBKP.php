<?php

class Turma
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ✅ Método para cadastrar turma
 public function cadastrar($dados)
{
    $sql = "INSERT INTO turma 
            (nome, curso_disponivel_id, capacidade_maxima, 
             status, professor_id, local, alocar_automaticamente)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $this->conn->prepare($sql);

    return $stmt->execute([
        $dados['nome'],
        $dados['curso_disponivel_id'],
        $dados['capacidade_maxima'],
        $dados['status'] ?? 'aberta',
        $dados['professor_id'] ?? null,
        $dados['local'],
        $dados['alocar_automaticamente'] ?? 0
    ]);
}

public function listarTodas() {
    $sql = "SELECT 
                t.*, 
                cd.data_inicio AS curso_data_inicio, 
                cd.data_termino AS curso_data_termino,
                c.nome AS curso_nome
            FROM turma t
            LEFT JOIN cursos_disponiveis cd ON t.curso_disponivel_id = cd.id
            LEFT JOIN cursos c ON cd.curso_id = c.id
            ORDER BY t.nome ASC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    // ✅ Método para buscar turma por ID
    public function buscarPorId($id) {
    $sql = "SELECT 
                t.*, 
                cd.data_inicio AS curso_data_inicio, 
                cd.data_termino AS curso_data_termino,
                c.nome AS curso_nome,
                p.nome AS professor_nome,
                e.nome AS empresa_nome
            FROM turma t
            LEFT JOIN cursos_disponiveis cd ON t.curso_disponivel_id = cd.id
            LEFT JOIN cursos c ON cd.curso_id = c.id
            LEFT JOIN professores p ON cd.professor_id = p.id
            LEFT JOIN empresas e ON cd.empresa_id = e.id
            WHERE t.id = :id";
    
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


    // ✅ Método para atualizar turma
    public function atualizar($id, $dados)
    {
        $sql = "UPDATE turma SET 
                    nome = ?, 
                    curso_disponivel_id = ?, 
                    data_inicio = ?, 
                    data_fim = ?, 
                    capacidade_maxima = ?, 
                    status = ?, 
                    professor_id = ?, 
                    local = ?, 
                    alocar_automaticamente = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $dados['nome'],
            $dados['curso_disponivel_id'],
            $dados['data_inicio'],
            $dados['data_fim'],
            $dados['capacidade_maxima'],
            $dados['status'],
            $dados['professor_id'],
            $dados['local'],
            $dados['alocar_automaticamente'],
            $id
        ]);
    }

    // ✅ Método para excluir turma
    public function excluir($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM turma WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
