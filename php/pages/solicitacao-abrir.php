<?php
require_once '../includes/config.php';
require_once '../includes/SolicitacaoService.php';

$mensagem = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service = new SolicitacaoService($pdo);
    // Captura os dados do formulário
    $aluno_id = $_POST['aluno_id'];
    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];

    $mensagem = $service->abrirSolicitacao($aluno_id, $tipo, $descricao);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>IESB - Abrir Solicitação</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Nova Solicitação Acadêmica</h2>
    
    <?php if ($mensagem): ?>
        <p class="alert"><strong><?php echo $mensagem; ?></strong></p>
    <?php endif; ?>

    <form method="POST">
        <label>ID do Aluno:</label><br>
        <input type="number" name="aluno_id" required><br>

        <label>Tipo de Solicitação:</label><br>
        <select name="tipo" required>
            <option value="Declaracao">Declaração de Matrícula</option>
            <option value="Historico">Histórico Escolar</option>
            <option value="Financeiro">Ajuste Financeiro</option>
            <option value="Outros">Outros</option>
        </select><br>

        <label>Descrição:</label><br>
        <textarea name="descricao" rows="4" cols="50" required></textarea><br>

        <button type="submit">Enviar Solicitação</button>
    </form>
    <br>
    <a href="../index.php">Voltar ao Painel</a>
</body>
</html>