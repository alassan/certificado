<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/menu.php';

$anoFiltro = $_GET['ano'] ?? date('Y');
$turmas = $conn->query("SELECT id, nome FROM turma ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-content">
    <h3><i class="bi bi-bar-chart-line me-2"></i> Relatório: Alunos por Turma</h3>

    <form method="GET" class="row g-3 align-items-end mb-4">
        <div class="col-md-3">
            <label class="form-label">Ano</label>
            <input type="number" name="ano" value="<?= $anoFiltro ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Turma (opcional)</label>
            <select name="turma_id" class="form-select">
                <option value="">Todas</option>
                <?php foreach ($turmas as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= ($_GET['turma_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
            <a href="#" onclick="exportarTabela('csv')" class="btn btn-success ms-2"><i class="bi bi-download"></i> Exportar CSV</a>
        </div>
    </form>

    <?php
    // Consulta dos dados
    $turmaIdFiltro = $_GET['turma_id'] ?? '';
    $params = [$anoFiltro . '-01-01', $anoFiltro . '-12-31'];
    $sql = "SELECT t.nome AS turma, COUNT(f.id) AS total 
            FROM fichas_inscricao f
            JOIN turma t ON f.turma_id = t.id
            WHERE f.data_inscricao BETWEEN ? AND ?";
    if ($turmaIdFiltro) {
        $sql .= " AND f.turma_id = ?";
        $params[] = $turmaIdFiltro;
    }
    $sql .= " GROUP BY t.nome ORDER BY total DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if ($resultados): ?>
        <div class="table-responsive mt-3">
            <table id="tabela" class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Turma</th>
                        <th>Total de Alunos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $linha): ?>
                        <tr>
                            <td><?= htmlspecialchars($linha['turma']) ?></td>
                            <td><?= $linha['total'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <canvas id="graficoTurmas" height="120" class="mt-4"></canvas>

        <script>
        const labels = <?= json_encode(array_column($resultados, 'turma')) ?>;
        const dados = <?= json_encode(array_column($resultados, 'total')) ?>;

        new Chart(document.getElementById('graficoTurmas'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Alunos por Turma',
                    data: dados,
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function exportarTabela(formato) {
            let csv = 'Turma,Total de Alunos\n';
            <?php foreach ($resultados as $linha): ?>
                csv += "<?= addslashes($linha['turma']) ?>,<?= $linha['total'] ?>\n";
            <?php endforeach; ?>

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.href = url;
            link.download = "alunos_por_turma.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        </script>
    <?php else: ?>
        <div class="alert alert-warning">Nenhum resultado encontrado para o filtro aplicado.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
