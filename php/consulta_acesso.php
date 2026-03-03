<?php
$resultado = null;
if (isset($_GET['matricula']) && !empty($_GET['matricula'])) {
    try {
        $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=iesb_acesso_academico", "postgres", "mamst1ns");
        $stmt = $pdo->prepare("SELECT * FROM vw_status_academico WHERE matricula = ?");
        $stmt->execute([$_GET['matricula']]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { die($e->getMessage()); }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Consulta de Acesso - Catraca</title>
    <style>
        body { font-family: Arial; text-align: center; padding: 50px; }
        .box { border: 2px solid #ccc; padding: 20px; display: inline-block; width: 400px; border-radius: 10px; }
        .liberado { background: #d4edda; color: #155724; border-color: #c3e6cb; }
        .bloqueado { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        input { padding: 10px; width: 200px; }
        button { padding: 10px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Consulta de Acesso</h2>
        <form method="GET">
            <input type="text" name="matricula" placeholder="Digite a Matrícula" required autofocus>
            <button type="submit">Validar</button>
        </form>

        <?php if ($resultado): ?>
            <div style="margin-top: 20px; padding: 15px;" class="<?= ($resultado['acesso_liberado'] == 'LIBERADO' ? 'liberado' : 'bloqueado') ?>">
                <h3><?= $resultado['nome'] ?></h3>
                <h1><?= $resultado['acesso_liberado'] ?></h1>
                <p>Pendências Acadêmicas: <?= $resultado['possui_pendencia'] ?></p>
            </div>
        <?php elseif (isset($_GET['matricula'])): ?>
            <p style="color: red; margin-top: 20px;">Matrícula não encontrada no sistema.</p>
        <?php endif; ?>
    </div>
    <br><br><a href="dashboard.php">Voltar ao Sistema</a>
</body>
</html>