<?php
/**
 * IESB - Sistema de Controle de Acesso Acadêmico
 * Listagem de Alunos
 */

define('IESB_ACCESS', true);
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/AlunoService.php';

session_start();

$alunoService = new AlunoService();

// Busca todos os alunos
$alunos = $alunoService->listar();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Alunos - IESB</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>IESB - Controle de Acesso Acadêmico</h1>
            <p>Listagem de Alunos</p>
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
            <h2>Lista de Alunos</h2>
            
            <p style="margin-bottom: 20px;">
                <a href="aluno-cadastrar.php" class="btn btn-success">+ Novo Aluno</a>
            </p>

            <?php if (empty($alunos)): ?>
                <div class="alert alert-info">
                    Nenhum aluno cadastrado.
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Matrícula</th>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Curso</th>
                                <th>Campus</th>
                                <th>Status</th>
                                <th>Cadastro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alunos as $aluno): ?>
                                <tr>
                                    <td><?php echo $aluno['id']; ?></td>
                                    <td><?php echo sanitize($aluno['matricula']); ?></td>
                                    <td><?php echo sanitize($aluno['nome']); ?></td>
                                    <td><?php echo sanitize($aluno['cpf']); ?></td>
                                    <td><?php echo sanitize($aluno['curso']); ?></td>
                                    <td><?php echo sanitize($aluno['campus']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $aluno['status'] === 'ATIVO' ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $aluno['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($aluno['data_cadastro'])); ?></td>
                                    <td>
                                        <a href="acesso-consultar.php?id=<?php echo $aluno['id']; ?>" 
                                           class="btn btn-primary btn-sm" title="Consultar Acesso">
                                            Acesso
                                        </a>
                                        <a href="solicitacao-abrir.php?id_aluno=<?php echo $aluno['id']; ?>" 
                                           class="btn btn-success btn-sm" title="Abrir Solicitação">
                                            + Solicitação
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Status Acadêmico Completo</h2>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Nome</th>
                            <th>Curso</th>
                            <th>Campus</th>
                            <th>Status Aluno</th>
                            <th>Possui Pendência</th>
                            <th>Acesso Liberado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $statusAcademico = $alunoService->obterStatusAcademico();
                        foreach ($statusAcademico as $aluno): 
                        ?>
                            <tr>
                                <td><?php echo sanitize($aluno['Matrícula']); ?></td>
                                <td><?php echo sanitize($aluno['Nome']); ?></td>
                                <td><?php echo sanitize($aluno['Curso']); ?></td>
                                <td><?php echo sanitize($aluno['Campus']); ?></td>
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
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> IESB - Instituto de Educação Superior de Brasília</p>
    </footer>
</body>
</html>
