<?php
// Inicia a sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclui o arquivo de conexão com o banco de dados
require 'conexao.php'; // Verifique se $pdo está sendo definido corretamente aqui

$etapa = 1; // Etapa inicial
$erro = "";

// Etapa 1: O usuário insere o e-mail
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["email"])) {
    $email = trim($_POST["email"]);

    // Verifica se o e-mail existe no banco de dados
    $sql = "SELECT id_usuario FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql); // Usando $pdo aqui
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $_SESSION["email_recuperacao"] = $email;
        $etapa = 2; // Avança para a etapa de redefinição de senha
    } else {
        $erro = "E-mail não encontrado!";
    }
}

// Etapa 2: O usuário define a nova senha
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nova_senha"])) {
    if (isset($_SESSION["email_recuperacao"])) {
        $nova_senha = password_hash($_POST["nova_senha"], PASSWORD_DEFAULT);
        $email = $_SESSION["email_recuperacao"];

        // Atualiza a senha no banco de dados
        $sql = "UPDATE usuarios SET senha = :senha WHERE email = :email";
        $stmt = $pdo->prepare($sql); // Usando $pdo aqui
        $stmt->bindParam(':senha', $nova_senha);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        unset($_SESSION["email_recuperacao"]); // Limpa a sessão

        // Redireciona para login.php
        header("Location: login.php");
        exit;
    } else {
        $erro = "Erro ao redefinir a senha!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha</title>
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
    <?php if ($etapa == 1): ?>
        <h2>Recuperar Senha</h2>
        <form method="POST">
            <div class="input-group">
                <input type="email" name="email" placeholder="Digite seu e-mail" required>
            </div>
            <button type="submit" class="btn">Avançar</button>
        </form>
        <?php if ($erro) echo "<p class='erro'>$erro</p>"; ?>
    <?php endif; ?>

    <?php if ($etapa == 2): ?>
        <h2>Redefinir Senha</h2>
        <form method="POST">
            <div class="input-group">
                <input type="password" name="nova_senha" placeholder="Digite a nova senha" required>
            </div>
            <button type="submit" class="btn">Redefinir Senha</button>
        </form>
        <?php if ($erro) echo "<p class='erro'>$erro</p>"; ?>
    <?php endif; ?>
</div>

</body>
</html>
