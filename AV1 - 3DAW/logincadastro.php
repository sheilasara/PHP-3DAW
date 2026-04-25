<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
<link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container">
        <h1>Jogo Corporativo</h1>
        
        <?php
        session_start();
        
        // Processar cadastro
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
            $ID = $_POST['ID'];
            $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            
            $arqUsers = fopen("usuarios.txt", "a+") or die("Erro ao abrir arquivo de usuários");
            $userExists = false;
            
            while(!feof($arqUsers)) {
                $linha = fgets($arqUsers);
                if(!empty(trim($linha))) {
                    $dados = explode(";", trim($linha));
                    if($dados[0] == $ID) {
                        $userExists = true;
                        break;
                    }
                }
            }
            
            if(!$userExists) {
                $linhaUsuario = $ID . ";" . $senha . ";" . $nome . ";" . $email . "\n";
                fwrite($arqUsers, $linhaUsuario);
                $msg_cadastro = "Usuário cadastrado com sucesso!";
            } else {
                $msg_cadastro = "ID já existe!";
            }
            fclose($arqUsers);
        }
        
// Processar login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $ID = $_POST['ID'];
    $senha = $_POST['senha'];
    
    $arqUsers = fopen("usuarios.txt", "r") or die("Erro ao abrir arquivo de usuários");
    $loginSuccess = false;
    $mensagem_erro = "";
    
    while(!feof($arqUsers)) {
        $linha = fgets($arqUsers);
        if(!empty(trim($linha))) {
            $dados = explode(";", trim($linha));
            
            // Debug (remova depois)
            echo "<!-- Verificando: " . $dados[0] . " vs " . $ID . " -->";
            
            if($dados[0] == $ID && password_verify($senha, $dados[1])) {
                $loginSuccess = true;
                $_SESSION['ID'] = $ID;
                $_SESSION['nome'] = $dados[2];
                break;
            }
        }
    }
    fclose($arqUsers);
    
    if($loginSuccess) {
        header("Location: menu.php");
        exit();
    } else {
        $msg_login = "ID ou senha inválidos! Verifique se o usuario existe.";
    }
}
        ?>
        
        <!-- Login -->
        <div class="form-box">
            <h3>Login</h3>
            <?php if(isset($msg_login)) echo "<p class='error'>$msg_login</p>"; ?>
            <form method="POST">
                <label>ID:</label>
                <input type="text" name="ID" required><br>
                <label>Senha:</label>
                <input type="password" name="senha" required><br>
                <button type="submit" name="login">Entrar</button>
            </form>
        </div>
        
        <!-- Cadastro -->
        <div class="form-box">
            <h3>Cadastrar Usuário</h3>
            <?php if(isset($msg_cadastro)) echo "<p class='success'>$msg_cadastro</p>"; ?>
            <form method="POST">
                <label>ID:</label>
                <input type="text" name="ID" required><br>
                <label>Senha:</label>
                <input type="password" name="senha" required><br>
                <label>Nome:</label>
                <input type="text" name="nome" required><br>
                <label>Email:</label>
                <input type="email" name="email" required><br>
                <button type="submit" name="register">Cadastrar</button>
            </form>
        </div>
    </div>
</body>
</html>