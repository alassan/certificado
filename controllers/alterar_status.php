<?php
require_once __DIR__ . '/../config/conexao.php';
session_start();

// Verifica se o usuário é Admin ou Funcionário
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['Admin', 'Funcionário'])) {
    header('Location: ../login/login.php');
    exit;
}

// Verifica se os dados foram enviados corretamente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $novo_status = $_POST['novo_status'] ?? null;

    if ($id && $novo_status && in_array($novo_status, ['cancelado'])) {
        try {
            $stmt = $conn->prepare("UPDATE fichas_inscricao SET status = ? WHERE id = ?");
            $stmt->execute([$novo_status, $id]);

            $_SESSION['mensagem'] = 'Status atualizado com sucesso!';
        } catch (PDOException $e) {
            $_SESSION['mensagem'] = 'Erro ao atualizar status: ' . $e->getMessage();
        }
    } else {
        $_SESSION['mensagem'] = 'Dados inválidos para atualização.';
    }
}

header('Location: listar_fichas.php');
exit; ?>
