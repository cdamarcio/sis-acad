<?php
// php/includes/SolicitacaoService.php
require_once 'config.php';

<?php
// php/includes/SolicitacaoService.php

class SolicitacaoService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ADICIONE ESTA FUNÇÃO ABAIXO:
    public function listar() {
        try {
            // Consulta que busca as solicitações e o nome do aluno relacionado
            $sql = "SELECT s.*, a.nome as aluno_nome 
                    FROM solicitacoes s 
                    JOIN alunos a ON s.aluno_id = a.id 
                    ORDER BY s.data_abertura DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // ... suas outras funções (abrirSolicitacao, etc) ...
}

    public function abrirSolicitacao($aluno_id, $tipo, $descricao) {
        try {
            // Regra de Negócio: Aluno deve estar ATIVO para abrir solicitação
            $sqlCheck = "SELECT status FROM alunos WHERE id = :id";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([':id' => $aluno_id]);
            $aluno = $stmtCheck->fetch();

            if (!$aluno || $aluno['status'] !== 'ATIVO') {
                return "Erro: Apenas alunos ATIVOS podem abrir solicitações.";
            }

            $sql = "INSERT INTO solicitacoes (aluno_id, tipo_solicitacao, descricao) 
                    VALUES (:aluno_id, :tipo, :descricao)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':tipo'     => $tipo,
                ':descricao' => $descricao
            ]);
            return "Solicitação aberta com sucesso!";
        } catch (PDOException $e) {
            return "Erro no banco: " . $e->getMessage();
        }
    }
}