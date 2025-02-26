<?php
// Inicia a sessão apenas se ainda não estiver ativa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$usuario = "root"; // Usuário do banco
$senha = ""; // Senha do banco (vazia no XAMPP)
$banco = "teste"; // Nome do banco de dados

$conn = new mysqli($host, $usuario, $senha, $banco);

// Verifica se a conexão falhou
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>
