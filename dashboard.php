<?php
session_start();
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h2>Bem-vindo, <?php echo $_SESSION["nome"]; ?>!</h2>
    <p>Você está logado como <?php echo $_SESSION["tipo"]; ?>.</p>
    <a href="logout.php">Sair</a>
</body>
</html>
