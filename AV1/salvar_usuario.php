<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $nivel = $_POST['nivel'];

    $linha = $nome . ";" . $email . ";" . $senha . ";" . $nivel . PHP_EOL;

    if (!file_exists("usuarios.txt")) {
        $arquivo = fopen("usuarios.txt", "w");
        fwrite($arquivo, "nome;email;senha;nivel\n");
        fclose($arquivo);
    }

    $arquivo = fopen("usuarios.txt", "a");
    if (fwrite($arquivo, $linha)) {
        fclose($arquivo);
        header("Location: listar_usuarios.php");
        exit;
    } else {
        echo "Erro ao salvar usuário.";
        fclose($arquivo);
    }
}
?>