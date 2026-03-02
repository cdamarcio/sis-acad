<?php
/**
 * IESB - Sistema de Controle de Acesso Acadêmico
 * Camada de Serviço para Gestão de Alunos
 */

class AlunoService {
    private $pdo;

    /**
     * Construtor com Injeção de Dependência do PDO
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Lista todos os alunos cadastrados no sistema
     */
    public function listar() {
        try {
            $sql = "SELECT * FROM alunos ORDER BY nome ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao listar alunos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cadastra um novo aluno utilizando a Stored Procedure do PostgreSQL
     */
    public function cadastrar($matricula, $nome, $cpf, $curso, $unidade) {
        try {
            // Chama a procedure sp_criar_aluno definida no banco de dados
            $sql = "CALL sp_criar_aluno(:matricula, :nome, :cpf, :curso, :unidade)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':matricula' => $matricula,
                ':nome'      => $nome,
                ':cpf'       => $cpf,
                ':curso'     => $curso,
                ':unidade'   => $unidade
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao cadastrar aluno: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém o resumo estatístico para os cards do Dashboard
     * Resolve o erro: Call to undefined method AlunoService::obterStatusAcademico()
     */
    public function obterStatusAcademico() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'ATIVO' THEN 1 ELSE 0 END) as ativos,
                        SUM(CASE WHEN status = 'INATIVO' THEN 1 ELSE 0 END) as inativos
                    FROM alunos";
            $stmt = $this->pdo->query($sql);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            // Garante que valores nulos sejam tratados como zero
            return [
                'total'   => (int)($resultado['total'] ?? 0),
                'ativos'  => (int)($resultado['ativos'] ?? 0),
                'inativos' => (int)($resultado['inativos'] ?? 0)
            ];
        } catch (PDOException $e) {
            error_log("Erro ao obter status acadêmico: " . $e->getMessage());
            return ['total' => 0, 'ativos' => 0, 'inativos' => 0];
        }
    }

    /**
     * Busca um aluno específico pela matrícula
     */
    public function buscarPorMatricula($matricula) {
        try {
            $sql = "SELECT * FROM alunos WHERE matricula = :matricula";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':matricula' => $matricula]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
}