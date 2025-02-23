<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        form { max-width: 400px; margin: auto; background: #f4f4f4; padding: 20px; border-radius: 8px; }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        button { background: #007bff; color: white; padding: 10px; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>

    <h2>Faça seu login</h2>
    <form action="processa_login.php" method="POST">
        <input type="email" name="email" placeholder="E-mail" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Entrar</button>
    </form>

</body>
</html>
