<?php
// Conexão direta (mesma que funcionou no dashboard)
try {
    $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=iesb_acesso_academico", "postgres", "mamst1ns");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lógica de Cadastro
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Chamada da Procedure sp_criar_aluno conforme o edital
        $stmt = $pdo->prepare("CALL sp_criar_aluno(?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['matricula'], 
            $_POST['nome'], 
            $_POST['cpf'], 
            $_POST['curso'], 
            $_POST['campus']
        ]);
        echo "<script>alert('Aluno cadastrado com sucesso!'); window.location='dashboard.php';</script>";
        exit;
    }
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>IESB - Cadastrar Aluno</title>
    <style>
        body { font-family: Arial; background: #f4f7f6; padding: 50px; text-align: center; }
        .form-box { background: white; padding: 30px; display: inline-block; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: left; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #1a73e8; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #1557b0; }
        a { display: block; margin-top: 15px; text-align: center; color: #666; text-decoration: none; }
    </style>
</head>
<body>

<div class="form-box">
    <h2>Novo Cadastro de Aluno</h2>
    <form method="POST">
        <label>Matrícula:</label>
        <input type="text" name="matricula" placeholder="Ex: 2026001" required>
        
        <label>Nome Completo:</label>
        <input type="text" name="nome" placeholder="Nome do Aluno" required>
        
        <label>CPF:</label>
        <input type="text" name="cpf" placeholder="000.000.000-00" required>
        
        <label>Curso:</label>
        <input type="text" name="curso" placeholder="Ex: Engenharia Civil">
        
        <label>Campus:</label>
        <input type="text" name="campus" placeholder="Ex: Campus Sul">
        
        <button type="submit">Finalizar Cadastro</button>
    </form>
    <a href="dashboard.php">← Voltar ao Painel</a>
</div>

</body>
</html>