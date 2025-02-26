<?php
require 'conexao.php';
echo "Conexão bem-sucedida!"; // Para testar

$nome = "Usuário Teste";
$email = "teste@email.com";
$senha = password_hash("123456", PASSWORD_BCRYPT);
$tipo = "cliente";

$sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $nome, $email, $senha, $tipo);

if ($stmt->execute()) {
    echo "Usuário cadastrado com sucesso!";
} else {
    echo "Erro ao cadastrar usuário: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

