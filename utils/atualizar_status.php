<?php
// utils/atualizar_status.php
require_once __DIR__ . '/../config/conexao.php';

try {
    // Atualiza para "ativo" se a data atual estiver entre o início e término da inscrição
    $stmt0 = $conn->prepare("
        UPDATE fichas_inscricao fi
        JOIN cursos_disponiveis cd ON fi.curso_disponivel_id = cd.id
        SET fi.status = 'ativo'
        WHERE fi.status NOT IN ('cancelado')
          AND CURDATE() BETWEEN cd.inicio_inscricao AND cd.termino_inscricao
    ");
    $stmt0->execute();

    // Atualiza para "andamento" se a data atual estiver entre início e término do curso
    $stmt1 = $conn->prepare("
        UPDATE fichas_inscricao fi
        JOIN cursos_disponiveis cd ON fi.curso_disponivel_id = cd.id
        SET fi.status = 'andamento'
        WHERE fi.status = 'ativo'
          AND CURDATE() BETWEEN cd.data_inicio AND cd.data_termino
    ");
    $stmt1->execute();

    // Atualiza para "concluido" se a data atual passou da data_termino
    $stmt2 = $conn->prepare("
        UPDATE fichas_inscricao fi
        JOIN cursos_disponiveis cd ON fi.curso_disponivel_id = cd.id
        SET fi.status = 'concluido'
        WHERE fi.status IN ('ativo', 'andamento')
          AND CURDATE() > cd.data_termino
    ");
    $stmt2->execute();

    // Obs: status 'cancelado' é alterado apenas manualmente por Admin/Funcionário

} catch (PDOException $e) {
    error_log("Erro ao atualizar status de inscrições: " . $e->getMessage());
    // Em produção, evite exibir diretamente erros de banco para o usuário
}
