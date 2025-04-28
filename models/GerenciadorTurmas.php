<?php
class GerenciadorTurmas {
    private $conn;
    private $turmaModel;
    private $fichaModel;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->turmaModel = new Turma($conn);
        $this->fichaModel = new FichaInscricao($conn);
    }
    
    public function criarTurmasAutomaticas($curso_disponivel_id) {
        // 1. Buscar todas inscrições não alocadas para este curso
        $inscricoes = $this->fichaModel->buscarInscricoesNaoAlocadas($curso_disponivel_id);
        
        if (empty($inscricoes)) {
            return false;
        }
        
        // 2. Buscar configuração do curso (capacidade padrão)
        $curso = $this->fichaModel->buscarCursoDisponivel($curso_disponivel_id);
        $capacidade_padrao = $curso['capacidade_padrao'] ?? 10;
        
        // 3. Processar inscrições e criar turmas conforme necessário
        $turmas_criadas = [];
        $inscricoes_restantes = $inscricoes;
        
        while (!empty($inscricoes_restantes)) {
            // Verifica se existe turma aberta com vagas
            $turma = $this->turmaModel->buscarTurmaComVagas($curso_disponivel_id);
            
            if (!$turma) {
                // Cria nova turma se não houver turma com vagas
                $turma_id = $this->criarNovaTurma($curso_disponivel_id, $capacidade_padrao);
                $turma = $this->turmaModel->buscarPorId($turma_id);
            }
            
            // Calcula quantos alunos podemos alocar
            $alocar = min($turma['vagas_disponiveis'], count($inscricoes_restantes));
            
            // Aloca os alunos
            for ($i = 0; $i < $alocar; $i++) {
                $inscricao = array_shift($inscricoes_restantes);
                $this->fichaModel->alocarAlunoTurma($inscricao['id'], $turma['id']);
                
                // Atualiza vagas disponíveis
                $this->turmaModel->atualizarVagasDisponiveis(
                    $turma['id'], 
                    $turma['vagas_disponiveis'] - 1
                );
            }
            
            $turmas_criadas[] = $turma['id'];
        }
        
        return $turmas_criadas;
    }
    
    private function criarNovaTurma($curso_disponivel_id, $capacidade) {
        // Busca informações do curso disponível
        $curso = $this->fichaModel->buscarCursoDisponivel($curso_disponivel_id);
        
        // Gera nome automático para a turma (ex: "Turma A - Introdução à Informática")
        $ultima_turma = $this->turmaModel->buscarUltimaTurmaCurso($curso['curso_id']);
        $letra_turma = $this->gerarProximaLetraTurma($ultima_turma['nome']);
        
        $nome_turma = "Turma $letra_turma - " . $curso['curso_nome'];
        
  
  // Cria a turma
        return $this->turmaModel->cadastrar(
            $nome_turma,
            $curso['data_inicio'],
            $curso['data_termino'],
            $curso['curso_id'],
            $curso['empresa_id'] ?? null,
            $curso['local'] ?? 'A definir',
            $curso['professor_id'] ?? null,
            $capacidade
        );
    }
    
    private function gerarProximaLetraTurma($ultimo_nome) {
        // Lógica para gerar A, B, C... AA, AB, etc.
        if (preg_match('/Turma ([A-Z]+)/', $ultimo_nome, $matches)) {
            $letra = $matches[1];
            return ++$letra; // Incrementa a letra (A->B, Z->AA)
        }
        return 'A';
    }
    
    public function redistribuirAlunosAposEdicao($turma_id, $nova_capacidade) {
        // Implementação similar mas considerando realocação entre turmas
        // ...
    }
}