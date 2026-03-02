<?php
/**
 * IESB - Sistema de Controle de Acesso Acadêmico
 * Página Inicial
 */

define('IESB_ACCESS', true);
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/AlunoService.php';
require_once 'includes/SolicitacaoService.php';

session_start();

// Instancia serviços
$alunoService = new AlunoService($pdo);
// Antes (Linha 17 com erro):
$solicitacaoService = new SolicitacaoService($pdo);

// Obtém estatísticas
$alunos = $alunoService->listar();
$solicitacoes = $solicitacaoService->listar();

$totalAlunos = count($alunos);
$totalSolicitacoes = count($solicitacoes);
$solicitacoesAbertas = array_filter($solicitacoes, fn($s) => $s['status'] === 'ABERTA');
$solicitacoesAnalise = array_filter($solicitacoes, fn($s) => $s['status'] === 'ANALISE');

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IESB - Controle de Acesso Acadêmico</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>IESB - Controle de Acesso Acadêmico</h1>
            <p>Sistema de Gestão de Alunos e Solicitações</p>
        </div>
    </header>

    <div class="container">
        <nav class="nav">
            <ul>
                <li><a href="index.php">Início</a></li>
                <li><a href="pages/aluno-cadastrar.php">Cadastrar Aluno</a></li>
                <li><a href="pages/aluno-listar.php">Listar Alunos</a></li>
                <li><a href="pages/solicitacao-abrir.php">Abrir Solicitação</a></li>
                <li><a href="pages/solicitacao-listar.php">Listar Solicitações</a></li>
                <li><a href="pages/acesso-consultar.php">Consultar Acesso</a></li>
            </ul>
        </nav>

        <?php $flash = getFlashMessage(); if ($flash): ?>
            <div class="alert alert-<?php echo $flash['tipo']; ?>">
                <?php echo sanitize($flash['mensagem']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Dashboard</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Total de Alunos</label>
                    <span style="font-size: 2rem; color: #2a5298;"><?php echo $totalAlunos; ?></span>
                </div>
                <div class="info-item">
                    <label>Total de Solicitações</label>
                    <span style="font-size: 2rem; color: #2a5298;"><?php echo $totalSolicitacoes; ?></span>
                </div>
                <div class="info-item">
                    <label>Solicitações Abertas</label>
                    <span style="font-size: 2rem; color: #dc3545;"><?php echo count($solicitacoesAbertas); ?></span>
                </div>
                <div class="info-item">
                    <label>Em Análise</label>
                    <span style="font-size: 2rem; color: #ffc107;"><?php echo count($solicitacoesAnalise); ?></span>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Status Acadêmico - Resumo</h2>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Nome</th>
                            <th>Curso</th>
                            <th>Status</th>
                            <th>Pendência</th>
                            <th>Acesso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $statusAcademico = $alunoService->obterStatusAcademico();
                        $limit = min(5, count($statusAcademico));
                        for ($i = 0; $i < $limit; $i++): 
                            $aluno = $statusAcademico[$i];
                        ?>
                            <tr>
                                <td><?php echo sanitize($aluno['Matrícula']); ?></td>
                                <td><?php echo sanitize($aluno['Nome']); ?></td>
                                <td><?php echo sanitize($aluno['Curso']); ?></td>
                                <td>
                                    <span class="badge <?php echo $aluno['Status Aluno'] === 'ATIVO' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $aluno['Status Aluno']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $aluno['Possui Pendência'] === 'SIM' ? 'badge-warning' : 'badge-secondary'; ?>">
                                        <?php echo $aluno['Possui Pendência']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $aluno['Acesso Liberado'] === 'SIM' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $aluno['Acesso Liberado'] === 'SIM' ? 'LIBERADO' : 'BLOQUEADO'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
            <p style="margin-top: 15px;">
                <a href="pages/aluno-listar.php" class="btn btn-secondary btn-sm">Ver todos</a>
            </p>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> IESB - Instituto de Educação Superior de Brasília</p>
    </footer>
</body>
</html>
