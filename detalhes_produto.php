<?php
session_start();
require 'conexao.php';

// Verifica se o ID do produto foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Produto não encontrado!";
    exit;
}

$id_produto = intval($_GET['id']); // Proteção contra SQL Injection

// Busca o produto
$sql = "SELECT * FROM produtos WHERE id_produto = :id_produto";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
$stmt->execute();
$produto = $stmt->fetch();

if (!$produto) {
    echo "Produto não encontrado!";
    exit;
}

// Busca média das avaliações
$sqlMedia = "SELECT AVG(nota) as media, COUNT(*) as total FROM avaliacoes WHERE id_produto = :id_produto";
$stmt = $pdo->prepare($sqlMedia);
$stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
$stmt->execute();
$resultMedia = $stmt->fetch();
$media = round($resultMedia['media'], 1);
$totalAvaliacoes = $resultMedia['total'];

// Buscar outros produtos
$sqlOutrosProdutos = "SELECT id_produto, nome, imagem, preco FROM produtos WHERE id_produto != :id_produto LIMIT 4";
$stmt = $pdo->prepare($sqlOutrosProdutos);
$stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
$stmt->execute();
$outrosProdutos = $stmt->fetchAll();

// Buscar perguntas sobre o produto
$sqlPerguntas = "SELECT p.pergunta, p.resposta, u.nome, p.data_pergunta 
                 FROM perguntas p 
                 JOIN usuarios u ON p.id_usuario = u.id_usuario 
                 WHERE p.id_produto = :id_produto 
                 ORDER BY p.data_pergunta DESC";
$stmt = $pdo->prepare($sqlPerguntas);
$stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
$stmt->execute();
$perguntas = $stmt->fetchAll();

// Processa a avaliação do usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["usuario_id"])) {
    $nota = intval($_POST["nota"]);
    $comentario = trim($_POST["comentario"]);
    $id_usuario = $_SESSION["usuario_id"];

    if ($nota >= 1 && $nota <= 5) {
        $sql = "INSERT INTO avaliacoes (id_produto, id_usuario, nota, comentario) VALUES (:id_produto, :id_usuario, :nota, :comentario)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(':nota', $nota, PDO::PARAM_INT);
        $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);
        $stmt->execute();
    }
    header("Location: detalhes_produto.php?id=$id_produto");
    exit;
}

// Buscar avaliações do produto
$sqlAvaliacoes = "SELECT a.nota, a.comentario, u.nome, a.data_avaliacao FROM avaliacoes a 
                  JOIN usuarios u ON a.id_usuario = u.id_usuario
                  WHERE a.id_produto = :id_produto ORDER BY a.data_avaliacao DESC";
$stmt = $pdo->prepare($sqlAvaliacoes);
$stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
$stmt->execute();
$avaliacoes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo $produto['nome']; ?> - Detalhes</title>
    <link rel="stylesheet" href="detalhes_produto.css">
</head>
<body>
<div class="container">
    <div class="produto-info">
        <div class="imagem-produto">
            <img src="uploads/<?php echo $produto['imagem']; ?>" alt="<?php echo $produto['nome']; ?>">
        </div>
        <div class="detalhes-produto">
            <h1><?php echo $produto['nome']; ?></h1>
            <div class="avaliacao">
                <?php
                $estrelasCheias = floor($media);
                $meiaEstrela = ($media - $estrelasCheias) >= 0.5 ? 1 : 0;
                $estrelasVazias = 5 - ($estrelasCheias + $meiaEstrela);

                for ($i = 0; $i < $estrelasCheias; $i++) {
                    echo "⭐";
                }
                if ($meiaEstrela) {
                    echo "⭐️½";
                }
                for ($i = 0; $i < $estrelasVazias; $i++) {
                    echo "☆";
                }
                ?>
                (<?php echo number_format($media, 1, ',', '.'); ?> / 5) - <?php echo $totalAvaliacoes; ?> avaliações
            </div>
            <p class="preco">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
            <p class="parcelas">Em até 12x de R$ <?php echo number_format($produto['preco'] / 12, 2, ',', '.'); ?> sem juros</p>
            <a href="carrinho.php?add=<?php echo $produto['id_produto']; ?>" class="botao-comprar">Comprar Agora</a>
            <div class="coisas-para-saber">
                <h3>Coisas que você precisa saber</h3>
                <ul>
                    <li>✔ Produto original com garantia</li>
                    <li>✔ Envio em até 24h após a compra</li>
                    <li>✔ Pagamento seguro</li>
                    <li>✔ Atendimento ao cliente 24/7</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="descricao">
        <h3>Descrição do Produto</h3>
        <p><?php echo nl2br($produto['descricao']); ?></p>
    </div>

    <div class="outros-produtos">
        <h3>Outros Produtos</h3>
        <div class="produtos-lista">
            <?php foreach ($outrosProdutos as $outro): ?>
                <div class="produto-card">
                    <img src="uploads/<?php echo $outro['imagem']; ?>" alt="<?php echo $outro['nome']; ?>">
                    <a href="detalhes_produto.php?id=<?php echo $outro['id_produto']; ?>"><?php echo $outro['nome']; ?></a>
                    <p>R$ <?php echo number_format($outro['preco'], 2, ',', '.'); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="avaliacoes">
        <h3>Avaliações</h3>
        <?php foreach ($avaliacoes as $avaliacao): ?>
            <div class="avaliacao-item">
                <strong><?php echo $avaliacao["nome"]; ?></strong>
                <p class="avaliacao">Nota: <?php echo str_repeat("⭐", $avaliacao["nota"]); ?></p>
                <p><?php echo nl2br($avaliacao["comentario"]); ?></p>
                <small><?php echo date("d/m/Y H:i", strtotime($avaliacao["data_avaliacao"])); ?></small>
            </div>
        <?php endforeach; ?>

        <?php if (isset($_SESSION["usuario_id"])): ?>
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

    <!-- Seção de Perguntas -->
    <div class="perguntas">
        <h3>Perguntas sobre este produto</h3>

        <?php foreach ($perguntas as $pergunta): ?>
            <div class="pergunta-item">
                <strong><?php echo $pergunta["nome"]; ?> perguntou:</strong>
                <p><?php echo nl2br($pergunta["pergunta"]); ?></p>
                <p class="data"><?php echo date("d/m/Y H:i", strtotime($pergunta["data_pergunta"])); ?></p>

                <?php if ($pergunta["resposta"]): ?>
                    <strong>Resposta:</strong>
                    <p><?php echo nl2br($pergunta["resposta"]); ?></p>
                <?php else: ?>
                    <p><em>Aguardando resposta...</em></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <?php if (isset($_SESSION["usuario_id"])): ?>
            <div class="form-pergunta">
                <h4>Tem alguma dúvida sobre este produto?</h4>
                <form method="POST">
                    <label for="pergunta">Sua Pergunta:</label>
                    <textarea name="pergunta" required></textarea>
                    <button type="submit">Enviar Pergunta</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
