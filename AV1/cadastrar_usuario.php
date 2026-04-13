<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Usuário</title>
</head>
<body>
    <h2>Novo Cadastro de Usuário (Gestor)</h2>
    <form action="salvar_usuario.php" method="POST">
        <label>Nome Completo:</label><br>
        <input type="text" name="nome" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Senha:</label><br>
        <input type="password" name="senha" required><br><br>

        <label>Nível de Acesso:</label><br>
        <select name="nivel">
            <option value="gestor">Gestor</option>
            <option value="admin">Administrador</option>
        </select><br><br>

        <button type="submit">Cadastrar Usuário</button>
        <a href="listar_usuarios.php">Cancelar</a>
    </form>
</body>
</html>