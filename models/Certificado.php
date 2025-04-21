<?php
class Certificado
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Buscar cursos concluídos do aluno logado
    public function buscarCursosConcluidosDoAluno($usuario_id)
    {
        $sql = "
            SELECT c.nome AS curso_nome, cd.id AS curso_disponivel_id
            FROM fichas_inscricao fi
            JOIN cursos_disponiveis cd ON cd.id = fi.curso_disponivel_id
            JOIN cursos c ON c.id = cd.curso_id
            WHERE fi.usuario_id = ? AND fi.status = 'concluido'
            ORDER BY c.nome ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar certificados por curso concluído para o Admin
    public function buscarPorCursoConcluido($termo)
    {
        $sql = "
            SELECT fi.*, u.nome AS aluno_nome, c.nome AS curso_nome, cd.data_inicio, cd.data_termino, cd.empresa
            FROM fichas_inscricao fi
            JOIN usuarios u ON u.id = fi.usuario_id
            JOIN cursos_disponiveis cd ON cd.id = fi.curso_disponivel_id
            JOIN cursos c ON c.id = cd.curso_id
            WHERE fi.status = 'concluido' AND c.nome LIKE ?
            ORDER BY u.nome ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['%' . $termo . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar certificado por nome ou CPF (Admin)
    public function buscarPorNomeOuCpf($termo)
    {
        $sql = "
            SELECT fi.*, u.nome AS aluno_nome, u.cpf, c.nome AS curso_nome, cd.data_inicio, cd.data_termino, cd.empresa
            FROM fichas_inscricao fi
            JOIN usuarios u ON u.id = fi.usuario_id
            JOIN cursos_disponiveis cd ON cd.id = fi.curso_disponivel_id
            JOIN cursos c ON c.id = cd.curso_id
            WHERE fi.status = 'concluido' AND (u.nome LIKE ? OR u.cpf LIKE ?)
            ORDER BY u.nome ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['%' . $termo . '%', '%' . $termo . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
