<?php
require 'conexao.php';

if (!isset($_SESSION["usuario_tipo"]) || $_SESSION["usuario_tipo"] !== "administrador") {
    die("Acesso negado!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = password_hash($_POST["senha"], PASSWORD_BCRYPT);
    $tipo = "administrador";

    $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nome, $email, $senha, $tipo);

    if ($stmt->execute()) {
        echo "Administrador cadastrado com sucesso!";
    } else {
        echo "Erro ao cadastrar administrador: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Criar Administrador</title>
</head>
<body>
<?php include 'navbar.php'; ?>

<h2>Cadastrar Administrador</h2>
<form action="cadastro_admin.php" method="POST">
    <label for="nome">Nome:</label>
    <input type="text" name="nome" required><br>

    <label for="email">E-mail:</label>
    <input type="email" name="email" required><br>

    <label for="senha">Senha:</label>
    <input type="password" name="senha" required><br>

    <button type="submit">Cadastrar</button>
</form>

</body>
</html>
