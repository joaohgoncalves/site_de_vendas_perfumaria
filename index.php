<?php
session_start();
require 'conexao.php'; // Inclui a conexão com o banco de dados

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Loja de Perfumes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; text-align: center; }
        .header { background: #333; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin-left: 20px; }
        .menu { margin-right: 20px; }
        .menu a { color: white; text-decoration: none; margin: 0 10px; font-size: 16px; }
        .container { width: 80%; margin: auto; padding: 20px; }
        .produtos { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }
        .produto { width: 30%; border: 1px solid #ddd; padding: 10px; text-align: center; background: white; }
        .produto img { width: 100%; height: 200px; object-fit: cover; }
        .botao { background: #28a745; color: white; padding: 10px; text-decoration: none; display: inline-block; margin-top: 10px; border-radius: 5px; }
        .botao:hover { background: #218838; }
    </style>
</head>
<body>

<div class="header">
    <h1>Perfumes Online</h1>
    <div class="menu">
    <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="perfil.php">Perfil</a>
            <a href="logout.php">Sair</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="criar_conta.php">Criar Conta</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <h2>Produtos</h2>
    <div class="produtos">
        <?php
        // Modificando para usar o PDO
        $sql = "SELECT * FROM produtos";
        $stmt = $pdo->query($sql); // Use o PDO para realizar a consulta

        if ($stmt->rowCount() > 0):
            while ($produto = $stmt->fetch(PDO::FETCH_ASSOC)):
        ?>
                <div class="produto">
                    <img src="uploads/<?php echo $produto['imagem']; ?>" alt="<?php echo $produto['nome']; ?>">
                    <h3><?php echo $produto['nome']; ?></h3>
                    <p><?php echo substr($produto['descricao'], 0, 100); ?>...</p> <!-- Limita a descrição -->
                    <p><strong>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></strong></p>
                    <a href="detalhes_produto.php?id=<?php echo $produto['id_produto']; ?>" class="botao">Ver mais</a>
                </div>
        <?php 
            endwhile;
        else:
            echo "<p>Nenhum produto disponível no momento.</p>";
        endif;
        ?>
    </div>
</div>

</body>
</html>
