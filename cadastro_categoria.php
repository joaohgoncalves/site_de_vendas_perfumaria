<?php
require 'conexao.php';

// Verifica se o usuário é administrador
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_tipo"] !== "administrador") {
    header("Location: index.php");
    exit;
}

// Se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_categoria = trim($_POST["nome_categoria"]);

    if (!empty($nome_categoria)) {
        $sql = "INSERT INTO categorias (nome) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nome_categoria);

        if ($stmt->execute()) {
            echo "<script>alert('Categoria cadastrada com sucesso!'); window.location='cadastrar_categoria.php';</script>";
        } else {
            echo "Erro ao cadastrar categoria: " . $stmt->error;
        }
    } else {
        echo "<script>alert('Preencha o nome da categoria!');</script>";
    }
}

// Buscar categorias existentes
$sql_categorias = "SELECT * FROM categorias ORDER BY nome";
$result_categorias = $conn->query($sql_categorias);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Categoria</title>
</head>
<body>

<?php include 'navbar.php'; ?>

<h2>Cadastrar Nova Categoria</h2>
<form action="cadastrar_categoria.php" method="POST">
    <label>Nome da Categoria:</label>
    <input type="text" name="nome_categoria" required><br>
    <button type="submit">Cadastrar</button>
</form>

<h3>Categorias Cadastradas</h3>
<ul>
    <?php while ($categoria = $result_categorias->fetch_assoc()) { ?>
        <li><?= $categoria['nome'] ?></li>
    <?php } ?>
</ul>

</body>
</html>
