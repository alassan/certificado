<?php
require_once __DIR__ . '/../config/database.php';

class CursoController {
    public function meusCursos() {
        session_start();

        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'Aluno') {
            header('Location: /certificado/login/login.php');
            exit;
        }

        $statusFiltro = $_GET['status'] ?? 'ativo';

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT 
                c.id,
                c.nome AS curso_nome,
                c.data_inicio,
                c.data_termino,
                c.carga_horaria,
                p.nome AS professor_nome,
                e.nome AS empresa,
                f.status
            FROM fichas_inscricao f
            JOIN cursos c ON f.curso_id = c.id
            LEFT JOIN professores p ON c.professor_id = p.id
            LEFT JOIN empresas e ON c.empresa_id = e.id
            WHERE f.usuario_id = :aluno_id 
            AND f.status = :status
        ");
        $stmt->execute([
            'aluno_id' => $_SESSION['usuario_id'],
            'status' => $statusFiltro
        ]);
        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../view/curso/meus_cursos.php';
    }
}