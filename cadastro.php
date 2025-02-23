<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        form { max-width: 400px; margin: auto; background: #f4f4f4; padding: 20px; border-radius: 8px; }
        input, select { width: 100%; padding: 10px; margin: 10px 0; }
        button { background: #28a745; color: white; padding: 10px; border: none; cursor: pointer; }
        button:hover { background: #218838; }
    </style>
</head>
<body>

    <h2>Crie sua conta</h2>
    <form action="processa_cadastro.php" method="POST">
        <input type="text" name="nome" placeholder="Nome Completo" required>
        <input type="email" name="email" placeholder="E-mail" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <select name="tipo">
            <option value="cliente">Cliente</option>
            <option value="administrador">Administrador</option>
        </select>
        <button type="submit">Cadastrar</button>
    </form>

</body>
</html>
