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

// Busca média das avaliações
$sqlMedia = "SELECT AVG(nota) as media, COUNT(*) as total FROM avaliacoes WHERE id_produto = ?";
$stmt = $conn->prepare($sqlMedia);
$stmt->bind_param("i", $id_produto);
$stmt->execute();
$resultMedia = $stmt->get_result()->fetch_assoc();
$media = round($resultMedia['media'], 1);
$totalAvaliacoes = $resultMedia['total'];

// Buscar outros produtos
$sqlOutrosProdutos = "SELECT id_produto, nome, imagem, preco FROM produtos WHERE id_produto != ? LIMIT 4"; // Ajuste a quantidade conforme necessário
$stmt = $conn->prepare($sqlOutrosProdutos);
$stmt->bind_param("i", $id_produto);
$stmt->execute();
$outrosProdutos = $stmt->get_result();

// Buscar perguntas sobre o produto
$sqlPerguntas = "SELECT p.pergunta, p.resposta, u.nome, p.data_pergunta 
                 FROM perguntas p 
                 JOIN usuarios u ON p.id_usuario = u.id_usuario 
                 WHERE p.id_produto = ? 
                 ORDER BY p.data_pergunta DESC";
$stmt = $conn->prepare($sqlPerguntas);
$stmt->bind_param("i", $id_produto);
$stmt->execute();
$perguntas = $stmt->get_result();


// Processa a avaliação do usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["usuario_id"])) {
    $nota = intval($_POST["nota"]);
    $comentario = trim($_POST["comentario"]);
    $id_usuario = $_SESSION["usuario_id"];

    if ($nota >= 1 && $nota <= 5) {
        $sql = "INSERT INTO avaliacoes (id_produto, id_usuario, nota, comentario) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
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
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Helvetica Neue', Arial, sans-serif;
    background-color: #f4f7fb;
    color: #333;
    padding: 30px;
}

.container {
    max-width: 1200px;
    margin: auto;
    background-color: #fff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.produto-info {
    display: flex;
    gap: 40px;
    flex-wrap: wrap;
    margin-bottom: 50px;
}

.imagem-produto img {
    width: 100%;
    max-width: 450px;
    border-radius: 12px;
    object-fit: cover;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.detalhes-produto {
    flex: 1;
    min-width: 300px;
    text-align: left;
    display: flex;
    flex-direction: column;
}

h1 {
    font-size: 32px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
}

.avaliacao {
    font-size: 18px;
    color: #FFD700;
    margin-bottom: 20px;
    font-weight: 500;
}

.preco {
    font-size: 28px;
    font-weight: 700;
    color: #2D8C5D;
    margin-bottom: 20px;
}

.parcelas {
    font-size: 16px;
    color: #777;
    margin-bottom: 25px;
}

.botao-comprar {
    display: inline-block;
    padding: 16px 32px;
    background-color: #007bff;
    color: #fff;
    text-decoration: none;
    font-size: 18px;
    font-weight: 600;
    border-radius: 8px;
    margin-top: 20px;
    text-align: center;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.botao-comprar:hover {
    background-color: #0056b3;
    transform: translateY(-3px);
}

.coisas-para-saber {
    margin-top: 30px;
    padding: 20px;
    background-color: #f9fafc;
    border-radius: 8px;
    border: 1px solid #e2e2e2;
    font-size: 16px;
    color: #555;
}

.coisas-para-saber h3 {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}

.coisas-para-saber ul {
    list-style: none;
    padding-left: 0;
}

.coisas-para-saber ul li {
    font-size: 16px;
    color: #555;
    margin-bottom: 10px;
    line-height: 1.6;
}

.descricao {
    margin-top: 50px;
    border-top: 2px solid #f0f0f0;
    padding-top: 30px;
}

.descricao h3 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

.descricao p {
    font-size: 16px;
    line-height: 1.7;
    color: #555;
}

.outros-produtos {
    margin-top: 50px;
}

.outros-produtos h3 {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 25px;
    color: #333;
}

.produtos-lista {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 30px;
    margin-top: 20px;
}

.produto-card {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.produto-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.produto-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 15px;
}

.produto-card a {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    text-decoration: none;
    margin-bottom: 10px;
    display: block;
    transition: color 0.3s ease;
}

.produto-card a:hover {
    color: #007bff;
}

.produto-card p {
    font-size: 18px;
    color: #2D8C5D;
    font-weight: 700;
}

.avaliacoes {
    margin-top: 50px;
}

.avaliacoes h3 {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 30px;
    color: #333;
}

.avaliacao-item {
    padding: 20px;
    border-bottom: 1px solid #e2e2e2;
    margin-bottom: 25px;
}

.avaliacao-item strong {
    font-size: 18px;
    color: #333;
}

.avaliacao-item .avaliacao {
    font-size: 18px;
    color: #FFD700;
    margin: 10px 0;
    font-weight: 500;
}

.avaliacao-item p {
    font-size: 16px;
    color: #555;
    line-height: 1.7;
}

.avaliacao-item small {
    font-size: 14px;
    color: #777;
}

.form-avaliacao {
    background-color: #f9fafc;
    padding: 30px;
    border-radius: 8px;
    margin-top: 40px;
    border: 1px solid #e2e2e2;
}

.form-avaliacao h4 {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}

.form-avaliacao label {
    font-size: 16px;
    color: #333;
    display: block;
    margin-bottom: 8px;
}

.form-avaliacao select,
.form-avaliacao textarea {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e2e2e2;
    font-size: 16px;
    margin-bottom: 20px;
    resize: vertical;
}

.form-avaliacao button {
    padding: 14px 30px;
    background-color: #28a745;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.form-avaliacao button:hover {
    background-color: #218838;
}

.perguntas {
    margin-top: 50px;
}

.perguntas h3 {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 30px;
    color: #333;
}

.pergunta-item {
    padding: 20px;
    border-bottom: 1px solid #e2e2e2;
    margin-bottom: 25px;
}

.pergunta-item strong {
    font-size: 18px;
    color: #333;
}

.pergunta-item p {
    font-size: 16px;
    color: #555;
    line-height: 1.7;
}

.form-pergunta {
    margin-top: 40px;
    padding: 30px;
    background-color: #f9fafc;
    border-radius: 8px;
    border: 1px solid #e2e2e2;
}

.form-pergunta h4 {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}

.form-pergunta textarea {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #e2e2e2;
    font-size: 16px;
    margin-bottom: 20px;
    resize: vertical;
}

.form-pergunta button {
    padding: 14px 30px;
    background-color: #28a745;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.form-pergunta button:hover {
    background-color: #218838;
}

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
            <?php while ($outro = $outrosProdutos->fetch_assoc()): ?>
                <div class="produto-card">
                    <img src="uploads/<?php echo $outro['imagem']; ?>" alt="<?php echo $outro['nome']; ?>">
                    <a href="detalhes_produto.php?id=<?php echo $outro['id_produto']; ?>"><?php echo $outro['nome']; ?></a>
                    <p>R$ <?php echo number_format($outro['preco'], 2, ',', '.'); ?></p>
                </div>
            <?php endwhile; ?>
        </div>
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

        <?php while ($pergunta = $perguntas->fetch_assoc()): ?>
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
        <?php endwhile; ?>

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
</div>
</body>
</html>
