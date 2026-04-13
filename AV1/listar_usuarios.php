<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Listar Usuários</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 10px; }
        th { background-color: #eee; }
        .btn-add { display: inline-block; margin-bottom: 15px; text-decoration: none; color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Usuários do Sistema</h1>
    <a href="cadastrar_usuario.php" class="btn-add">+ Novo Usuário</a>
    <a href="listar_perguntas.php" style="margin-left: 15px;">Gerenciar Perguntas</a>

    <table>
        <tr><th>Nome</th><th>Email</th><th>Nível</th><th>Ações</th></tr>
        <?php
        $caminho = "usuarios.txt";
        if (file_exists($caminho)) {
            $arq = fopen($caminho, "r");
            fgets($arq);
            
            while (!feof($arq)) {
                $linha = trim(fgets($arq));
                if (!empty($linha)) {
                    $dados = explode(";", $linha);
                    if (count($dados) >= 4) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($dados[0]) . "</td>";
                        echo "<td>" . htmlspecialchars($dados[1]) . "</td>";
                        echo "<td>" . htmlspecialchars($dados[3]) . "</td>";
                        echo "<td>
                                <a href='editar_usuario.php?email=" . urlencode($dados[1]) . "'>Editar</a>
                                <a href='excluir_usuario.php?email=" . urlencode($dados[1]) . "' 
                                   onclick='return confirm(\"Tem certeza?\")' 
                                   style='color:red;'>Excluir</a>
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