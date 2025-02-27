<?php
require 'conexao.php'; // Incluindo a conexão com o banco de dados

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $senha_atual = $_POST['senha_atual'];
    $novo_email = $_POST['novo_email'];

    try {
        // Verificar se a senha atual está correta
        $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id_usuario = :usuario_id");
        $stmt->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario && password_verify($senha_atual, $usuario['senha'])) {
            // Atualizar o email
            $stmt_update = $pdo->prepare("UPDATE usuarios SET email = :novo_email WHERE id_usuario = :usuario_id");
            $stmt_update->bindParam(':novo_email', $novo_email);
            $stmt_update->bindParam(':usuario_id', $_SESSION['usuario_id']);
            $stmt_update->execute();

            $mensagem = "Email atualizado com sucesso!";
        } else {
            $erro = "Senha atual incorreta!";
        }
    } catch (PDOException $e) {
        $erro = 'Erro ao conectar com o banco de dados: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Email</title>
    <link rel="stylesheet" href="styles.css">
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
            max-width: 400px;
            text-align: center;
        }
        header nav ul {
            list-style: none;
            padding: 0;
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        header nav ul li {
            margin-right: 20px;
        }
        header nav ul li a {
            text-decoration: none;
            color: rgb(1, 9, 44);
        }
        header nav ul li a:hover {
            color: rgb(75, 105, 162);
        }
        h2 {
            margin-bottom: 20px;
            color: rgb(1, 9, 44);
        }
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: rgb(1, 9, 44);
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
        .erro, .sucesso {
            font-weight: bold;
            margin-bottom: 20px;
        }
        .erro {
            color: red;
        }
        .sucesso {
            color: green;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <ul>
                    <li><a href="perfil.php">Voltar ao Perfil</a></li>
                    <li><a href="logout.php" class="btn">Sair</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <h2>Alterar Email</h2>

            <?php if (isset($mensagem)) { ?>
                <p class="sucesso"><?php echo $mensagem; ?></p>
            <?php } ?>

            <?php if (isset($erro)) { ?>
                <p class="erro"><?php echo $erro; ?></p>
            <?php } ?>

            <form action="alterar_email.php" method="POST">
                <div class="input-group">
                    <label for="senha_atual">Senha Atual</label>
                    <input type="password" id="senha_atual" name="senha_atual" required>
                </div>
                <div class="input-group">
                    <label for="novo_email">Novo Email</label>
                    <input type="email" id="novo_email" name="novo_email" required>
                </div>
                <button type="submit" class="btn">Alterar Email</button>
            </form>
        </main>
    </div>
</body>
</html>
