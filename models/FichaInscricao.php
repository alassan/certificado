<?php
class FichaInscricao
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function save($dados)
    {
        try {
            // Validação dos campos obrigatórios
            $camposObrigatorios = [
                'nome_aluno', 'cpf', 'data_nascimento', 'contato',
                'curso_id', 'curso_disponivel_id', 'endereco_id', 'usuario_id'
            ];
            
            foreach ($camposObrigatorios as $campo) {
                if (!isset($dados[$campo])) {
                    throw new InvalidArgumentException("Campo obrigatório '$campo' não fornecido");
                }
            }

            $sql = "INSERT INTO fichas_inscricao (
                        nome_aluno, cpf, data_nascimento, contato, 
                        curso_id, curso_disponivel_id, endereco_id,
                        pmt_funcionario, data_inscricao, observacoes,
                        usuario_id, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, 'ativo')";

            $stmt = $this->conn->prepare($sql);
            
            $params = [
                $dados['nome_aluno'],
                $dados['cpf'],
                $dados['data_nascimento'],
                $dados['contato'],
                $dados['curso_id'],
                $dados['curso_disponivel_id'],
                $dados['endereco_id'],
                $dados['pmt_funcionario'],
                $dados['observacoes'],
                $dados['usuario_id']
            ];

            $success = $stmt->execute($params);
            
            if (!$success) {
                error_log("Erro ao salvar ficha: " . print_r($stmt->errorInfo(), true));
                return false;
            }
            
            return $this->conn->lastInsertId(); // Retorna o ID inserido
            
        } catch (PDOException $e) {
            error_log("PDOException ao salvar ficha: " . $e->getMessage());
            throw $e;
        }
    }

    public function getById($id)
    {
        $sql = "SELECT f.*, cd.id AS curso_disponivel_id, c.nome AS curso_nome, e.* 
                FROM fichas_inscricao f
                JOIN cursos_disponiveis cd ON cd.id = f.curso_disponivel_id
                JOIN cursos c ON c.id = cd.curso_id
                JOIN enderecos e ON e.id = f.endereco_id
                WHERE f.id = ? LIMIT 1";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listar()
    {
        $sql = "SELECT fi.*, c.nome AS curso_nome, p.nome AS professor_nome
                FROM fichas_inscricao fi
                LEFT JOIN cursos c ON fi.curso_id = c.id
                LEFT JOIN professores p ON fi.professor_id = p.id
                ORDER BY fi.data_inscricao DESC";
                
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
	public function verificarInscricoesCurso($cursoDisponivelId) {
    $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM fichas_inscricao WHERE curso_disponivel_id = ?");
    $stmt->execute([$cursoDisponivelId]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    return $resultado['total'] > 0;
   }
   public function listarPorCursoDisponivel($cursoDisponivelId) {
    $stmt = $this->conn->prepare("
        SELECT fi.*, u.nome as nome_aluno 
        FROM fichas_inscricao fi
        JOIN usuarios u ON fi.usuario_id = u.id
        WHERE fi.curso_disponivel_id = ?
        ORDER BY fi.data_inscricao DESC
    ");
    $stmt->execute([$cursoDisponivelId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }
}