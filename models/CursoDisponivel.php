<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class CursoDisponivel
{
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getConn() {
        return $this->conn;
    }

    public function listarTodos() {
        $sql = "SELECT cd.*, c.nome AS curso_nome, e.nome AS empresa_nome
                FROM cursos_disponiveis cd
                JOIN cursos c ON cd.curso_id = c.id
                LEFT JOIN empresas e ON cd.empresa_id = e.id
                ORDER BY cd.data_inicio DESC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAtivos() {
        $hoje = date('Y-m-d');
        $sql = "SELECT cd.*, c.nome AS nome, c.descricao, c.carga_horaria
                FROM cursos_disponiveis cd
                JOIN cursos c ON c.id = cd.curso_id
                WHERE :hoje BETWEEN cd.inicio_inscricao AND cd.termino_inscricao
                ORDER BY cd.data_inicio ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['hoje' => $hoje]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->conn->prepare("SELECT cd.*, c.nome AS curso_nome
                                      FROM cursos_disponiveis cd
                                      JOIN cursos c ON cd.curso_id = c.id
                                      WHERE cd.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarComEmpresaPorId($id) {
        $sql = "SELECT cd.*, c.nome AS curso_nome, e.nome AS empresa_nome, p.nome AS professor_nome
                FROM cursos_disponiveis cd
                JOIN cursos c ON cd.curso_id = c.id
                LEFT JOIN empresas e ON cd.empresa_id = e.id
                LEFT JOIN professores p ON cd.professor_id = p.id
                WHERE cd.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function cadastrar($dados) {
        $stmt = $this->conn->prepare("INSERT INTO cursos_disponiveis 
                (curso_id, empresa_id, data_inicio, data_termino, inicio_inscricao, termino_inscricao) 
                VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $dados['curso_id'], $dados['empresa_id'],
            $dados['data_inicio'], $dados['data_termino'],
            $dados['inicio_inscricao'], $dados['termino_inscricao']
        ]);
    }

    public function atualizar($id, $dados) {
        $stmt = $this->conn->prepare("UPDATE cursos_disponiveis SET 
                    curso_id = ?, empresa_id = ?, data_inicio = ?, data_termino = ?, 
                    inicio_inscricao = ?, termino_inscricao = ? WHERE id = ?");
        return $stmt->execute([
            $dados['curso_id'], $dados['empresa_id'],
            $dados['data_inicio'], $dados['data_termino'],
            $dados['inicio_inscricao'], $dados['termino_inscricao'], $id
        ]);
    }

    public function podeExcluir($id) {
        // Verifica turmas
        $stmt1 = $this->conn->prepare("SELECT COUNT(*) FROM turma WHERE curso_disponivel_id = ?");
        $stmt1->execute([$id]);
        if ($stmt1->fetchColumn() > 0) return false;

        // Verifica alunos inscritos
        $stmt2 = $this->conn->prepare("SELECT COUNT(*) FROM fichas_inscricao WHERE curso_disponivel_id = ?");
        $stmt2->execute([$id]);
        if ($stmt2->fetchColumn() > 0) return false;

        // Verifica tópicos de conteúdo
        $stmt3 = $this->conn->prepare("SELECT COUNT(*) FROM conteudo_topico WHERE curso_disponivel_id = ?");
        $stmt3->execute([$id]);
        if ($stmt3->fetchColumn() > 0) return false;

        return true;
    }

    public function remover($id) {
        if (!$this->podeExcluir($id)) {
            return false; // Impede exclusão
        }

        $stmt = $this->conn->prepare("DELETE FROM cursos_disponiveis WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function verificarDuplicidade($curso_id, $data_inicio, $data_termino) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM cursos_disponiveis 
                                      WHERE curso_id = ? AND data_inicio = ? AND data_termino = ?");
        $stmt->execute([$curso_id, $data_inicio, $data_termino]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($resultado['total'] ?? 0) > 0;
    }

    public function listarCursosDisponiveisParaInscricao($usuario_id) {
        $hoje = date('Y-m-d');
        $sql = "SELECT cd.id, c.nome AS curso_nome, cd.inicio_inscricao, cd.termino_inscricao, cd.data_inicio, cd.data_termino
                FROM cursos_disponiveis cd
                JOIN cursos c ON c.id = cd.curso_id
                WHERE cd.inicio_inscricao <= :hoje AND cd.termino_inscricao >= :hoje
                  AND cd.id NOT IN (
                      SELECT curso_disponivel_id 
                      FROM fichas_inscricao 
                      WHERE usuario_id = :usuario_id
                  )
                ORDER BY c.nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'hoje' => $hoje,
            'usuario_id' => $usuario_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorUsuarioEStatusAluno($usuario_id, $status) {
        $statusPermitidos = ['matriculado', 'em_andamento', 'concluido', 'espera', 'cancelado'];
        if (!in_array($status, $statusPermitidos)) {
            return [];
        }

        $sql = "SELECT 
                    MAX(fi.id) AS ficha_id,
                    fi.status_aluno,
                    cd.id AS curso_disponivel_id,
                    c.nome AS curso_nome,
                    cd.data_inicio, cd.data_termino,
                    t.nome AS turma_nome,
                    p.nome AS professor_nome,
                    e.nome AS empresa_nome,
                    CASE 
                        WHEN cd.data_termino < CURDATE() THEN 'concluido'
                        WHEN cd.data_inicio <= CURDATE() AND cd.data_termino >= CURDATE() THEN 'em_andamento'
                        WHEN cd.data_inicio > CURDATE() THEN 'matriculado'
                        ELSE fi.status_aluno
                    END AS status_curso
                FROM fichas_inscricao fi
                JOIN cursos_disponiveis cd ON fi.curso_disponivel_id = cd.id
                JOIN cursos c ON cd.curso_id = c.id
                LEFT JOIN turma t ON fi.turma_id = t.id
                LEFT JOIN professores p ON t.professor_id = p.id
                LEFT JOIN empresas e ON cd.empresa_id = e.id
                WHERE fi.usuario_id = ? AND fi.status_aluno = ?
                GROUP BY cd.id
                ORDER BY cd.data_inicio DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuario_id, $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
	
	public function temTurmas($curso_disponivel_id) {
    $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM turma WHERE curso_disponivel_id = ?");
    $stmt->execute([$curso_disponivel_id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($resultado['total'] ?? 0) > 0;
}

public function temTopicos($curso_disponivel_id) {
    $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM conteudo_topico WHERE curso_disponivel_id = ?");
    $stmt->execute([$curso_disponivel_id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($resultado['total'] ?? 0) > 0;
}

	
}
