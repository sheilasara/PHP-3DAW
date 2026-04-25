<!DOCTYPE html>
<html>
<head>
    <title>Listar uma Pergunta</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Buscar Pergunta Específica</h1>
        <a href="menu.php">← Voltar ao Menu</a>
        
        <div class="form-box">
            <form method="GET">
                <label>ID da Pergunta:</label>
                <input type="number" name="id" required min="1">
                <button type="submit">Buscar</button>
            </form>
        </div>
        
        <?php
        if(isset($_GET['id']) && !empty($_GET['id'])) {
            $idBusca = $_GET['id'];
            $encontrada = false;
            
            $arqQuest = fopen("perguntas.txt", "r") or die("Erro ao abrir arquivo");
            
            while(!feof($arqQuest)) {
                $linha = fgets($arqQuest);
                if(!empty(trim($linha))) {
                    $colunaDados = explode(";", trim($linha));
                    
                    if($colunaDados[0] == $idBusca) {
                        $encontrada = true;
                        ?>
                        
                        <div class="pergunta-detalhe">
                            <h2>📋 Detalhes da Pergunta</h2>
                            <p><strong>ID:</strong> <?php echo $colunaDados[0]; ?></p>
                            <p><strong>Tipo:</strong> <?php echo ($colunaDados[1] == "multipla") ? "Múltipla Escolha" : "Discursiva"; ?></p>
                            <p><strong>Pergunta:</strong></p>
                            <div class="pergunta-texto"><?php echo nl2br($colunaDados[2]); ?></div>
                            
                            <?php if($colunaDados[1] == "multipla"): ?>
                                <p><strong>Opções de Resposta:</strong></p>
                                <ul class="opcoes">
                                    <li><strong>A)</strong> <?php echo $colunaDados[3]; ?></li>
                                    <li><strong>B)</strong> <?php echo $colunaDados[4]; ?></li>
                                    <li><strong>C)</strong> <?php echo $colunaDados[5]; ?></li>
                                    <li><strong>D)</strong> <?php echo $colunaDados[6]; ?></li>
                                </ul>
                                <p class="resposta-correta"><strong> Resposta Correta:</strong> <?php echo $colunaDados[7]; ?></p>
                            <?php else: ?>
                                <p><strong>Resposta Esperada (palavras-chave):</strong></p>
                                <div class="resposta-esperada"><?php echo nl2br($colunaDados[3]); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <?php
                        break;
                    }
                }
            }
            
            fclose($arqQuest);
            
            if(!$encontrada) {
                echo "<p class='error'> Pergunta com ID " . $idBusca . " não encontrada!</p>";
            }
        }
        ?>
    </div>
</body>
</html>