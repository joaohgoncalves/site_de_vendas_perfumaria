<?php
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pegando os dados enviados pelo formulário
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $cpf = $_POST['cpf'];
    $data_nascimento = $_POST['data_nascimento'];
    $endereco = $_POST['endereco'];

    // Validar se os campos não estão vazios
    if (empty($nome) || empty($telefone) || empty($cpf) || empty($data_nascimento) || empty($endereco)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        try {
            // Atualizar os dados do usuário
            $stmt_update = $pdo->prepare("UPDATE usuarios SET nome = :nome, telefone = :telefone, cpf = :cpf, data_nascimento = :data_nascimento, endereco = :endereco WHERE id_usuario = :usuario_id");
            $stmt_update->bindParam(':nome', $nome);
            $stmt_update->bindParam(':telefone', $telefone);
            $stmt_update->bindParam(':cpf', $cpf);
            $stmt_update->bindParam(':data_nascimento', $data_nascimento);
            $stmt_update->bindParam(':endereco', $endereco);
            $stmt_update->bindParam(':usuario_id', $_SESSION['usuario_id']);
            $stmt_update->execute();

            $mensagem = "Dados atualizados com sucesso!";
        } catch (PDOException $e) {
            $erro = 'Erro ao conectar com o banco de dados: ' . $e->getMessage();
        }
    }
}

// Recuperando os dados atuais do usuário
$stmt = $pdo->prepare("SELECT nome, telefone, cpf, data_nascimento, endereco FROM usuarios WHERE id_usuario = :usuario_id");
$stmt->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Garantindo que todos os campos existem
$usuario_nome = $usuario['nome'];
$usuario_telefone = $usuario['telefone'];
$usuario_cpf = $usuario['cpf'];
$usuario_data_nascimento = $usuario['data_nascimento'];
$usuario_endereco = isset($usuario['endereco']) ? $usuario['endereco'] : '';  // Evitar erro se o campo não existir

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Dados</title>
    <link rel="stylesheet" href="atualizar_dados.css">
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
            <h2>Alterar Dados Pessoais</h2>

            <?php if (isset($mensagem)) { ?>
                <p class="sucesso"><?php echo $mensagem; ?></p>
            <?php } ?>

            <?php if (isset($erro)) { ?>
                <p class="erro"><?php echo $erro; ?></p>
            <?php } ?>

            <form action="alterar_dados.php" method="POST">
                <div class="input-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario_nome); ?>" required>
                </div>
                <div class="input-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($usuario_telefone); ?>" required>
                </div>
                <div class="input-group">
                    <label for="cpf">CPF</label>
                    <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($usuario_cpf); ?>" required>
                </div>
                <div class="input-group">
                    <label for="data_nascimento">Data de Nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($usuario_data_nascimento); ?>" required>
                </div>
                <div class="input-group">
                    <label for="endereco">Endereço</label>
                    <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($usuario_endereco); ?>" required>
                </div>
                <button type="submit" class="btn">Alterar Dados</button>
            </form>
        </main>
    </div>
</body>
</html>
