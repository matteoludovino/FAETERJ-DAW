<?php

class VeiculoController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    public function index(array $query = []): void
    {
        $where  = ['v.ativo = 1'];
        $params = [];

        if (!empty($query['status'])) {
            $where[]  = 'v.status = ?';
            $params[] = $query['status'];
        }
        if (!empty($query['categoria'])) {
            $where[]  = 'v.categoria = ?';
            $params[] = $query['categoria'];
        }
        if (!empty($query['cidade'])) {
            $where[]  = 'l.cidade = ?';
            $params[] = $query['cidade'];
        }
        if (!empty($query['loja_id'])) {
            $where[]  = 'v.loja_id = ?';
            $params[] = (int)$query['loja_id'];
        }

        $whereSQL = implode(' AND ', $where);

        $sql = "
            SELECT
                v.*,
                l.nome       AS loja_nome,
                l.cidade     AS loja_cidade,
                l.estado     AS loja_estado,
                l.tipo       AS loja_tipo
            FROM veiculos v
            JOIN lojas l ON l.id = v.loja_id
            WHERE $whereSQL
            ORDER BY v.marca, v.modelo
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $veiculos = $stmt->fetchAll();

        $periodo = (int)($query['periodo_dias'] ?? 0);
        if (in_array($periodo, PERIODOS_PERMITIDOS)) {
            foreach ($veiculos as &$v) {
                $campo           = "preco_diaria_$periodo";
                $v['preco_periodo'] = $v[$campo] * $periodo;
                $v['periodo_selecionado'] = $periodo;
            }
        }

        Response::success($veiculos);
    }

    public function show(int $id): void
    {
        $stmt = $this->db->prepare("
            SELECT v.*, l.nome AS loja_nome, l.cidade AS loja_cidade,
                   l.estado AS loja_estado, l.tipo AS loja_tipo
            FROM veiculos v
            JOIN lojas l ON l.id = v.loja_id
            WHERE v.id = ? AND v.ativo = 1
        ");
        $stmt->execute([$id]);
        $veiculo = $stmt->fetch();

        if (!$veiculo) {
            Response::error('Veículo não encontrado.', 404);
        }

        Response::success($veiculo);
    }

    public function store(array $body): void
    {
        Auth::requireAdmin();

        $required = ['placa','modelo','marca','ano','categoria','loja_id',
                     'preco_diaria_7','preco_diaria_15','preco_diaria_30'];
        $errors   = [];
        foreach ($required as $f) {
            if (empty($body[$f])) $errors[] = "Campo '$f' obrigatório.";
        }
        if (!empty($errors)) Response::error('Dados incompletos.', 422, $errors);

        $stmt = $this->db->prepare("SELECT id FROM veiculos WHERE placa = ?");
        $stmt->execute([$body['placa']]);
        if ($stmt->fetch()) Response::error('Placa já cadastrada.', 409);

        $stmt2 = $this->db->prepare("SELECT id FROM lojas WHERE id = ? AND ativa = 1");
        $stmt2->execute([$body['loja_id']]);
        if (!$stmt2->fetch()) Response::error('Loja não encontrada.', 404);

        $this->db->prepare("
            INSERT INTO veiculos
                (placa,modelo,marca,ano,cor,categoria,quilometragem,loja_id,
                 preco_diaria_7,preco_diaria_15,preco_diaria_30,custo_motorista_dia,
                 imagem_url,transmissao,combustivel,ar_condicionado,capacidade_passageiros)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ")->execute([
            strtoupper($body['placa']),
            $body['modelo'],
            $body['marca'],
            (int)$body['ano'],
            $body['cor']            ?? null,
            $body['categoria'],
            (int)($body['quilometragem'] ?? 0),
            (int)$body['loja_id'],
            (float)$body['preco_diaria_7'],
            (float)$body['preco_diaria_15'],
            (float)$body['preco_diaria_30'],
            (float)($body['custo_motorista_dia'] ?? 80.00),
            $body['imagem_url']     ?? null,
            $body['transmissao']    ?? 'automatico',
            $body['combustivel']    ?? 'flex',
            (int)($body['ar_condicionado'] ?? 1),
            (int)($body['capacidade_passageiros'] ?? 5),
        ]);

        $id = $this->db->lastInsertId();
        $this->show($id);
    }

    public function update(int $id, array $body): void
    {
        Auth::requireAdmin();

        $stmt = $this->db->prepare("SELECT id FROM veiculos WHERE id = ? AND ativo = 1");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) Response::error('Veículo não encontrado.', 404);

        $fields = [];
        $params = [];

        $updatable = [
            'modelo','marca','ano','cor','categoria','quilometragem','loja_id',
            'preco_diaria_7','preco_diaria_15','preco_diaria_30','custo_motorista_dia',
            'imagem_url','transmissao','combustivel','ar_condicionado','capacidade_passageiros'
        ];

        foreach ($updatable as $f) {
            if (isset($body[$f])) {
                $fields[] = "$f = ?";
                $params[] = $body[$f];
            }
        }

        if (empty($fields)) Response::error('Nenhum campo para atualizar.', 422);

        $params[] = $id;
        $this->db->prepare("UPDATE veiculos SET " . implode(', ', $fields) . " WHERE id = ?")
                 ->execute($params);

        $this->show($id);
    }

    public function updateStatus(int $id, array $body): void
    {
        Auth::requireAdmin();

        $statusValidos = ['livre', 'alugado', 'reservado', 'manutencao'];
        $novoStatus    = $body['status'] ?? '';

        if (!in_array($novoStatus, $statusValidos)) {
            Response::error('Status inválido. Use: ' . implode(', ', $statusValidos), 422);
        }

        $stmt = $this->db->prepare("SELECT id, status FROM veiculos WHERE id = ? AND ativo = 1");
        $stmt->execute([$id]);
        $veiculo = $stmt->fetch();

        if (!$veiculo) Response::error('Veículo não encontrado.', 404);

        $this->db->prepare("UPDATE veiculos SET status = ? WHERE id = ?")
                 ->execute([$novoStatus, $id]);

        Response::success(['id' => $id, 'status' => $novoStatus],
                          "Status atualizado para '$novoStatus'.");
    }

    public function destroy(int $id): void
    {
        Auth::requireAdmin();

        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM reservas
            WHERE veiculo_id = ? AND status IN ('confirmada','ativa')
        ");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            Response::error('Veículo possui reservas ativas e não pode ser excluído.', 409);
        }

        $this->db->prepare("UPDATE veiculos SET ativo = 0 WHERE id = ?")->execute([$id]);
        Response::success(null, 'Veículo removido com sucesso.');
    }

    /**
     * @param int
     * @param string
     * @param string
     * @param string
     * @return array|null
     */
    public static function alocarVeiculo(
        int    $lojaId,
        string $categoria,
        string $dataRetirada,
        string $dataDevolucao
    ): ?array {
        $db = getDB();

        $stmt = $db->prepare("SELECT cidade FROM lojas WHERE id = ?");
        $stmt->execute([$lojaId]);
        $loja = $stmt->fetch();
        if (!$loja) return null;

        $cidade = $loja['cidade'];

        $sql = "
            SELECT v.*, l.nome AS loja_nome, l.cidade AS loja_cidade
            FROM veiculos v
            JOIN lojas l ON l.id = v.loja_id
            WHERE v.categoria = ?
              AND v.status = 'livre'
              AND v.ativo = 1
              AND l.cidade = ?
              AND v.id NOT IN (
                  SELECT veiculo_id FROM reservas
                  WHERE status IN ('confirmada','ativa')
                    AND data_retirada_prevista < ?
                    AND data_devolucao_prevista > ?
              )
            ORDER BY
                (v.loja_id = ?) DESC,   -- Prefere mesma loja
                v.quilometragem ASC     -- Menor quilometragem primeiro
            LIMIT 1
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$categoria, $cidade, $dataDevolucao, $dataRetirada, $lojaId]);
        return $stmt->fetch() ?: null;
    }
}
