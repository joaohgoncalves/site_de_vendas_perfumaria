<?php
session_start();
require 'conexao.php'; // Conexão com o banco

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Usuário não encontrado!";
    exit;
}

$id_usuario = intval($_GET['id']);

// Buscar dados do usuário
$sql = "SELECT nome, email FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    echo "Usuário não encontrado!";
    exit;
}

// Atualizar dados do usuário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novo_nome = trim($_POST["nome"]);
    $novo_email = trim($_POST["email"]);

    $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $novo_nome, $novo_email, $id_usuario);
    $stmt->execute();

    header("Location: gerenciar_usuarios.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
        form { display: inline-block; text-align: left; background: #f8f8f8; padding: 20px; border-radius: 5px; }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        button { background: #28a745; color: white; padding: 10px; border: none; cursor: pointer; width: 100%; }
    </style>
</head>
<body>

<h2>Editar Usuário</h2>
<form method="POST">
    <label>Nome:</label>
    <input type="text" name="nome" value="<?php echo $usuario['nome']; ?>" required>
    
    <label>Email:</label>
    <input type="email" name="email" value="<?php echo $usuario['email']; ?>" required>
    
    <button type="submit">Salvar Alterações</button>
</form>

</body>
</html>
    