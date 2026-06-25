<?php
class LojaController
{
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function index(array $query = []): void
    {
        $where  = ['l.ativa = 1'];
        $params = [];

        if (!empty($query['cidade'])) {
            $where[]  = 'l.cidade = ?';
            $params[] = $query['cidade'];
        }
        if (!empty($query['tipo'])) {
            $where[]  = 'l.tipo = ?';
            $params[] = $query['tipo'];
        }

        $whereSQL = 'WHERE ' . implode(' AND ', $where);
        $stmt = $this->db->prepare("
            SELECT l.*,
                   COUNT(DISTINCT v.id)  AS total_veiculos,
                   SUM(v.status = 'livre') AS veiculos_livres
            FROM lojas l
            LEFT JOIN veiculos v ON v.loja_id = l.id AND v.ativo = 1
            $whereSQL
            GROUP BY l.id
            ORDER BY l.cidade, l.nome
        ");
        $stmt->execute($params);
        Response::success($stmt->fetchAll());
    }

    public function show(int $id): void
    {
        $stmt = $this->db->prepare("SELECT * FROM lojas WHERE id = ?");
        $stmt->execute([$id]);
        $loja = $stmt->fetch();
        if (!$loja) Response::error('Loja não encontrada.', 404);

        $stmt2 = $this->db->prepare("
            SELECT * FROM veiculos WHERE loja_id = ? AND ativo = 1 ORDER BY marca, modelo
        ");
        $stmt2->execute([$id]);
        $loja['veiculos'] = $stmt2->fetchAll();

        Response::success($loja);
    }

    public function store(array $body): void
    {
        Auth::requireAdmin();
        $required = ['nome','endereco','cidade','estado','tipo'];
        $errors   = [];
        foreach ($required as $f) {
            if (empty($body[$f])) $errors[] = "Campo '$f' obrigatório.";
        }
        if (!empty($errors)) Response::error('Dados incompletos.', 422, $errors);

        $this->db->prepare("
            INSERT INTO lojas (nome,endereco,cidade,estado,cep,telefone,tipo,latitude,longitude)
            VALUES (?,?,?,?,?,?,?,?,?)
        ")->execute([
            $body['nome'],    $body['endereco'], $body['cidade'],
            $body['estado'],  $body['cep']       ?? null,
            $body['telefone'] ?? null,
            $body['tipo'],    $body['latitude']  ?? null,
            $body['longitude'] ?? null,
        ]);
        $this->show($this->db->lastInsertId());
    }

    public function update(int $id, array $body): void
    {
        Auth::requireAdmin();
        $fields = [];
        $params = [];
        $updatable = ['nome','endereco','cidade','estado','cep','telefone','tipo','latitude','longitude','ativa'];
        foreach ($updatable as $f) {
            if (isset($body[$f])) { $fields[] = "$f = ?"; $params[] = $body[$f]; }
        }
        if (empty($fields)) Response::error('Nenhum campo para atualizar.', 422);
        $params[] = $id;
        $this->db->prepare("UPDATE lojas SET " . implode(', ', $fields) . " WHERE id = ?")
                 ->execute($params);
        $this->show($id);
    }

    public function destroy(int $id): void
    {
        Auth::requireAdmin();
        $this->db->prepare("UPDATE lojas SET ativa = 0 WHERE id = ?")->execute([$id]);
        Response::success(null, 'Loja desativada.');
    }
}

class MotoristaController
{
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function index(array $query = []): void
    {
        Auth::requireAuth();
        $stmt = $this->db->prepare("SELECT * FROM motoristas WHERE ativo = 1 ORDER BY nome");
        $stmt->execute();
        Response::success($stmt->fetchAll());
    }

    public function show(int $id): void
    {
        Auth::requireAuth();
        $stmt = $this->db->prepare("SELECT * FROM motoristas WHERE id = ? AND ativo = 1");
        $stmt->execute([$id]);
        $m = $stmt->fetch();
        if (!$m) Response::error('Motorista não encontrado.', 404);
        Response::success($m);
    }

    public function store(array $body): void
    {
        Auth::requireAdmin();
        $required = ['nome','cpf','cnh'];
        $errors   = [];
        foreach ($required as $f) {
            if (empty($body[$f])) $errors[] = "Campo '$f' obrigatório.";
        }
        if (!empty($errors)) Response::error('Dados incompletos.', 422, $errors);

        $stmt = $this->db->prepare("SELECT id FROM motoristas WHERE cpf = ?");
        $stmt->execute([$body['cpf']]);
        if ($stmt->fetch()) Response::error('CPF já cadastrado.', 409);

        $this->db->prepare("
            INSERT INTO motoristas (nome,cpf,cnh,validade_cnh,telefone,email)
            VALUES (?,?,?,?,?,?)
        ")->execute([
            $body['nome'],   $body['cpf'],         $body['cnh'],
            $body['validade_cnh'] ?? null,
            $body['telefone']     ?? null,
            $body['email']        ?? null,
        ]);
        $this->show($this->db->lastInsertId());
    }

