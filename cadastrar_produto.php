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
        // Usando PDO para inserir os dados no banco
        try {
            $sql = "INSERT INTO produtos (nome, descricao, preco, estoque, id_categoria, imagem) VALUES (:nome, :descricao, :preco, :estoque, :id_categoria, :imagem)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':preco', $preco);
            $stmt->bindParam(':estoque', $estoque);
            $stmt->bindParam(':id_categoria', $id_categoria);
            $stmt->bindParam(':imagem', $imagensBanco);

            if ($stmt->execute()) {
                echo "<script>alert('Produto cadastrado com sucesso!'); window.location='gerenciar_produtos.php';</script>";
            } else {
                echo "Erro ao cadastrar produto!";
            }
        } catch (PDOException $e) {
            echo "Erro ao cadastrar produto: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Preencha todos os campos corretamente!');</script>";
    }
}

// Buscar categorias do banco
try {
    $sql_categorias = "SELECT * FROM categorias";
    $result_categorias = $pdo->query($sql_categorias);
} catch (PDOException $e) {
    echo "Erro ao buscar categorias: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Produto</title>
    <script src="cadastrar_produto.js"></script>
</head>
<link rel="stylesheet" href="cadastrar_produto.css">
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
        <?php while ($categoria = $result_categorias->fetch(PDO::FETCH_ASSOC)) { ?>
            <option value="<?= $categoria['id_categoria'] ?>"><?= $categoria['nome'] ?></option>
        <?php } ?>
    </select><br>

    <label>Imagens:</label>
    <input type="file" name="imagens[]" accept="image/*" multiple required><br>

    <button type="submit">Cadastrar</button>
</form>

</body>
</html>
