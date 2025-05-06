<?php
require_once __DIR__ . '/../../../config/conexao.php';
require_once __DIR__ . '/../../../models/CursoDisponivel.php';
require_once __DIR__ . '/../../../models/FichaInscricao.php';
$fichaModel = new FichaInscricao($conn);
$fichaModel->atualizarStatusAutomaticamente();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /certificado/view/dashboard/painel.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /certificado/view/admin/cursos_disponiveis/listar.php');
    exit;
}

$id = (int)$_GET['id'];

$cursoDisponivelModel = new CursoDisponivel($conn);
$fichaInscricaoModel = new FichaInscricao($conn);

$curso = $cursoDisponivelModel->buscarComEmpresaPorId($id);

if (!$curso || !is_array($curso)) {
    header('Location: /certificado/view/admin/cursos_disponiveis/listar.php?erro=Curso não encontrado');
    exit;
}

$inscricoes = $fichaInscricaoModel->listarPorCursoDisponivel($id);

// Inicializa $totaisPorStatus com valores padrão
$totaisPorStatus = [
    'matriculado' => 0,
    'em_andamento' => 0,
    'concluido' => 0,
    'cancelado' => 0
];

// Calcula os totais por status se houver inscrições
if (!empty($inscricoes)) {
    foreach ($inscricoes as $inscricao) {
        $status = strtolower($inscricao['status_aluno'] ?? 'indefinido');
        $status = str_replace(' ', '_', $status); // Padroniza para o formato da chave
        
        if (array_key_exists($status, $totaisPorStatus)) {
            $totaisPorStatus[$status]++;
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h5 mb-0">
                            <?= isset($curso['nome_curso']) ? htmlspecialchars($curso['nome_curso']) : 'Curso não identificado' ?>
                        </h2>
                        <?php if (isset($curso['nome_empresa'])): ?>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-building me-1"></i> <?= htmlspecialchars($curso['nome_empresa']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Cards de Status Aprimorados -->
                    <div class="row mb-4">
                        <?php
                        $statusVisual = [
                            'matriculado' => [
                                'color' => 'primary',
                                'icon' => 'bi-person-check',
                                'title' => 'Matriculados',
                                'description' => 'Alunos matriculados'
                            ],
                            'em_andamento' => [
                                'color' => 'warning',
                                'icon' => 'bi-hourglass-split',
                                'title' => 'Em Andamento',
                                'description' => 'Cursando atualmente'
                            ],
                            'concluido' => [
                                'color' => 'success',
                                'icon' => 'bi-check-circle',
                                'title' => 'Concluídos',
                                'description' => 'Finalizaram o curso'
                            ],
                            'cancelado' => [
                                'color' => 'danger',
                                'icon' => 'bi-x-circle',
                                'title' => 'Cancelados',
                                'description' => 'Matrículas canceladas'
                            ]
                        ];

                        foreach ($totaisPorStatus as $status => $quantidade):
                            if (isset($statusVisual[$status])) {
                                $config = $statusVisual[$status];
                        ?>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-square bg-<?= $config['color'] ?>-subtle text-<?= $config['color'] ?> rounded-3 me-3 p-2">
                                            <i class="<?= $config['icon'] ?> fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-muted fw-normal"><?= $config['title'] ?></h6>
                                            <h3 class="mb-0 text-<?= $config['color'] ?>"><?= $quantidade ?></h3>
                                            <small class="text-muted"><?= $config['description'] ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                            }
                        endforeach;
                        ?>
                    </div>

                    <!-- Tabela de Inscrições -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom-0 py-3">
                            <h5 class="mb-0 d-flex align-items-center">
                                <i class="bi bi-people-fill text-primary me-2"></i>
                                Inscrições
                                <span class="badge bg-primary ms-2"><?= is_array($inscricoes) ? count($inscricoes) : 0 ?></span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($inscricoes) && is_array($inscricoes)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Nome</th>
                                                <th>CPF</th>
                                                <th>Status</th>
                                                <th class="pe-4">Data de Inscrição</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $statusMap = [
                                                    'matriculado' => ['primary', 'person-check', 'Matriculado'],
                                                    'em andamento' => ['warning', 'hourglass-split', 'Em Andamento'],
                                                    'concluido' => ['success', 'check-circle', 'Concluído'],
                                                    'cancelado' => ['danger', 'x-circle', 'Cancelado'],
                                                    'indefinido' => ['secondary', 'question-circle', 'Indefinido']
                                                ];
                                            ?>
                                            <?php foreach ($inscricoes as $inscricao): ?>
                                                <?php
                                                    $status = strtolower(trim($inscricao['status_aluno'] ?? 'indefinido'));
                                                    $badgeClass = $statusMap[$status][0] ?? 'secondary';
                                                    $icon = $statusMap[$status][1] ?? 'question-circle';
                                                    $statusText = $statusMap[$status][2] ?? ucfirst($status);
                                                ?>
                                                <tr>
                                                    <td class="ps-4 fw-medium"><?= isset($inscricao['nome_aluno']) ? htmlspecialchars($inscricao['nome_aluno']) : '' ?></td>
                                                    <td><?= isset($inscricao['cpf']) ? htmlspecialchars($inscricao['cpf']) : '' ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $badgeClass ?>-subtle text-<?= $badgeClass ?> d-flex align-items-center" style="width: fit-content;">
                                                            <i class="bi bi-<?= $icon ?> me-1"></i> <?= $statusText ?>
                                                        </span>
                                                    </td>
                                                    <td class="pe-4"><?= isset($inscricao['data_inscricao']) ? date('d/m/Y H:i', strtotime($inscricao['data_inscricao'])) : '' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-people-slash text-muted fs-1"></i>
                                    <h5 class="mt-3 text-muted">Nenhuma inscrição encontrada</h5>
                                    <p class="text-muted mb-0">Não há alunos inscritos neste curso no momento.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>