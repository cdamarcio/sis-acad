<?php
require_once '../includes/config.php';
require_once '../includes/AlunoService.php';

$mensagem = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service = new AlunoService($pdo);
    $mensagem = $service->cadastrar(
        $_POST['nome'], 
        $_POST['matricula'], 
        $_POST['cpf'], 
        $_POST['email']
    );
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>IESB - Cadastrar Aluno</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Cadastro de Aluno - Controle de Acesso</h2>
    <?php if ($mensagem): ?>
        <p><strong><?php echo $mensagem; ?></strong></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="nome" placeholder="Nome Completo" required><br>
        <input type="text" name="matricula" placeholder="Matrícula" required><br>
        <input type="text" name="cpf" placeholder="CPF (apenas números)" required><br>
        <input type="email" name="email" placeholder="E-mail Acadêmico"><br>
        <button type="submit">Salvar Aluno</button>
    </form>
</body>
</html>