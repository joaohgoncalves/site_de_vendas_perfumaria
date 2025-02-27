<?php
session_start();
require 'conexao.php'; // O arquivo onde a conexão PDO é configurada

$erro = ""; // Variável para armazenar mensagens de erro

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);

    if (!empty($email) && !empty($senha)) {
        // Usando PDO para preparar a consulta
        $sql = "SELECT id_usuario, nome, senha, tipo FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Verificando se o usuário existe
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($senha, $user["senha"])) {
                // Armazenando informações na sessão
                $_SESSION["usuario_id"] = $user["id_usuario"];
                $_SESSION["usuario_nome"] = $user["nome"];
                $_SESSION["usuario_tipo"] = $user["tipo"];

                // Redirecionando com base no tipo de usuário
                if ($user["tipo"] == "administrador") {
                    header("Location: painel_admin.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $erro = "⚠️ Senha incorreta!";
            }
        } else {
            $erro = "⚠️ Usuário não encontrado!";
        }
    } else {
        $erro = "⚠️ Preencha todos os campos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
        .forgot-password {
            margin-top: 10px;
            font-size: 0.9rem;
        }
        .forgot-password a {
            color: rgb(54, 79, 190);
            text-decoration: none;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Login</h2>
    <?php if (!empty($erro)) echo "<p class='erro'>$erro</p>"; ?>
    <form action="login.php" method="POST">
        <div class="input-group">
            <input type="email" name="email" placeholder="Digite seu e-mail" required>
        </div>
        <div class="input-group">
            <input type="password" name="senha" placeholder="Digite sua senha" required>
        </div>
        <button type="submit" class="btn">Entrar</button>
    </form>
    <p class="forgot-password"><a href="recuperar_senha.php">Esqueci minha senha</a></p>
</div>

</body>
</html>
