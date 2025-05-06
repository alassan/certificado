<?php
class Turma
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function cadastrar(array $dados): int
    {
        $curso = $this->buscarCursoDisponivel($dados['curso_disponivel_id']);
        $dataInicio = $curso['data_inicio'] ?? null;
        $dataTermino = $curso['data_termino'] ?? null;

        $sql = "INSERT INTO turma 
        (nome, curso_disponivel_id, capacidade_maxima, 
         status, professor_id, local, alocar_automaticamente)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $this->conn->prepare($sql);
$stmt->execute([
    $dados['nome'] ?? '',
    $dados['curso_disponivel_id'] ?? null,
    $dados['capacidade_maxima'] ?? 20,
    $dados['status'] ?? 'aberta',
    $dados['professor_id'] ?? null,
    $dados['local'] ?? '',
    $dados['alocar_automaticamente'] ?? 0
]);

        return (int)$this->conn->lastInsertId();
    }

 private function buscarCursoDisponivel($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM cursos_disponiveis WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarTurmaOuCriarSeNaoExistir($cursoDisponivelId): int
    {
        $turma = $this->buscarTurmaComVagas($cursoDisponivelId);

        if ($turma) {
            return $turma['id'];
        }

        $ultima = $this->buscarUltimaTurmaCurso($cursoDisponivelId);
        $letra = $this->determinarProximaLetra($ultima['nome'] ?? '');
        $nome = "Turma $letra";

        return $this->cadastrar([
            'nome' => $nome,
            'curso_disponivel_id' => $cursoDisponivelId,
            'capacidade_maxima' => 20,
            'status' => 'aberta',
            'local' => 'A definir',
            'alocar_automaticamente' => 1
        ]);
    }

    private function determinarProximaLetra($ultimoNome)
    {
        preg_match('/([A-Z])$/', $ultimoNome, $match);
        $letraAtual = $match[1] ?? 'A';
        return chr((ord($letraAtual) + 1) <= ord('Z') ? ord($letraAtual) + 1 : ord('A'));
    }


    public function listarTodas(): array
    {
        $sql = "SELECT 
                    t.*, 
                    cd.data_inicio AS curso_data_inicio, 
                    cd.data_termino AS curso_data_termino,
                    c.nome AS curso_nome
                FROM turma t
                LEFT JOIN cursos_disponiveis cd ON t.curso_disponivel_id = cd.id
                LEFT JOIN cursos c ON cd.curso_id = c.id
                ORDER BY t.nome ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT t.*, 
                       cd.data_inicio AS curso_data_inicio, 
                       cd.data_termino AS curso_data_termino,
                       c.nome AS curso_nome,
                       p.nome AS professor_nome,
                       e.nome AS empresa_nome
                FROM turma t
                LEFT JOIN cursos_disponiveis cd ON t.curso_disponivel_id = cd.id
                LEFT JOIN cursos c ON cd.curso_id = c.id
                LEFT JOIN professores p ON t.professor_id = p.id
                LEFT JOIN empresas e ON cd.empresa_id = e.id
                WHERE t.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $turma = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$turma) return false;

        $turma['alunos_matriculados'] = $this->contarAlunosMatriculados($id);
        $turma['vagas_disponiveis'] = $turma['capacidade_maxima'] - $turma['alunos_matriculados'];
        $this->atualizarVagasDisponiveis($id, $turma['vagas_disponiveis']);

        return $turma;
    }

    public function atualizar(int $id, array $dados): bool
    {
        $vagasDisponiveis = $dados['capacidade_maxima'] - $this->contarAlunosMatriculados($id);

        $sql = "UPDATE turma SET 
                    nome = ?, 
                    curso_disponivel_id = ?, 
                    capacidade_maxima = ?, 
                    status = ?, 
                    professor_id = ?, 
                    local = ?, 
                    alocar_automaticamente = ?,
                    vagas_disponiveis = ?
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $dados['nome'],
            $dados['curso_disponivel_id'],
            $dados['capacidade_maxima'],
            $dados['status'],
            $dados['professor_id'],
            $dados['local'],
            $dados['alocar_automaticamente'] ?? 0,
            $vagasDisponiveis,
            $id
        ]);
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM turma WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function listarAlunosTurma(int $turmaId): array
{
    $sql = "SELECT fi.id, fi.nome_aluno, fi.cpf, fi.contato, fi.status_aluno as status
            FROM fichas_inscricao fi
            WHERE fi.turma_id = ?
              AND fi.status_aluno IN ('matriculado', 'em_andamento')
            ORDER BY fi.nome_aluno ASC";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$turmaId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



    public function contarAlunosMatriculados(int $turmaId): int
{
    $sql = "SELECT COUNT(*) FROM fichas_inscricao 
            WHERE turma_id = ? AND status_aluno IN ('matriculado', 'em_andamento')";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$turmaId]);
    return (int)$stmt->fetchColumn();
}


    public function listarListaEsperaTurma(int $turmaId): array
    {
        $sql = "SELECT fi.id, fi.nome_aluno, fi.cpf, fi.data_inscricao
                FROM fichas_inscricao fi
                WHERE fi.turma_id = ? AND fi.status_aluno = 'espera'
                ORDER BY fi.data_inscricao ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$turmaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarUltimaTurmaCurso(int $cursoDisponivelId): array|false
    {
        $sql = "SELECT nome FROM turma 
                WHERE curso_disponivel_id = ? 
                ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$cursoDisponivelId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

     public function buscarTurmaComVagas(int $cursoDisponivelId): array|false
    {
        $sql = "SELECT t.* FROM turma t
                WHERE t.curso_disponivel_id = ?
                  AND t.status = 'aberta'
                  AND t.capacidade_maxima > (
                      SELECT COUNT(*) FROM fichas_inscricao
                      WHERE turma_id = t.id AND status_aluno = 'matriculado'
                  )
                ORDER BY t.id ASC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$cursoDisponivelId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizarVagasDisponiveis(int $turmaId, int $vagas): bool
    {
        $stmt = $this->conn->prepare("UPDATE turma SET vagas_disponiveis = ? WHERE id = ?");
        return $stmt->execute([$vagas, $turmaId]);
    }

    public function corrigirTodasVagas(): int
    {
        $sql = "SELECT id, capacidade_maxima FROM turma";
        $stmt = $this->conn->query($sql);
        $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $corrigidas = 0;

        foreach ($turmas as $turma) {
            $vagas = $turma['capacidade_maxima'] - $this->contarAlunosMatriculados($turma['id']);
            $this->atualizarVagasDisponiveis($turma['id'], max($vagas, 0));
            $corrigidas++;
        }

        return $corrigidas;
    }

    public function listarTurmasAbertas(int $cursoDisponivelId): array
    {
        $sql = "SELECT t.* FROM turma t
                WHERE t.curso_disponivel_id = ? AND t.status = 'aberta'
                ORDER BY t.nome ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$cursoDisponivelId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   public function alocarAlunoNaTurma(int $turmaId, int $fichaId): bool
    {
        try {
            $this->conn->beginTransaction();

            $turma = $this->buscarPorId($turmaId);
            if ($turma['vagas_disponiveis'] <= 0) return false;

            $stmt = $this->conn->prepare("SELECT status_aluno FROM fichas_inscricao WHERE id = ?");
            $stmt->execute([$fichaId]);
            $status = $stmt->fetchColumn();

            if ($status !== 'espera') return false;

            $sql = "UPDATE fichas_inscricao 
                    SET turma_id = ?, status_aluno = 'matriculado' 
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$turmaId, $fichaId]);

            $this->atualizarVagasDisponiveis($turmaId, $turma['vagas_disponiveis'] - 1);
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Erro ao alocar aluno: " . $e->getMessage());
            return false;
        }
    }
}
