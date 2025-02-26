<?php
session_start();
require 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION["id_usuario"])) {
    header("Location: login.php");
    exit();
}

// Obtém os dados do usuário logado
$id_usuario = $_SESSION["id_usuario"];
$sql = "SELECT nome, email, telefone FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($nome, $email, $telefone);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; text-align: center; }
        header { background: #333; color: white; padding: 15px; text-align: center; }
        nav a { color: white; text-decoration: none; padding: 10px; }
        nav a:hover { background: #555; border-radius: 5px; }
        .container { max-width: 400px; margin: auto; padding: 20px; background: #f4f4f4; border-radius: 8px; }
        button { background: #28a745; color: white; padding: 10px; border: none; cursor: pointer; margin-top: 10px; }
        button:hover { background: #218838; }
        input { width: 100%; padding: 10px; margin: 5px 0; }
    </style>
</head>
<body>

<header>
    <nav>
        <a href="index.php">Home</a>
        <a href="logout.php">Sair</a>
    </nav>
</header>

<div class="container">
    <h2>Meu Perfil</h2>
    <form action="atualizar_perfil.php" method="POST">
        <input type="text" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        <input type="text" name="telefone" value="<?php echo htmlspecialchars($telefone); ?>" required>
        <button type="submit">Salvar Alterações</button>
    </form>
</div>

</body>
</html>
