<?php
$msg = "";
$alunos = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['excluir'])) {
    $matriculaExcluir = $_POST["matricula"];
    
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
    
    $alunosAtualizados = array();
    $encontrado = false;
    
    foreach ($alunos as $aluno) {
        if ($aluno['matricula'] != $matriculaExcluir) {
            $alunosAtualizados[] = $aluno;
        } else {
            $encontrado = true;
            $nomeExcluido = $aluno['nome'];
        }
    }
    
    if ($encontrado) {
        $arqAluno = fopen("alunos.txt", "w") or die("erro ao abrir arquivo");
        fwrite($arqAluno, "matricula;nome;email\n");
        
        foreach ($alunosAtualizados as $aluno) {
            $linha = $aluno['matricula'] . ";" . $aluno['nome'] . ";" . $aluno['email'] . "\n";
            fwrite($arqAluno, $linha);
        }
        fclose($arqAluno);
        
        $msg = "Aluno '$nomeExcluido' (matrícula: $matriculaExcluir) excluído com sucesso!";
    } else {
        $msg = "Aluno com matrícula $matriculaExcluir não encontrado!";
    }
}

$arqAluno = fopen("alunos.txt", "r") or die("erro ao abrir arquivo");
$cabecalho = fgets($arqAluno);
$listaAlunos = array();

while (!feof($arqAluno)) {
    $linha = fgets($arqAluno);
    if (trim($linha) != "") {
        $colunaDados = explode(";", $linha);
        $listaAlunos[] = array(
            'matricula' => $colunaDados[0],
            'nome' => $colunaDados[1],
            'email' => trim($colunaDados[2])
        );
    }
}
fclose($arqAluno);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Excluir Aluno - Versão Simples</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn-excluir {
            background-color: #ff4444;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }
        .btn-excluir:hover {
            background-color: #cc0000;
        }
        .mensagem {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .sucesso {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
<h1>Excluir Aluno</h1>

<?php if (!empty($msg)): ?>
    <div class="mensagem <?php echo strpos($msg, 'sucesso') !== false ? 'sucesso' : (strpos($msg, 'encontrado') !== false ? 'erro' : 'sucesso'); ?>">
        <?php echo $msg; ?>
    </div>
<?php endif; ?>

<h2>Lista de Alunos Cadastrados</h2>
<table>
    <tr>
        <th>Matrícula</th>
        <th>Nome</th>
        <th>Email</th>
        <th>Ação</th>
    </tr>
    <?php foreach ($listaAlunos as $aluno): ?>
    <tr>
        <td><?php echo $aluno['matricula']; ?></td>
        <td><?php echo $aluno['nome']; ?></td>
        <td><?php echo $aluno['email']; ?></td>
        <td>
            <form action="ex07_ExcluirAluno_Simples.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir o aluno <?php echo $aluno['nome']; ?>?');">
                <input type="hidden" name="matricula" value="<?php echo $aluno['matricula']; ?>">
                <input type="submit" name="excluir" value="Excluir" class="btn-excluir">
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<br>
<a href="ex05_IncluirAluno.php">Incluir novo aluno</a>
<br>
<a href="ex06_AlterarAluno.php">Alterar dados do aluno</a>
</body>
</html>