<?php
/**
 * IESB - Sistema de Controle de Acesso Acadêmico
 * Camada de Serviço - Alunos
 * 
 * Implementa a lógica de negócio e integração com procedures do banco
 */

if (!defined('IESB_ACCESS')) {
    define('IESB_ACCESS', true);
}

require_once 'config.php';

class AlunoService {
    
    private PDO $pdo;
    
    /**
     * Construtor - inicializa conexão
     */
    public function __construct() {
        $this->pdo = getConnection();
    }
    
    // =====================================================
    // OPERAÇÕES CRUD
    // =====================================================
    
    /**
     * Cria um novo aluno usando stored procedure
     * 
     * @param array $dados Dados do aluno
     * @return array Resultado da operação
     */
    public function criar(array $dados): array {
        try {
            // Validações de negócio no PHP
            if (empty($dados['matricula'])) {
                return ['sucesso' => false, 'mensagem' => 'Matrícula é obrigatória'];
            }
            
            if (empty($dados['nome'])) {
                return ['sucesso' => false, 'mensagem' => 'Nome é obrigatório'];
            }
            
            if (empty($dados['cpf'])) {
                return ['sucesso' => false, 'mensagem' => 'CPF é obrigatório'];
            }
            
            if (empty($dados['curso'])) {
                return ['sucesso' => false, 'mensagem' => 'Curso é obrigatório'];
            }
            
            if (empty($dados['campus'])) {
                return ['sucesso' => false, 'mensagem' => 'Campus é obrigatório'];
            }
            
            // Chama stored procedure
            $stmt = $this->pdo->prepare("CALL sp_criar_aluno(:matricula, :nome, :cpf, :curso, :campus)");
            $stmt->execute([
                ':matricula' => $dados['matricula'],
                ':nome' => $dados['nome'],
                ':cpf' => $dados['cpf'],
                ':curso' => $dados['curso'],
                ':campus' => $dados['campus']
            ]);
            
            return ['sucesso' => true, 'mensagem' => 'Aluno cadastrado com sucesso!'];
            
        } catch (PDOException $e) {
            error_log("Erro ao criar aluno: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => 'Erro ao cadastrar: ' . $e->getMessage()];
        }
    }
    
    /**
     * Lista todos os alunos
     * 
     * @return array Lista de alunos
     */
    public function listar(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM alunos ORDER BY nome");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro ao listar alunos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca aluno por ID
     * 
     * @param int $id ID do aluno
     * @return array|null Dados do aluno
     */
    public function buscarPorId(int $id): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM alunos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar aluno: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Busca aluno por matrícula
     * 
     * @param string $matricula Matrícula do aluno
     * @return array|null Dados do aluno
     */
    public function buscarPorMatricula(string $matricula): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM alunos WHERE matricula = :matricula");
            $stmt->execute([':matricula' => $matricula]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar aluno por matrícula: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Atualiza status do aluno
     * 
     * @param int $id ID do aluno
     * @param string $status Novo status (ATIVO/INATIVO)
     * @return array Resultado da operação
     */
    public function atualizarStatus(int $id, string $status): array {
        try {
            $stmt = $this->pdo->prepare("UPDATE alunos SET status = :status WHERE id = :id");
            $stmt->execute([':id' => $id, ':status' => $status]);
            
            return ['sucesso' => true, 'mensagem' => 'Status atualizado com sucesso!'];
        } catch (PDOException $e) {
            error_log("Erro ao atualizar status: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar status'];
        }
    }
    
    // =====================================================
    // CONSULTA DE ACESSO
    // =====================================================
    
    /**
     * Consulta se aluno tem acesso liberado
     * 
     * @param int $id ID do aluno
     * @return array Dados da consulta de acesso
     */
    public function consultarAcesso(int $id): array {
        try {
            $aluno = $this->buscarPorId($id);
            
            if (!$aluno) {
                return ['sucesso' => false, 'mensagem' => 'Aluno não encontrado'];
            }
            
            // Conta pendências
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM solicitacoes WHERE id_aluno = :id AND status IN ('ABERTA', 'ANALISE')");
            $stmt->execute([':id' => $id]);
            $pendencias = $stmt->fetch()['total'];
            
            // Determina acesso
            $acessoLiberado = ($aluno['status'] === 'ATIVO' && $pendencias == 0);
            
            return [
                'sucesso' => true,
                'aluno' => $aluno,
                'pendencias' => $pendencias,
                'acesso_liberado' => $acessoLiberado,
                'mensagem' => $acessoLiberado ? 'Acesso LIBERADO' : ($aluno['status'] !== 'ATIVO' ? 'Acesso BLOQUEADO: Aluno ' . $aluno['status'] : 'Acesso BLOQUEADO: Possui ' . $pendencias . ' solicitação(ões) pendente(s)')
            ];
            
        } catch (PDOException $e) {
            error_log("Erro na consulta de acesso: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => 'Erro na consulta'];
        }
    }
    
    /**
     * Obtém status acadêmico de todos os alunos via view
     * 
     * @return array Lista de status
     */
    public function obterStatusAcademico(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM vw_status_academico");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro ao obter status acadêmico: " . $e->getMessage());
            return [];
        }
    }
}
