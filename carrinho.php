<?php
session_start();

// Inicializa o carrinho se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras</title>
</head>
<body>
    <h2>Carrinho de Compras</h2>
    <ul>
        <?php if (!empty($_SESSION['carrinho'])) : ?>
            <?php foreach ($_SESSION['carrinho'] as $id => $produto) : ?>
                <li>
                    <?php echo $produto['nome'] . " - R$" . number_format($produto['preco'], 2, ',', '.') . " x " . $produto['quantidade']; ?>
                </li>
            <?php endforeach; ?>
        <?php else : ?>
            <p>O carrinho está vazio.</p>
        <?php endif; ?>
    </ul>
</body>
</html>
