<?php
/**
 * IESB - Sistema de Controle de Acesso Acadêmico
 * Camada de Serviço - Solicitações
 */

if (!defined('IESB_ACCESS')) {
    define('IESB_ACCESS', true);
}

require_once 'config.php';

class SolicitacaoService {
    
    private PDO $pdo;
    
    public function __construct() {
        $this->pdo = getConnection();
    }
    
    // =====================================================
    // OPERAÇÕES DE SOLICITAÇÃO
    // =====================================================
    
    /**
     * Cria uma nova solicitação usando stored procedure
     * 
     * @param array $dados Dados da solicitação
     * @return array Resultado da operação
     */
    public function criar(array $dados): array {
        try {
            // Validações
            if (empty($dados['id_aluno'])) {
                return ['sucesso' => false, 'mensagem' => 'Aluno é obrigatório'];
            }
            
            if (empty($dados['tipo'])) {
                return ['sucesso' => false, 'mensagem' => 'Tipo de solicitação é obrigatório'];
            }
            
            // Chama stored procedure
            $stmt = $this->pdo->prepare("CALL sp_criar_solicitacao(:id_aluno, :tipo, :descricao)");
            $stmt->execute([
                ':id_aluno' => $dados['id_aluno'],
                ':tipo' => $dados['tipo'],
                ':descricao' => $dados['descricao'] ?? null
            ]);
            
            return ['sucesso' => true, 'mensagem' => 'Solicitação criada com sucesso!'];
            
        } catch (PDOException $e) {
            error_log("Erro ao criar solicitação: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => 'Erro ao criar solicitação: ' . $e->getMessage()];
        }
    }
    
    /**
     * Altera status de uma solicitação usando stored procedure
     * 
     * @param int $id ID da solicitação
     * @param string $novoStatus Novo status
     * @return array Resultado da operação
     */
    public function alterarStatus(int $id, string $novoStatus): array {
        try {
            // Chama stored procedure
            $stmt = $this->pdo->prepare("CALL sp_alterar_status_solicitacao(:id, :status)");
            $stmt->execute([
                ':id' => $id,
                ':status' => $novoStatus
            ]);
            
            return ['sucesso' => true, 'mensagem' => 'Status alterado com sucesso!'];
            
        } catch (PDOException $e) {
            error_log("Erro ao alterar status: " . $e->getMessage());
            return ['sucesso' => false, 'mensagem' => 'Erro ao alterar status: ' . $e->getMessage()];
        }
    }
    
    /**
     * Lista todas as solicitações com dados do aluno
     * 
     * @return array Lista de solicitações
     */
    public function listar(): array {
        try {
            $sql = "SELECT s.*, a.nome as aluno_nome, a.matricula as aluno_matricula 
                    FROM solicitacoes s 
                    JOIN alunos a ON s.id_aluno = a.id 
                    ORDER BY s.data_abertura DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro ao listar solicitações: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista solicitações de um aluno específico
     * 
     * @param int $idAluno ID do aluno
     * @return array Lista de solicitações
     */
    public function listarPorAluno(int $idAluno): array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM solicitacoes WHERE id_aluno = :id_aluno ORDER BY data_abertura DESC");
            $stmt->execute([':id_aluno' => $idAluno]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro ao listar solicitações do aluno: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca solicitação por ID
     * 
     * @param int $id ID da solicitação
     * @return array|null Dados da solicitação
     */
    public function buscarPorId(int $id): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT s.*, a.nome as aluno_nome, a.matricula as aluno_matricula 
                                         FROM solicitacoes s 
                                         JOIN alunos a ON s.id_aluno = a.id 
                                         WHERE s.id = :id");
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar solicitação: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtém logs de uma solicitação
     * 
     * @param int $idSolicitacao ID da solicitação
     * @return array Lista de logs
     */
    public function obterLogs(int $idSolicitacao): array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM log_solicitacoes WHERE id_solicitacao = :id ORDER BY data_alteracao DESC");
            $stmt->execute([':id' => $idSolicitacao]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro ao obter logs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcula dias em aberto usando function do banco
     * 
     * @param int $id ID da solicitação
     * @return int Dias em aberto
     */
    public function calcularDiasEmAberto(int $id): int {
        try {
            $stmt = $this->pdo->prepare("SELECT fn_dias_em_aberto(:id) as dias");
            $stmt->execute([':id' => $id]);
            return (int) $stmt->fetch()['dias'];
        } catch (PDOException $e) {
            error_log("Erro ao calcular dias: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtém tipos de solicitação disponíveis
     * 
     * @return array Tipos de solicitação
     */
    public function obterTipos(): array {
        return [
            'MATRICULA' => 'Matrícula',
            'TRANCAMENTO' => 'Trancamento',
            'CANCELAMENTO' => 'Cancelamento',
            'REMATRICULA' => 'Rematrícula',
            'DECLARACAO' => 'Declaração'
        ];
    }
    
    /**
     * Obtém status disponíveis
     * 
     * @return array Status
     */
    public function obterStatus(): array {
        return [
            'ABERTA' => 'Aberta',
            'ANALISE' => 'Em Análise',
            'FINALIZADA' => 'Finalizada'
        ];
    }
}
