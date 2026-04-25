<?php
session_start();
if(!isset($_SESSION['ID'])) {
    header("Location: logincadastro.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Menu - Jogo Corporativo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header-dashboard">
            <h1>Sistema de Jogo Corporativo</h1>
            <p>Bem-vindo, <strong><?php echo $_SESSION['nome']; ?></strong>!</p>
            <a href="logout.php" class="logout">Sair</a>
        </div>
        
        <div class="menu-grid">
            <div class="menu-item">
                <h3>Criar Perguntas</h3>
                <a href="criarperguntamultipla.php">Multipla Escolha</a><br>
                <a href="criarperguntadissertativa.php">Discursiva (Texto)</a>
            </div>
            
            <div class="menu-item">
                <h3>Alterar Perguntas</h3>
                <a href="alterarpergunta.php">Alterar Pergunta</a><br>
                <small>(Multipla Escolha e Discursiva)</small>
            </div>
            
            <div class="menu-item">
                <h3>Listar Perguntas</h3>
                <a href="listarperguntas.php">Todas as Perguntas</a><br>
                <a href="listarumapergunta.php">Buscar uma Pergunta</a>
            </div>
            
            <div class="menu-item">
                <h3>Excluir</h3>
                <a href="excluirpergunta.php">Excluir Pergunta</a>
            </div>
        </div>
    </div>
</body>
</html>