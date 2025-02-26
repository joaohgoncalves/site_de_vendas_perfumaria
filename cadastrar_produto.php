<?php
require 'conexao.php';

// Verifica se o usuário é administrador
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_tipo"] !== "administrador") {
    header("Location: index.php");
    exit;
}

// Função para redimensionar imagem
function redimensionarImagem($caminhoOrigem, $caminhoDestino, $largura, $altura) {
    list($larguraOriginal, $alturaOriginal, $tipo) = getimagesize($caminhoOrigem);
    
    // Cria uma nova imagem com o tamanho definido
    $imagemRedimensionada = imagecreatetruecolor($largura, $altura);
    
    // Carrega a imagem original conforme o tipo
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            $imagemOriginal = imagecreatefromjpeg($caminhoOrigem);
            break;
        case IMAGETYPE_PNG:
            $imagemOriginal = imagecreatefrompng($caminhoOrigem);
            imagealphablending($imagemRedimensionada, false);
            imagesavealpha($imagemRedimensionada, true);
            break;
        case IMAGETYPE_GIF:
            $imagemOriginal = imagecreatefromgif($caminhoOrigem);
            break;
        default:
            return false; // Tipo de imagem não suportado
    }

    // Redimensiona a imagem
    imagecopyresampled($imagemRedimensionada, $imagemOriginal, 0, 0, 0, 0, $largura, $altura, $larguraOriginal, $alturaOriginal);

    // Salva a nova imagem conforme o tipo
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            imagejpeg($imagemRedimensionada, $caminhoDestino, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($imagemRedimensionada, $caminhoDestino);
            break;
        case IMAGETYPE_GIF:
            imagegif($imagemRedimensionada, $caminhoDestino);
            break;
    }

    imagedestroy($imagemOriginal);
    imagedestroy($imagemRedimensionada);
    return true;
}

// Se o formulário for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $descricao = trim($_POST["descricao"]);
    $preco = floatval($_POST["preco"]);
    $estoque = intval($_POST["estoque"]);
    $id_categoria = intval($_POST["id_categoria"]);

    // Verifica se a pasta uploads existe
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Upload e redimensionamento das imagens
    $imagens = [];
    if (!empty($_FILES["imagens"]["name"][0])) {
        foreach ($_FILES["imagens"]["tmp_name"] as $key => $imagemTemp) {
            $imagemNome = basename($_FILES["imagens"]["name"][$key]);
            $imagemDestino = $target_dir . $imagemNome;

            // Verifica o tipo da imagem
            $infoImagem = getimagesize($imagemTemp);
            if ($infoImagem === false) {
                echo "<script>alert('Um dos arquivos enviados não é uma imagem válida!');</script>";
                exit;
            }

            // Move o arquivo temporariamente
            if (move_uploaded_file($imagemTemp, $imagemDestino)) {
                // Redimensiona a imagem para 900x900
                if (!redimensionarImagem($imagemDestino, $imagemDestino, 900, 900)) {
                    echo "<script>alert('Erro ao redimensionar uma das imagens!');</script>";
                    exit;
                }
                $imagens[] = $imagemNome;
            } else {
                echo "<script>alert('Erro ao fazer upload de uma das imagens!');</script>";
                exit;
            }
        }
    }

    // Concatena as imagens para salvar no banco (separadas por ;)
    $imagensBanco = implode(";", $imagens);

    if (!empty($nome) && !empty($descricao) && $preco > 0 && $estoque >= 0 && $id_categoria > 0 && !empty($imagensBanco)) {
        $sql = "INSERT INTO produtos (nome, descricao, preco, estoque, id_categoria, imagem) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdiss", $nome, $descricao, $preco, $estoque, $id_categoria, $imagensBanco);

        if ($stmt->execute()) {
            echo "<script>alert('Produto cadastrado com sucesso!'); window.location='gerenciar_produtos.php';</script>";
        } else {
            echo "Erro ao cadastrar produto: " . $stmt->error;
        }
    } else {
        echo "<script>alert('Preencha todos os campos corretamente!');</script>";
    }
}

// Buscar categorias do banco
$sql_categorias = "SELECT * FROM categorias";
$result_categorias = $conn->query($sql_categorias);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Produto</title>
</head>
<style>
    /* Reset básico */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

/* Estilo geral */
body {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg,rgb(1, 11, 31), #2A5298);
    color: white;
    min-height: 100vh;
    padding: 20px;
}

/* Container principal */
.container {
    background: white;
    color: #333;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(255, 255, 255, 0.25);
    width: 90%;
    max-width: 500px;
    text-align: center;
    animation: fadeIn 0.5s ease-in-out;
}

/* Títulos */
h2 {
    margin-bottom: 15px;
    color:rgb(255, 255, 255);
    font-size: 1.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-bottom: 3px solid #2A5298;
    display: inline-block;
    padding-bottom: 5px;
}

/* Estilização do formulário */
form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

label {
    font-weight: 500;
    text-align: left;
    display: block;
    margin-bottom: 5px;
    color: white;
    font-size: 1rem;
}

input, textarea, select {
    width: 100%;
    padding: 12px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f9f9f9;
}

input:focus, textarea:focus, select:focus {
    border-color:rgb(255, 255, 255);
    outline: none;
    box-shadow: 0 0 8px rgba(30, 60, 114, 0.5);
}

textarea {
    resize: vertical;
    min-height: 120px;
}

/* Estilização do botão */
button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #1E3C72, #2A5298);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
}

button:hover {
    background: linear-gradient(135deg, #2A5298, #1E3C72);
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(30, 60, 114, 0.3);
}

/* Estilização do input de arquivos */
input[type="file"] {
    border: none;
    background: #f4f4f4;
    padding: 10px;
    cursor: pointer;
}

input[type="file"]:hover {
    background: #e0e0e0;
}

/* Responsividade */
@media (max-width: 480px) {
    .container {
        padding: 1.5rem;
    }
    
    h2 {
        font-size: 1.5rem;
    }

    input, textarea, select, button {
        font-size: 0.9rem;
    }
}

/* Animação de entrada */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}




</style>
<body>



<h2>Cadastrar Produto</h2>
<form action="cadastrar_produto.php" method="POST" enctype="multipart/form-data">
    <label>Nome:</label>
    <input type="text" name="nome" required><br>

    <label>Descrição:</label>
    <textarea name="descricao" required></textarea><br>

    <label>Preço:</label>
    <input type="number" step="0.01" name="preco" required><br>

    <label>Estoque:</label>
    <input type="number" name="estoque" required><br>

    <label>Categoria:</label>
    <select name="id_categoria" required>
        <option value="">Selecione uma categoria</option>
        <?php while ($categoria = $result_categorias->fetch_assoc()) { ?>
            <option value="<?= $categoria['id_categoria'] ?>"><?= $categoria['nome'] ?></option>
        <?php } ?>
    </select><br>

    <label>Imagens:</label>
    <input type="file" name="imagens[]" accept="image/*" multiple required><br>

    <button type="submit">Cadastrar</button>
</form>

</body>
</html>
