<?php
require_once '../includes/config.php';

$resultado = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = $_POST['matricula'];
    
    try {
        // Busca o status do aluno para autorizar entrada
        $stmt = $pdo->prepare("SELECT nome, status FROM alunos WHERE matricula = :mat");
        $stmt->execute([':mat' => $matricula]);
        $aluno = $stmt->fetch();

        if ($aluno) {
            $resultado = ($aluno['status'] === 'ATIVO') ? "ACESSO LIBERADO: " . $aluno['nome'] : "ACESSO NEGADO: Aluno Inativo";
        } else {
            $resultado = "ERRO: Matrícula não encontrada.";
        }
    } catch (PDOException $e) {
        $resultado = "Erro na consulta.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>IESB - Controle de Portaria</title>
</head>
<body>
    <h2>Consulta de Acesso Acadêmico</h2>
    <form method="POST">
        <input type="text" name="matricula" placeholder="Digite a Matrícula" required>
        <button type="submit">Validar Entrada</button>
    </form>

    <?php if ($resultado): ?>
        <h3 style="color: <?php echo strpos($resultado, 'LIBERADO') !== false ? 'green' : 'red'; ?>">
            <?php echo $resultado; ?>
        </h3>
    <?php endif; ?>
</body>
</html>