<?php
session_start();

// Verificação simplificada e sem loop



require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/models/CursoDisponivel.php';

$cursoDisponivelModel = new CursoDisponivel($conn);
$cursos = $cursoDisponivelModel->listarAtivos();

$title = "Capacitação Profissional";
ob_start();
?>


<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-blue-900 to-blue-700 text-white overflow-hidden">
  <div class="absolute inset-0 opacity-10">
    <div class="absolute top-1/4 left-1/4 w-32 h-32 bg-blue-400 rounded-full mix-blend-screen filter blur-3xl"></div>
  </div>

  <div class="container mx-auto px-4 py-20 relative z-10">
    <div class="flex flex-col md:flex-row items-center gap-8">
      <div class="md:w-1/2">
        <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">
          Formação <span class="text-yellow-300">Gratuita</span>, Oportunidade <span class="text-yellow-300">Real</span>
        </h1>
        <p class="text-xl text-blue-100 mb-8">
          O <?= NOME_ORGAO ?> oferece cursos profissionalizantes para transformar vidas.
        </p>
        <div class="flex flex-wrap gap-4">
          <a href="#cursos" class="bg-white text-blue-900 hover:bg-gray-100 px-6 py-3 rounded-full font-medium flex items-center shadow-md">
            <i class="bi bi-search mr-2"></i> Ver Cursos
          </a>
          <a href="view/login/login.php" class="border-2 border-white text-white hover:bg-white hover:text-blue-900 px-6 py-3 rounded-full font-medium flex items-center">
            <i class="bi bi-box-arrow-in-right mr-2"></i> Área do Aluno
          </a>
        </div>
      </div>
      <div class="md:w-1/2 mt-10 md:mt-0">
        <img src="assets/img/hero-education.svg" alt="Capacitação" class="w-full max-w-md mx-auto animate-float" loading="lazy">
      </div>
    </div>
  </div>
  
  <div class="hero-wave"></div>
</section>

<!-- Cursos Section -->
<section id="cursos" class="py-16 bg-white">
  <div class="container mx-auto px-4">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold mb-3">Cursos Disponíveis</h2>
      <p class="text-gray-600 max-w-2xl mx-auto">
        Todos os cursos são gratuitos e com emissão de certificado
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($cursos as $curso): ?>
      <div class="card rounded-xl overflow-hidden">
        <div class="relative h-48 bg-gray-200 bg-cover bg-center overflow-hidden" style="background-image: url('assets/img/cursos/<?= $curso['id'] % 4 + 1 ?>.jpg')">
          <span class="absolute top-3 right-3 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium">
            Gratuito
          </span>
        </div>
        <div class="p-6">
          <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($curso['nome']) ?></h3>
          <p class="text-gray-600 text-sm mb-4"><?= htmlspecialchars(substr($curso['descricao'], 0, 100)) ?>...</p>
          
          <div class="flex justify-between text-sm text-gray-500 mb-5">
            <span class="flex items-center">
              <i class="bi bi-calendar-check mr-2"></i> Até <?= date('d/m/Y', strtotime($curso['termino_inscricao'])) ?>
            </span>
            <span class="flex items-center">
              <i class="bi bi-clock mr-2"></i> <?= $curso['carga_horaria'] ?>h
            </span>
          </div>
          
          <a href="index.php?page=inscricao&curso_id=<?= $curso['id'] ?>" class="btn-primary text-white text-center py-2.5 rounded-lg font-medium block">
            <i class="bi bi-pencil-square mr-2"></i> Inscrever-se
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php

$content = ob_get_clean();

if (isset($_GET['page'])) {
    $page = $_GET['page'];

    // Caminhos válidos possíveis
    $viewPath = __DIR__ . '/view/' . $page . '.php';
    $rootPath = __DIR__ . '/' . $page . '.php';

    if (file_exists($viewPath)) {
        include $viewPath;
        exit;
    } elseif (file_exists($rootPath)) {
        include $rootPath;
        exit;
    } else {
        echo "<p class='text-center mt-10 text-red-500'>Página não encontrada.</p>";
    }
} else {
    include 'template.php';
}
?>