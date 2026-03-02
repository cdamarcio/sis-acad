<?php
/**
 * IESB - Sistema de Controle de Acesso Acadêmico
 * Abertura de Solicitação
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
$alunoSelecionado = null;

// Se veio com ID do aluno pré-selecionado
$idAlunoPre = $_GET['id_aluno'] ?? null;
if ($idAlunoPre) {
    $alunoSelecionado = $alunoService->buscarPorId((int)$idAlunoPre);
}

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'id_aluno' => $_POST['id_aluno'] ?? '',
        'tipo' => $_POST['tipo'] ?? '',
        'descricao' => $_POST['descricao'] ?? ''
    ];
    
    $resultado = $solicitacaoService->criar($dados);
    
    if ($resultado['sucesso']) {
        setFlashMessage('success', $resultado['mensagem']);
        redirect('solicitacao-listar.php');
    }
}

// Lista de alunos ativos para o select
$alunos = array_filter($alunoService->listar(), fn($a) => $a['status'] === 'ATIVO');
$tipos = $solicitacaoService->obterTipos();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrir Solicitação - IESB</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>IESB - Controle de Acesso Acadêmico</h1>
            <p>Abertura de Solicitação Acadêmica</p>
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

        <?php if ($resultado && !$resultado['sucesso']): ?>
            <div class="alert alert-danger">
                <?php echo sanitize($resultado['mensagem']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Abrir Nova Solicitação</h2>
            
            <?php if (empty($alunos)): ?>
                <div class="alert alert-warning">
                    Não há alunos ativos disponíveis para abrir solicitações.
                    <a href="aluno-cadastrar.php">Cadastre um aluno primeiro</a>.
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="id_aluno">Aluno *</label>
                        <select id="id_aluno" name="id_aluno" required>
                            <option value="">Selecione o aluno</option>
                            <?php foreach ($alunos as $aluno): ?>
                                <option value="<?php echo $aluno['id']; ?>" 
                                    <?php echo (($alunoSelecionado['id'] ?? '') == $aluno['id']) ? 'selected' : ''; ?>>
                                    <?php echo sanitize($aluno['matricula'] . ' - ' . $aluno['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($alunoSelecionado): ?>
                            <small>Aluno pré-selecionado: <?php echo sanitize($alunoSelecionado['nome']); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="tipo">Tipo de Solicitação *</label>
                        <select id="tipo" name="tipo" required>
                            <option value="">Selecione o tipo</option>
                            <?php foreach ($tipos as $key => $label): ?>
                                <option value="<?php echo $key; ?>" 
                                    <?php echo (($_POST['tipo'] ?? '') === $key) ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" rows="4" 
                                  placeholder="Descreva os detalhes da solicitação..."><?php echo $_POST['descricao'] ?? ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Abrir Solicitação</button>
                        <a href="solicitacao-listar.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Informações Importantes</h2>
            <div class="alert alert-info">
                <strong>Regras para abertura de solicitações:</strong>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li>Apenas alunos com status <strong>ATIVO</strong> podem abrir solicitações</li>
                    <li>Alunos <strong>INATIVOS</strong> estão bloqueados para novas solicitações</li>
                    <li>Todas as solicitações iniciam com status <strong>ABERTA</strong></li>
                    <li>O status pode ser alterado para <strong>ANÁLISE</strong> ou <strong>FINALIZADA</strong></li>
                </ul>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> IESB - Instituto de Educação Superior de Brasília</p>
    </footer>
</body>
</html>
