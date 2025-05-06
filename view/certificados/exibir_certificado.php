<?php
require_once __DIR__ . '/../../config/conexao.php';
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login/login.php');
    exit;
}

// Valida o ID recebido
if (!isset($_GET['ficha_id']) || !is_numeric($_GET['ficha_id'])) {
    $mensagem = "ID da ficha inválido.";
} else {
    $ficha_id = $_GET['ficha_id'];

    // Consulta os dados da inscrição com status concluído
    $stmt = $conn->prepare("
        SELECT f.id, f.nome_aluno, f.cpf, f.status,
               c.nome AS curso, c.carga_horaria,
               cd.empresa, cd.data_inicio, cd.data_termino
        FROM fichas_inscricao f
        JOIN cursos_disponiveis cd ON cd.id = f.curso_disponivel_id
        JOIN cursos c ON c.id = cd.curso_id
        WHERE f.id = ? AND f.status = 'concluido'
    ");
    $stmt->execute([$ficha_id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dados) {
        $mensagem = "Certificado não disponível. A inscrição não foi concluída ou não existe.";
    } else {
        // Redireciona para o gerador de certificado
        $url = 'gerar_certificado.php?' . http_build_query(['ficha_id' => $ficha_id]);
        header("Location: $url");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Exibir Certificado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="alert alert-danger d-flex align-items-center shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
        <div>
            <strong>Erro:</strong> <?= htmlspecialchars($mensagem ?? "Ocorreu um erro inesperado.") ?>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="../dashboard/painel.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar ao Painel
        </a>
    </div>
</div>

</body>
</html>
