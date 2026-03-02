<?php
// php/includes/config.php

$host = 'localhost';
$port = '5432';
$db   = 'iesb_acesso_academico';
$user = 'postgres';
$pass = 'mamst1ns'; // A senha que você usou no psql

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erro na conexão com o sistema IESB: " . $e->getMessage());
}