<?php
session_start();
require 'conexao.php';

// Verifica se o usuário está logado e é administrador
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_tipo"] !== "administrador") {
    header("Location: index.php");
    exit;
}

// Contagem de registros
$sql_total_usuarios = "SELECT COUNT(*) AS total FROM usuarios";
$sql_total_produtos = "SELECT COUNT(*) AS total FROM produtos";
$sql_total_categorias = "SELECT COUNT(*) AS total FROM categorias";
$sql_total_vendas = "SELECT SUM(valor) AS total FROM vendas";

$total_usuarios = $conn->query($sql_total_usuarios)->fetch_assoc()['total'];
$total_produtos = $conn->query($sql_total_produtos)->fetch_assoc()['total'];
$total_categorias = $conn->query($sql_total_categorias)->fetch_assoc()['total'];
$total_vendas = $conn->query($sql_total_vendas)->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="styles.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: linear-gradient(135deg, #1E3C72, #2A5298);
            color: white;
            min-height: 100vh;
        }
        .header {
            width: 100%;
            background-color: #1E3C72;
            padding: 20px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .menu {
            display: flex;
            justify-content: center;
            background: #2A5298;
            padding: 15px;
            width: 100%;
        }
        .menu a {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            margin: 0 5px;
            font-size: 1rem;
            transition: 0.3s;
            border-radius: 5px;
        }
        .menu a:hover {
            background: #1E3C72;
        }
        .container {
            background: white;
            color: black;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 900px;
            margin-top: 30px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background: #1E3C72;
            color: white;
            font-size: 1.1rem;
        }
        .resumo {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        .resumo div {
            background: #2A5298;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            flex: 1;
            margin: 5px;
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Painel Administrativo</h1>
</div>

<div class="menu">
    <a href="painel_admin.php">Dashboard</a>
    <a href="gerenciar_usuarios.php">Gerenciar Usuários</a>
    <a href="gerenciar_produtos.php">Gerenciar Produtos</a>
    <a href="gerenciar_categorias.php">Gerenciar Categorias</a>
    <a href="criar_conta_adm.php">Criar Conta Administrador</a>
    <a href="logout.php">Sair</a>
</div>

<div class="container">
    <h2>Bem-vindo, <?php echo $_SESSION["usuario_nome"]; ?>!</h2>
    <p>Você está no painel administrativo da perfumaria online.</p>

    <div class="resumo">
        <div>Total de Usuários: <?php echo $total_usuarios; ?></div>
        <div>Total de Produtos: <?php echo $total_produtos; ?></div>
        <div>Total de Categorias: <?php echo $total_categorias; ?></div>
        <div>Total de Renda: R$ <?php echo number_format($total_vendas, 2, ',', '.'); ?></div>
    </div>

    <h3>Resumo Gráfico</h3>
    <canvas id="chart"></canvas>
</div>

<script>
    var ctx = document.getElementById('chart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Usuários', 'Produtos', 'Categorias', 'Renda'],
            datasets: [{
                label: 'Total',
                data: [<?php echo $total_usuarios; ?>, <?php echo $total_produtos; ?>, <?php echo $total_categorias; ?>, <?php echo $total_vendas; ?>],
                backgroundColor: ['#FFA500', '#32CD32', '#4682B4', '#FF4500']
            }]
        }
    });
</script>

</body>
</html>
