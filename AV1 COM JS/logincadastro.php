<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">

    <script>
        function contemPontoVirgula(texto){
            return texto.includes(";");
        }

        function validarLogin(){

            let id = document.getElementById("loginID").value.trim();
            let senha = document.getElementById("loginSenha").value;

            if(id.length < 3){
                alert("O ID deve possuir pelo menos 3 caracteres.");
                return false;
            }

            if(contemPontoVirgula(id)){
                alert("O caractere ';' não é permitido.");
                return false;
            }

            if(senha.length < 6){
                alert("A senha deve possuir pelo menos 6 caracteres.");
                return false;
            }

            return true;
        }

        function validarCadastro(){

            let id = document.getElementById("cadastroID").value.trim();
            let senha = document.getElementById("cadastroSenha").value;
            let nome = document.getElementById("cadastroNome").value.trim();
            let email = document.getElementById("cadastroEmail").value.trim();

            if(id.length < 3){
                alert("O ID deve possuir pelo menos 3 caracteres.");
                return false;
            }

            if(contemPontoVirgula(id)){
                alert("O caractere ';' não é permitido.");
                return false;
            }

            let regexID = /^[a-zA-Z0-9_]+$/;

            if(!regexID.test(id)){
                alert("O ID deve conter apenas letras, números e underscore (_).");
                return false;
            }

            if(senha.length < 6){
                alert("A senha deve possuir pelo menos 6 caracteres.");
                return false;
            }

            if(nome.length < 3){
                alert("O nome deve possuir pelo menos 3 caracteres.");
                return false;
            }

            if(contemPontoVirgula(nome)){
                alert("O caractere ';' não é permitido.");
                return false;
            }

            let regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if(!regexEmail.test(email)){
                alert("Digite um e-mail válido.");
                return false;
            }

            return true;
        }
    </script>
</head>

<body>
    <div class="container">
        <h1>Jogo Corporativo</h1>

        <?php
        session_start();

        // Processar cadastro
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {

            $ID = trim($_POST['ID']);
            $senhaOriginal = $_POST['senha'];
            $nome = trim($_POST['nome']);
            $email = trim($_POST['email']);

            // Validação no servidor
            if(
                strpos($ID, ";") !== false ||
                strpos($nome, ";") !== false ||
                strpos($email, ";") !== false
            ){
                $msg_cadastro = "Caracteres inválidos detectados.";
            }
            elseif(strlen($ID) < 3){
                $msg_cadastro = "ID deve possuir pelo menos 3 caracteres.";
            }
            elseif(strlen($senhaOriginal) < 6){
                $msg_cadastro = "Senha deve possuir pelo menos 6 caracteres.";
            }
            elseif(strlen($nome) < 3){
                $msg_cadastro = "Nome deve possuir pelo menos 3 caracteres.";
            }
            elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $msg_cadastro = "E-mail inválido.";
            }
            else{

                $senha = password_hash($senhaOriginal, PASSWORD_DEFAULT);

                $arqUsers = fopen("usuarios.txt", "a+") or die("Erro ao abrir arquivo de usuários");

                $userExists = false;

                rewind($arqUsers);

                while(!feof($arqUsers)){

                    $linha = fgets($arqUsers);

                    if(!empty(trim($linha))){

                        $dados = explode(";", trim($linha));

                        if($dados[0] == $ID){
                            $userExists = true;
                            break;
                        }
                    }
                }

                if(!$userExists){

                    $linhaUsuario =
                        $ID . ";" .
                        $senha . ";" .
                        $nome . ";" .
                        $email . "\n";

                    fwrite($arqUsers, $linhaUsuario);

                    $msg_cadastro = "Usuário cadastrado com sucesso!";

                }else{

                    $msg_cadastro = "ID já existe!";
                }

                fclose($arqUsers);
            }
        }

        // Processar login
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {

            $ID = trim($_POST['ID']);
            $senha = $_POST['senha'];

            $arqUsers = fopen("usuarios.txt", "r") or die("Erro ao abrir arquivo de usuários");

            $loginSuccess = false;

            while(!feof($arqUsers)){

                $linha = fgets($arqUsers);

                if(!empty(trim($linha))){

                    $dados = explode(";", trim($linha));

                    if(
                        $dados[0] == $ID &&
                        password_verify($senha, $dados[1])
                    ){
                        $loginSuccess = true;

                        $_SESSION['ID'] = $ID;
                        $_SESSION['nome'] = $dados[2];

                        break;
                    }
                }
            }

            fclose($arqUsers);

            if($loginSuccess){

                header("Location: menu.php");
                exit();

            }else{

                $msg_login =
                    "ID ou senha inválidos! Verifique se o usuário existe.";
            }
        }
        ?>

        <!-- LOGIN -->
        <div class="form-box">

            <h3>Login</h3>

            <?php
            if(isset($msg_login)){
                echo "<p class='error'>$msg_login</p>";
            }
            ?>

            <form method="POST" onsubmit="return validarLogin()">

                <label>ID:</label>
                <input
                    type="text"
                    id="loginID"
                    name="ID"
                    required
                >
                <br>

                <label>Senha:</label>
                <input
                    type="password"
                    id="loginSenha"
                    name="senha"
                    required
                >
                <br>

                <button type="submit" name="login">
                    Entrar
                </button>

            </form>

        </div>

        <!-- CADASTRO -->
        <div class="form-box">

            <h3>Cadastrar Usuário</h3>

            <?php
            if(isset($msg_cadastro)){

                if(
                    $msg_cadastro == "Usuário cadastrado com sucesso!"
                ){
                    echo "<p class='success'>$msg_cadastro</p>";
                }else{
                    echo "<p class='error'>$msg_cadastro</p>";
                }
            }
            ?>

            <form method="POST" onsubmit="return validarCadastro()">

                <label>ID:</label>
                <input
                    type="text"
                    id="cadastroID"
                    name="ID"
                    required
                >
                <br>

                <label>Senha:</label>
                <input
                    type="password"
                    id="cadastroSenha"
                    name="senha"
                    required
                >
                <br>

                <label>Nome:</label>
                <input
                    type="text"
                    id="cadastroNome"
                    name="nome"
                    required
                >
                <br>

                <label>Email:</label>
                <input
                    type="email"
                    id="cadastroEmail"
                    name="email"
                    required
                >
                <br>

                <button type="submit" name="register">
                    Cadastrar
                </button>

            </form>

        </div>

    </div>
</body>
</html>
```
