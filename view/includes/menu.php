<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/conexao.php';

// Verifica autenticação
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php?page=login/login");
    exit;
}

$nome = htmlspecialchars($_SESSION['usuario_nome']);
$nivel = htmlspecialchars($_SESSION['usuario_nivel']);
$usuario_id = $_SESSION['usuario_id'];

$uri = $_SERVER['REQUEST_URI'];
$paginaAtual = basename(parse_url($uri, PHP_URL_PATH));

$mostrarFichaInscricao = false;
$mostrarMeusCertificados = false;
if ($nivel === 'Aluno') {
    $stmt = $conn->prepare("SELECT COUNT(cd.id) FROM cursos_disponiveis cd WHERE NOW() BETWEEN cd.inicio_inscricao AND cd.termino_inscricao AND cd.id NOT IN (SELECT curso_disponivel_id FROM fichas_inscricao WHERE usuario_id = ?)");
    $stmt->execute([$usuario_id]);
    $mostrarFichaInscricao = $stmt->fetchColumn() > 0;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM fichas_inscricao WHERE usuario_id = ? AND status_aluno = 'concluido'");
    $stmt->execute([$usuario_id]);
    $mostrarMeusCertificados = $stmt->fetchColumn() > 0;
}
?>

<!-- Sidebar Elegante -->
<div class="sidebar">
    <!-- Cabeçalho do Menu -->
    <div class="sidebar-header text-center p-3">
        <div class="logo-container mb-2">
            <img src="/certificado/assets/img/logo_fwf.png" alt="Fundação Wall Ferraz" class="logo-img">
        </div>
        <h6 class="institution-name">Prefeitura Municipal de Teresina/PI</h6>
    </div>

    <!-- Itens do Menu -->
    <div class="menu-content">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="index.php?page=dashboard/painel" class="nav-link <?= $paginaAtual === 'painel.php' ? 'active' : '' ?>">
                    <i class="bi bi-house-door me-2"></i>
                    <span>Painel</span>
                </a>
            </li>

            <?php if ($nivel !== 'Aluno'): ?>
                <!-- Menu Administrativo -->
                <li class="nav-item menu-group">
                    <a class="nav-link menu-toggle" data-bs-toggle="collapse" href="#menuCursos">
                        <i class="bi bi-mortarboard me-2"></i>
                        <span>Cursos</span>
                        <i class="bi bi-chevron-down arrow-icon"></i>
                    </a>
                    <div class="collapse menu-subgroup" id="menuCursos">
                        <a href="index.php?page=curso/curso_cadastrar" class="nav-link">Cadastrar</a>
                        <a href="index.php?page=curso/curso_listar" class="nav-link">Listar</a>
                        <a href="index.php?page=admin/cursos_disponiveis/cadastrar" class="nav-link">Gerenciar</a>
                        <a href="index.php?page=admin/cursos_disponiveis/listar" class="nav-link">Disponíveis</a>
                    </div>
                </li>
				
	<!-- Categoria -->
                <li class="nav-item menu-group">
                    <a class="nav-link menu-toggle" data-bs-toggle="collapse" href="#menuCategorias">
                        <i class="bi bi-folder2 me-2"></i>
                        <span>Categorias</span>
                        <i class="bi bi-chevron-down arrow-icon"></i>
                    </a>
                    <div class="collapse menu-subgroup" id="menuCategorias">
                        <a href="index.php?page=categoria/categoria_cadastrar" class="nav-link">Cadastrar</a>
                        <a href="index.php?page=categoria/categoria_listar" class="nav-link">Listar</a>
                    </div>
                </li>
							<!-- Novo menu para Tópicos de Conteúdo -->
    <li class="nav-item menu-group">
        <a class="nav-link menu-toggle" data-bs-toggle="collapse" href="#menuTopicos">
            <i class="bi bi-journal-text me-2"></i>
            <span>Tópicos de Conteúdo</span>
            <i class="bi bi-chevron-down arrow-icon"></i>
        </a>
        <div class="collapse menu-subgroup" id="menuTopicos">
            <a href="index.php?page=admin/conteudo_topico/form_topico" class="nav-link">Cadastrar Tópico</a>
            <a href="index.php?page=admin/conteudo_topico/listar_topicos" class="nav-link">Listar Tópicos</a>
        </div>
    </li>

                <li class="nav-item menu-group">
                    <a class="nav-link menu-toggle" data-bs-toggle="collapse" href="#menuProfessores">
                        <i class="bi bi-person-badge me-2"></i>
                        <span>Professores</span>
                        <i class="bi bi-chevron-down arrow-icon"></i>
                    </a>
                    <div class="collapse menu-subgroup" id="menuProfessores">
                        <a href="index.php?page=professor/professor_cadastrar" class="nav-link">Cadastrar</a>
                        <a href="index.php?page=professor/professor_listar" class="nav-link">Listar</a>
                    </div>
                </li>

                <li class="nav-item menu-group">
                    <a class="nav-link menu-toggle" data-bs-toggle="collapse" href="#menuTurmas">
                        <i class="bi bi-people me-2"></i>
                        <span>Turmas</span>
                        <i class="bi bi-chevron-down arrow-icon"></i>
                    </a>
                    <div class="collapse menu-subgroup" id="menuTurmas">
                        <a href="index.php?page=turmas/cadastrar" class="nav-link">Cadastrar</a>
                        <a href="index.php?page=turmas/listar" class="nav-link">Listar</a>
                        <a href="index.php?page=turmas/gerenciar" class="nav-link">Gerenciar</a>
                    </div>
                </li>

                <li class="nav-item menu-group">
                    <a class="nav-link menu-toggle" data-bs-toggle="collapse" href="#menuEmpresas">
                        <i class="bi bi-building me-2"></i>
                        <span>Empresas</span>
                        <i class="bi bi-chevron-down arrow-icon"></i>
                    </a>
                    <div class="collapse menu-subgroup" id="menuEmpresas">
                        <a href="index.php?page=empresas/cadastrar" class="nav-link">Cadastrar</a>
                        <a href="index.php?page=empresas/listar" class="nav-link">Listar</a>
                    </div>
                </li>

                <li class="nav-item menu-group">
                    <a class="nav-link menu-toggle" data-bs-toggle="collapse" href="#menuRelatorios">
                        <i class="bi bi-graph-up me-2"></i>
                        <span>Relatórios</span>
                        <i class="bi bi-chevron-down arrow-icon"></i>
                    </a>
                    <div class="collapse menu-subgroup" id="menuRelatorios">
                        <a href="index.php?page=relatorios/alunos_por_turma" class="nav-link">Alunos por Turma</a>
                        <a href="index.php?page=relatorios/alunos_por_curso" class="nav-link">Alunos por Curso</a>
                        <a href="index.php?page=relatorios/relacao_por_turma" class="nav-link">Relação por Turma</a>
                        <a href="index.php?page=relatorios/relacao_por_curso" class="nav-link">Relação por Curso</a>
                        <a href="index.php?page=relatorios/alunos_por_ano" class="nav-link">Alunos por Ano</a>
                        <a href="index.php?page=relatorios/concluidos_por_ano" class="nav-link">Concluídos por Ano</a>
                    </div>
                </li>
            <?php endif; ?>

            <?php if ($nivel === 'Aluno'): ?>
                <li class="nav-item menu-group">
                    <a class="nav-link menu-toggle" data-bs-toggle="collapse" href="#menuMeusCursos">
                        <i class="bi bi-journal-bookmark me-2"></i>
                        <span>Meus Cursos</span>
                        <i class="bi bi-chevron-down arrow-icon"></i>
                    </a>
                    <div class="collapse menu-subgroup" id="menuMeusCursos">
                        <a href="index.php?page=curso/meus_cursos&status=ativo" class="nav-link">Ativos</a>
                        <a href="index.php?page=curso/meus_cursos&status=concluido" class="nav-link">Concluídos</a>
                        <a href="index.php?page=curso/meus_cursos&status=cancelado" class="nav-link">Cancelados</a>
                    </div>
                </li>
            <?php endif; ?>

            <li class="nav-item menu-group">
                <a class="nav-link menu-toggle" data-bs-toggle="collapse" href="#menuAlunos">
                    <i class="bi bi-person me-2"></i>
                    <span>Aluno</span>
                    <i class="bi bi-chevron-down arrow-icon"></i>
                </a>
                <div class="collapse menu-subgroup" id="menuAlunos">
                    <?php if ($nivel === 'Aluno' && $mostrarFichaInscricao): ?>
                        <a href="index.php?page=aluno/ficha_inscricao" class="nav-link">Nova Inscrição</a>
                    <?php endif; ?>
                    <a href="index.php?page=aluno/listar_fichas" class="nav-link">Minhas Inscrições</a>
                </div>
            </li>

            <?php if ($nivel !== 'Aluno' || $mostrarMeusCertificados): ?>
                <li class="nav-item menu-group">
                    <a class="nav-link menu-toggle" data-bs-toggle="collapse" href="#menuCertificados">
                        <i class="bi bi-award me-2"></i>
                        <span>Certificados</span>
                        <i class="bi bi-chevron-down arrow-icon"></i>
                    </a>
                    <div class="collapse menu-subgroup" id="menuCertificados">
                        <?php if ($nivel === 'Aluno' && $mostrarMeusCertificados): ?>
                            <a href="index.php?page=certificados/exibir_certificado" class="nav-link">Meus Certificados</a>
                        <?php endif; ?>
                        <?php if ($nivel !== 'Aluno'): ?>
                            <a href="index.php?page=certificados/form_busca" class="nav-link">Validar Certificado</a>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endif; ?>

            <li class="nav-divider my-2"></li>

            <li class="nav-item">
                <a href="index.php?page=perfil/perfil" class="nav-link">
                    <i class="bi bi-person-circle me-2"></i>
                    <span>Meu Perfil</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?page=logout/logout" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    <span>Sair</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
