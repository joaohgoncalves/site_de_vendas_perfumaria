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

try {
    // Consulta para obter o produto
    $sql = "SELECT * FROM produtos WHERE id_produto = :id";
    $stmt = $pdo->prepare($sql);  // Usar $pdo aqui
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        // Produto não encontrado
        header("Location: gerenciar_produtos.php");
        exit;
    }
} catch (PDOException $e) {
    echo "Erro ao consultar produto: " . $e->getMessage();
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $descricao = $_POST["descricao"];
    $preco = $_POST["preco"];
    $estoque = $_POST["estoque"];

    // Processamento das fotos
    $foto_atual = $produto['foto']; // Foto atual no banco de dados
    $novas_fotos = $_FILES['fotos'];  // Para receber várias imagens
    $diretorio = 'uploads/'; // Diretório onde as fotos serão armazenadas
    $fotos_novas = [];

    // Processar todas as fotos enviadas
    if (isset($novas_fotos['name'][0]) && !empty($novas_fotos['name'][0])) {
        for ($i = 0; $i < count($novas_fotos['name']); $i++) {
            $foto_nome = uniqid() . "_" . basename($novas_fotos['name'][$i]);
            $foto_temp = $novas_fotos['tmp_name'][$i];
            $foto_destino = $diretorio . $foto_nome;

            if (move_uploaded_file($foto_temp, $foto_destino)) {
                $fotos_novas[] = $foto_nome; // Salvar o nome da foto para armazenar no banco
            } else {
                echo "Erro ao fazer upload da foto.";
                exit;
            }
        }
    } else {
        // Se não houver novas fotos, mantém a foto atual
        $fotos_novas = explode(',', $foto_atual);
    }

    // Verifica e atualiza a promoção
    $percentual_promocao = isset($_POST['percentual_promocao']) ? $_POST['percentual_promocao'] : null;
    $preco_promocional = null;
    if ($percentual_promocao !== null && $percentual_promocao > 0) {
        // Calcula o preço com o desconto aplicado
        $preco_promocional = $preco - ($preco * ($percentual_promocao / 100));
    }

    // Agora a variável $foto_atual está corretamente definida
    $foto_atual = implode(",", $fotos_novas); // Isso resolve o "Only variables" error

    $sql_update = "UPDATE produtos SET 
                    nome = :nome,
                    descricao = :descricao,
                    preco = :preco,
                    estoque = :estoque,
                    foto = :foto,
                    preco_promocional = :preco_promocional,
                    data_fim_promocao = :data_fim_promocao
                  WHERE id_produto = :id_produto";
    
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':nome', $nome, PDO::PARAM_STR);
    $stmt_update->bindParam(':descricao', $descricao, PDO::PARAM_STR);
    $stmt_update->bindParam(':preco', $preco, PDO::PARAM_STR);
    $stmt_update->bindParam(':estoque', $estoque, PDO::PARAM_INT);
    $stmt_update->bindParam(':foto', $foto_atual, PDO::PARAM_STR);
    $stmt_update->bindParam(':preco_promocional', $preco_promocional, PDO::PARAM_STR);
    $stmt_update->bindParam(':data_fim_promocao', $_POST['data_fim_promocao'], PDO::PARAM_STR);
    $stmt_update->bindParam(':id_produto', $id, PDO::PARAM_INT);

    if ($stmt_update->execute()) {
        // Redirecionar após a atualização
        header("Location: gerenciar_produtos.php");
        exit;
    } else {
        echo "Erro ao atualizar produto.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Produto</title>
    <script src="cadastrar_produto.js"></script>
    <link rel="stylesheet" href="editar_produto.css">
</head>
<body>

<h2>Editar Produto</h2>
<form action="editar_produto.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
    <?php
    // Exibir as fotos existentes
    if ($produto['foto']) {
        $fotos = explode(',', $produto['foto']);
        echo "<h3>Fotos Atuais do Produto:</h3>";
        echo "<div class='foto-preview'>";
        foreach ($fotos as $foto) {
            $foto_atual = 'uploads/' . $foto;
            echo "<img src='$foto_atual' alt='Foto do Produto'>";
        }
        echo "</div>";
    }
    ?>
    
    <label>Nome:</label>
    <input type="text" name="nome" value="<?php echo $produto['nome']; ?>" required><br>

    <label>Descrição:</label>
    <textarea name="descricao" required><?php echo $produto['descricao']; ?></textarea><br>

    <label>Preço:</label>
    <input type="number" step="0.01" name="preco" value="<?php echo $produto['preco']; ?>" required><br>

    <label>Estoque:</label>
    <input type="number" name="estoque" value="<?php echo $produto['estoque']; ?>" required><br>

    <label>Alterar Fotos:</label>
    <input type="file" name="fotos[]" accept="image/*" multiple><br>

    <label>Percentual de Promoção (%):</label>
    <input type="number" step="0.01" name="percentual_promocao" value="<?php echo $produto['preco_promocional']; ?>"><br>

    <label>Data Fim da Promoção:</label>
    <input type="datetime-local" name="data_fim_promocao" value="<?php echo $produto['data_fim_promocao']; ?>"><br>

    <button type="submit">Salvar Alterações</button>
</form>

</body>
</html>
