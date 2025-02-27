<?php
session_start();
require 'conexao.php'; // Conexão com o banco

// Verifica se o usuário está logado e tem permissão de administrador (se necessário)
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Excluir usuário
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    $sql = "DELETE FROM usuarios WHERE id_usuario = :id_usuario"; // Usando marcador de parâmetro
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_excluir, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: gerenciar_usuarios.php");
    exit;
}

// Buscar todos os usuários
$sql = "SELECT id_usuario, nome, email FROM usuarios";
$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciamento de Usuários</title>
    <style>
       /* Reset básico */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Arial', sans-serif;
}

/* Corpo da página */
body {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg,rgb(8, 21, 46), #2A5298);
    color: white;
    min-height: 100vh;
    padding: 20px;
}

/* Título */
h2 {
    color: white;
    font-size: 2rem;
    margin-bottom: 20px;
}

/* Tabela */
table {
    width: 90%;
    max-width: 1000px;
    margin: 20px auto;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

/* Estilo das células da tabela */
th, td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
}

/* Cor do texto dentro da tabela */
td {
    color: black;
}

/* Cabeçalho da tabela */
th {
    background-color: #1E3C72;
    color: white;
}

/* Estilo para os botões */
button {
    padding: 8px 15px;
    cursor: pointer;
    border-radius: 5px;
    font-size: 1rem;
    border: none;
    transition: 0.3s;
}

/* Botão de editar */
button.editar {
    background-color: #007bff;
    color: white;
}

button.editar:hover {
    background-color: #0056b3;
}

/* Botão de excluir */
button.excluir {
    background-color: #dc3545;
    color: white;
}

button.excluir:hover {
    background-color: #c82333;
}

/* Responsividade para telas menores */
@media (max-width: 600px) {
    table {
        width: 100%;
    }

    th, td {
        font-size: 0.9rem;
        padding: 10px;
    }

    h2 {
        font-size: 1.5rem;
    }
}

    </style>
</head>
<body>

<h2>Gerenciamento de Usuários</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Email</th>
        <th>Ações</th>
    </tr>
    <?php while ($usuario = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
    <tr>
        <td><?php echo $usuario['id_usuario']; ?></td>
        <td><?php echo $usuario['nome']; ?></td>
        <td><?php echo $usuario['email']; ?></td>
        <td>
            <a href="editar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>">
                <button class="editar">Editar</button>
            </a>
            <a href="gerenciar_usuarios.php?excluir=<?php echo $usuario['id_usuario']; ?>" onclick="return confirm('Tem certeza que deseja excluir?')">
                <button class="excluir">Excluir</button>
            </a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
