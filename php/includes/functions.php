<?php
/**
 * IESB - Sistema de Controle de Acesso Acadêmico
 * Funções Utilitárias
 */

if (!defined('IESB_ACCESS')) {
    define('IESB_ACCESS', true);
}

require_once 'config.php';

// =====================================================
// FUNÇÕES DE VALIDAÇÃO
// =====================================================

/**
 * Valida formato de CPF
 * 
 * @param string $cpf CPF a ser validado
 * @return bool True se válido
 */
function validarCPF(string $cpf): bool {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    
    // Valida dígitos verificadores
    for ($t = 9; $t < 11; $t++) {
        $d = 0;
        for ($c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    
    return true;
}

/**
 * Formata CPF para exibição
 * 
 * @param string $cpf CPF sem formatação
 * @return string CPF formatado
 */
function formatarCPF(string $cpf): string {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    return substr($cpf, 0, 3) . '.' . 
           substr($cpf, 3, 3) . '.' . 
           substr($cpf, 6, 3) . '-' . 
           substr($cpf, 9, 2);
}

/**
 * Sanitiza string para prevenção de XSS
 * 
 * @param string $string String a ser sanitizada
 * @return string String limpa
 */
function sanitize(string $string): string {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

// =====================================================
// FUNÇÕES DE MENSAGEM
// =====================================================

/**
 * Define mensagem flash para exibição
 * 
 * @param string $tipo Tipo: success, error, warning, info
 * @param string $mensagem Mensagem a ser exibida
 */
function setFlashMessage(string $tipo, string $mensagem): void {
    $_SESSION['flash'] = [
        'tipo' => $tipo,
        'mensagem' => $mensagem
    ];
}

/**
 * Obtém e limpa mensagem flash
 * 
 * @return array|null Mensagem flash ou null
 */
function getFlashMessage(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// =====================================================
// FUNÇÕES DE REDIRECIONAMENTO
// =====================================================

/**
 * Redireciona para uma página
 * 
 * @param string $url URL de destino
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Obtém URL base da aplicação
 * 
 * @return string URL base
 */
function baseUrl(): string {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = dirname($_SERVER['SCRIPT_NAME']);
    return "$protocol://$host$script";
}
