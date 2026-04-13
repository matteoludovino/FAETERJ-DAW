<?php
$emailExcluir = $_GET['email'] ?? null;

if ($emailExcluir) {
    $caminho = "usuarios.txt";
    
    if (file_exists($caminho)) {
        $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $cabecalho = array_shift($linhas);
        $novasLinhas = [$cabecalho];

        foreach ($linhas as $linha) {
            $dados = explode(";", $linha);
            if ($dados[1] !== $emailExcluir) {
                $novasLinhas[] = $linha;
            }
        }

        file_put_contents($caminho, implode(PHP_EOL, $novasLinhas) . PHP_EOL);
    }
}

header("Location: listar_usuarios.php");
exit();
?>