<?php
require_once 'AlunoService.php';

// Conexão PDO (usando seus dados confirmados)
try {
    $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=iesb_acesso_academico", "postgres", "mamst1ns");
    $service = new AlunoService($pdo);
    $alunos = $service->listar();
    $status = $service->obterStatusAcademico();
} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sistema IESB</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f4f4f4; }
        .card { background: white; padding: 15px; border-radius: 8px; display: inline-block; margin-right: 10px; box-shadow: 2px 2px 5px #ccc; }
    </style>
</head>
<body>
    <h1>Painel Acadêmico - IESB</h1>
    
    <div class="card">Total: <?php echo $status['total']; ?></div>
    <div class="card">Ativos: <?php echo $status['ativos']; ?></div>

    <h3>Lista de Alunos</h3>
    <table border="1" cellpadding="10" style="width:100%; background:white;">
        <tr>
            <th>Matrícula</th>
            <th>Nome</th>
            <th>Curso</th>
        </tr>
        <?php foreach ($alunos as $aluno): ?>
        <tr>
            <td><?php echo $aluno['matricula']; ?></td>
            <td><?php echo $aluno['nome']; ?></td>
            <td><?php echo $aluno['curso']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>