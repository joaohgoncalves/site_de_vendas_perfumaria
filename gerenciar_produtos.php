<?php
require 'conexao.php';

// Verifica se o usuário é administrador
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_tipo"] !== "administrador") {
    header("Location: index.php");
    exit;
}

// Se um produto for deletado
if (isset($_GET['deletar'])) {
    $id = $_GET['deletar'];
    $sql = "DELETE FROM produtos WHERE id_produto = :id_produto";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_produto', $id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        echo "<script>alert('Produto deletado com sucesso!'); window.location='gerenciar_produtos.php';</script>";
    }
}

// Consulta os produtos cadastrados
$sql = "SELECT * FROM produtos";
$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Produtos</title>
    <link rel="stylesheet" href="gerenciar_produtos.css">
</head>
<body>

<div class="container">
    <h2>Gerenciar Produtos</h2>
    <a href="cadastrar_produto.php" class="btn btn-add">Adicionar Produto</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Preço</th>
            <th>Estoque</th>
            <th>Ações</th>
        </tr>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
        <tr>
            <td><?php echo $row['id_produto']; ?></td>
            <td><?php echo $row['nome']; ?></td>
            <td>R$ <?php echo number_format($row['preco'], 2, ',', '.'); ?></td>
            <td><?php echo $row['estoque']; ?></td>
            <td>
                <a href="editar_produto.php?id=<?php echo $row['id_produto']; ?>" class="btn btn-edit">Editar</a>
                <a href="gerenciar_produtos.php?deletar=<?php echo $row['id_produto']; ?>" onclick="return confirm('Tem certeza?')" class="btn btn-delete">Excluir</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
