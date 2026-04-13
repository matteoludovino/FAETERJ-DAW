<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Listar Perguntas</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 10px; }
        th { background-color: #eee; }
        .btn-add { display: inline-block; margin-bottom: 15px; text-decoration: none; color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Perguntas do Jogo</h1>
    <a href="cadastrar_pergunta.php" class="btn-add">+ Criar Nova Pergunta</a>

    <table>
        <tr><th>Tipo</th><th>Pergunta</th><th>Ações</th></tr>
        <?php
        $caminho = "perguntas.txt";
        if (file_exists($caminho)) {
            $arq = fopen($caminho, "r");
            while (!feof($arq)) {
                $linha = trim(fgets($arq));
                if (!empty($linha)) {
                    $dados = explode(";", $linha);
                    if (count($dados) >= 2) {
                        $tipo = $dados[0];
                        $pergunta = $dados[1];
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($tipo) . "</td>";
                        echo "<td>" . htmlspecialchars($pergunta) . "</td>";
                        echo "<td>
                                <a href='visualizar_pergunta.php?pergunta=" . urlencode($pergunta) . "'>Ver</a>
                                <a href='editar_pergunta.php?pergunta=" . urlencode($pergunta) . "'>Editar</a>
                                <a href='excluir_pergunta.php?pergunta=" . urlencode($pergunta) . "' onclick='return confirm(\"Tem certeza?\")' style='color:red;'>Excluir</a>
                              </td>";
                        echo "</tr>";
                    }
                }
            }
            fclose($arq);
        }
        ?>
    </table>
</body>
</html>