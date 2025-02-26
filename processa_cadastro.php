<?php
require 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $telefone = trim($_POST["telefone"]);
    $senha = password_hash(trim($_POST["senha"]), PASSWORD_BCRYPT);
    $tipo = "cliente"; // Todos os novos usuários são clientes por padrão.

    // Verifica se o e-mail já existe
    $sql = "SELECT id_usuario FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Erro: Este e-mail já está cadastrado.";
    } else {
        // Insere o usuário no banco
        $sql = "INSERT INTO usuarios (nome, email, telefone, senha, tipo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $nome, $email, $telefone, $senha, $tipo);

        if ($stmt->execute()) {
            echo "Cadastro realizado com sucesso!";
            header("Location: login.php"); // Redireciona para login após cadastro
            exit();
        } else {
            echo "Erro ao cadastrar usuário: " . $stmt->error;
        }
    }

    $stmt->close();
}

$conn->close();
?>
