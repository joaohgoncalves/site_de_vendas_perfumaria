<?php
session_start();
require 'conexao.php';

// Verifica se o usuário é administrador
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_tipo"] !== "administrador") {
    header("Location: index.php");
    exit;
}

$erro = "";

// Adicionar nova categoria
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nome_categoria"])) {
    $nome_categoria = trim($_POST["nome_categoria"]);

    if (!empty($nome_categoria)) {
        $sql = "INSERT INTO categorias (nome) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $nome_categoria, PDO::PARAM_STR);
        $stmt->execute();
        header("Location: gerenciar_categorias.php");
        exit;
    } else {
        $erro = "O nome da categoria não pode estar vazio!";
    }
}

if (isset($_GET["excluir"])) {
    $id_categoria = intval($_GET["excluir"]);

    // Excluir produtos relacionados a essa categoria
    $sql_delete_produtos = "DELETE FROM produtos WHERE id_categoria = ?";
    $stmt_produtos = $pdo->prepare($sql_delete_produtos);
    $stmt_produtos->bindParam(1, $id_categoria, PDO::PARAM_INT);
    $stmt_produtos->execute();

    // Agora, excluir a categoria
    $sql = "DELETE FROM categorias WHERE id_categoria = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $id_categoria, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: gerenciar_categorias.php");
    exit;
}

// Buscar categorias existentes
$sql_categorias = "SELECT * FROM categorias ORDER BY nome";
$stmt_categorias = $pdo->query($sql_categorias);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Categorias</title>
   <link rel="stylesheet" href="gerenciar__categoria.css">
</head>
<body>

<div class="container">
    <h2>Gerenciar Categorias</h2>
    <form action="gerenciar_categorias.php" method="POST">
        <div class="input-group">
            <input type="text" name="nome_categoria" placeholder="Nome da Categoria" required>
        </div>
        <button type="submit" class="btn">Adicionar</button>
    </form>
    <?php if ($erro) echo "<p class='erro'>$erro</p>"; ?>
    
    <h3>Categorias Cadastradas</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Ação</th>
        </tr>
        <?php while ($categoria = $stmt_categorias->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td><?= $categoria['id_categoria'] ?></td>
                <td><?= $categoria['nome'] ?></td>
                <td><a href="gerenciar_categorias.php?excluir=<?= $categoria['id_categoria'] ?>" class="delete-btn">Excluir</a></td>
            </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
