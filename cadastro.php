<?php
session_start();
require 'conexao.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Produto não encontrado!";
    exit;
}

$id_produto = intval($_GET['id']); 

// Buscar informações do produto
$sql = "SELECT * FROM produtos WHERE id_produto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_produto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Produto não encontrado!";
    exit;
}

$produto = $result->fetch_assoc();

// Buscar média das avaliações
$sqlMedia = "SELECT AVG(nota) as media, COUNT(*) as total FROM avaliacoes WHERE id_produto = ?";
$stmt = $conn->prepare($sqlMedia);
$stmt->bind_param("i", $id_produto);
$stmt->execute();
$resultMedia = $stmt->get_result()->fetch_assoc();
$media = round($resultMedia['media'], 1);
$totalAvaliacoes = $resultMedia['total'];

// Verifica se o usuário já avaliou este produto
$jaAvaliou = false;
if (isset($_SESSION["usuario_id"])) {
    $id_usuario = $_SESSION["usuario_id"];
    $sqlVerifica = "SELECT COUNT(*) as jaAvaliou FROM avaliacoes WHERE id_produto = ? AND id_usuario = ?";
    $stmt = $conn->prepare($sqlVerifica);
    $stmt->bind_param("ii", $id_produto, $id_usuario);
    $stmt->execute();
    $resultadoVerifica = $stmt->get_result()->fetch_assoc();
    $jaAvaliou = $resultadoVerifica['jaAvaliou'] > 0;
}

// Processa a avaliação do usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["usuario_id"]) && !$jaAvaliou) {
    $nota = intval($_POST["nota"]);
    $comentario = trim($_POST["comentario"]);

    if ($nota >= 1 && $nota <= 5) {
        $sqlInsert = "INSERT INTO avaliacoes (id_produto, id_usuario, nota, comentario) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sqlInsert);
        $stmt->bind_param("iiis", $id_produto, $id_usuario, $nota, $comentario);
        $stmt->execute();
    }
    header("Location: detalhes_produto.php?id=$id_produto");
    exit;
}

// Buscar avaliações do produto
$sqlAvaliacoes = "SELECT a.nota, a.comentario, u.nome, a.data_avaliacao FROM avaliacoes a 
                  JOIN usuarios u ON a.id_usuario = u.id_usuario
                  WHERE a.id_produto = ? ORDER BY a.data_avaliacao DESC";
$stmt = $conn->prepare($sqlAvaliacoes);
$stmt->bind_param("i", $id_produto);
$stmt->execute();
$avaliacoes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo $produto['nome']; ?> - Detalhes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .produto-info { display: flex; gap: 20px; }
        .imagem-produto img { width: 100%; max-width: 400px; border-radius: 5px; }
        .detalhes-produto { flex: 1; text-align: left; }
        h1 { font-size: 24px; margin-bottom: 10px; }
        .avaliacao { font-size: 18px; color: #FFD700; }
        .preco { font-size: 22px; font-weight: bold; color: #28a745; margin-bottom: 10px; }
        .parcelas { font-size: 14px; color: #555; margin-bottom: 10px; }
        .botao-comprar { display: inline-block; padding: 12px 20px; background: #ff5733; color: white; text-decoration: none; font-size: 18px; border-radius: 5px; margin-top: 10px; }
        .descricao { margin-top: 30px; border-top: 1px solid #ddd; font-size: 16px; color: #333; line-height: 1.5; padding-top: 10px; }
        .avaliacoes { margin-top: 30px; }
        .avaliacao-item { border-bottom: 1px solid #ddd; padding: 10px 0; }
        .form-avaliacao { margin-top: 20px; padding: 10px; background: #f8f8f8; border-radius: 5px; }
        .form-avaliacao select, .form-avaliacao textarea { width: 100%; padding: 5px; margin-top: 5px; }
        .form-avaliacao button { margin-top: 10px; padding: 10px; background: #28a745; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <div class="produto-info">
        <div class="imagem-produto">
            <img src="uploads/<?php echo $produto['imagem']; ?>" alt="<?php echo $produto['nome']; ?>">
        </div>

        <div class="detalhes-produto">
            <h1><?php echo $produto['nome']; ?></h1>
            <p class="avaliacao">
    <?php
    if ($totalAvaliacoes > 0) {
        $estrelasCheias = floor($media); // Número inteiro de estrelas cheias
        $meiaEstrela = ($media - $estrelasCheias) >= 0.5 ? 1 : 0; // Se houver decimal >= 0.5, adiciona meia estrela
        $estrelasVazias = 5 - ($estrelasCheias + $meiaEstrela); // Completa com estrelas vazias

        // Exibir estrelas cheias
        for ($i = 0; $i < $estrelasCheias; $i++) {
            echo "⭐";
        }

        // Exibir meia estrela (se houver)
        if ($meiaEstrela) {
            echo "⭐️½";
        }

        // Exibir estrelas vazias
        for ($i = 0; $i < $estrelasVazias; $i++) {
            echo "☆";
        }

        echo " ({$media} / 5) - {$totalAvaliacoes} avaliações";
    } else {
        echo "Sem avaliações ainda.";
    }
    ?>
</p>


            <p class="preco">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
            <p class="parcelas">Em até 12x de R$ <?php echo number_format($produto['preco'] / 12, 2, ',', '.'); ?> sem juros</p>
            <a href="carrinho.php?add=<?php echo $produto['id_produto']; ?>" class="botao-comprar">Comprar Agora</a>
        </div>
    </div>

    <div class="descricao">
        <h3>Descrição do Produto</h3>
        <p><?php echo nl2br($produto['descricao']); ?></p>
    </div>

    <div class="avaliacoes">
        <h3>Avaliações</h3>
        <?php while ($avaliacao = $avaliacoes->fetch_assoc()): ?>
            <div class="avaliacao-item">
                <strong><?php echo $avaliacao["nome"]; ?></strong>
                <p class="avaliacao">Nota: <?php echo str_repeat("⭐", $avaliacao["nota"]); ?></p>
                <p><?php echo nl2br($avaliacao["comentario"]); ?></p>
                <small><?php echo date("d/m/Y H:i", strtotime($avaliacao["data_avaliacao"])); ?></small>
            </div>
        <?php endwhile; ?>

        <?php if (isset($_SESSION["usuario_id"]) && !$jaAvaliou): ?>
            <div class="form-avaliacao">
                <h4>Deixe sua avaliação</h4>
                <form method="POST">
                    <label>Nota:</label>
                    <select name="nota" required>
                        <option value="5">⭐️⭐️⭐️⭐️⭐️</option>
                        <option value="4">⭐️⭐️⭐️⭐️</option>
                        <option value="3">⭐️⭐️⭐️</option>
                        <option value="2">⭐️⭐️</option>
                        <option value="1">⭐️</option>
                    </select>
                    <label>Comentário:</label>
                    <textarea name="comentario" required></textarea>
                    <button type="submit">Enviar Avaliação</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
