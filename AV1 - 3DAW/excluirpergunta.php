<!DOCTYPE html>
<html>
<head>
    <title>Excluir Pergunta</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Excluir Pergunta e Respostas</h1>
        <a href="menu.php">← Voltar ao Menu</a>
        
        <?php
        // Processar exclusão
        if(isset($_POST['confirmar']) && isset($_POST['id'])) {
            $idExcluir = $_POST['id'];
            $todasPerguntas = [];
            $excluida = false;
            
            // Ler todas as perguntas
            $arqLeitura = fopen("perguntas.txt", "r");
            while(!feof($arqLeitura)) {
                $linha = fgets($arqLeitura);
                if(!empty(trim($linha))) {
                    $dados = explode(";", trim($linha));
                    if($dados[0] != $idExcluir) {
                        $todasPerguntas[] = trim($linha);
                    } else {
                        $excluida = true;
                        $perguntaExcluida = $dados[2];
                    }
                }
            }
            fclose($arqLeitura);
            
            if($excluida) {
                // Reescrever arquivo sem a pergunta excluída
                $arqGravacao = fopen("perguntas.txt", "w");
                foreach($todasPerguntas as $linha) {
                    fwrite($arqGravacao, $linha . "\n");
                }
                fclose($arqGravacao);
                
                echo "<p class='success'>✅ Pergunta excluída com sucesso!<br>";
                echo "<strong>Pergunta removida:</strong> " . $perguntaExcluida . "</p>";
            } else {
                echo "<p class='error'>❌ Pergunta com ID " . $idExcluir . " não encontrada!</p>";
            }
        }
        
        // Listar perguntas para exclusão
        $perguntas = [];
        $arqLista = fopen("perguntas.txt", "r");
        while(!feof($arqLista)) {
            $linha = fgets($arqLista);
            if(!empty(trim($linha))) {
                $dados = explode(";", trim($linha));
                $perguntas[] = [
                    'id' => $dados[0],
                    'tipo' => $dados[1],
                    'pergunta' => $dados[2]
                ];
            }
        }
        fclose($arqLista);
        ?>
        
        <?php if(count($perguntas) > 0): ?>
            <div class="form-box">
                <h3>Selecione a Pergunta para Excluir</h3>
                <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta pergunta? Esta ação não pode ser desfeita!');">
                    <label>Escolha a pergunta:</label>
                    <select name="id" required>
                        <option value="">Selecione...</option>
                        <?php foreach($perguntas as $p): ?>
                        <option value="<?php echo $p['id']; ?>">
                            ID <?php echo $p['id']; ?> - 
                            <?php echo ($p['tipo'] == 'multipla') ? '[Múltipla]' : '[Discursiva]'; ?> 
                            - <?php echo substr($p['pergunta'], 0, 100); ?>...
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <br><br>
                    <button type="submit" name="confirmar" style="background-color: #dc3545;">🗑️ Excluir Pergunta</button>
                </form>
            </div>
            
            <h3>Todas as Perguntas Cadastradas</h3>
            <table border="1" width="100%">
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Pergunta</th>
                </tr>
                <?php foreach($perguntas as $p): ?>
                <tr>
                    <td><?php echo $p['id']; ?></td>
                    <td><?php echo ($p['tipo'] == 'multipla') ? 'Múltipla Escolha' : 'Discursiva'; ?></td>
                    <td><?php echo $p['pergunta']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p class="error">Nenhuma pergunta cadastrada para excluir.</p>
        <?php endif; ?>
    </div>
</body>
</html>