.sidebar {
    width: 260px;
    background: white;
    height: 100vh;
    position: fixed;
    z-index: 1030;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 1.5rem 1rem 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.logo-container {
    padding: 0.5rem;
    margin-bottom: 0.5rem;
}

.logo-img {
    max-height: 50px;
    width: auto;
}

.institution-name {
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 500;
}

.menu-content {
    flex: 1;
    overflow-y: auto;
    padding: 0.5rem 0;
}

.nav-item {
    position: relative;
}

.nav-link {
    display: flex;
    align-items: center;
    color: #495057;
    padding: 0.65rem 1.5rem;
    margin: 0.1rem 0;
    font-weight: 500;
    transition: all 0.2s;
}

.nav-link:hover {
    color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

.nav-link.active {
    color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.1);
}

.nav-link i {
    width: 24px;
    text-align: center;
    font-size: 1.1rem;
}

.arrow-icon {
    margin-left: auto;
    font-size: 0.8rem;
    transition: transform 0.2s;
}

.menu-toggle:not(.collapsed) .arrow-icon {
    transform: rotate(180deg);
}

.menu-subgroup {
    padding-left: 2.5rem;
}

.menu-subgroup .nav-link {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    font-weight: 400;
}

.menu-subgroup .nav-link:hover {
    background-color: rgba(13, 110, 253, 0.03);
}

.nav-divider {
    height: 1px;
    background-color: rgba(0, 0, 0, 0.05);
    margin: 0.5rem 1.5rem;
}

.text-danger {
    color: #dc3545 !important;
}

/* Responsividade */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    .sidebar.active {
        transform: translateX(0);
    }
}
</style>

<script>
// Ativar submenus quando a página for carregada
document.addEventListener('DOMContentLoaded', function() {
    // Mantém o submenu aberto se estiver na página correspondente
    const currentPath = window.location.pathname.split('/').pop();
    const menuLinks = document.querySelectorAll('.menu-subgroup a.nav-link');
    
    menuLinks.forEach(link => {
        if (link.getAttribute('href').includes(currentPath)) {
            link.classList.add('active');
            const parentCollapse = link.closest('.collapse');
            if (parentCollapse) {
                parentCollapse.classList.add('show');
                const toggle = document.querySelector(`[href="#${parentCollapse.id}"]`);
                if (toggle) {
                    toggle.classList.remove('collapsed');
                }
            }
        }
    });
});
</script>