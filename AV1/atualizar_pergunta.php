<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pergunta_antiga = $_POST['pergunta_antiga'];
    $tipo = $_POST['tipo'];
    $nova_pergunta = $_POST['pergunta'];

    $linhas = file("perguntas.txt");
    $conteudo_final = "";

    foreach ($linhas as $linha) {
        $linha_limpa = trim($linha);
        if (empty($linha_limpa)) continue;

        $dados = explode(";", $linha_limpa);
        if ($dados[1] == $pergunta_antiga) {
            if ($tipo == 'multipla') {
                $opcao1 = $_POST['opcao1'];
                $opcao2 = $_POST['opcao2'];
                $opcao3 = $_POST['opcao3'];
                $opcao4 = $_POST['opcao4'];
                $resposta_correta = $_POST['resposta_correta'];
                $conteudo_final .= "$tipo;$nova_pergunta;$opcao1;$opcao2;$opcao3;$opcao4;$resposta_correta" . PHP_EOL;
            } else {
                $conteudo_final .= "$tipo;$nova_pergunta;;" . PHP_EOL;
            }
        } else {
            $conteudo_final .= $linha_limpa . PHP_EOL;
        }
    }

    if (file_put_contents("perguntas.txt", $conteudo_final)) {
        header("Location: listar_perguntas.php");
        exit;
    } else {
        echo "Erro ao atualizar.";
    }
}
?>