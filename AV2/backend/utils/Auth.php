<?php

class Auth
{
    private static ?array $currentUser = null;

    /**
     * @return array|null
     */
    public static function check(): ?array
    {
        if (self::$currentUser !== null) {
            return self::$currentUser;
        }

        $authHeader = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($authHeader, 7));
        if (empty($token)) {
            return null;
        }

        $db  = getDB();
        $sql = "
            SELECT u.id, u.nome, u.email, u.tipo, u.ativo, t.expires_at
            FROM tokens t
            JOIN usuarios u ON u.id = t.usuario_id
            WHERE t.token = ?
              AND t.expires_at > NOW()
              AND u.ativo = 1
            LIMIT 1
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            return null;
        }

        if ($user['tipo'] === 'cliente') {
            $stmt2 = $db->prepare("SELECT id FROM clientes WHERE usuario_id = ? LIMIT 1");
            $stmt2->execute([$user['id']]);
            $cliente = $stmt2->fetch();
            $user['cliente_id'] = $cliente['id'] ?? null;
        }

        self::$currentUser = $user;
        return $user;
    }

    public static function requireAuth(): array
    {
        $user = self::check();
        if (!$user) {
            Response::error('Autenticação necessária. Faça login.', 401);
            exit;
        }
        return $user;
    }

    public static function requireAdmin(): array
    {
        $user = self::requireAuth();
        if (!in_array($user['tipo'], ['admin', 'funcionario'])) {
            Response::error('Acesso negado. Permissão insuficiente.', 403);
            exit;
        }
        return $user;
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function login(string $email, string $senha): array
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($senha, $user['senha'])) {
            Response::error('E-mail ou senha inválidos.', 401);
            exit;
        }

        $db->prepare("DELETE FROM tokens WHERE usuario_id = ? AND expires_at < NOW()")->execute([$user['id']]);

        $token   = self::generateToken();
        $expires = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY_HOURS * 3600);

        $db->prepare("INSERT INTO tokens (usuario_id, token, expires_at) VALUES (?, ?, ?)")
           ->execute([$user['id'], $token, $expires]);

        $clienteId = null;
        if ($user['tipo'] === 'cliente') {
            $stmt2 = $db->prepare("SELECT id FROM clientes WHERE usuario_id = ? LIMIT 1");
            $stmt2->execute([$user['id']]);
            $c = $stmt2->fetch();
            $clienteId = $c['id'] ?? null;
        }

        return [
            'token'      => $token,
            'expires_at' => $expires,
            'user' => [
                'id'         => $user['id'],
                'nome'       => $user['nome'],
                'email'      => $user['email'],
                'tipo'       => $user['tipo'],
                'cliente_id' => $clienteId,
            ]
        ];
    }

    public static function logout(): void
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = trim(substr($authHeader, 7));
            getDB()->prepare("DELETE FROM tokens WHERE token = ?")->execute([$token]);
        }
    }
}
