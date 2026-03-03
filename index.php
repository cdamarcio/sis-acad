<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Configuração da Conexão
try {
    $host = 'localhost';
    $port = '5432';
    $db   = 'iesb_acesso_academico';
    $user = 'postgres';
    $pass = 'mamst1ns';

    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// 2. Redirecionamento ou inclusão do sistema
// Se o seu sistema principal estiver na pasta php, vamos incluí-lo:
if (file_exists('php/index.php')) {
    include_once 'php/index.php';
} else {
    echo "Conexão OK, mas o arquivo php/index.php não foi encontrado.";
}
?>