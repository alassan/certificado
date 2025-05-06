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
        $inscricoes = $this->fichaModel->buscarFichasListaEsperaPorCursoDisponivel($curso_disponivel_id);

        if (empty($inscricoes)) {
            return false;
        }

        $curso = $this->fichaModel->buscarCursoDisponivel($curso_disponivel_id);
        if (!$curso) {
            return false;
        }

        $quantidadeInscritos = count($inscricoes);
        $capacidadePorTurma = 20;
        $numeroTurmas = ceil($quantidadeInscritos / $capacidadePorTurma);
        $turmasCriadas = [];

        for ($i = 1; $i <= $numeroTurmas; $i++) {
            $nomeTurma = 'Turma ' . chr(64 + $i); // A, B, C...

            $turmaId = $this->turmaModel->cadastrar(
                $nomeTurma,
                $curso['data_inicio'],
                $curso['data_termino'],
                $curso_disponivel_id,
                $curso['local'] ?? 'A definir',
                $curso['professor_id'] ?? null,
                $capacidadePorTurma
            );

            if ($turmaId) {
                $alocar = array_splice($inscricoes, 0, $capacidadePorTurma);
                foreach ($alocar as $inscricao) {
                    $this->fichaModel->alocarAlunoTurma($inscricao['id'], $turmaId);
                }

                $this->turmaModel->atualizarVagasDisponiveis($turmaId, $capacidadePorTurma - count($alocar));
                $turmasCriadas[] = $turmaId;
            }
        }

        return $turmasCriadas;
    }
}
?>
