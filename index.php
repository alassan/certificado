<?php
session_start();

// Rota principal
$rota = $_GET['rota'] ?? 'dashboard';

// Definição de todas as rotas possíveis
$rotas = [
    // Admin
    'admin/listar' => 'view/admin/listar.php',
    'admin/cadastrar' => 'view/admin/cadastrar.php',
    'admin/editar' => 'view/admin/editar.php',
    'admin/visualizar' => 'view/admin/visualizar.php',

    // Aluno
    'aluno/listar' => 'view/aluno/listar.php',
    'aluno/cadastrar' => 'view/aluno/cadastrar.php',
    'aluno/editar' => 'view/aluno/editar.php',
    'aluno/visualizar' => 'view/aluno/visualizar.php',

    // Cadastro
    'cadastro/listar' => 'view/cadastro/listar.php',
    'cadastro/cadastrar' => 'view/cadastro/cadastrar.php',
    'cadastro/editar' => 'view/cadastro/editar.php',
    'cadastro/visualizar' => 'view/cadastro/visualizar.php',

    // Categoria
    'categoria/listar' => 'view/categoria/listar.php',
    'categoria/cadastrar' => 'view/categoria/cadastrar.php',
    'categoria/editar' => 'view/categoria/editar.php',
    'categoria/visualizar' => 'view/categoria/visualizar.php',

    // Certificados
    'certificados/listar' => 'view/certificados/listar.php',
    'certificados/cadastrar' => 'view/certificados/cadastrar.php',
    'certificados/editar' => 'view/certificados/editar.php',
    'certificados/visualizar' => 'view/certificados/visualizar.php',

    // Curso
    'curso/listar' => 'view/curso/listar.php',
    'curso/cadastrar' => 'view/curso/cadastrar.php',
    'curso/editar' => 'view/curso/editar.php',
    'curso/visualizar' => 'view/curso/visualizar.php',

    // Dashboard
    'dashboard' => 'view/dashboard/index.php',

    // Empresas
    'empresas/listar' => 'view/empresas/listar.php',
    'empresas/cadastrar' => 'view/empresas/cadastrar.php',
    'empresas/editar' => 'view/empresas/editar.php',
    'empresas/visualizar' => 'view/empresas/visualizar.php',

    // Includes
    'includes/listar' => 'view/includes/listar.php',
    'includes/cadastrar' => 'view/includes/cadastrar.php',
    'includes/editar' => 'view/includes/editar.php',
    'includes/visualizar' => 'view/includes/visualizar.php',

    // Login
    'login' => 'view/login/login.php',

    // Logout
    'logout' => 'view/logout/logout.php',

    // Perfil
    'perfil/listar' => 'view/perfil/listar.php',
    'perfil/cadastrar' => 'view/perfil/cadastrar.php',
    'perfil/editar' => 'view/perfil/editar.php',
    'perfil/visualizar' => 'view/perfil/visualizar.php',

    // Professor
    'professor/listar' => 'view/professor/listar.php',
    'professor/cadastrar' => 'view/professor/cadastrar.php',
    'professor/editar' => 'view/professor/editar.php',
    'professor/visualizar' => 'view/professor/visualizar.php',

    // Turmas
    'turmas/listar' => 'view/turmas/listar.php',
    'turmas/cadastrar' => 'view/turmas/cadastrar.php',
    'turmas/editar' => 'view/turmas/editar.php',
    'turmas/visualizar' => 'view/turmas/visualizar.php',
];

// Verifica se a rota existe
if (array_key_exists($rota, $rotas)) {
    require_once $rotas[$rota];
} else {
    http_response_code(404);
    echo "
        <div style='font-family: Arial; padding: 40px; text-align: center;'>
            <h1 style='color: #cc0000;'>Erro 404</h1>
            <p>Rota <strong>$rota</strong> não encontrada.</p>
            <a href='?rota=dashboard' style='color: #007bff; text-decoration: none;'>Voltar para o Painel</a>
        </div>
    ";
}
?>
