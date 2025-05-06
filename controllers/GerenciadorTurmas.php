<?php
require_once __DIR__ . '/../models/Turma.php';
require_once __DIR__ . '/../models/FichaInscricao.php';

class GerenciadorTurmas
{
    private $conn;
    private $turmaModel;
    private $fichaModel;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->turmaModel = new Turma($conn);
        $this->fichaModel = new FichaInscricao($conn);
    }

    public function criarTurmasAutomaticas($curso_disponivel_id)
    {
        try {
            $this->conn->beginTransaction();

            $inscricoes = $this->fichaModel->buscarFichasListaEsperaPorCursoDisponivel($curso_disponivel_id);

            if (empty($inscricoes)) {
                $this->conn->commit();
                return false;
            }

            $inscricoes = $this->alocarEmTurmasExistentes($curso_disponivel_id, $inscricoes);

            $turmasCriadas = [];
            if (!empty($inscricoes)) {
                $turmasCriadas = $this->criarNovasTurmas($curso_disponivel_id, $inscricoes);
            }

            $this->conn->commit();
            return !empty($turmasCriadas) ? $turmasCriadas : true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Erro ao criar turmas: " . $e->getMessage());
            return false;
        }
    }

    private function alocarEmTurmasExistentes($curso_disponivel_id, $inscricoes)
    {
        $turmas = $this->turmaModel->listarTurmasAbertas($curso_disponivel_id);

        foreach ($turmas as $turma) {
            $vagasDisponiveis = $turma['capacidade_maxima'] - $this->turmaModel->contarAlunosMatriculados($turma['id']);
            if ($vagasDisponiveis <= 0) continue;

            $inscricoesParaAlocar = array_splice($inscricoes, 0, $vagasDisponiveis);

            foreach ($inscricoesParaAlocar as $inscricao) {
                if (empty($inscricao['turma_id']) || $inscricao['turma_id'] == 0) {
                    $this->turmaModel->alocarAlunoNaTurma($turma['id'], $inscricao['id']);
                    $this->fichaModel->atualizarStatus($inscricao['id'], 'matriculado');
                }
            }

            if (empty($inscricoes)) break;
        }

        return $inscricoes;
    }

    private function criarNovasTurmas($curso_disponivel_id, $inscricoes)
    {
        $capacidadePadrao = 20;
        $turmasCriadas = [];

        $ultimaTurma = $this->turmaModel->buscarUltimaTurmaCurso($curso_disponivel_id);
        $letra = $this->determinarProximaLetra($ultimaTurma['nome'] ?? '');

        while (!empty($inscricoes)) {
            $nomeTurma = "Turma $letra";

            $turmaId = $this->turmaModel->cadastrar([
                'nome' => $nomeTurma,
                'curso_disponivel_id' => $curso_disponivel_id,
                'capacidade_maxima' => $capacidadePadrao,
                'status' => 'aberta',
                'local' => 'A definir',
                'professor_id' => null,
                'alocar_automaticamente' => 1
            ]);

            $turmasCriadas[] = $turmaId;

            $alocar = array_splice($inscricoes, 0, $capacidadePadrao);
            foreach ($alocar as $ficha) {
                if (empty($ficha['turma_id']) || $ficha['turma_id'] == 0) {
                    $this->turmaModel->alocarAlunoNaTurma($turmaId, $ficha['id']);
                    $this->fichaModel->atualizarStatus($ficha['id'], 'matriculado');
                }
            }

            $letra = chr(ord($letra) + 1);
        }

        return $turmasCriadas;
    }

    private function determinarProximaLetra($ultimoNome)
    {
        if (!$ultimoNome) return 'A';

        preg_match('/([A-Z])$/', $ultimoNome, $match);
        if (isset($match[1])) {
            $proxima = chr(ord($match[1]) + 1);
            return $proxima <= 'Z' ? $proxima : 'A';
        }

        return 'A';
    }

    public function gerenciarTurmasInteligente($cursoId)
    {
        try {
            $this->conn->beginTransaction();

            $espera = $this->fichaModel->buscarFichasListaEsperaPorCursoDisponivel($cursoId);
            if (empty($espera)) {
                $this->conn->commit();
                return false;
            }

            $turmas = $this->turmaModel->listarTurmasAbertas($cursoId);
            foreach ($turmas as $turma) {
                $matriculados = $this->turmaModel->contarAlunosMatriculados($turma['id']);
                $capacidade = $turma['capacidade_maxima'];
                $expandirPara = min(30, $capacidade + count($espera));

                if ($expandirPara > $capacidade) {
                    $this->turmaModel->atualizar($turma['id'], [
                        'capacidade_maxima' => $expandirPara
                    ]);

                    $alocar = array_splice($espera, 0, $expandirPara - $matriculados);
                    foreach ($alocar as $aluno) {
                        if (empty($aluno['turma_id']) || $aluno['turma_id'] == 0) {
                            $this->turmaModel->alocarAlunoNaTurma($turma['id'], $aluno['id']);
                            $this->fichaModel->atualizarStatus($aluno['id'], 'matriculado');
                        }
                    }
                }
            }

            if (!empty($espera) && count($espera) >= 10) {
                $this->criarTurmasAutomaticas($cursoId);
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Erro no gerenciamento inteligente: " . $e->getMessage());
            return false;
        }
    }

    private function verificarCursoExistente($curso_disponivel_id)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM cursos_disponiveis WHERE id = ?");
        $stmt->execute([$curso_disponivel_id]);
        return $stmt->fetchColumn() > 0;
    }
}
