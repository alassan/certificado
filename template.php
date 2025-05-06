<?php if (!isset($title)) $title = 'Sistema de Capacitação'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?> - <?= defined('NOME_ORGAO') ? NOME_ORGAO : '' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link rel="shortcut icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    :root {
      --primary: #0056b3;
      --primary-dark: #004494;
    }
    body { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
    .card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); box-shadow: 0 8px 32px rgba(0,0,0,0.1); transition: 0.3s; }
    .card:hover { box-shadow: 0 10px 40px rgba(0,0,0,0.15); transform: translateY(-5px); }
    .btn-primary { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); transition: 0.3s; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,86,179,0.3); }
    .hero-wave { height: 150px; background: url('data:image/svg+xml;...') no-repeat; background-size: cover; }
  </style>
</head>
<body class="min-h-screen flex flex-col">
  <header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
      <a href="" class="flex items-center">
        <img src="assets/img/logo_fwf.png" alt="Logo" class="h-10 mr-3">
        <span class="text-xl font-bold text-blue-800"><?= defined('NOME_ORGAO') ? NOME_ORGAO : '' ?></span>
      </a>
      <a href="/certificado/view/login/login.php" class="btn-primary text-white px-5 py-2 rounded-full font-medium flex items-center">
        <i class="bi bi-box-arrow-in-right mr-2"></i> Entrar
      </a>
    </div>
  </header>

  <main class="flex-grow">
    <?= $content ?>
  </main>

  <footer class="bg-white border-t mt-10">
    <div class="container mx-auto px-4 py-6 text-center">
      <p class="text-gray-600">© <?= date('Y') ?> <?= defined('NOME_ORGAO') ? NOME_ORGAO : '' ?>. Todos os direitos reservados.</p>
      <p class="text-gray-500 text-sm mt-2">Desenvolvido com <i class="bi bi-heart-fill text-red-500"></i> por TeresinaDev</p>
    </div>
  </footer>
</body>
</html>
