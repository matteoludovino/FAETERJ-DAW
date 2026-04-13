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
<head><title>Ver Pergunta</title></head>
<body>
    <h2>Pergunta</h2>
    <p><strong>Tipo:</strong> <?= htmlspecialchars($pergunta_dados[0]) ?></p>
    <p><strong>Pergunta:</strong> <?= htmlspecialchars($pergunta_dados[1]) ?></p>

    <?php if ($pergunta_dados[0] == 'multipla'): ?>
        <p><strong>Opções:</strong></p>
        <ul>
            <li><?= htmlspecialchars($pergunta_dados[2]) ?></li>
            <li><?= htmlspecialchars($pergunta_dados[3]) ?></li>
            <li><?= htmlspecialchars($pergunta_dados[4]) ?></li>
            <li><?= htmlspecialchars($pergunta_dados[5]) ?></li>
        </ul>
        <p><strong>Resposta Correta:</strong> <?= htmlspecialchars($pergunta_dados[6]) ?></p>
    <?php else: ?>
        <p><strong>Resposta:</strong> Texto livre (avaliado manualmente)</p>
    <?php endif; ?>

    <a href="listar_perguntas.php">Voltar</a>
</body>
</html>