<?php
/**
 * IESB - Sistema de Controle de Acesso Acadêmico
 * Consulta de Acesso do Aluno
 */

define('IESB_ACCESS', true);
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/AlunoService.php';
require_once '../includes/SolicitacaoService.php';

session_start();

$alunoService = new AlunoService();
$solicitacaoService = new SolicitacaoService();

$resultado = null;
$alunoId = $_GET['id'] ?? ($_POST['id_aluno'] ?? null);

// Processa consulta
if ($alunoId) {
    $resultado = $alunoService->consultarAcesso((int)$alunoId);
    if ($resultado['sucesso']) {
        $resultado['solicitacoes'] = $solicitacaoService->listarPorAluno((int)$alunoId);
    }
}

// Lista de alunos para o select
$alunos = $alunoService->listar();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Acesso - IESB</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>IESB - Controle de Acesso Acadêmico</h1>
            <p>Consulta de Acesso do Aluno</p>
        </div>
    </header>

    <div class="container">
        <nav class="nav">
            <ul>
                <li><a href="../index.php">Início</a></li>
                <li><a href="aluno-cadastrar.php">Cadastrar Aluno</a></li>
                <li><a href="aluno-listar.php">Listar Alunos</a></li>
                <li><a href="solicitacao-abrir.php">Abrir Solicitação</a></li>
                <li><a href="solicitacao-listar.php">Listar Solicitações</a></li>
                <li><a href="acesso-consultar.php">Consultar Acesso</a></li>
            </ul>
        </nav>

        <div class="card">
            <h2>Consultar Acesso do Aluno</h2>
            
            <form method="GET" action="">
                <div class="form-group">
                    <label for="id">Selecione o Aluno</label>
                    <select id="id" name="id" required onchange="this.form.submit()">
                        <option value="">Selecione...</option>
                        <?php foreach ($alunos as $aluno): ?>
                            <option value="<?php echo $aluno['id']; ?>" 
                                <?php echo (($resultado['aluno']['id'] ?? '') == $aluno['id']) ? 'selected' : ''; ?>>
                                <?php echo sanitize($aluno['matricula'] . ' - ' . $aluno['nome'] . ' (' . $aluno['status'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <?php if ($resultado): ?>
            <?php if ($resultado['sucesso']): ?>
                <div class="card">
                    <h2>Resultado da Consulta</h2>
                    
                    <div class="status-card <?php echo $resultado['acesso_liberado'] ? 'liberado' : 'bloqueado'; ?>">
                        <h3><?php echo $resultado['acesso_liberado'] ? '✓ ACESSO LIBERADO' : '✗ ACESSO BLOQUEADO'; ?></h3>
                        <p><?php echo sanitize($resultado['mensagem']); ?></p>
                    </div>

                    <div class="info-grid">
                        <div class="info-item">
                            <label>Matrícula</label>
                            <span><?php echo sanitize($resultado['aluno']['matricula']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Nome</label>
                            <span><?php echo sanitize($resultado['aluno']['nome']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Curso</label>
                            <span><?php echo sanitize($resultado['aluno']['curso']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Campus</label>
                            <span><?php echo sanitize($resultado['aluno']['campus']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Status Acadêmico</label>
                            <span>
                                <span class="badge <?php echo $resultado['aluno']['status'] === 'ATIVO' ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo $resultado['aluno']['status']; ?>
                                </span>
                            </span>
                        </div>
                        <div class="info-item">
                            <label>Solicitações Pendentes</label>
                            <span>
                                <span class="badge <?php echo $resultado['pendencias'] > 0 ? 'badge-warning' : 'badge-secondary'; ?>">
                                    <?php echo $resultado['pendencias']; ?> pendência(s)
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($resultado['solicitacoes'])): ?>
                    <div class="card">
                        <h2>Histórico de Solicitações</h2>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tipo</th>
                                        <th>Descrição</th>
                                        <th>Status</th>
                                        <th>Data Abertura</th>
                                        <th>Data Fechamento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resultado['solicitacoes'] as $sol): 
                                        $tipos = $solicitacaoService->obterTipos();
                                        $status = $solicitacaoService->obterStatus();
                                    ?>
                                        <tr>
                                            <td><?php echo $sol['id']; ?></td>
                                            <td><?php echo $tipos[$sol['tipo']] ?? $sol['tipo']; ?></td>
                                            <td><?php echo sanitize(substr($sol['descricao'] ?? '', 0, 40)) . (strlen($sol['descricao'] ?? '') > 40 ? '...' : ''); ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php 
                                                    echo $sol['status'] === 'ABERTA' ? 'badge-warning' : 
                                                         ($sol['status'] === 'ANALISE' ? 'badge-info' : 'badge-success'); 
                                                    ?>">
                                                    <?php echo $status[$sol['status']] ?? $sol['status']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($sol['data_abertura'])); ?></td>
                                            <td><?php echo $sol['data_fechamento'] ? date('d/m/Y H:i', strtotime($sol['data_fechamento'])) : '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="alert alert-info">
                            Este aluno não possui solicitações registradas.
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="card">
                    <div class="alert alert-danger">
                        <?php echo sanitize($resultado['mensagem']); ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="card">
            <h2>Regras de Acesso</h2>
            <div class="alert alert-info">
                <strong>O acesso é liberado quando TODAS as condições são atendidas:</strong>
                <ol style="margin-top: 10px; margin-left: 20px;">
                    <li>Aluno está com status <strong>ATIVO</strong></li>
                    <li>Aluno <strong>NÃO possui solicitações pendentes</strong> (ABERTA ou EM ANÁLISE)</li>
                </ol>
                <p style="margin-top: 15px;">
                    <strong>Integração futura:</strong> Esta consulta poderá ser exposta via API REST para integração 
                    com sistemas de catraca, permitindo liberação automática de acesso.
                </p>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> IESB - Instituto de Educação Superior de Brasília</p>
    </footer>
</body>
</html>
