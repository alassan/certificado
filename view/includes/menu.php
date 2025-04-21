<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../conexao.php';

// Verifica autenticação
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit;
}

// Dados do usuário
$nome = htmlspecialchars($_SESSION['usuario_nome']);
$nivel = htmlspecialchars($_SESSION['usuario_nivel']);
$usuario_id = $_SESSION['usuario_id'];

// Verifica página atual
$uri = $_SERVER['REQUEST_URI'];
$paginaAtual = basename($uri);

// Verificar cursos disponíveis para alunos
$mostrarFichaInscricao = false;
if ($nivel === 'Aluno') {
    $stmt = $conn->prepare("
        SELECT COUNT(cd.id) 
        FROM cursos_disponiveis cd
        WHERE NOW() BETWEEN cd.inicio_inscricao AND cd.termino_inscricao
          AND cd.id NOT IN (
              SELECT curso_disponivel_id 
              FROM fichas_inscricao 
              WHERE usuario_id = ?
          )
    ");
    $stmt->execute([$usuario_id]);
    $mostrarFichaInscricao = $stmt->fetchColumn() > 0;
}
?>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header text-center mb-4">
        <img src="/assets/images/logo-fundacao-wall-ferraz.png" alt="Fundação Wall Ferraz" class="img-fluid mb-2" style="max-height: 60px;">
        <h6 class="mb-0 text-muted">Prefeitura Municipal de Teresina/PI</h6>
    </div>
    
    <ul class="nav flex-column">
        <!-- Painel -->
        <li class="nav-item">
            <a href="../dashboard/painel.php" 
               class="nav-link <?= $paginaAtual === 'painel.php' ? 'active' : '' ?>">
                <i class="bi bi-house-door-fill"></i> Painel
            </a>
        </li>

        <?php if ($nivel !== 'Aluno'): ?>
        <!-- Cursos -->
        <li class="nav-item">
            <a class="nav-link <?= str_contains($uri, 'curso/') ? '' : 'collapsed' ?>" 
               data-bs-toggle="collapse" href="#menuCursos" role="button" 
               aria-expanded="<?= str_contains($uri, 'curso/') ? 'true' : 'false' ?>">
                <i class="bi bi-mortarboard-fill"></i> Cursos
            </a>
            <div class="collapse submenu <?= str_contains($uri, 'curso/') ? 'show' : '' ?>" id="menuCursos">
                <a href="../curso/curso_cadastrar.php" 
                   class="nav-link <?= $paginaAtual === 'curso_cadastrar.php' ? 'active' : '' ?>">
                    <i class="bi bi-plus-circle"></i> Cadastrar
                </a>
                <a href="../curso/curso_listar.php" 
                   class="nav-link <?= $paginaAtual === 'curso_listar.php' ? 'active' : '' ?>">
                    <i class="bi bi-card-list"></i> Listar
                </a>
                <a href="../admin/cursos_disponiveis/cadastrar.php" 
                   class="nav-link <?= $paginaAtual === 'cadastrar.php' ? 'active' : '' ?>">
                    <i class="bi bi-gear-fill"></i> Gerenciar
                </a>
                <a href="../admin/cursos_disponiveis/listar.php" 
                   class="nav-link <?= $paginaAtual === 'listar.php' ? 'active' : '' ?>">
                    <i class="bi bi-check2-square"></i> Disponíveis
                </a>
            </div>
        </li>

        <!-- Categorias -->
        <li class="nav-item">
            <a class="nav-link <?= str_contains($uri, 'categoria/') ? '' : 'collapsed' ?>" 
               data-bs-toggle="collapse" href="#menuCategorias" role="button" 
               aria-expanded="<?= str_contains($uri, 'categoria/') ? 'true' : 'false' ?>">
                <i class="bi bi-folder2-open"></i> Categorias
            </a>
            <div class="collapse submenu <?= str_contains($uri, 'categoria/') ? 'show' : '' ?>" id="menuCategorias">
                <a href="../categoria/categoria_cadastrar.php" 
                   class="nav-link <?= $paginaAtual === 'categoria_cadastrar.php' ? 'active' : '' ?>">
                    <i class="bi bi-plus-circle"></i> Cadastrar
                </a>
                <a href="../categoria/categoria_listar.php" 
                   class="nav-link <?= $paginaAtual === 'categoria_listar.php' ? 'active' : '' ?>">
                    <i class="bi bi-card-list"></i> Listar
                </a>
            </div>
        </li>

        <!-- Professores -->
        <li class="nav-item">
            <a class="nav-link <?= str_contains($uri, 'professor/') ? '' : 'collapsed' ?>" 
               data-bs-toggle="collapse" href="#menuProfessores" role="button" 
               aria-expanded="<?= str_contains($uri, 'professor/') ? 'true' : 'false' ?>">
                <i class="bi bi-person-badge-fill"></i> Professores
            </a>
            <div class="collapse submenu <?= str_contains($uri, 'professor/') ? 'show' : '' ?>" id="menuProfessores">
                <a href="../professor/professor_cadastrar.php" 
                   class="nav-link <?= $paginaAtual === 'professor_cadastrar.php' ? 'active' : '' ?>">
                    <i class="bi bi-plus-circle"></i> Cadastrar
                </a>
                <a href="../professor/professor_listar.php" 
                   class="nav-link <?= $paginaAtual === 'professor_listar.php' ? 'active' : '' ?>">
                    <i class="bi bi-card-list"></i> Listar
                </a>
            </div>
        </li>
        <?php endif; ?>

        <?php if ($nivel === 'Aluno'): ?>
        <!-- Meus Cursos -->
        <li class="nav-item">
            <a class="nav-link <?= str_contains($uri, 'meus_cursos.php') ? '' : 'collapsed' ?>" 
               data-bs-toggle="collapse" href="#menuMeusCursos" role="button" 
               aria-expanded="<?= str_contains($uri, 'meus_cursos.php') ? 'true' : 'false' ?>">
                <i class="bi bi-journal-bookmark-fill"></i> Meus Cursos
            </a>
            <div class="collapse submenu <?= str_contains($uri, 'meus_cursos.php') ? 'show' : '' ?>" id="menuMeusCursos">
                <a href="../curso/meus_cursos.php?status=ativo" 
                   class="nav-link <?= isset($_GET['status']) && $_GET['status'] === 'ativo' ? 'active' : '' ?>">
                    <i class="bi bi-hourglass-split"></i> Ativos
                </a>
                <a href="../curso/meus_cursos.php?status=concluido" 
                   class="nav-link <?= isset($_GET['status']) && $_GET['status'] === 'concluido' ? 'active' : '' ?>">
                    <i class="bi bi-check2-circle"></i> Concluídos
                </a>
                <a href="../curso/meus_cursos.php?status=cancelado" 
                   class="nav-link <?= isset($_GET['status']) && $_GET['status'] === 'cancelado' ? 'active' : '' ?>">
                    <i class="bi bi-x-circle"></i> Cancelados
                </a>
            </div>
        </li>
        <?php endif; ?>

        <!-- Aluno -->
        <li class="nav-item">
            <a class="nav-link <?= str_contains($uri, 'aluno/') ? '' : 'collapsed' ?>" 
               data-bs-toggle="collapse" href="#menuAlunos" role="button" 
               aria-expanded="<?= str_contains($uri, 'aluno/') ? 'true' : 'false' ?>">
                <i class="bi bi-people-fill"></i> Aluno
            </a>
            <div class="collapse submenu <?= str_contains($uri, 'aluno/') ? 'show' : '' ?>" id="menuAlunos">
                <?php if ($nivel === 'Aluno' && $mostrarFichaInscricao): ?>
                <a href="../aluno/ficha_inscricao.php" 
                   class="nav-link <?= $paginaAtual === 'ficha_inscricao.php' ? 'active' : '' ?>">
                    <i class="bi bi-pencil-square"></i> Nova Inscrição
                </a>
                <?php endif; ?>
                <a href="../aluno/listar_fichas.php" 
                   class="nav-link <?= $paginaAtual === 'listar_fichas.php' ? 'active' : '' ?>">
                    <i class="bi bi-list-task"></i> Minhas Inscrições
                </a>
            </div>
        </li>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../conexao.php';

// Verifica autenticação
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit;
}

