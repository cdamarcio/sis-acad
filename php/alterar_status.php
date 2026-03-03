<?php
try {
    $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=iesb_acesso_academico", "postgres", "mamst1ns");
    
    // Processa a alteração de status se o ID for enviado via GET
    if (isset($_GET['finalizar_id'])) {
        $stmt = $pdo->prepare("CALL sp_alterar_status_solicitacao(?, 'FINALIZADA')");
        $stmt->execute([$_GET['finalizar_id']]);
        header("Location: alterar_status.php?sucesso=1");
        exit;
    }
    
    // Busca solicitações que NÃO estão finalizadas
    $sql = "SELECT s.id, a.nome, s.tipo, s.status, s.data_abertura 
            FROM solicitacoes s 
            JOIN alunos a ON s.id_aluno = a.id 
            WHERE s.status != 'FINALIZADA'
            ORDER BY s.data_abertura DESC";
    $solicitacoes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar Status - IESB</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #1a73e8; color: white; }
        .btn { padding: 5px 10px; background: #28a745; color: white; text-decoration: none; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Gerenciar Solicitações Pendentes</h1>
    
    <?php if(isset($_GET['sucesso'])): ?>
        <p style="color: green;">✔ Status atualizado e acesso liberado!</p>
    <?php endif; ?>

    <table>
        <tr>
            <th>Aluno</th>
            <th>Tipo</th>
            <th>Status Atual</th>
            <th>Data Abertura</th>
            <th>Ação</th>
        </tr>
        <?php foreach($solicitacoes as $s): ?>
        <tr>
            <td><?= $s['nome'] ?></td>
            <td><?= $s['tipo'] ?></td>
            <td><strong><?= $s['status'] ?></strong></td>
            <td><?= date('d/m/Y H:i', strtotime($s['data_abertura'])) ?></td>
            <td><a href="?finalizar_id=<?= $s['id'] ?>" class="btn">Finalizar Solicitação</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <br><a href="dashboard.php">Voltar ao Painel</a>
</body>
</html>