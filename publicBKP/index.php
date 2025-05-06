<?php
session_start();

// Caminho base das views
define('VIEW_PATH', __DIR__ . '/view/');

// Página solicitada
$page = $_GET['page'] ?? 'login';

// Mapeamento das rotas reais do seu sistema
$routes = [

    // Login / Logout
    'login' => 'login/login.php',
    'logout' => 'logout/logout.php',

    // Dashboard
    'painel' => 'dashboard/painel.php',

    // Admin - Cursos Disponíveis
    'admin_cursos_disponiveis_listar' => 'admin/cursos_disponiveis/listar.php',
    'admin_cursos_disponiveis_cadastrar' => 'admin/cursos_disponiveis/cadastrar.php',
    'admin_cursos_disponiveis_editar' => 'admin/cursos_disponiveis/editar.php',
    'admin_cursos_disponiveis_visualizar' => 'admin/cursos_disponiveis/visualizar.php',

    // Aluno
    'aluno_ficha_inscricao' => 'aluno/ficha_inscricao.php',
    'aluno_listar_fichas' => 'aluno/listar_fichas.php',
    'aluno_visualizar_ficha' => 'aluno/visualizar_ficha.php',
    'aluno_comprovante_inscricao' => 'aluno/comprovante_inscricao.php',
    'aluno_minhas_inscricoes' => 'aluno/minhas_inscricoes.php',
    'aluno_ficha_editar' => 'aluno/ficha_editar.php',

    // Cadastro
    'cadastro_cadastro' => 'cadastro/cadastro.php',
    'cadastro_listar_fichas' => 'cadastro/listar_fichas.php',
    'cadastro_minhas_inscricoes' => 'cadastro/minhas_inscricoes.php',
    'cadastro_ficha_inscricao' => 'cadastro/ficha_inscricao.php',
    'cadastro_comprovante_inscricao' => 'cadastro/comprovante_inscricao.php',
    'cadastro_ficha_editar' => 'cadastro/ficha_editar.php',

    // Categoria
    'categoria_listar' => 'categoria/categoria_listar.php',
    'categoria_cadastrar' => 'categoria/categoria_cadastrar.php',

    // Certificados
    'certificado_gerar' => 'certificados/gerar_certificado.php',
    'certificado_form_busca' => 'certificados/form_busca.php',
    'certificado_ver' => 'certificados/ver_certificado.php',
    'certificado_exibir' => 'certificados/exibir_certificado.php',

    // Curso
    'curso_listar' => 'curso/curso_listar.php',
    'curso_cadastrar' => 'curso/curso_cadastrar.php',
    'curso_editar' => 'curso/curso_editar.php',
    'curso_excluir' => 'curso/curso_excluir.php',
    'curso_meus_cursos' => 'curso/meus_cursos.php',
    'curso_listar_curso_disponivel' => 'curso/listar_curso_disponivel.php',
    'curso_gerenciar_curso' => 'curso/gerenciar_curso.php',

    // Empresas
    'empresa_listar' => 'empresas/listar.php',
    'empresa_cadastrar' => 'empresas/cadastrar.php',
    'empresa_editar' => 'empresas/editar.php',
    'empresa_excluir' => 'empresas/excluir.php',
    'empresa_visualizar' => 'empresas/visualizar.php',

    // Perfil
    'perfil_listar' => 'perfil/listar.php',
    'perfil_editar' => 'perfil/editar.php',

    // Professor
    'professor_listar' => 'professor/professor_listar.php',
    'professor_cadastrar' => 'professor/professor_cadastrar.php',
    'professor_editar' => 'professor/professor_editar.php',
    'professor_excluir' => 'professor/professor_excluir.php',

    // Turmas
    'turmas_listar' => 'turmas/listar.php',
    'turmas_cadastrar' => 'turmas/cadastrar.php',
    'turmas_editar' => 'turmas/editar.php',
    'turmas_visualizar' => 'turmas/visualizar.php',
];

// Verificar se a página existe nas rotas
if (array_key_exists($page, $routes)) {
    require_once VIEW_PATH . $routes[$page];
} else {
    // Página não encontrada (404)
    echo "<h1 style='text-align:center; margin-top:50px;'>404 - Página não encontrada</h1>";
    echo "<div style='text-align:center; margin-top:20px;'><a href='?page=painel' style='color:blue; text-decoration:underline;'>Voltar ao Painel</a></div>";
}
?>
