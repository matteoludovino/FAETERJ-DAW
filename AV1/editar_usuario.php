<?php
$email_alvo = $_GET['email'] ?? '';
$usuario_encontrado = null;

if (!empty($email_alvo)) {
    $arq = fopen("usuarios.txt", "r");
    fgets($arq);
    
    while (!feof($arq)) {
        $linha = trim(fgets($arq));
        if ($linha) {
            $dados = explode(";", $linha);
            if ($dados[1] == $email_alvo) {
                $usuario_encontrado = $dados;
                break;
            }
        }
    }
    fclose($arq);
}

if (!$usuario_encontrado) {
    die("Usuário não encontrado! <a href='listar_usuarios.php'>Voltar</a>");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head><title>Editar Usuário</title></head>
<body>
    <h2>Editar Usuário</h2>
    <form action="atualizar_usuario.php" method="POST">
        <input type="hidden" name="email_antigo" value="<?= htmlspecialchars($usuario_encontrado[1]) ?>">
        
        <label>Nome:</label><br>
        <input type="text" name="nome" value="<?= htmlspecialchars($usuario_encontrado[0]) ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($usuario_encontrado[1]) ?>" required><br><br>

        <label>Senha:</label><br>
        <input type="password" name="senha" value="<?= htmlspecialchars($usuario_encontrado[2]) ?>" required><br><br>

        <label>Nível:</label><br>
        <select name="nivel">
            <option value="gestor" <?= $usuario_encontrado[3] == 'gestor' ? 'selected' : '' ?>>Gestor</option>
            <option value="admin" <?= $usuario_encontrado[3] == 'admin' ? 'selected' : '' ?>>Administrador</option>
        </select><br><br>

        <button type="submit">Salvar Alterações</button>
        <a href="listar_usuarios.php">Cancelar</a>
    </form>
</body>
</html>