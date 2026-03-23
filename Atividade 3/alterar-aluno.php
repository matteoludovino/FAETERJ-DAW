<?php
$msg = "";
$alunoEncontrado = false;
$matriculaBusca = "";
$alunoAtual = null;

if (isset($_GET['matricula']) && empty($_POST)) {
    $matriculaBusca = $_GET['matricula'];
    $arqAluno = fopen("alunos.txt", "r") or die("erro ao abrir arquivo");
    
    while (!feof($arqAluno)) {
        $linha = fgets($arqAluno);
        if (trim($linha) != "") {
            $colunaDados = explode(";", $linha);
            if (isset($colunaDados[0]) && $colunaDados[0] == $matriculaBusca) {
                $alunoEncontrado = true;
                $alunoAtual = array(
                    'matricula' => $colunaDados[0],
                    'nome' => $colunaDados[1],
                    'email' => trim($colunaDados[2])
                );
                break;
            }
        }
    }
    fclose($arqAluno);
    
    if (!$alunoEncontrado) {
        $msg = "Aluno não encontrado!";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar'])) {
    $matriculaOriginal = $_POST["matricula_original"];
    $novaMatricula = $_POST["matricula"];
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    
    $alunos = array();
    $arqAluno = fopen("alunos.txt", "r") or die("erro ao abrir arquivo");
    
    $cabecalho = fgets($arqAluno);
    
    while (!feof($arqAluno)) {
        $linha = fgets($arqAluno);
        if (trim($linha) != "") {
            $colunaDados = explode(";", $linha);
            $alunos[] = array(
                'matricula' => $colunaDados[0],
                'nome' => $colunaDados[1],
                'email' => trim($colunaDados[2])
            );
        }
    }
    fclose($arqAluno);
    
    $encontrado = false;
    for ($i = 0; $i < count($alunos); $i++) {
        if ($alunos[$i]['matricula'] == $matriculaOriginal) {
            $alunos[$i]['matricula'] = $novaMatricula;
            $alunos[$i]['nome'] = $nome;
            $alunos[$i]['email'] = $email;
            $encontrado = true;
            break;
        }
    }
    
    if ($encontrado) {
        $arqAluno = fopen("alunos.txt", "w") or die("erro ao abrir arquivo");
        fwrite($arqAluno, "matricula;nome;email\n");
        
        foreach ($alunos as $aluno) {
            $linha = $aluno['matricula'] . ";" . $aluno['nome'] . ";" . $aluno['email'] . "\n";
            fwrite($arqAluno, $linha);
        }
        fclose($arqAluno);
        
        $msg = "Aluno alterado com sucesso!";
    } else {
        $msg = "Erro ao alterar aluno!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Alterar Aluno</title>
</head>
<body>
<h1>Alterar Dados do Aluno</h1>

<?php if ($alunoEncontrado && $alunoAtual != null): ?>
    <form action="ex06_AlterarAluno.php" method="POST">
        <input type="hidden" name="matricula_original" value="<?php echo $alunoAtual['matricula']; ?>">
        
        Matrícula: <input type="text" name="matricula" value="<?php echo $alunoAtual['matricula']; ?>">
        <br><br>
        Nome: <input type="text" name="nome" value="<?php echo $alunoAtual['nome']; ?>">
        <br><br>
        Email: <input type="text" name="email" value="<?php echo $alunoAtual['email']; ?>">
        <br><br>
        <input type="submit" name="alterar" value="Alterar Aluno">
    </form>
<?php elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_GET['matricula'])): ?>
    <form action="ex06_AlterarAluno.php" method="GET">
        Digite a matrícula do aluno para alterar: 
        <input type="text" name="matricula">
        <br><br>
        <input type="submit" value="Buscar Aluno">
    </form>
<?php endif; ?>

<p><?php echo $msg; ?></p>
<br>
<a href="ex04_listarTodosAlunos.php">Listar todos os alunos</a>
<br>
<a href="ex05_IncluirAluno.php">Incluir novo aluno</a>
</body>
</html>