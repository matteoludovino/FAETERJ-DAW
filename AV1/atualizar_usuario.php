<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_antigo = $_POST['email_antigo'];
    $novo_nome = $_POST['nome'];
    $novo_email = $_POST['email'];
    $nova_senha = $_POST['senha'];
    $novo_nivel = $_POST['nivel'];

    $linhas = file("usuarios.txt");
    $conteudo_final = "";
    $cabecalho = array_shift($linhas);

    $conteudo_final .= "nome;email;senha;nivel" . PHP_EOL;

    foreach ($linhas as $linha) {
        $linha_limpa = trim($linha);
        if (empty($linha_limpa)) continue;

        $dados = explode(";", $linha_limpa);
        if ($dados[1] == $email_antigo) {
            $conteudo_final .= "$novo_nome;$novo_email;$nova_senha;$novo_nivel" . PHP_EOL;
        } else {
            $conteudo_final .= $linha_limpa . PHP_EOL;
        }
    }

    if (file_put_contents("usuarios.txt", $conteudo_final)) {
        header("Location: listar_usuarios.php");
        exit;
    } else {
        echo "Erro ao atualizar usuário.";
    }
}
?>