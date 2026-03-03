<?php
// Configurações de erro para desenvolvimento
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Conexão com o PostgreSQL
    $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=iesb_acesso_academico", "postgres", "mamst1ns");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta à VIEW vw_status_academico (Regra de Ouro do Edital)
    $sql = "SELECT * FROM vw_status_academico ORDER BY nome ASC";
    $stmt = $pdo->query($sql);
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cálculos para os Cards de Resumo
    $totalAlunos = count($alunos);
    $totalLiberados = 0;
    foreach ($alunos as $aluno) {
        if ($aluno['acesso_liberado'] === 'LIBERADO') {
            $totalLiberados++;
        }
    }

} catch (PDOException $e) {
    die("Erro na fundação do sistema (Banco de Dados): " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IESB - Painel de Controle Acadêmico</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f7f6; color: #333; }
        .container { max-width: 1100px; margin: auto; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #1a73e8; padding-bottom: 10px; }
        
        /* Cards de Estatísticas */
        .stats-container { display: flex; gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); flex: 1; text-align: center; }
        .card h3 { margin: 0; color: #666; font-size: 14px; text-transform: uppercase; }
        .card p { margin: 10px 0 0; font-size: 32px; font-weight: bold; color: #1a73e8; }
        
        /* Menu de Navegação */
        nav { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .btn { padding: 12px 20px; border-radius: 5px; text-decoration: none; color: white; font-weight: bold; font-size: 14px; transition: 0.3s; }
        .btn-blue { background: #1a73e8; }
        .btn-yellow { background: #f4b400; }
        .btn-green { background: #0f9d58; }
        .btn-red { background: #d93025; }
        .btn:hover { opacity: 0.8; }

        /* Tabela de Dados */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; color: #555; font-weight: 600; }
        tr:hover { background-color: #f1f4f9; }
        
        /* Badges de Status */
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .bg-success { background: #e6ffed; color: #1e7e34; }
        .bg-danger { background: #ffeef0; color: #d73a49; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Controle de Acesso Acadêmico</h1>
        <div style="text-align: right;">
            <strong>Analista:</strong> Márcio Oliveira<br>
            <small>Sistema IESB Sênior</small>
        </div>
    </header>

    <div class="stats-container">
        <div class="card">
            <h3>Total de Alunos</h3>
            <p><?= $totalAlunos ?></p>
        </div>
        <div class="card">
            <h3>Acessos Liberados</h3>
            <p style="color: #0f9d58;"><?= $totalLiberados ?></p>
        </div>
        <div class="card">
            <h3>Bloqueados / Pendentes</h3>
            <p style="color: #d93025;"><?= ($totalAlunos - $totalLiberados) ?></p>
        </div>
    </div>

    <nav>
        <a href="aluno-cadastrar.php" class="btn btn-blue">+ Novo Aluno</a>
        <a href="abrir_solicitacao.php" class="btn btn-yellow">! Abrir Solicitação</a>
        <a href="alterar_status.php" class="btn btn-green">⚙ Gerenciar Status</a>
        <a href="consulta_acesso.php" class="btn btn-red">🔍 Consulta Catraca</a>
    </nav>

    <table>
        <thead>
            <tr>
                <th>Matrícula</th>
                <th>Nome</th>
                <th>Status Acadêmico</th>
                <th>Possui Pendência?</th>
                <th>Acesso</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($alunos)): ?>
                <tr><td colspan="5" style="text-align:center;">Nenhum aluno cadastrado no sistema.</td></tr>
            <?php else: ?>
                <?php foreach ($alunos as $aluno): ?>
                <tr>
                    <td><strong><?= $aluno['matricula'] ?></strong></td>
                    <td><?= $aluno['nome'] ?></td>
                    <td><?= $aluno['status_aluno'] ?></td>
                    <td><?= $aluno['possui_pendencia'] ?></td>
                    <td>
                        <span class="badge <?= ($aluno['acesso_liberado'] == 'LIBERADO' ? 'bg-success' : 'bg-danger') ?>">
                            <?= $aluno['acesso_liberado'] ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>