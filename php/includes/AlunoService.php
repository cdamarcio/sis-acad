<?php
// php/includes/AlunoService.php
require_once 'config.php';

class AlunoService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function listar() {
        $stmt = $this->pdo->query("SELECT * FROM alunos ORDER BY nome ASC");
        return $stmt->fetchAll();
    }
    
    public function cadastrar($nome, $matricula, $cpf, $email) {
        try {
            // Chamada da Procedure com tratamento de exceção (TRY/CATCH)
            $sql = "CALL sp_criar_aluno(:nome, :matricula, :cpf, :email)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nome'      => $nome,
                ':matricula' => $matricula,
                ':cpf'       => $cpf,
                ':email'     => $email
            ]);
            return "Aluno cadastrado com sucesso!";
        } catch (PDOException $e) {
            return "Erro ao cadastrar: " . $e->getMessage();
        }
    }
}