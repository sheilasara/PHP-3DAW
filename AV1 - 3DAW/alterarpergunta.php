<!DOCTYPE html>
<html>
<head>
    <title>Alterar Pergunta</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Alterar Pergunta</h1>
        <a href="menu.php">← Voltar ao Menu</a>
        
        <?php
        // Carregar todas as perguntas
        $perguntas = [];
        $arqLeitura = fopen("perguntas.txt", "r") or die("Erro ao abrir arquivo");
        while(!feof($arqLeitura)) {
            $linha = fgets($arqLeitura);
            if(!empty(trim($linha))) {
                $dados = explode(";", trim($linha));
                $perguntas[] = $dados;
            }
        }
        fclose($arqLeitura);
        
        // Processar alteracao
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar'])) {
            $id = $_POST['id'];
            $tipo = $_POST['tipo'];
            
            if($tipo == 'multipla') {
                $pergunta = $_POST['pergunta'];
                $opcao_a = $_POST['opcao_a'];
                $opcao_b = $_POST['opcao_b'];
                $opcao_c = $_POST['opcao_c'];
                $opcao_d = $_POST['opcao_d'];
                $resposta_correta = $_POST['resposta_correta'];
                $novaLinha = $id . ";multipla;" . $pergunta . ";" . $opcao_a . ";" . $opcao_b . ";" . $opcao_c . ";" . $opcao_d . ";" . $resposta_correta;
            } else {
                $pergunta = $_POST['pergunta'];
                $resposta_esperada = $_POST['resposta_esperada'];
                $novaLinha = $id . ";texto;" . $pergunta . ";" . $resposta_esperada;
            }
            
            // Ler todas as perguntas
            $todasPerguntas = [];
            $arqLeitura2 = fopen("perguntas.txt", "r");
            while(!feof($arqLeitura2)) {
                $linha = fgets($arqLeitura2);
                if(!empty(trim($linha))) {
                    $todasPerguntas[] = trim($linha);
                }
            }
            fclose($arqLeitura2);
            
            // Alterar a pergunta especifica
            foreach($todasPerguntas as $key => $linhaExistente) {
                $dadosExistente = explode(";", $linhaExistente);
                if($dadosExistente[0] == $id) {
                    $todasPerguntas[$key] = $novaLinha;
                    break;
                }
            }
            
            // Salvar arquivo
            $arqGravacao = fopen("perguntas.txt", "w");
            foreach($todasPerguntas as $linha) {
                fwrite($arqGravacao, $linha . "\n");
            }
            fclose($arqGravacao);
            
            echo "<p class='success'>Pergunta alterada com sucesso!</p>";
            header("refresh:2;url=alterarpergunta.php");
        }
        
        // Buscar pergunta para edicao
        $perguntaEdit = null;
        if(isset($_GET['id'])) {
            $idBusca = $_GET['id'];
            foreach($perguntas as $p) {
                if($p[0] == $idBusca) {
                    $perguntaEdit = $p;
                    break;
                }
            }
        }
        ?>
        
        <!-- Listar todas as perguntas -->
        <h2>Perguntas Cadastradas</h2>
        <table border="1" width="100%">
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Pergunta</th>
                <th>Acao</th>
            </tr>
            <?php foreach($perguntas as $p): ?>
            <tr>
                <td><?php echo $p[0]; ?></td>
                <td><?php echo ($p[1] == "multipla") ? "Multipla Escolha" : "Discursiva"; ?></td>
                <td><?php echo substr($p[2], 0, 80); ?>...</td>
                <td><a href="?id=<?php echo $p[0]; ?>">Editar</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <!-- Formulario de edicao -->
        <?php if($perguntaEdit): ?>
        <div class="form-box">
            <h3>Editando Pergunta ID: <?php echo $perguntaEdit[0]; ?></h3>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $perguntaEdit[0]; ?>">
                <input type="hidden" name="tipo" value="<?php echo $perguntaEdit[1]; ?>">
                
                <label>Pergunta:</label>
                <textarea name="pergunta" rows="4" required><?php echo $perguntaEdit[2]; ?></textarea><br>
                
                <?php if($perguntaEdit[1] == "multipla"): ?>
                    <!-- Campos para multipla escolha -->
                    <label>Opcao A:</label>
                    <input type="text" name="opcao_a" value="<?php echo $perguntaEdit[3]; ?>" required><br>
                    
                    <label>Opcao B:</label>
                    <input type="text" name="opcao_b" value="<?php echo $perguntaEdit[4]; ?>" required><br>
                    
                    <label>Opcao C:</label>
                    <input type="text" name="opcao_c" value="<?php echo $perguntaEdit[5]; ?>" required><br>
                    
                    <label>Opcao D:</label>
                    <input type="text" name="opcao_d" value="<?php echo $perguntaEdit[6]; ?>" required><br>
                    
                    <label>Resposta Correta:</label>
                    <select name="resposta_correta" required>
                        <option value="A" <?php echo ($perguntaEdit[7] == 'A') ? 'selected' : ''; ?>>A</option>
                        <option value="B" <?php echo ($perguntaEdit[7] == 'B') ? 'selected' : ''; ?>>B</option>
                        <option value="C" <?php echo ($perguntaEdit[7] == 'C') ? 'selected' : ''; ?>>C</option>
                        <option value="D" <?php echo ($perguntaEdit[7] == 'D') ? 'selected' : ''; ?>>D</option>
                    </select><br>
                    
                <?php else: ?>
                    <!-- Campos para pergunta discursiva -->
                    <label>Resposta Esperada (palavras-chave):</label>
                    <textarea name="resposta_esperada" rows="3" required><?php echo $perguntaEdit[3]; ?></textarea><br>
                <?php endif; ?>
                
                <button type="submit" name="alterar">Salvar Alteracoes</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>