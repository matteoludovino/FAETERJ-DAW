<?php
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sigla   = $_POST["sigla"];
    $nome    = $_POST["nome"];
    $carga   = $_POST["carga"];

    if (!file_exists("disciplina.txt")) {
        $arqDisc = fopen("disciplina.txt", "w") or die("Erro ao criar arquivo");
        fwrite($arqDisc, "sigla;nome;carga\n");
        fclose($arqDisc);
    }

    $arqDisc = fopen("disciplina.txt", "a") or die("Erro ao abrir arquivo");
    $linha = $sigla . ";" . $nome . ";" . $carga . "\n";
    fwrite($arqDisc, $linha);
    fclose($arqDisc);

    $msg = "Disciplina incluída com sucesso!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Incluir Disciplina</title>
</head>
<body>
<h1>Incluir Nova Disciplina</h1>
<form action="incluir_disciplina.php" method="POST">
    Sigla: <input type="text" name="sigla" required>
    <br><br>
    Nome: <input type="text" name="nome" required>
    <br><br>
    Carga Horária: <input type="text" name="carga" required>
    <br><br>
    <input type="submit" value="Incluir Disciplina">
</form>
<p><?php echo $msg; ?></p>
</body>
</html>