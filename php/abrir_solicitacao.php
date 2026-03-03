<?php
try {
    $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=iesb_acesso_academico", "postgres", "mamst1ns");
    
    // Busca alunos para o SELECT
    $alunos = $pdo->query("SELECT id, nome FROM alunos WHERE status = 'ATIVO'")->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // O edital pede uma Procedure para criar solicitação. Vamos criá-la no passo abaixo.
        $stmt = $pdo->prepare("CALL sp_criar_solicitacao(?, ?, ?)");
        $stmt->execute([$_POST['id_aluno'], $_POST['tipo'], $_POST['descricao']]);
        header("Location: dashboard.php");
    }
} catch (Exception $e) { $erro = $e->getMessage(); }
?>

<!DOCTYPE html>
<html>
<head><title>Abrir Solicitação</title></head>
<body>
    <h2>Nova Solicitação Acadêmica</h2>
    <?php if(isset($erro)) echo "<p style='color:red'>$erro</p>"; ?>
    
    <form method="POST">
        <select name="id_aluno" required>
            <option value="">Selecione o Aluno</option>
            <?php foreach($alunos as $a): ?>
                <option value="<?= $a['id'] ?>"><?= $a['nome'] ?></option>
            <?php endforeach; ?>
        </select><br><br>
        
        <select name="tipo" required>
            <option value="MATRICULA">Matrícula</option>
            <option value="TRANCAMENTO">Trancamento</option>
            <option value="CANCELAMENTO">Cancelamento</option>
        </select><br><br>
        
        <textarea name="descricao" placeholder="Descrição da solicitação"></textarea><br><br>
        <button type="submit">Abrir Solicitação</button>
    </form>
    <br><a href="dashboard.php">Voltar</a>
</body>
</html>