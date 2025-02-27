<?php
require 'conexao.php'; // Incluindo a conexão com o banco de dados

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $senha_atual = $_POST['senha_atual'];
    $novo_email = $_POST['novo_email'];

    // Validar se os campos não estão vazios
    if (empty($senha_atual) || empty($novo_email)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        try {
            // Verificar se a senha atual está correta
            $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id_usuario = :usuario_id");
            $stmt->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($usuario && password_verify($senha_atual, $usuario['senha'])) {
                // Verificar se o novo email já está em uso
                $stmt_check_email = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :novo_email");
                $stmt_check_email->bindParam(':novo_email', $novo_email);
                $stmt_check_email->execute();

                $email_count = $stmt_check_email->fetchColumn();

                if ($email_count > 0) {
                    $erro = "Este email já está em uso. Escolha outro.";
                } else {
                    // Atualizar o email
                    $stmt_update = $pdo->prepare("UPDATE usuarios SET email = :novo_email WHERE id_usuario = :usuario_id");
                    $stmt_update->bindParam(':novo_email', $novo_email);
                    $stmt_update->bindParam(':usuario_id', $_SESSION['usuario_id']);
                    $stmt_update->execute();

                    $mensagem = "Email atualizado com sucesso!";
                }
            } else {
                $erro = "Senha atual incorreta!";
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao conectar com o banco de dados: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Email</title>
    <link rel="stylesheet" href="alterar_email.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <ul>
                    <li><a href="perfil.php">Voltar ao Perfil</a></li>
                    <li><a href="logout.php" class="btn">Sair</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <h2>Alterar Email</h2>

            <?php if (isset($mensagem)) { ?>
                <p class="sucesso"><?php echo $mensagem; ?></p>
            <?php } ?>

            <?php if (isset($erro)) { ?>
                <p class="erro"><?php echo $erro; ?></p>
            <?php } ?>

            <form action="alterar_email.php" method="POST">
                <div class="input-group">
                    <label for="senha_atual">Senha Atual</label>
                    <input type="password" id="senha_atual" name="senha_atual" value="<?php echo isset($senha_atual) ? htmlspecialchars($senha_atual) : ''; ?>" required>
                </div>
                <div class="input-group">
                    <label for="novo_email">Novo Email</label>
                    <input type="email" id="novo_email" name="novo_email" value="<?php echo isset($novo_email) ? htmlspecialchars($novo_email) : ''; ?>" required>
                </div>
                <button type="submit" class="btn">Alterar Email</button>
            </form>
        </main>
    </div>
</body>
</html>
