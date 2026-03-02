<?php
/**
 * IESB - Sistema de Controle de Acesso Acadêmico
 * Dashboard Principal
 */

require_once 'includes/config.php';
require_once 'includes/AlunoService.php';
require_once 'includes/SolicitacaoService.php';

// Instanciação dos serviços com Injeção de Dependência (PDO)
$alunoService = new AlunoService($pdo);
$solicitacaoService = new SolicitacaoService($pdo);

// Coleta de dados para o Dashboard
$statusGeral = $alunoService->obterStatusAcademico();
$ultimosAlunos = $alunoService->listar();
$ultimasSolicitacoes = $solicitacaoService->listar();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IESB - Dashboard de Acesso</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dashboard-cards { display: flex; gap: 20px; margin-bottom: 30px; }
        .card { padding: 20px; border-radius: 8px; color: white; flex: 1; text-align: center; }
        .card.total { background-color: #2c3e50; }
        .card.ativos { background-color: #27ae60; }
        .card.inativos { background-color: #c0392b; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f4f4f4; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .status-ativo { background: #eafaf1; color: #27ae60; }
        .status-inativo { background: #fdedec; color: #e74c3c; }
    </style>
</head>
<body>
    <header>
        <h1>🎓 IESB - Gestão de Acesso Acadêmico</h1>
        <nav>
            <a href="pages/aluno-cadastrar.php">Novo Aluno</a> | 
            <a href="pages/solicitacao-abrir.php">Nova Solicitação</a> | 
            <a href="pages/acesso-consultar.php">Portaria (Acesso)</a>
        </nav>
    </header>

    <main>
        <h2>Resumo Institucional</h2>
        <div class="dashboard-cards">
            <div class="card total">
                <h3>Total de Alunos</h3>
                <p style="font-size: 2em;"><?php echo $statusGeral['total']; ?></p>
            </div>
            <div class="card ativos">
                <h3>Alunos Ativos</h3>
                <p style="font-size: 2em;"><?php echo $statusGeral['ativos']; ?></p>
            </div>
            <div class="card inativos">
                <h3>Alunos Inativos</h3>
                <p style="font-size: 2em;"><?php echo $statusGeral['inativos']; ?></p>
            </div>
        </div>

        <section>
            <h3>Últimas Solicitações Registradas</h3>
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Aluno</th>
                        <th>Tipo</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($ultimasSolicitacoes, 0, 5) as $sol): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($sol['data_abertura'])); ?></td>
                        <td><?php echo htmlspecialchars($sol['aluno_nome']); ?></td>
                        <td><?php echo $sol['tipo_solicitacao']; ?></td>
                        <td><span class="status-badge"><?php echo $sol['status']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer style="margin-top: 50px; color: #666;">
        <p>&copy; 2026 IESB - Analista de Sistemas: Márcio Rodrigues de Oliveira</p>
    </footer>
</body>
</html>