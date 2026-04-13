<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Pergunta</title>
</head>
<body>
    <h2>Criar Nova Pergunta</h2>
    <form action="salvar_pergunta.php" method="POST">
        <label>Tipo de Pergunta:</label><br>
        <select name="tipo" id="tipo" required onchange="toggleOpcoes()">
            <option value="multipla">Múltipla Escolha</option>
            <option value="texto">Texto Livre</option>
        </select><br><br>

        <label>Pergunta:</label><br>
        <input type="text" name="pergunta" size="80" required><br><br>

        <div id="opcoes_multipla">
            <label>Opção 1:</label><br>
            <input type="text" name="opcao1"><br>
            <label>Opção 2:</label><br>
            <input type="text" name="opcao2"><br>
            <label>Opção 3:</label><br>
            <input type="text" name="opcao3"><br>
            <label>Opção 4:</label><br>
            <input type="text" name="opcao4"><br>
            <label>Resposta Correta (exatamente como escrita acima):</label><br>
            <input type="text" name="resposta_correta"><br><br>
        </div>

        <button type="submit">Salvar Pergunta</button>
        <a href="listar_perguntas.php">Cancelar</a>
    </form>

    <script>
        function toggleOpcoes() {
            var tipo = document.getElementById('tipo').value;
            var divMultipla = document.getElementById('opcoes_multipla');
            if (tipo === 'multipla') {
                divMultipla.style.display = 'block';
            } else {
                divMultipla.style.display = 'none';
            }
        }
        toggleOpcoes();
    </script>
</body>
</html>