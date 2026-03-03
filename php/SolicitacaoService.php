<?php
/**
 * IESB - Sistema de Controle de Acesso Acadêmico
 * Camada de Serviço para Gestão de Solicitações
 */

class SolicitacaoService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Lista todas as solicitações com o nome do aluno relacionado
     * Utilizado para alimentar o Dashboard principal.
     */
    public function listar() {
        try {
            $sql = "SELECT s.*, a.nome as aluno_nome 
                    FROM solicitacoes s 
                    JOIN alunos a ON s.aluno_id = a.id 
                    ORDER BY s.data_abertura DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Em caso de erro, retorna array vazio para não quebrar o foreach no index
            return [];
        }
    }

    /**
     * Abre uma nova solicitação acadêmica
     * Validações de regras de negócio são tratadas via Procedure ou Service.
     */
    public function abrirSolicitacao($aluno_id, $tipo, $descricao) {
        try {
            $sql = "INSERT INTO solicitacoes (aluno_id, tipo_solicitacao, descricao, status) 
                    VALUES (:aluno_id, :tipo, :descricao, 'ABERTA')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':aluno_id'  => $aluno_id,
                ':tipo'      => $tipo,
                ':descricao' => $descricao
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao abrir solicitação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Altera o status de uma solicitação (Ex: ABERTA -> FINALIZADA)
     */
    public function alterarStatus($id, $novoStatus) {
        try {
            $sql = "UPDATE solicitacoes SET status = :status WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':status' => $novoStatus,
                ':id'     => $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}