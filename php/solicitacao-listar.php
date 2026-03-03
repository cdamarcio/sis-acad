<?php
/**
 * IESB - Sistema de Controle de Acesso Acadêmico
 * Listagem de Solicitações
 */

define('IESB_ACCESS', true);
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/SolicitacaoService.php';

session_start();

$solicitacaoService = new SolicitacaoService();

// Processa alteração de status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'alterar_status') {
    $id = (int)($_POST['id_solicitacao'] ?? 0);
    $novoStatus = $_POST['novo_status'] ?? '';
    
    if ($id && $novoStatus) {
        $resultado = $solicitacaoService->alterarStatus($id, $novoStatus);
        
        if ($resultado['sucesso']) {
            setFlashMessage('success', $resultado['mensagem']);
        } else {
            setFlashMessage('danger', $resultado['mensagem']);
        }
        redirect('solicitacao-listar.php');
    }
}

// Busca todas as solicitações
$solicitacoes = $solicitacaoService->listar();
$status = $solicitacaoService->obterStatus();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Solicitações - IESB</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>IESB - Controle de Acesso Acadêmico</h1>
            <p>Gerenciamento de Solicitações</p>
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

        <?php $flash = getFlashMessage(); if ($flash): ?>
            <div class="alert alert-<?php echo $flash['tipo']; ?>">
                <?php echo sanitize($flash['mensagem']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Lista de Solicitações</h2>
            
            <p style="margin-bottom: 20px;">
                <a href="solicitacao-abrir.php" class="btn btn-success">+ Nova Solicitação</a>
            </p>

            <?php if (empty($solicitacoes)): ?>
                <div class="alert alert-info">
                    Nenhuma solicitação cadastrada.
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Aluno</th>
                                <th>Matrícula</th>
                                <th>Tipo</th>
                                <th>Descrição</th>
                                <th>Status</th>
                                <th>Abertura</th>
                                <th>Dias em Aberto</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitacoes as $solicitacao): 
                                $diasAberto = $solicitacaoService->calcularDiasEmAberto($solicitacao['id']);
                                $podeAlterar = $solicitacao['status'] !== 'FINALIZADA';
                            ?>
                                <tr>
                                    <td><?php echo $solicitacao['id']; ?></td>
                                    <td><?php echo sanitize($solicitacao['aluno_nome']); ?></td>
                                    <td><?php echo sanitize($solicitacao['aluno_matricula']); ?></td>
                                    <td><?php echo $status[$solicitacao['tipo']] ?? $solicitacao['tipo']; ?></td>
                                    <td><?php echo sanitize(substr($solicitacao['descricao'] ?? '', 0, 50)) . (strlen($solicitacao['descricao'] ?? '') > 50 ? '...' : ''); ?></td>
                                    <td>
                                        <span class="badge 
                                            <?php 
                                            echo $solicitacao['status'] === 'ABERTA' ? 'badge-warning' : 
                                                 ($solicitacao['status'] === 'ANALISE' ? 'badge-info' : 'badge-success'); 
                                            ?>">
                                            <?php echo $status[$solicitacao['status']] ?? $solicitacao['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($solicitacao['data_abertura'])); ?></td>
                                    <td>
                                        <?php if ($solicitacao['status'] !== 'FINALIZADA'): ?>
                                            <span class="badge <?php echo $diasAberto > 7 ? 'badge-danger' : 'badge-secondary'; ?>">
                                                <?php echo $diasAberto; ?> dia(s)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($podeAlterar): ?>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="acao" value="alterar_status">
                                                <input type="hidden" name="id_solicitacao" value="<?php echo $solicitacao['id']; ?>">
                                                <select name="novo_status" onchange="this.form.submit()" 
                                                        style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
                                                    <option value="">Alterar status...</option>
                                                    <?php foreach ($status as $key => $label): ?>
                                                        <?php if ($key !== $solicitacao['status']): ?>
                                                            <option value="<?php echo $key; ?>">
                                                                <?php echo $label; ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Finalizada</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Legenda de Status</h2>
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div>
                    <span class="badge badge-warning">ABERTA</span>
                    <span style="margin-left: 5px;">Solicitação recém-criada</span>
                </div>
                <div>
                    <span class="badge badge-info">ANÁLISE</span>
                    <span style="margin-left: 5px;">Em análise pela coordenação</span>
                </div>
                <div>
                    <span class="badge badge-success">FINALIZADA</span>
                    <span style="margin-left: 5px;">Processo concluído</span>
                </div>
            </div>
            <div class="alert alert-warning" style="margin-top: 20px;">
                <strong>Atenção:</strong> Solicitações com mais de 7 dias em aberto são destacadas em vermelho.
                Solicitações finalizadas não podem ser alteradas.
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> IESB - Instituto de Educação Superior de Brasília</p>
    </footer>
</body>
</html>
