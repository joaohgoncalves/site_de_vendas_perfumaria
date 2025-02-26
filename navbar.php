<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav>
    <a href="index.php">Home</a>
    <?php if (isset($_SESSION["usuario_id"])): ?>
        <a href="perfil.php">Perfil</a>
        <a href="logout.php">Sair</a>
    <?php else: ?>
        <a href="login.php">Entrar</a>
        <a href="cadastro.php">Criar Conta</a>
    <?php endif; ?>
</nav>
