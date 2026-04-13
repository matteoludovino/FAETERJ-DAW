<?php
$pergunta_alvo = $_GET['pergunta'] ?? '';
$pergunta_dados = null;

if (!empty($pergunta_alvo)) {
    $arq = fopen("perguntas.txt", "r");
    while (!feof($arq)) {
        $linha = trim(fgets($arq));
        if ($linha) {
            $dados = explode(";", $linha);
            if ($dados[1] == $pergunta_alvo) {
                $pergunta_dados = $dados;
                break;
            }
        }
    }
    fclose($arq);
}

if (!$pergunta_dados) {
    die("Pergunta não encontrada! <a href='listar_perguntas.php'>Voltar</a>");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head><title>Editar Pergunta</title></head>
<body>
    <h2>Editar Pergunta</h2>
    <form action="atualizar_pergunta.php" method="POST">
        <input type="hidden" name="pergunta_antiga" value="<?= htmlspecialchars($pergunta_dados[1]) ?>">
        <input type="hidden" name="tipo" value="<?= htmlspecialchars($pergunta_dados[0]) ?>">

        <label>Pergunta:</label><br>
        <input type="text" name="pergunta" value="<?= htmlspecialchars($pergunta_dados[1]) ?>" size="80" required><br><br>

        <?php if ($pergunta_dados[0] == 'multipla'): ?>
            <label>Opção 1:</label><br>
            <input type="text" name="opcao1" value="<?= htmlspecialchars($pergunta_dados[2]) ?>"><br>
            <label>Opção 2:</label><br>
            <input type="text" name="opcao2" value="<?= htmlspecialchars($pergunta_dados[3]) ?>"><br>
            <label>Opção 3:</label><br>
            <input type="text" name="opcao3" value="<?= htmlspecialchars($pergunta_dados[4]) ?>"><br>
            <label>Opção 4:</label><br>
            <input type="text" name="opcao4" value="<?= htmlspecialchars($pergunta_dados[5]) ?>"><br>
            <label>Resposta Correta:</label><br>
            <input type="text" name="resposta_correta" value="<?= htmlspecialchars($pergunta_dados[6]) ?>"><br><br>
        <?php endif; ?>

        <button type="submit">Salvar Alterações</button>
        <a href="listar_perguntas.php">Cancelar</a>
    </form>
</body>
</html>