// Dados do usuário
$nome = htmlspecialchars($_SESSION['usuario_nome']);
$nivel = htmlspecialchars($_SESSION['usuario_nivel']);
$usuario_id = $_SESSION['usuario_id'];

// Verifica página atual
$uri = $_SERVER['REQUEST_URI'];
$paginaAtual = basename($uri);

// Verificar cursos disponíveis para alunos
$mostrarFichaInscricao = false;
$mostrarMeusCertificados = false;

if ($nivel === 'Aluno') {
    // Verifica se há cursos abertos para inscrição
    $stmt = $conn->prepare("
        SELECT COUNT(cd.id) 
        FROM cursos_disponiveis cd
        WHERE NOW() BETWEEN cd.inicio_inscricao AND cd.termino_inscricao
          AND cd.id NOT IN (
              SELECT curso_disponivel_id 
              FROM fichas_inscricao 
              WHERE usuario_id = ?
          )
    ");
    $stmt->execute([$usuario_id]);
    $mostrarFichaInscricao = $stmt->fetchColumn() > 0;

    // Verifica se há certificados disponíveis (curso concluído)
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM fichas_inscricao 
        WHERE usuario_id = ? AND status = 'concluido'
    ");
    $stmt->execute([$usuario_id]);
    $mostrarMeusCertificados = $stmt->fetchColumn() > 0;
}
?>

<!-- Certificados -->
<?php if ($nivel !== 'Aluno' || $mostrarMeusCertificados): ?>
<li class="nav-item">
    <a class="nav-link <?= str_contains($uri, 'certificados/') ? '' : 'collapsed' ?>" 
       data-bs-toggle="collapse" href="#menuCertificados" role="button" 
       aria-expanded="<?= str_contains($uri, 'certificados/') ? 'true' : 'false' ?>">
        <i class="bi bi-award"></i> Certificados
    </a>
    <div class="collapse submenu <?= str_contains($uri, 'certificados/') ? 'show' : '' ?>" id="menuCertificados">
        <?php if ($nivel === 'Aluno' && $mostrarMeusCertificados): ?>
        <a href="../certificados/exibir_certificado.php" 
           class="nav-link <?= $paginaAtual === 'exibir_certificado.php' ? 'active' : '' ?>">
            <i class="bi bi-card-checklist"></i> Meus Certificados
        </a>
        <?php endif; ?>

        <?php if ($nivel !== 'Aluno'): ?>
        <a href="../certificados/form_busca.php" 
           class="nav-link <?= $paginaAtual === 'form_busca.php' ? 'active' : '' ?>">
            <i class="bi bi-search"></i> Validar Certificado
        </a>
        <?php endif; ?>
    </div>
</li>
<?php endif; ?>



        <hr class="my-3">

        <!-- Perfil e Sair -->
        <li class="nav-item">
            <a href="../perfil/perfil.php" 
               class="nav-link <?= $paginaAtual === 'perfil.php' ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i> Meu Perfil
            </a>
        </li>
        <li class="nav-item">
            <a href="../logout/logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-right"></i> Sair
            </a>
        </li>
    </ul>
</div>

<style>
.sidebar {
    width: 250px;
    background-color: #fff;
    min-height: 100vh;
    padding: 1.5rem 1rem;
    position: fixed;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    border-right: 1px solid #e9ecef;
}

.sidebar-header {
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 1rem;
}

.sidebar .nav-link {
    color: #495057;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
    transition: all 0.2s;
}

.sidebar .nav-link i {
    margin-right: 10px;
    font-size: 1rem;
    width: 20px;
    text-align: center;
}

.sidebar .nav-link:hover {
    background-color: #f8f9fa;
    color: #0d6efd;
}

.sidebar .nav-link.active {
    background-color: #e7f1ff;
    color: #0d6efd;
    font-weight: 500;
}

.sidebar .nav-link.text-danger:hover {
    color: #dc3545 !important;
    background-color: #f8f9fa;
}

.submenu {
    padding-left: 1.5rem;
    margin: 0.5rem 0;
}

.submenu .nav-link {
    padding: 0.4rem 1rem;
    font-size: 0.85rem;
}

@media (max-width: 768px) {
    .sidebar {
        position: relative;
        width: 100%;
        min-height: auto;
        box-shadow: none;
        border-right: none;
        border-bottom: 1px solid #e9ecef;
    }
}
</style>