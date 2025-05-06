<?php
require_once __DIR__ . '/Turma.php';

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
        $statusAluno = 'espera';

        // Buscar as datas do curso_disponivel
        $curso = $this->buscarCursoDisponivel($dados['curso_disponivel_id']);
        $dataInicio = $curso['data_inicio'] ?? null;
        $dataTermino = $curso['data_termino'] ?? null;

        $sql = "INSERT INTO fichas_inscricao (
                    nome_aluno, cpf, data_nascimento, contato,
                    curso_id, curso_disponivel_id, endereco_id,
                    pmt_funcionario, data_inscricao, data_inicio, data_termino,
                    observacoes, usuario_id, status_aluno
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $dados['nome_aluno'], $dados['cpf'], $dados['data_nascimento'], $dados['contato'],
            $dados['curso_id'], $dados['curso_disponivel_id'], $dados['endereco_id'],
            $dados['pmt_funcionario'] ?? 0, // parâmetro 8
            $dataInicio, // parâmetro 9
            $dataTermino, // parâmetro 10
            $dados['observacoes'] ?? null, // parâmetro 11
            $dados['usuario_id'], // parâmetro 12
            $statusAluno // parâmetro 13
        ]);

        $novaFichaId = $this->conn->lastInsertId();
        $turmaModel = new Turma($this->conn);

        // Buscar turma existente com vaga
        $turma = $turmaModel->buscarTurmaComVagas($dados['curso_disponivel_id']);

        // Caso não exista, criar nova turma automaticamente
        if (!$turma) {
            $ultimaTurma = $turmaModel->buscarUltimaTurmaCurso($dados['curso_disponivel_id']);
            $letra = 'A';

            if ($ultimaTurma && preg_match('/([A-Z])$/', $ultimaTurma['nome'], $match)) {
                $letra = chr(ord($match[1]) + 1);
            }

            $nomeTurma = "Turma $letra";

            $turmaId = $turmaModel->cadastrar([
                'nome' => $nomeTurma,
                'curso_disponivel_id' => $dados['curso_disponivel_id'],
                'capacidade_maxima' => 20,
                'status' => 'aberta',
                'professor_id' => null,
                'local' => 'A definir',
                'alocar_automaticamente' => 1
            ]);

            $turma = $turmaModel->buscarPorId($turmaId);
        }

        // Alocar aluno e atualizar status
        if ($turma && $turma['vagas_disponiveis'] > 0) {
            if ($turmaModel->alocarAlunoNaTurma($turma['id'], $novaFichaId)) {
                $this->atualizarStatus($novaFichaId, 'matriculado');
            }
        }

        return $novaFichaId;

    } catch (PDOException $e) {
        error_log("Erro ao salvar ficha: " . $e->getMessage());
        throw $e;
    }
}




    public function getById($id)
    {
        $sql = "SELECT 
                    f.*, 
                    cd.id AS curso_disponivel_id, 
                    c.nome AS curso_nome, 
                    e.logradouro, e.numero, e.bairro, e.cidade, e.uf, e.cep
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

        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function verificarInscricoesCurso($cursoDisponivelId)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM fichas_inscricao WHERE curso_disponivel_id = ?");
        $stmt->execute([$cursoDisponivelId]);
        return $stmt->fetchColumn() > 0;
    }

    public function listarPorCursoDisponivel($cursoDisponivelId)
    {
        $stmt = $this->conn->prepare("SELECT fi.*, u.nome as nome_aluno FROM fichas_inscricao fi
                                       JOIN usuarios u ON fi.usuario_id = u.id
                                       WHERE fi.curso_disponivel_id = ?
                                       ORDER BY fi.data_inscricao DESC");
        $stmt->execute([$cursoDisponivelId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarFichasMatriculadasPorCursoDisponivel($cursoDisponivelId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM fichas_inscricao 
                                       WHERE curso_disponivel_id = ? 
                                         AND turma_id IS NOT NULL 
                                         AND turma_id != 0 
                                         AND status_aluno = 'matriculado'");
        $stmt->execute([$cursoDisponivelId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarFichasListaEsperaPorCursoDisponivel($cursoDisponivelId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM fichas_inscricao 
                                       WHERE curso_disponivel_id = ? 
                                         AND status_aluno = 'espera'");
        $stmt->execute([$cursoDisponivelId]);
        return $stmt->fetchAll();
    }

    public function buscarUltimaInscricao($usuario_id)
    {
        $stmt = $this->conn->prepare("SELECT f.*, e.* FROM fichas_inscricao f
                                       JOIN enderecos e ON e.id = f.endereco_id
                                       WHERE f.usuario_id = ? 
                                       ORDER BY f.data_inscricao DESC LIMIT 1");
        $stmt->execute([$usuario_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarCursoDisponivel($cursoDisponivelId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM cursos_disponiveis WHERE id = ?");
        $stmt->execute([$cursoDisponivelId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizarStatus($fichaId, $novoStatus)
    {
        $stmt = $this->conn->prepare("UPDATE fichas_inscricao SET status_aluno = ? WHERE id = ?");
        return $stmt->execute([$novoStatus, $fichaId]);
    }

    public function buscarParaAtualizacaoStatus()
{
    $sql = "SELECT f.id, f.status_aluno, f.curso_disponivel_id 
            FROM fichas_inscricao f
            JOIN cursos_disponiveis cd ON f.curso_disponivel_id = cd.id
            WHERE f.status_aluno IN ('matriculado', 'em_andamento')
              AND (
                  (f.status_aluno = 'matriculado' AND cd.data_inicio <= CURDATE()) OR
                  (f.status_aluno = 'em_andamento' AND cd.data_termino < CURDATE())
              )";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



    public function atualizarStatusAutomaticamente()
{
    $fichas = $this->buscarParaAtualizacaoStatus();

    foreach ($fichas as $ficha) {
        $curso = $this->buscarCursoDisponivel($ficha['curso_disponivel_id']);
        $statusAtual = $ficha['status_aluno'];
        $novoStatus = $statusAtual;

        $hoje = date('Y-m-d');
        $dataInicio = $curso['data_inicio'] ?? null;
        $dataFim = $curso['data_termino'] ?? null; // ✅ corrigido aqui

        if ($statusAtual === 'matriculado' && $dataInicio && $hoje >= $dataInicio) {
            $novoStatus = 'em_andamento';
        } elseif ($statusAtual === 'em_andamento' && $dataFim && $hoje > $dataFim) {
            $novoStatus = 'concluido';
        }

        if ($novoStatus !== $statusAtual) {
            $this->atualizarStatus($ficha['id'], $novoStatus);
        }
    }

    return count($fichas);
}


    public function contarListaEsperaPorCurso($curso_disponivel_id)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM fichas_inscricao WHERE curso_disponivel_id = ? AND status_aluno = 'espera'");
        $stmt->execute([$curso_disponivel_id]);
        return (int) $stmt->fetchColumn();
    }

    public function buscarInscricoesNaoAlocadas($cursoDisponivelId)
    {
        $stmt = $this->conn->prepare("SELECT fi.* FROM fichas_inscricao fi
                                       WHERE fi.curso_disponivel_id = ?
                                         AND (fi.turma_id IS NULL OR fi.turma_id = 0)
                                         AND fi.status_aluno = 'matriculado'");
        $stmt->execute([$cursoDisponivelId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
	
	
	
	public function buscarAlunosEmEsperaPorCurso($cursoId)
{
    $sql = "SELECT f.*, cd.data_inicio AS data_inicio_curso, cd.data_termino AS data_fim_curso
            FROM fichas_inscricao f
            JOIN cursos_disponiveis cd ON cd.id = f.curso_disponivel_id
            WHERE f.status_aluno = 'espera'
              AND cd.curso_id = ?
            ORDER BY f.data_inscricao ASC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$cursoId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function buscarFichaPorUsuarioECurso($usuario_id, $curso_disponivel_id)
{
    $sql = "SELECT * FROM fichas_inscricao 
            WHERE usuario_id = ? AND curso_disponivel_id = ?
            ORDER BY data_inscricao DESC
            LIMIT 1";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$usuario_id, $curso_disponivel_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


public function buscarTodasPorUsuarioEStatusAgrupado($usuario_id, $statusFiltro)
{
    $sql = "SELECT 
                fi.id as ficha_id,
                fi.curso_disponivel_id,
                cd.data_inicio,
                cd.data_termino,
                t.nome AS turma_nome,
                p.nome AS professor_nome,
                e.nome AS empresa_nome,
                fi.status_aluno,
                CASE 
                    WHEN fi.status_aluno = 'cancelado' THEN 'cancelado'
                    WHEN t.id IS NULL OR fi.turma_id IS NULL THEN 'espera'
                    WHEN CURDATE() < cd.data_inicio THEN 'matriculado'
                    WHEN CURDATE() BETWEEN cd.data_inicio AND cd.data_termino THEN 'em_andamento'
                    WHEN CURDATE() > cd.data_termino THEN 'concluido'
                    ELSE fi.status_aluno
                END AS status_calculado
            FROM fichas_inscricao fi
            JOIN cursos_disponiveis cd ON fi.curso_disponivel_id = cd.id
            LEFT JOIN turma t ON fi.turma_id = t.id
            LEFT JOIN professores p ON t.professor_id = p.id
            LEFT JOIN empresas e ON cd.empresa_id = e.id
            WHERE fi.usuario_id = ?
            GROUP BY fi.curso_disponivel_id";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$usuario_id]);
    $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filtrar com base no status desejado
    return array_filter($todos, function ($linha) use ($statusFiltro) {
        return $linha['status_calculado'] === $statusFiltro;
    });
}




}