    public function update(int $id, array $body): void
    {
        Auth::requireAdmin();
        $fields = [];
        $params = [];
        foreach (['nome','cnh','validade_cnh','telefone','email'] as $f) {
            if (isset($body[$f])) { $fields[] = "$f = ?"; $params[] = $body[$f]; }
        }
        if (empty($fields)) Response::error('Nenhum campo para atualizar.', 422);
        $params[] = $id;
        $this->db->prepare("UPDATE motoristas SET " . implode(', ', $fields) . " WHERE id = ?")
                 ->execute($params);
        $this->show($id);
    }

    public function destroy(int $id): void
    {
        Auth::requireAdmin();
        $this->db->prepare("UPDATE motoristas SET ativo = 0 WHERE id = ?")->execute([$id]);
        Response::success(null, 'Motorista desativado.');
    }
}

class PagamentoController
{
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function index(array $query = []): void
    {
        Auth::requireAdmin();
        $where  = [];
        $params = [];

        if (!empty($query['status'])) {
            $where[]  = 'p.status = ?';
            $params[] = $query['status'];
        }
        if (!empty($query['reserva_id'])) {
            $where[]  = 'p.reserva_id = ?';
            $params[] = (int)$query['reserva_id'];
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt = $this->db->prepare("
            SELECT p.*,
                   r.periodo_dias,
                   u.nome AS cliente_nome,
                   v.placa, v.modelo
            FROM pagamentos p
            JOIN reservas r  ON r.id = p.reserva_id
            JOIN clientes c  ON c.id = r.cliente_id
            JOIN usuarios u  ON u.id = c.usuario_id
            JOIN veiculos v  ON v.id = r.veiculo_id
            $whereSQL
            ORDER BY p.created_at DESC
        ");
        $stmt->execute($params);
        Response::success($stmt->fetchAll());
    }

    public function show(int $id): void
    {
        Auth::requireAuth();
        $stmt = $this->db->prepare("SELECT * FROM pagamentos WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if (!$p) Response::error('Pagamento não encontrado.', 404);
        Response::success($p);
    }

    public function store(array $body): void
    {
        Response::error('Pagamentos são criados automaticamente ao confirmar reserva.', 405);
    }
}

class RelatorioController
{
    private PDO $db;
    public function __construct() { $this->db = getDB(); }

    public function dashboard(): void
    {
        Auth::requireAdmin();
        $db = $this->db;

        $stats = [];

        $stmt = $db->prepare("
            SELECT status, COUNT(*) AS total FROM veiculos WHERE ativo = 1 GROUP BY status
        ");
        $stmt->execute();
        $statusVeiculos = ['livre' => 0, 'alugado' => 0, 'reservado' => 0, 'manutencao' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $statusVeiculos[$row['status']] = (int)$row['total'];
        }
        $stats['veiculos'] = $statusVeiculos;
        $stats['veiculos']['total'] = array_sum($statusVeiculos);

        $stmt2 = $db->prepare("SELECT status, COUNT(*) AS total FROM reservas GROUP BY status");
        $stmt2->execute();
        $stats['reservas'] = array_column($stmt2->fetchAll(), 'total', 'status');

        $stmt3 = $db->prepare("SELECT SUM(valor) FROM pagamentos WHERE status = 'aprovado'");
        $stmt3->execute();
        $stats['receita_total'] = (float)($stmt3->fetchColumn() ?? 0);

        $stmt4 = $db->prepare("
            SELECT SUM(valor) FROM pagamentos
            WHERE status = 'aprovado'
              AND MONTH(data_pagamento) = MONTH(CURDATE())
              AND YEAR(data_pagamento)  = YEAR(CURDATE())
        ");
        $stmt4->execute();
        $stats['receita_mes'] = (float)($stmt4->fetchColumn() ?? 0);

        $stmt5 = $db->prepare("SELECT COUNT(*) FROM clientes");
        $stmt5->execute();
        $stats['total_clientes'] = (int)$stmt5->fetchColumn();

        $stmt6 = $db->prepare("SELECT COUNT(*) FROM reservas WHERE status = 'ativa'");
        $stmt6->execute();
        $stats['locacoes_ativas'] = (int)$stmt6->fetchColumn();

        $stmt7 = $db->prepare("
            SELECT DATE(created_at) AS dia, COUNT(*) AS total
            FROM reservas
            WHERE created_at >= CURDATE() - INTERVAL 6 DAY
            GROUP BY dia
            ORDER BY dia
        ");
        $stmt7->execute();
        $stats['reservas_semana'] = $stmt7->fetchAll();

        Response::success($stats);
    }

    public function reservas(array $query = []): void
    {
        Auth::requireAdmin();

        $dataInicio = $query['data_inicio'] ?? date('Y-m-01');
        $dataFim    = $query['data_fim']    ?? date('Y-m-d');

        $stmt = $this->db->prepare("
            SELECT
                r.id,
                r.status,
                r.periodo_dias,
                r.canal,
                r.data_retirada_prevista,
                r.data_devolucao_prevista,
                r.valor_total,
                r.created_at,
                u.nome    AS cliente_nome,
                v.modelo, v.marca, v.categoria,
                lr.nome   AS loja_retirada,
                lr.cidade AS cidade_retirada
            FROM reservas r
            JOIN clientes c  ON c.id = r.cliente_id
            JOIN usuarios u  ON u.id = c.usuario_id
            JOIN veiculos v  ON v.id = r.veiculo_id
            JOIN lojas lr    ON lr.id = r.loja_retirada_id
            WHERE DATE(r.created_at) BETWEEN ? AND ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$dataInicio, $dataFim]);
        $reservas = $stmt->fetchAll();

        $resumo = [];
        foreach ($reservas as $r) {
            $resumo[$r['status']] = ($resumo[$r['status']] ?? 0) + 1;
        }

        $stmt2 = $this->db->prepare("
            SELECT SUM(p.valor) FROM pagamentos p
            JOIN reservas r ON r.id = p.reserva_id
            WHERE p.status = 'aprovado'
              AND DATE(r.created_at) BETWEEN ? AND ?
        ");
        $stmt2->execute([$dataInicio, $dataFim]);
        $receita = (float)($stmt2->fetchColumn() ?? 0);

        Response::success([
            'periodo'    => ['inicio' => $dataInicio, 'fim' => $dataFim],
            'total'      => count($reservas),
            'receita'    => $receita,
            'por_status' => $resumo,
            'reservas'   => $reservas,
        ]);
    }

    public function veiculos(array $query = []): void
    {
        Auth::requireAdmin();

        $stmt = $this->db->prepare("
            SELECT
                v.id, v.placa, v.modelo, v.marca, v.categoria, v.status, v.quilometragem,
                l.nome AS loja_nome, l.cidade,
                COUNT(r.id) AS total_locacoes,
                SUM(r.valor_total) AS receita_gerada,
                MAX(r.created_at) AS ultima_locacao
            FROM veiculos v
            JOIN lojas l ON l.id = v.loja_id
            LEFT JOIN reservas r ON r.veiculo_id = v.id AND r.status = 'concluida'
            WHERE v.ativo = 1
            GROUP BY v.id
            ORDER BY total_locacoes DESC
        ");
        $stmt->execute();
        Response::success($stmt->fetchAll());
    }

    public function financeiro(array $query = []): void
    {
        Auth::requireAdmin();

        $stmt = $this->db->prepare("
            SELECT
                DATE_FORMAT(data_pagamento, '%Y-%m') AS mes,
                COUNT(*) AS total_pagamentos,
                SUM(valor) AS receita,
                SUM(CASE WHEN status = 'reembolso_parcial' THEN valor_reembolso ELSE 0 END) AS reembolsos
            FROM pagamentos
            WHERE status IN ('aprovado','reembolso_parcial')
              AND data_pagamento >= CURDATE() - INTERVAL 6 MONTH
            GROUP BY mes
            ORDER BY mes
        ");
        $stmt->execute();
        $mensal = $stmt->fetchAll();

        $stmt2 = $this->db->prepare("
            SELECT v.categoria, COUNT(r.id) AS reservas, SUM(r.valor_total) AS receita
            FROM reservas r
            JOIN veiculos v ON v.id = r.veiculo_id
            WHERE r.status IN ('confirmada','ativa','concluida')
            GROUP BY v.categoria
            ORDER BY receita DESC
        ");
        $stmt2->execute();
        $porCategoria = $stmt2->fetchAll();

        $stmt3 = $this->db->prepare("
            SELECT l.nome, l.cidade, COUNT(r.id) AS reservas, SUM(r.valor_total) AS receita
            FROM reservas r
            JOIN lojas l ON l.id = r.loja_retirada_id
            WHERE r.status IN ('confirmada','ativa','concluida')
            GROUP BY l.id
            ORDER BY receita DESC
        ");
        $stmt3->execute();
        $porLoja = $stmt3->fetchAll();

        Response::success([
            'mensal'        => $mensal,
            'por_categoria' => $porCategoria,
            'por_loja'      => $porLoja,
        ]);
    }
}
