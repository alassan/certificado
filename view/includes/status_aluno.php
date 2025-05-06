<?php

class StatusAluno
{
    // Define a lógica de atualização de status com base nas datas
    public static function atualizar(array $ficha, array $datas): string
    {
        $hoje = date('Y-m-d');
        $statusAtual = strtolower($ficha['status_aluno'] ?? '');

        $dataInicio = $datas['data_inicio_curso'] ?? null;
        $dataFim    = $datas['data_fim_curso'] ?? null;

        if ($statusAtual === 'matriculado' && $dataInicio && $hoje >= $dataInicio) {
            return 'em_andamento';
        }

        if ($statusAtual === 'em_andamento' && $dataFim && $hoje > $dataFim) {
            return 'concluido';
        }

        return $statusAtual;
    }

    // Classe de badge visual para cada status
    public static function getBadgeClass($status): string
    {
        return match ($status) {
            'concluido'     => 'success',
            'em_andamento'  => 'info',
            'matriculado'   => 'primary',
            'cancelado'     => 'danger',
            'espera'        => 'warning',
            default         => 'secondary'
        };
    }

    // Ícone associado ao status
    public static function getIcon($status): string
    {
        return match ($status) {
            'concluido'     => 'check-circle',
            'em_andamento'  => 'play-circle',
            'matriculado'   => 'hourglass-split',
            'cancelado'     => 'x-circle',
            'espera'        => 'clock',
            default         => 'question-circle'
        };
    }

    // Texto de exibição amigável
    public static function getLabel($status): string
    {
        return match ($status) {
            'concluido'     => 'Concluído',
            'em_andamento'  => 'Em andamento',
            'matriculado'   => 'Matriculado',
            'cancelado'     => 'Cancelado',
            'espera'        => 'Em espera',
            default         => 'Indefinido'
        };
    }
}
