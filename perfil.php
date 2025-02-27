<?php
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "teste";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$banco", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar informações do usuário
    $stmt = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id_usuario = :usuario_id");
    $stmt->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $usuario_nome = $usuario['nome'];
        $usuario_email = $usuario['email'];
    } else {
        header('Location: login.php');
        exit();
    }

    // Buscar histórico de compras
    $stmt_compras = $pdo->prepare("SELECT data_compra, valor_total FROM compras WHERE usuario_id = :usuario_id ORDER BY data_compra DESC");
    $stmt_compras->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
    $stmt_compras->execute();
    $compras = $stmt_compras->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo 'Erro ao conectar com o banco de dados: ' . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, rgb(1, 9, 44), rgb(75, 105, 162));
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 350px;
            text-align: center;
        }
        .container h2 {
            margin-bottom: 1rem;
            color: rgb(1, 9, 44);
        }
        .input-group {
            margin-bottom: 1rem;
            text-align: left;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid rgb(75, 105, 162);
            border-radius: 5px;
            font-size: 1rem;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background: rgb(1, 9, 44);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn:hover {
            background: rgb(75, 105, 162);
        }
        .erro {
            color: red;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .forgot-password {
            margin-top: 10px;
            font-size: 0.9rem;
        }
        .forgot-password a {
            color: rgb(54, 79, 190);
            text-decoration: none;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
        header nav ul {
            list-style: none;
            padding: 0;
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px; /* Espaço entre o título e o botão */
        }
        header nav ul li {
            margin-right: 20px;
        }
        header nav ul li a {
            text-decoration: none;
            color: rgb(1, 9, 44);
        }
        header nav ul li a:hover {
            color: rgb(75, 105, 162);
        }
        .user-info, .historico-compras {
            margin-top: 20px;
            text-align: left;
        }
        .user-info h3, .historico-compras h3 {
            margin-bottom: 1rem;
            color: rgb(1, 9, 44);
        }
        .historico-compras ul {
            list-style: none;
            padding: 0;
        }
        .historico-compras ul li {
            margin-bottom: 1rem;
        }
        .precisa-ajuda {
            color: rgb(54, 79, 190);
            font-size: 0.9rem;
            text-decoration: none;
        }
        .precisa-ajuda:hover {
            text-decoration: underline;
        }
    </style>
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
            <h2>Bem-vindo ao seu perfil, <?php echo htmlspecialchars($usuario_nome); ?>!</h2>
            <p>Aqui você pode ver seu histórico de compras e editar suas informações pessoais.</p>

            <section class="user-info">
                <h3>Informações Pessoais</h3>
                <p><strong>Nome:</strong> <?php echo htmlspecialchars($usuario_nome); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario_email); ?></p>
                <p><strong>ID de Usuário:</strong> <?php echo $_SESSION['usuario_id']; ?></p>
                <p><a href="ajuda.php" class="precisa-ajuda">Precisa de ajuda?</a></p> <!-- Aqui -->
            </section>

            <section class="historico-compras">
                <h3>Histórico de Compras</h3>
                <?php if ($compras): ?>
                    <ul>
                        <?php foreach ($compras as $compra): ?>
                            <li>
                                <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($compra['data_compra'])); ?></p>
                                <p><strong>Valor Total:</strong> R$ <?php echo number_format($compra['valor_total'], 2, ',', '.'); ?></p>
                                <hr>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Você ainda não fez nenhuma compra.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
