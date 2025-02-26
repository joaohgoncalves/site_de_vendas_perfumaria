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
    $sql = "DELETE FROM produtos WHERE id_produto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>alert('Produto deletado com sucesso!'); window.location='gerenciar_produtos.php';</script>";
    }
}

// Consulta os produtos cadastrados
$sql = "SELECT * FROM produtos";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Produtos</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
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
            width: 90%;
            max-width: 1200px;
            text-align: center;
            margin-top: 20px;
        }
        h2 {
            margin-bottom: 1rem;
            color: rgb(1, 9, 44);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background: rgb(1, 9, 44);
            color: white;
            font-size: 16px;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .btn {
            padding: 10px 15px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            transition: 0.3s;
        }
        .btn-edit {
            background: #007bff;
        }
        .btn-edit:hover {
            background: #0056b3;
        }
        .btn-delete {
            background: #dc3545;
        }
        .btn-delete:hover {
            background: #a71d2a;
        }
        .btn-add {
            background:rgb(36, 21, 170);
            padding: 12px 20px;
            display: inline-block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .btn-add:hover {
            background:rgb(79, 60, 255);
        }
    </style>
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
        <?php while ($row = $result->fetch_assoc()) { ?>
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
