<?php
session_start();
require 'conexao.php'; // Arquivo de conexão com o banco

// Verifica se o usuário é um administrador logado
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_tipo"] !== "administrador") {
    header("Location: index.php");
    exit;
}

$erro = "";

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT); // Criptografa a senha

    // Verifica se o e-mail já existe no banco
    $sql_check = "SELECT id_usuario FROM usuarios WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $erro = "Erro: Este e-mail já está cadastrado!";
    } else {
        // Insere novo administrador no banco
        $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'administrador')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nome, $email, $senha);

        if ($stmt->execute()) {
            header("Location: painel_admin.php"); // Redireciona para o painel do administrador
            exit;
        } else {
            $erro = "Erro ao cadastrar administrador!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta Administrador</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, rgb(1, 9, 44), rgb(75, 105, 162));
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 350px;
            text-align: center;
        }
        .container h2 {
            margin-bottom: 1rem;
            color: rgb(1, 9, 44);
        }
        .input-group {
            margin-bottom: 1rem;
            text-align: left;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid rgb(75, 105, 162);
            border-radius: 5px;
            font-size: 1rem;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background: rgb(1, 9, 44);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn:hover {
            background: rgb(75, 105, 162);
        }
        .erro {
            color: red;
            font-weight: bold;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Criar Conta de Administrador</h2>
    <form action="criar_conta_adm.php" method="POST">
        <div class="input-group">
            <input type="text" name="nome" placeholder="Nome" required>
        </div>
        <div class="input-group">
            <input type="email" name="email" placeholder="E-mail" required>
        </div>
        <div class="input-group">
            <input type="password" name="senha" placeholder="Senha" required>
        </div>
        <button type="submit" class="btn">Criar Conta</button>
    </form>
    <?php if ($erro) echo "<p class='erro'>\$erro</p>"; ?>
</div>

</body>
</html>
