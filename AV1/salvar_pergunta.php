<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo = $_POST['tipo'];
    $pergunta = $_POST['pergunta'];

    if ($tipo == 'multipla') {
        $opcao1 = $_POST['opcao1'];
        $opcao2 = $_POST['opcao2'];
        $opcao3 = $_POST['opcao3'];
        $opcao4 = $_POST['opcao4'];
        $resposta_correta = $_POST['resposta_correta'];
        $linha = "$tipo;$pergunta;$opcao1;$opcao2;$opcao3;$opcao4;$resposta_correta" . PHP_EOL;
    } else {
        $linha = "$tipo;$pergunta;;" . PHP_EOL;
    }

    $arquivo = fopen("perguntas.txt", "a");
    if (fwrite($arquivo, $linha)) {
        fclose($arquivo);
        header("Location: listar_perguntas.php");
        exit;
    } else {
        echo "Erro ao salvar.";
        fclose($arquivo);
    }
}
?>