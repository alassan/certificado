<?php
class Endereco
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function save($dados)
    {
        $sql = "INSERT INTO enderecos (cep, logradouro, bairro, cidade, uf, numero)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $dados['cep'],
            $dados['logradouro'],
            $dados['bairro'],
            $dados['cidade'],
            $dados['uf'],
            $dados['numero']
        ]);

        return $this->conn->lastInsertId();
    }
}
