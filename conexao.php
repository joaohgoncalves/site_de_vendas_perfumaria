<?php
$servername = "localhost"; // Altere se necessário
$username = "root"; // Usuário do MySQL
$password = ""; // Senha do MySQL (caso tenha)
$database = "teste"; // Nome do banco de dados

// Criando conexão
$conn = new mysqli($servername, $username, $password, $database);

// Verificando conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
