<?php
function getStatusTurma($dataInicio, $dataFim = null) {
    $hoje = new DateTime();
    $inicio = new DateTime($dataInicio);

    if ($hoje < $inicio) {
        return 'Planejada';
    }

    if (!empty($dataFim)) {
        $fim = new DateTime($dataFim);
        if ($hoje >= $inicio && $hoje <= $fim) {
            return 'Em andamento';
        } elseif ($hoje > $fim) {
            return 'Concluída';
        }
    }

    return 'Em andamento'; // Se não tem data término mas já começou
}

function getStatusClass($status) {
    switch ($status) {
        case 'Planejada': return 'warning';
        case 'Em andamento': return 'info';
        case 'Concluída': return 'success';
        case 'Cancelada': return 'danger';
        default: return 'secondary';
    }
}
?>
