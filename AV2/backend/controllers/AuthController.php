<?php
class AuthController
{
    public function login(array $body): void
    {
        $email = trim($body['email'] ?? '');
        $senha = $body['senha'] ?? '';

        if (!$email || !$senha) {
            Response::error('E-mail e senha são obrigatórios.', 422);
        }

        $result = Auth::login($email, $senha);
        Response::success($result, 'Login realizado com sucesso.');
    }

    public function register(array $body): void
    {
        $required = ['nome', 'email', 'senha', 'cpf', 'cnh', 'telefone'];
        $errors   = [];

        foreach ($required as $field) {
            if (empty($body[$field])) {
                $errors[] = "Campo '$field' é obrigatório.";
            }
        }

        if (!empty($errors)) {
            Response::error('Dados incompletos.', 422, $errors);
        }

        if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            Response::error('E-mail inválido.', 422);
        }

        if (strlen($body['senha']) < 6) {
            Response::error('Senha deve ter pelo menos 6 caracteres.', 422);
        }

        $db = getDB();

        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$body['email']]);
        if ($stmt->fetch()) {
            Response::error('E-mail já cadastrado.', 409);
        }

        $stmt2 = $db->prepare("SELECT id FROM clientes WHERE cpf = ? LIMIT 1");
        $stmt2->execute([$body['cpf']]);
        if ($stmt2->fetch()) {
            Response::error('CPF já cadastrado.', 409);
        }

        $db->beginTransaction();
        try {
            $hash = password_hash($body['senha'], PASSWORD_BCRYPT);
            $db->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'cliente')")
               ->execute([$body['nome'], $body['email'], $hash]);
            $userId = $db->lastInsertId();

            $db->prepare("
                INSERT INTO clientes
                    (usuario_id, cpf, telefone, data_nascimento, endereco, cidade, estado, cep, cnh, validade_cnh)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $userId,
                $body['cpf'],
                $body['telefone'] ?? null,
                $body['data_nascimento'] ?? null,
                $body['endereco'] ?? null,
                $body['cidade'] ?? null,
                $body['estado'] ?? null,
                $body['cep'] ?? null,
                $body['cnh'],
                $body['validade_cnh'] ?? null,
            ]);

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Response::error('Erro ao registrar usuário: ' . $e->getMessage(), 500);
        }

        $result = Auth::login($body['email'], $body['senha']);
        Response::created($result, 'Cadastro realizado com sucesso.');
    }

    public function logout(): void
    {
        Auth::requireAuth();
        Auth::logout();
        Response::success(null, 'Logout realizado.');
    }

    public function me(): void
    {
        $user = Auth::requireAuth();
        unset($user['senha']);

        if ($user['tipo'] === 'cliente') {
            $db   = getDB();
            $stmt = $db->prepare("SELECT * FROM clientes WHERE usuario_id = ? LIMIT 1");
            $stmt->execute([$user['id']]);
            $cliente = $stmt->fetch();
            if ($cliente) {
                $user['perfil_cliente'] = $cliente;
            }
        }

        Response::success($user, 'Usuário autenticado.');
    }
}
