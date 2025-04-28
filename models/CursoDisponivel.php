<?php
class CursoDisponivel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function listarTodos()
{
    $sql = "
        SELECT cd.*, 
               c.nome AS curso_nome,
               e.nome AS empresa_nome
        FROM cursos_disponiveis cd
        JOIN cursos c ON cd.curso_id = c.id
        JOIN empresas e ON cd.empresa_id = e.id
        ORDER BY cd.data_inicio DESC
    ";

    $stmt = $this->conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function buscarPorId($id)
{
    $stmt = $this->conn->prepare("
        SELECT cd.*, c.nome AS curso_nome
        FROM cursos_disponiveis cd
        JOIN cursos c ON cd.curso_id = c.id
        WHERE cd.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


  public function cadastrar($dados)
{
    $stmt = $this->conn->prepare("
        INSERT INTO cursos_disponiveis (curso_id, empresa_id, data_inicio, data_termino, inicio_inscricao, termino_inscricao) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    return $stmt->execute([
        $dados['curso_id'],
        $dados['empresa_id'], // Aqui agora é empresa_id (chave estrangeira)
        $dados['data_inicio'],
        $dados['data_termino'],
        $dados['inicio_inscricao'],
        $dados['termino_inscricao']
    ]);
}




    public function atualizar($id, $dados)
    {
        $stmt = $this->conn->prepare("UPDATE cursos_disponiveis SET curso_id = ?, empresa = ?, data_inicio = ?, data_termino = ?, inicio_inscricao = ?, termino_inscricao = ? WHERE id = ?");
        return $stmt->execute([
            $dados['curso_id'],
            $dados['empresa'],
            $dados['data_inicio'],
            $dados['data_termino'],
            $dados['inicio_inscricao'],
            $dados['termino_inscricao'],
            $id
        ]);
    }

    public function remover($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM cursos_disponiveis WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function verificarDuplicidade($curso_id, $data_inicio, $data_termino)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM cursos_disponiveis WHERE curso_id = ? AND data_inicio = ? AND data_termino = ?");
        $stmt->execute([$curso_id, $data_inicio, $data_termino]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

    public function buscarPorUsuarioEStatus($usuario_id, $status)
{
    $stmt = $this->conn->prepare("SELECT cd.*, c.nome AS curso_nome
        FROM fichas_inscricao fi
        JOIN cursos_disponiveis cd ON fi.curso_disponivel_id = cd.id
        JOIN cursos c ON cd.curso_id = c.id
        WHERE fi.usuario_id = ? AND fi.status = ?
        ORDER BY cd.data_inicio DESC");
    $stmt->execute([$usuario_id, $status]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

	
	public function listarAtivos() {
    $sql = "SELECT cd.*, c.nome, c.carga_horaria 
            FROM cursos_disponiveis cd
            JOIN cursos c ON c.id = cd.curso_id
            WHERE cd.data_termino >= CURDATE()";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getConn()
    {
        return $this->conn;
    }
}
