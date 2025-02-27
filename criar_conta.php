<?php
session_start();
require 'conexao.php'; // Arquivo de conexão com o banco

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);
    $telefone = trim($_POST["telefone"]);
    $cpf = trim($_POST["cpf"]);
    $data_nascimento = trim($_POST["data_nascimento"]);
    $endereco = trim($_POST["endereco"]);
    $tipo = "cliente"; // Apenas administradores podem cadastrar outros admins

    $data_minima = date('Y-m-d', strtotime('-3 days'));
    
    if (!empty($nome) && !empty($email) && !empty($senha) && !empty($telefone) && !empty($cpf) && !empty($data_nascimento) && !empty($endereco)) {
        if ($data_nascimento > $data_minima) {
            $erro = "⚠️ A data da de nascimento é inválida.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = "⚠️ E-mail inválido!";
        } elseif (!preg_match("/^[0-9]{11}$/", str_replace(['.', '-'], '', $cpf))) {
            $erro = "⚠️ CPF inválido!";
        } elseif (strlen($senha) < 6) {
            $erro = "⚠️ A senha deve ter pelo menos 6 caracteres!";
        } else {
            // Verifica se o e-mail já existe
            $sql = "SELECT id_usuario FROM usuarios WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $erro = "⚠️ Este e-mail já está cadastrado!";
            } else {
                // Hash da senha
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                // Inserção no banco
                $sql = "INSERT INTO usuarios (nome, email, senha, telefone, cpf, data_nascimento, endereco, tipo, data_criacao) VALUES (:nome, :email, :senha, :telefone, :cpf, :data_nascimento, :endereco, :tipo, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':senha', $senha_hash, PDO::PARAM_STR);
                $stmt->bindParam(':telefone', $telefone, PDO::PARAM_STR);
                $stmt->bindParam(':cpf', $cpf, PDO::PARAM_STR);
                $stmt->bindParam(':data_nascimento', $data_nascimento, PDO::PARAM_STR);
                $stmt->bindParam(':endereco', $endereco, PDO::PARAM_STR);
                $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    header("Location: login.php?success=1");
                    exit;
                } else {
                    $erro = "⚠️ Erro ao criar conta. Tente novamente!";
                }
            }
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
    <title>Cadastro</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #001F3F, #0074D9);
            font-family: Arial, sans-serif;
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
        .container h2 {
            margin-bottom: 1rem;
            color: #001F3F;
        }
        .input-group {
            margin-bottom: 1rem;
            text-align: left;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #0074D9;
            border-radius: 5px;
            font-size: 1rem;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background: #001F3F;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn:hover {
            background: #0074D9;
        }
        .erro {
            color: red;
            font-weight: bold;
            margin-bottom: 1rem;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let minDate = new Date();
            minDate.setDate(minDate.getDate() - 3);
            document.getElementById("data_nascimento").setAttribute("max", minDate.toISOString().split('T')[0]);
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>Cadastro de Usuário</h2>
        <?php if (!empty($erro)) echo "<p class='erro'>$erro</p>"; ?>
        <form action="criar_conta.php" method="POST">
            <div class="input-group"><input type="text" name="nome" placeholder="Nome completo" required></div>
            <div class="input-group"><input type="email" name="email" placeholder="E-mail" required></div>
            <div class="input-group"><input type="password" name="senha" placeholder="Senha" required></div>
            <div class="input-group"><input type="text" name="telefone" placeholder="Telefone" required></div>
            <div class="input-group"><input type="text" name="cpf" placeholder="CPF" required></div>
            <div class="input-group"><input type="date" id="data_nascimento" name="data_nascimento" required></div>
            <div class="input-group"><input type="text" name="endereco" placeholder="Endereço" required></div>
            <button type="submit" class="btn">Cadastrar</button>
        </form>
    </div>
</body>
</html>