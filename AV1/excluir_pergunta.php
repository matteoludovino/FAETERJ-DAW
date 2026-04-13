<?php
$perguntaExcluir = $_GET['pergunta'] ?? null;

if ($perguntaExcluir) {
    $caminho = "perguntas.txt";
    if (file_exists($caminho)) {
        $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $novasLinhas = [];

        foreach ($linhas as $linha) {
            $dados = explode(";", $linha);
            if ($dados[1] !== $perguntaExcluir) {
                $novasLinhas[] = $linha;
            }
        }

        file_put_contents($caminho, implode(PHP_EOL, $novasLinhas) . PHP_EOL);
    }
}

header("Location: listar_perguntas.php");
exit();
?>