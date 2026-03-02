<?php
/**
 * IESB - Sistema de Controle de Acesso Acadêmico
 * Arquivo de Configuração do Banco de Dados
 * 
 * @author Analista de Sistemas IESB
 * @version 1.0
 */

// Previne acesso direto ao arquivo
if (!defined('IESB_ACCESS')) {
    define('IESB_ACCESS', true);
}

// =====================================================
// CONFIGURAÇÕES DO BANCO DE DADOS POSTGRESQL
// =====================================================

// Dados de conexão (ajustar conforme ambiente)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'iesb_acesso_academico');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: 'postgres');

// String de conexão DSN
$dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;

// =====================================================
// OPÇÕES DE CONEXÃO PDO
// =====================================================
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Lança exceções em erros
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Retorna arrays associativos
    PDO::ATTR_EMULATE_PREPARES => false,                // Usa prepared statements nativos
    PDO::ATTR_PERSISTENT => true                         // Conexões persistentes
];

// =====================================================
// FUNÇÃO DE CONEXÃO
// =====================================================

/**
 * Retorna uma conexão PDO com o banco de dados
 * 
 * @return PDO Conexão com o banco
 * @throws PDOException Se houver erro na conexão
 */
function getConnection(): PDO {
    global $dsn, $options;
    
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erro de conexão: " . $e->getMessage());
        throw new Exception("Não foi possível conectar ao banco de dados.");
    }
}

/**
 * Testa a conexão com o banco
 * 
 * @return bool True se conectou com sucesso
 */
function testConnection(): bool {
    try {
        $pdo = getConnection();
        $stmt = $pdo->query("SELECT version()");
        return $stmt !== false;
    } catch (Exception $e) {
        return false;
    }
}
