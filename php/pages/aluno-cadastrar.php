<?php
/**
 * IESB - Sistema de Controle de Acesso Acadêmico
 * Cadastro de Aluno
 */

define('IESB_ACCESS', true);
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/AlunoService.php';

session_start();

$alunoService = new AlunoService();
$resultado = null;

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'matricula' => $_POST['matricula'] ?? '',
        'nome' => $_POST['nome'] ?? '',
        'cpf' => $_POST['cpf'] ?? '',
        'curso' => $_POST['curso'] ?? '',
        'campus' => $_POST['campus'] ?? ''
    ];
    
    $resultado = $alunoService->criar($dados);
    
    if ($resultado['sucesso']) {
        setFlashMessage('success', $resultado['mensagem']);
        redirect('aluno-listar.php');
    }
}

// Lista de cursos e campi para o formulário
$cursos = [
    'Engenharia de Software',
    'Administração',
    'Direito',
    'Psicologia',
    'Engenharia Civil',
    'Medicina',
    'Enfermagem',
    'Arquitetura',
    'Design',
    'Marketing'
];

$campi = [
    'Brasília',
    'Taguatinga',
    'Águas Claras'
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Aluno - IESB</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>IESB - Controle de Acesso Acadêmico</h1>
            <p>Cadastro de Novo Aluno</p>
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
            <h2>Cadastrar Novo Aluno</h2>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="matricula">Matrícula *</label>
                    <input type="text" id="matricula" name="matricula" required 
                           maxlength="20" placeholder="Ex: 20250001"
                           value="<?php echo $_POST['matricula'] ?? ''; ?>">
                    <small>Número único de matrícula do aluno</small>
                </div>

                <div class="form-group">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" required 
                           maxlength="100" placeholder="Nome completo do aluno"
                           value="<?php echo $_POST['nome'] ?? ''; ?>">
                </div>

                <div class="form-group">
                    <label for="cpf">CPF *</label>
                    <input type="text" id="cpf" name="cpf" required 
                           maxlength="14" placeholder="000.000.000-00"
                           value="<?php echo $_POST['cpf'] ?? ''; ?>">
                    <small>Formato: 000.000.000-00</small>
                </div>

                <div class="form-group">
                    <label for="curso">Curso *</label>
                    <select id="curso" name="curso" required>
                        <option value="">Selecione o curso</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?php echo $curso; ?>" 
                                <?php echo (($_POST['curso'] ?? '') === $curso) ? 'selected' : ''; ?>>
                                <?php echo $curso; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="campus">Campus *</label>
                    <select id="campus" name="campus" required>
                        <option value="">Selecione o campus</option>
                        <?php foreach ($campi as $campus): ?>
                            <option value="<?php echo $campus; ?>"
                                <?php echo (($_POST['campus'] ?? '') === $campus) ? 'selected' : ''; ?>>
                                <?php echo $campus; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Cadastrar Aluno</button>
                    <a href="aluno-listar.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> IESB - Instituto de Educação Superior de Brasília</p>
    </footer>

    <script>
        // Máscara para CPF
        document.getElementById('cpf').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                e.target.value = value;
            }
        });
    </script>
</body>
</html>
