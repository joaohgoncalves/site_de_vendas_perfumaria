<?php
// Página de ajuda, onde o usuário pode escolher o problema que deseja resolver
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Precisa de Ajuda?</title>
    <link rel="stylesheet" href="ajuda.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="logout.php" class="btn">Sair</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <h2>Precisa de ajuda?</h2>
            <p>Escolha uma opção abaixo para resolver seu problema:</p>

            <section class="opcoes-ajuda">
                <ul>
                    <li><a href="recuperar_email.php">Perdeu seu email?</a></li>
                    <li><a href="recuperar_senha.php">Esqueceu sua senha?</a></li>
                    <li><a href="atualizar_dados.php">Deseja atualizar seus dados?</a></li>
                </ul>
            </section>
        </main>
    </div>
</body>
</html>
