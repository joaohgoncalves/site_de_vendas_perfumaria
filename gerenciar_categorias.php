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
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nome_categoria);
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
        $stmt_produtos = $conn->prepare($sql_delete_produtos);
        $stmt_produtos->bind_param("i", $id_categoria);
        $stmt_produtos->execute();

        // Agora, excluir a categoria
        $sql = "DELETE FROM categorias WHERE id_categoria = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_categoria);
        $stmt->execute();

        header("Location: gerenciar_categorias.php");
        exit;
    }


    // Buscar categorias existentes
    $sql_categorias = "SELECT * FROM categorias ORDER BY nome";
    $result_categorias = $conn->query($sql_categorias);
    ?>

    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gerenciar Categorias</title>
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
            h2 {
                color: rgb(1, 9, 44);
                margin-bottom: 1rem;
            }
            .input-group {
                margin-bottom: 1rem;
                text-align: left;
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
            .erro {
                color: red;
                font-weight: bold;
                margin-bottom: 1rem;
            }
            table {
                width: 100%;
                margin-top: 1rem;
                border-collapse: collapse;
            }
            table, th, td {
                border: 1px solid rgb(75, 105, 162);
            }
            th, td {
                padding: 10px;
                text-align: left;
            }
            .delete-btn {
                color: red;
                text-decoration: none;
                font-weight: bold;
            }
        </style>
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
        <?php if ($erro) echo "<p class='erro'>\$erro</p>"; ?>
        
        <h3>Categorias Cadastradas</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Ação</th>
            </tr>
            <?php while ($categoria = $result_categorias->fetch_assoc()) { ?>
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
