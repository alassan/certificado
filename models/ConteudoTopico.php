<?php
// models/ConteudoTopico.php
class ConteudoTopico {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }
	
public function listarAgrupadoPorCurso() {
    $sql = "SELECT ct.*, 
                   c.nome AS curso_nome, 
                   c.carga_horaria AS ch_total_horas,
                   cd.id AS curso_disponivel_id
            FROM conteudo_topico ct
            JOIN cursos_disponiveis cd ON cd.id = ct.curso_disponivel_id
            JOIN cursos c ON c.id = cd.curso_id
            ORDER BY cd.id, ct.id";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $agrupado = [];
    foreach ($dados as $linha) {
        $cursoId = $linha['curso_disponivel_id'];
        if (!isset($agrupado[$cursoId])) {
            $agrupado[$cursoId] = [
                'curso_nome' => $linha['curso_nome'],
                'carga_horaria_curso' => intval($linha['ch_total_horas']), // em horas
                'total_ch' => 0, // em minutos
                'topicos' => []
            ];
        }

        $agrupado[$cursoId]['topicos'][] = $linha;
        $agrupado[$cursoId]['total_ch'] += (int) $linha['ch'];
    }

    return $agrupado;
}





    public function listarPorCurso($cursoDisponivelId) {
        $stmt = $this->conn->prepare("SELECT * FROM conteudo_topico WHERE curso_disponivel_id = ? ORDER BY id ASC");
        $stmt->execute([$cursoDisponivelId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cadastrar($dados) {
        $stmt = $this->conn->prepare("INSERT INTO conteudo_topico (curso_disponivel_id, conteudo, ch) VALUES (?, ?, ?)");
        return $stmt->execute([
            $dados['curso_disponivel_id'],
            $dados['conteudo'],
            $dados['ch']
        ]);
    }

    public function excluir($id) {
        $stmt = $this->conn->prepare("DELETE FROM conteudo_topico WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function somarCargaHoraria($cursoDisponivelId) {
        $stmt = $this->conn->prepare("SELECT SUM(ch) AS total_ch FROM conteudo_topico WHERE curso_disponivel_id = ?");
        $stmt->execute([$cursoDisponivelId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total_ch'] ?? 0;
    }
}
