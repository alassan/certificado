<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: view/login/login.php");
    exit;
}

$nome = $_SESSION['usuario_nome'];
$nivel = $_SESSION['usuario_nivel'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Sistema de Certificados</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      margin: 0;
      background-color: #f8f9fa;
    }
  </style>
</head>
<body>
  <main>
    <?php
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
        $path = __DIR__ . '/' . $page . '.php';
        if (file_exists($path)) {
            include $path;
        } else {
            echo "<div class='container mt-5'><div class='alert alert-danger'>Página não encontrada: <code>$page</code></div></div>";
        }
    } else {
        header("Location: view/dashboard/painel.php");
        exit;
    }
    ?>
  </main>
</body>
</html>
