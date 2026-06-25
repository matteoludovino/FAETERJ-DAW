<?php
class ClienteController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    public function index(array $query = []): void
    {
        Auth::requireAdmin();

        $where  = [];
        $params = [];

        if (!empty($query['search'])) {
            $where[]  = '(u.nome LIKE ? OR u.email LIKE ? OR c.cpf LIKE ?)';
            $s = '%' . $query['search'] . '%';
            $params = array_merge($params, [$s, $s, $s]);
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT c.*, u.nome, u.email, u.ativo, u.created_at AS usuario_desde,
                   COUNT(r.id) AS total_reservas
            FROM clientes c
            JOIN usuarios u ON u.id = c.usuario_id
            LEFT JOIN reservas r ON r.cliente_id = c.id
            $whereSQL
            GROUP BY c.id
            ORDER BY u.nome
        ");
        $stmt->execute($params);
        Response::success($stmt->fetchAll());
    }

    public function show(int $id): void
    {
        $user = Auth::requireAuth();

        if ($user['tipo'] === 'cliente' && $user['cliente_id'] !== $id) {
            Response::error('Acesso negado.', 403);
        }

        $stmt = $this->db->prepare("
            SELECT c.*, u.nome, u.email, u.tipo, u.ativo
            FROM clientes c
            JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch();

        if (!$cliente) Response::error('Cliente não encontrado.', 404);
        Response::success($cliente);
    }

    public function store(array $body): void
    {
        Auth::requireAdmin();
        $required = ['nome','email','senha','cpf','cnh'];
        $errors   = [];
        foreach ($required as $f) {
            if (empty($body[$f])) $errors[] = "Campo '$f' obrigatório.";
        }
        if (!empty($errors)) Response::error('Dados incompletos.', 422, $errors);

        $db = $this->db;
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$body['email']]);
        if ($stmt->fetch()) Response::error('E-mail já cadastrado.', 409);

        $db->beginTransaction();
        try {
            $hash = password_hash($body['senha'], PASSWORD_BCRYPT);
            $db->prepare("INSERT INTO usuarios (nome,email,senha,tipo) VALUES (?,?,?,'cliente')")
               ->execute([$body['nome'], $body['email'], $hash]);
            $userId = $db->lastInsertId();

            $db->prepare("
                INSERT INTO clientes (usuario_id,cpf,telefone,data_nascimento,cidade,estado,cnh,validade_cnh)
                VALUES (?,?,?,?,?,?,?,?)
            ")->execute([
                $userId,
                $body['cpf'],
                $body['telefone']       ?? null,
                $body['data_nascimento'] ?? null,
                $body['cidade']         ?? null,
                $body['estado']         ?? null,
                $body['cnh'],
                $body['validade_cnh']   ?? null,
            ]);
            $clienteId = $db->lastInsertId();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            Response::error('Erro ao criar cliente: ' . $e->getMessage(), 500);
        }

        $this->show($clienteId);
    }

    public function update(int $id, array $body): void
    {
        $user = Auth::requireAuth();
        if ($user['tipo'] === 'cliente' && $user['cliente_id'] !== $id) {
            Response::error('Acesso negado.', 403);
        }

        $stmt = $this->db->prepare("SELECT usuario_id FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        $c = $stmt->fetch();
        if (!$c) Response::error('Cliente não encontrado.', 404);

        if (!empty($body['nome'])) {
            $this->db->prepare("UPDATE usuarios SET nome = ? WHERE id = ?")
                     ->execute([$body['nome'], $c['usuario_id']]);
        }

        $fields = [];
        $params = [];
        $updatable = ['telefone','data_nascimento','endereco','cidade','estado','cep','cnh','validade_cnh'];
        foreach ($updatable as $f) {
            if (isset($body[$f])) {
                $fields[] = "$f = ?";
                $params[] = $body[$f];
            }
        }
        if (!empty($fields)) {
            $params[] = $id;
            $this->db->prepare("UPDATE clientes SET " . implode(', ', $fields) . " WHERE id = ?")
                     ->execute($params);
        }

        $this->show($id);
    }

    public function destroy(int $id): void
    {
        Auth::requireAdmin();
        $stmt = $this->db->prepare("SELECT usuario_id FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        $c = $stmt->fetch();
        if (!$c) Response::error('Cliente não encontrado.', 404);

        $this->db->prepare("UPDATE usuarios SET ativo = 0 WHERE id = ?")->execute([$c['usuario_id']]);
        Response::success(null, 'Cliente desativado.');
    }
}
