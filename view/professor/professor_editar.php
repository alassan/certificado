<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php?page=login/login");
    exit;
}

require_once __DIR__ . '/../../config/conexao.php';

// Verifica o ID
$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['msg_erro'] = 'ID do professor não informado.';
    header("Location: index.php?page=professor/professor_listar");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM professores WHERE id = ?");
$stmt->execute([$id]);
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$professor) {
    $_SESSION['msg_erro'] = 'Professor não encontrado.';
    header("Location: index.php?page=professor/professor_listar");
    exit;
}

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $assinaturaPath = $professor['assinatura_path'];

    // Verifica se nova assinatura foi enviada
    if (isset($_FILES['assinatura']) && $_FILES['assinatura']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['assinatura']['name'], PATHINFO_EXTENSION);
        $novoNome = 'uploads/assinaturas/' . uniqid('assin_') . '.' . strtolower($ext);

        if (!is_dir(__DIR__ . '/../../uploads/assinaturas')) {
            mkdir(__DIR__ . '/../../uploads/assinaturas', 0777, true);
        }

        if (move_uploaded_file($_FILES['assinatura']['tmp_name'], __DIR__ . '/../../' . $novoNome)) {
            $assinaturaPath = $novoNome;
        }
    }

    $stmt = $conn->prepare("UPDATE professores SET nome = ?, email = ?, telefone = ?, assinatura_path = ? WHERE id = ?");
    $stmt->execute([$nome, $email, $telefone, $assinaturaPath, $id]);

    $_SESSION['msg_successo'] = 'Professor atualizado com sucesso!';
    header("Location: index.php?page=professor/professor_listar");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Professor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card p-4 shadow-sm">
            <h4 class="mb-3 text-primary"><i class="bi bi-pencil-fill me-2"></i>Editar Professor</h4>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $professor['id'] ?>">

                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($professor['nome']) ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($professor['email']) ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="telefone" value="<?= htmlspecialchars($professor['telefone']) ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Assinatura do Professor (PNG/JPG)</label>
                    <input type="file" name="assinatura" accept="image/*" class="form-control">
                </div>

                <?php if (!empty($professor['assinatura_path']) && file_exists(__DIR__ . '/../../' . $professor['assinatura_path'])): ?>
                    <div class="mb-3">
                        <label class="form-label">Assinatura Atual:</label><br>
                        <img src="../../<?= $professor['assinatura_path'] ?>" alt="Assinatura" height="80">
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Atualizar</button>
                    <a href="index.php?page=professor/professor_listar" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
