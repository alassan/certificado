<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../models/FichaInscricao.php';
require_once __DIR__ . '/../models/Endereco.php';

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $usuario_id = $_SESSION['usuario_id'];

        $ficha = new FichaInscricao($conn);
        $endereco = new Endereco($conn);

        // Valida curso selecionado
        $curso_disponivel_id = $_POST['curso_disponivel_id'] ?? null;
        if (!$curso_disponivel_id || !is_numeric($curso_disponivel_id)) {
            throw new Exception("Por favor, selecione um curso válido.");
        }

        // Confirma se o curso está no período de inscrição
        $sqlCurso = "
            SELECT curso_id, data_inicio, data_termino
            FROM cursos_disponiveis
            WHERE id = ? AND NOW() BETWEEN inicio_inscricao AND termino_inscricao
            LIMIT 1
        ";
        $stmtCurso = $conn->prepare($sqlCurso);
        $stmtCurso->execute([$curso_disponivel_id]);
        $cursoRow = $stmtCurso->fetch(PDO::FETCH_ASSOC);

        if (!$cursoRow) {
            throw new Exception("Curso indisponível ou fora do período de inscrição.");
        }

        $curso_id = $cursoRow['curso_id'];
        $data_inicio = $cursoRow['data_inicio'];
        $data_termino = $cursoRow['data_termino'];

        // Verifica duplicidade de inscrição
        $verifica = $conn->prepare("
            SELECT id FROM fichas_inscricao 
            WHERE usuario_id = ? AND curso_disponivel_id = ?
        ");
        $verifica->execute([$usuario_id, $curso_disponivel_id]);
        $fichaExistente = $verifica->fetchColumn();

        if ($fichaExistente) {
            $_SESSION['mensagem_info'] = "Você já está inscrito neste curso.";
            header("Location: ../view/aluno/comprovante_inscricao.php?id=$fichaExistente");
            exit;
        }

        // Validação dos campos obrigatórios
        $camposObrigatorios = ['nome_aluno', 'cpf', 'data_nascimento', 'contato', 'cep', 'logradouro', 'bairro', 'cidade', 'uf', 'numero'];
        foreach ($camposObrigatorios as $campo) {
            if (empty($_POST[$campo])) {
                throw new Exception("O campo " . ucfirst(str_replace('_', ' ', $campo)) . " é obrigatório.");
            }
        }

        // Salvar endereço
        $dadosEndereco = [
            'cep' => trim($_POST['cep']),
            'logradouro' => trim($_POST['logradouro']),
            'bairro' => trim($_POST['bairro']),
            'cidade' => trim($_POST['cidade']),
            'uf' => trim($_POST['uf']),
            'numero' => trim($_POST['numero'])
        ];
        $endereco_id = $endereco->save($dadosEndereco);

        // Dados da ficha
        $dadosFicha = [
            'nome_aluno' => trim($_POST['nome_aluno']),
            'cpf' => preg_replace('/[^0-9]/', '', $_POST['cpf']),
            'data_nascimento' => $_POST['data_nascimento'],
            'contato' => preg_replace('/[^0-9]/', '', $_POST['contato']),
            'curso_id' => $curso_id,
            'curso_disponivel_id' => $curso_disponivel_id,
            'endereco_id' => $endereco_id,
            'pmt_funcionario' => isset($_POST['pmt_funcionario']) ? 1 : 0,
            'observacoes' => trim($_POST['observacoes'] ?? ''),
            'usuario_id' => $usuario_id,
            'data_inicio' => $data_inicio,
            'data_termino' => $data_termino
        ];

        // Salvar ficha
        $novaId = $ficha->save($dadosFicha);
        if ($novaId) {
            $_SESSION['mensagem_sucesso'] = "Inscrição realizada com sucesso!";
            header("Location: ../view/aluno/comprovante_inscricao.php?id=$novaId");
            exit;
        } else {
            throw new Exception("Erro ao salvar ficha de inscrição.");
        }

    } catch (Exception $e) {
        $_SESSION['mensagem_erro'] = $e->getMessage();
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}
