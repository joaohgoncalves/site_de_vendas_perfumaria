<?php
require 'conexao.php';

// Verifica se o usuário é administrador
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_tipo"] !== "administrador") {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: gerenciar_produtos.php");
    exit;
}

$id = $_GET['id'];
$sql = "SELECT * FROM produtos WHERE id_produto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$produto = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $descricao = $_POST["descricao"];
    $preco = $_POST["preco"];
    $estoque = $_POST["estoque"];

    // Processamento da foto
    $foto_atual = $produto['foto']; // Foto atual no banco de dados
    $novo_foto = $_FILES['foto']['name'];
    $foto_temp = $_FILES['foto']['tmp_name'];
    $diretorio = 'uploads/'; // Diretório onde as fotos serão armazenadas

    if ($novo_foto) {
        $foto_nome = uniqid() . "_" . basename($novo_foto);
        $foto_destino = $diretorio . $foto_nome;
        
        if (move_uploaded_file($foto_temp, $foto_destino)) {
            // Apagar a foto antiga do servidor (se existir)
            if ($foto_atual && file_exists($diretorio . $foto_atual)) {
                unlink($diretorio . $foto_atual);
            }
            // Atualizar o nome da foto no banco de dados
            $foto_atual = $foto_nome;
        } else {
            echo "Erro ao fazer upload da foto.";
            exit;
        }
    }

    // Atualiza o produto no banco de dados
    $sql = "UPDATE produtos SET nome=?, descricao=?, preco=?, estoque=?, foto=? WHERE id_produto=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdiss", $nome, $descricao, $preco, $estoque, $foto_atual, $id);

    if ($stmt->execute()) {
        // Sem mensagem de sucesso ou redirecionamento
        // Você pode, por exemplo, redirecionar automaticamente sem mostrar o alerta
        header("Location: gerenciar_produtos.php");
        exit;
    } else {
        echo "Erro ao atualizar: " . $stmt->error;
    }
    
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Produto</title>
    <style>
        /* Reset básico */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        /* Corpo da página */
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1E3C72, #2A5298);
            color: white;
            min-height: 100vh;
            padding: 20px;
        }

        /* Container do formulário */
        form {
            background: white;
            color: black;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 500px;
            text-align: left;
        }

        /* Estilo dos rótulos */
        form label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }

        /* Estilo dos inputs e textarea */
        form input,
        form textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        /* Ajuste para textarea */
        form textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Botão de envio */
        form button {
            width: 100%;
            padding: 12px;
            background: #1E3C72;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 20px;
        }

        /* Efeito hover no botão */
        form button:hover {
            background: #2A5298;
        }

        /* Responsividade para telas menores */
        @media (max-width: 600px) {
            form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

<h2>Editar Produto</h2>
<form action="editar_produto.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
    <label>Nome:</label>
    <input type="text" name="nome" value="<?php echo $produto['nome']; ?>" required><br>

    <label>Descrição:</label>
    <textarea name="descricao" required><?php echo $produto['descricao']; ?></textarea><br>

    <label>Preço:</label>
    <input type="number" step="0.01" name="preco" value="<?php echo $produto['preco']; ?>" required><br>

    <label>Estoque:</label>
    <input type="number" name="estoque" value="<?php echo $produto['estoque']; ?>" required><br>

   


    <label>Alterar Foto:</label>
    <input type="file" name="foto" accept="image/*"><br>

    <button type="submit">Salvar Alterações</button>
</form>

</body>
</html>
