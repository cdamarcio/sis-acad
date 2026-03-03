<?php
require_once '../includes/config.php';

try {
    // Consulta simples para listar todos os alunos
    $stmt = $pdo->query("SELECT id, matricula, nome, status, data_cadastro FROM alunos ORDER BY nome ASC");
    $alunos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao listar alunos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>IESB - Listar Alunos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Alunos Cadastrados - IESB</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Matrícula</th>
                <th>Nome</th>
                <th>Status</th>
                <th>Data Cadastro</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alunos as $aluno): ?>
            <tr>
                <td><?php echo $aluno['id']; ?></td>
                <td><?php echo $aluno['matricula']; ?></td>
                <td><?php echo $aluno['nome']; ?></td>
                <td><?php echo $aluno['status']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($aluno['data_cadastro'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br>
    <a href="aluno-cadastrar.php">Cadastrar Novo Aluno</a>
</body>
</html>