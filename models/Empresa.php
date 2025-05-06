<?php

class Empresa {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Cadastrar nova empresa
    public function cadastrar($dados) {
        try {
            if (empty($dados['nome'])) {
                throw new Exception('O nome da empresa é obrigatório');
            }

            $cnpj = preg_replace('/[^0-9]/', '', $dados['cnpj']);

            // Verifica duplicidade de CNPJ
            if ($this->buscarPorCNPJ($cnpj)) {
                throw new Exception('Já existe uma empresa cadastrada com este CNPJ');
            }

            $sql = "INSERT INTO empresas 
                    (nome, cnpj, endereco, telefone, email, responsavel, data_cadastro, ativo) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), 1)";
            
            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                $dados['nome'],
                $cnpj,
                $dados['endereco'],
                $dados['telefone'],
                $dados['email'],
                $dados['responsavel']
            ]);

        } catch (PDOException $e) {
            error_log("Erro ao cadastrar empresa: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Validação de cadastro: " . $e->getMessage());
            return false;
        }
    }

    // Listar empresas ativas
    public function listar($somenteAtivos = true) {
    try {
        $sql = "SELECT * FROM empresas";
        if ($somenteAtivos) {
            $sql .= " WHERE ativo = 1";
        }
        $sql .= " ORDER BY nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao listar empresas: " . $e->getMessage());
        return false;
    }
}
  public function listarAtivas() {
    return $this->listar(true);
}


    // Buscar empresa por ID
    public function buscarPorId($id) {
        try {
            $sql = "SELECT * FROM empresas WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar empresa por ID: " . $e->getMessage());
            return false;
        }
    }

    // Atualizar empresa
    public function atualizar($id, $dados) {
        try {
            $sql = "UPDATE empresas SET 
                        nome = ?, 
                        cnpj = ?, 
                        endereco = ?, 
                        telefone = ?, 
                        email = ?, 
                        responsavel = ?, 
                        ativo = ?
                    WHERE id = ?";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $dados['nome'],
                preg_replace('/[^0-9]/', '', $dados['cnpj']),
                $dados['endereco'],
                $dados['telefone'],
                $dados['email'],
                $dados['responsavel'],
                $dados['ativo'] ?? 1,
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar empresa: " . $e->getMessage());
            return false;
        }
    }

    // Exclusão lógica
    public function excluir($id) {
        try {
            $sql = "UPDATE empresas SET ativo = 0 WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erro ao excluir empresa: " . $e->getMessage());
            return false;
        }
    }

    // Buscar empresa por CNPJ (sem máscara)
    public function buscarPorCNPJ($cnpj) {
        try {
            $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
            $sql = "SELECT id FROM empresas WHERE cnpj = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$cnpj]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar empresa por CNPJ: " . $e->getMessage());
            return false;
        }
    }
	
	/*public function listarAtivas() {
    $sql = "SELECT * FROM empresas WHERE ativo = 1 ORDER BY nome";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}*/







}
?>
