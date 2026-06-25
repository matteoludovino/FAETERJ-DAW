<?php
class ReservaController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    private function baseSelect(): string
    {
        return "
            SELECT
                r.*,
                u.nome            AS cliente_nome,
                u.email           AS cliente_email,
                v.placa, v.modelo, v.marca, v.ano, v.categoria, v.cor,
                lr.nome           AS loja_retirada_nome,
                lr.cidade         AS loja_retirada_cidade,
                ld.nome           AS loja_devolucao_nome,
                ld.cidade         AS loja_devolucao_cidade,
                m.nome            AS motorista_nome,
                p.status          AS pagamento_status,
                p.valor           AS pagamento_valor,
                p.numero_cartao_mascarado
            FROM reservas r
            JOIN clientes c   ON c.id = r.cliente_id
            JOIN usuarios u   ON u.id = c.usuario_id
            JOIN veiculos v   ON v.id = r.veiculo_id
            JOIN lojas lr     ON lr.id = r.loja_retirada_id
            JOIN lojas ld     ON ld.id = r.loja_devolucao_id
            LEFT JOIN motoristas m ON m.id = r.motorista_id
            LEFT JOIN pagamentos p ON p.reserva_id = r.id
        ";
    }

    public function index(array $query = []): void
    {
        $user  = Auth::requireAuth();
        $where = [];
        $params = [];

        if ($user['tipo'] === 'cliente') {
            $where[]  = 'r.cliente_id = ?';
            $params[] = $user['cliente_id'];
        } else {
            if (!empty($query['cliente_id'])) {
                $where[]  = 'r.cliente_id = ?';
                $params[] = (int)$query['cliente_id'];
            }
        }

        if (!empty($query['status'])) {
            $where[]  = 'r.status = ?';
            $params[] = $query['status'];
        }
        if (!empty($query['loja_id'])) {
            $where[]  = 'r.loja_retirada_id = ?';
            $params[] = (int)$query['loja_id'];
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = $this->baseSelect() . $whereSQL . ' ORDER BY r.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        Response::success($stmt->fetchAll());
    }

    public function show(int $id): void
    {
        $user = Auth::requireAuth();
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE r.id = ?');
        $stmt->execute([$id]);
        $reserva = $stmt->fetch();

        if (!$reserva) Response::error('Reserva não encontrada.', 404);

        if ($user['tipo'] === 'cliente' && $reserva['cliente_id'] !== $user['cliente_id']) {
            Response::error('Acesso negado.', 403);
        }

        $stmt2 = $this->db->prepare("SELECT * FROM locacoes WHERE reserva_id = ?");
        $stmt2->execute([$id]);
        $reserva['locacao'] = $stmt2->fetch() ?: null;

        Response::success($reserva);
    }

    public function store(array $body): void
    {
        $user = Auth::requireAuth();

        $clienteId = ($user['tipo'] === 'cliente')
            ? $user['cliente_id']
            : (int)($body['cliente_id'] ?? 0);

        if (!$clienteId) Response::error('cliente_id é obrigatório.', 422);

        $required = ['loja_retirada_id', 'categoria', 'periodo_dias',
                     'data_retirada_prevista', 'numero_cartao', 'nome_titular'];
        $errors = [];
        foreach ($required as $f) {
            if (empty($body[$f])) $errors[] = "Campo '$f' obrigatório.";
        }
        if (!empty($errors)) Response::error('Dados incompletos.', 422, $errors);

        $periodo = (int)$body['periodo_dias'];
        if (!in_array($periodo, PERIODOS_PERMITIDOS)) {
            Response::error('Período inválido. Use 7, 15 ou 30 dias.', 422);
        }

        $dataRetirada   = $body['data_retirada_prevista'];
        $dataDevolucao  = date('Y-m-d', strtotime("$dataRetirada +$periodo days"));

        if (strtotime($dataRetirada) < strtotime('today')) {
            Response::error('Data de retirada deve ser hoje ou futura.', 422);
        }

        $lojaRetiradaId = (int)$body['loja_retirada_id'];

        $lojaDevolucaoId = (int)($body['loja_devolucao_id'] ?? $lojaRetiradaId);
        $this->validarMesmaCidade($lojaRetiradaId, $lojaDevolucaoId);

        $veiculo = VeiculoController::alocarVeiculo(
            $lojaRetiradaId,
            $body['categoria'],
            $dataRetirada,
            $dataDevolucao
        );

        if (!$veiculo) {
            Response::error(
                'Nenhum veículo disponível da categoria solicitada nessa cidade e período.',
                409
            );
        }

        $motoristaId     = null;
        $valorMotorista  = 0.0;
        if (!empty($body['motorista_id'])) {
            $stmt = $this->db->prepare("SELECT id FROM motoristas WHERE id = ? AND ativo = 1");
            $stmt->execute([$body['motorista_id']]);
            if (!$stmt->fetch()) Response::error('Motorista não encontrado.', 404);

            $motoristaId    = (int)$body['motorista_id'];
            $valorMotorista = $veiculo['custo_motorista_dia'] * $periodo;
        }

        $campo        = "preco_diaria_$periodo";
        $valorVeiculo = $veiculo[$campo] * $periodo;
        $valorTotal   = $valorVeiculo + $valorMotorista;

        $this->db->beginTransaction();
        try {
            $this->db->prepare("
                INSERT INTO reservas
                    (cliente_id,veiculo_id,loja_retirada_id,loja_devolucao_id,motorista_id,
                     periodo_dias,data_retirada_prevista,data_devolucao_prevista,canal,status,
                     valor_veiculo,valor_motorista,valor_total,observacoes)
                VALUES (?,?,?,?,?,?,?,?,'internet','confirmada',?,?,?,?)
            ")->execute([
                $clienteId,
                $veiculo['id'],
                $lojaRetiradaId,
                $lojaDevolucaoId,
                $motoristaId,
                $periodo,
                $dataRetirada,
                $dataDevolucao,
                $valorVeiculo,
                $valorMotorista,
                $valorTotal,
                $body['observacoes'] ?? null,
            ]);
            $reservaId = $this->db->lastInsertId();

            $this->db->prepare("UPDATE veiculos SET status = 'reservado' WHERE id = ?")
                     ->execute([$veiculo['id']]);

            $cartaoMascarado = '**** **** **** ' . substr(preg_replace('/\D/', '', $body['numero_cartao']), -4);
            $transacaoId     = 'TXN-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('Y');

            $this->db->prepare("
                INSERT INTO pagamentos
                    (reserva_id,valor,metodo,status,numero_cartao_mascarado,nome_titular,data_pagamento,transacao_id)
                VALUES (?,?,'cartao_credito','aprovado',?,?,NOW(),?)
            ")->execute([
                $reservaId,
                $valorTotal,
                $cartaoMascarado,
                $body['nome_titular'],
                $transacaoId,
            ]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error('Erro ao criar reserva: ' . $e->getMessage(), 500);
        }

        $this->show($reservaId);
    }

    public function update(int $id, array $body): void
    {
        Auth::requireAdmin();
        $stmt = $this->db->prepare("SELECT id FROM reservas WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) Response::error('Reserva não encontrada.', 404);

        if (isset($body['observacoes'])) {
            $this->db->prepare("UPDATE reservas SET observacoes = ? WHERE id = ?")
                     ->execute([$body['observacoes'], $id]);
        }

        $this->show($id);
    }

    public function cancelar(int $id, array $body): void
    {
        $user = Auth::requireAuth();
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE r.id = ?');
        $stmt->execute([$id]);
        $reserva = $stmt->fetch();

        if (!$reserva) Response::error('Reserva não encontrada.', 404);

        if ($user['tipo'] === 'cliente' && $reserva['cliente_id'] !== $user['cliente_id']) {
            Response::error('Acesso negado.', 403);
        }

        if (!in_array($reserva['status'], ['pendente', 'confirmada'])) {
            Response::error("Reserva com status '{$reserva['status']}' não pode ser cancelada.", 409);
        }

        $horasAteRetirada = (strtotime($reserva['data_retirada_prevista']) - time()) / 3600;
        if ($horasAteRetirada < CANCELAMENTO_LIMITE_HORAS) {
            Response::error(
                'Cancelamento não permitido. Faltam menos de 24h para a retirada.',
                409
            );
        }

        $this->db->beginTransaction();
        try {
            $this->db->prepare("
                UPDATE reservas
                SET status = 'cancelada',
                    motivo_cancelamento = ?,
                    data_cancelamento = NOW()
                WHERE id = ?
            ")->execute([$body['motivo'] ?? 'Cancelado pelo cliente', $id]);

            $this->db->prepare("UPDATE veiculos SET status = 'livre' WHERE id = ?")
                     ->execute([$reserva['veiculo_id']]);

            $valorReembolso = round($reserva['valor_total'] * REEMBOLSO_PERCENTUAL, 2);
            $this->db->prepare("
                UPDATE pagamentos
                SET status = 'reembolso_parcial',
                    valor_reembolso = ?,
                    data_reembolso = NOW()
                WHERE reserva_id = ?
            ")->execute([$valorReembolso, $id]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error('Erro ao cancelar reserva: ' . $e->getMessage(), 500);
        }

        Response::success([
            'reserva_id'     => $id,
            'status'         => 'cancelada',
            'valor_reembolso' => $valorReembolso,
        ], 'Reserva cancelada. Reembolso de 80% será processado em até 5 dias úteis.');
    }

    public function retirar(int $id, array $body): void
    {
        Auth::requireAdmin();

        $stmt = $this->db->prepare("SELECT * FROM reservas WHERE id = ?");
        $stmt->execute([$id]);
        $reserva = $stmt->fetch();

        if (!$reserva) Response::error('Reserva não encontrada.', 404);
        if ($reserva['status'] !== 'confirmada') {
            Response::error("Reserva não está confirmada (status: {$reserva['status']}).", 409);
        }

        $kmInicial = (int)($body['quilometragem_inicial'] ?? 0);

        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE reservas SET status = 'ativa' WHERE id = ?")
                     ->execute([$id]);

            $this->db->prepare("UPDATE veiculos SET status = 'alugado' WHERE id = ?")
                     ->execute([$reserva['veiculo_id']]);

            $this->db->prepare("
                INSERT INTO locacoes (reserva_id, data_retirada_real, quilometragem_inicial, status)
                VALUES (?, NOW(), ?, 'ativa')
            ")->execute([$id, $kmInicial]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error('Erro ao registrar retirada: ' . $e->getMessage(), 500);
        }

        Response::success(['reserva_id' => $id, 'status' => 'ativa'],
                          'Retirada registrada. Locação iniciada.');
    }

    public function devolver(int $id, array $body): void
    {
        Auth::requireAdmin();

        $stmt = $this->db->prepare("SELECT * FROM reservas WHERE id = ?");
        $stmt->execute([$id]);
        $reserva = $stmt->fetch();

        if (!$reserva) Response::error('Reserva não encontrada.', 404);
        if ($reserva['status'] !== 'ativa') {
            Response::error("Reserva não está ativa (status: {$reserva['status']}).", 409);
        }

        $kmFinal = (int)($body['quilometragem_final'] ?? 0);

        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE reservas SET status = 'concluida' WHERE id = ?")
                     ->execute([$id]);

            $this->db->prepare("UPDATE veiculos SET status = 'livre', quilometragem = ? WHERE id = ?")
                     ->execute([$kmFinal, $reserva['veiculo_id']]);

            $this->db->prepare("
                UPDATE locacoes
                SET status = 'concluida',
                    data_devolucao_real = NOW(),
                    quilometragem_final = ?,
                    observacoes = ?
                WHERE reserva_id = ?
            ")->execute([$kmFinal, $body['observacoes'] ?? null, $id]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error('Erro ao registrar devolução: ' . $e->getMessage(), 500);
        }

        Response::success(['reserva_id' => $id, 'status' => 'concluida'],
                          'Devolução registrada. Locação concluída.');
    }

    private function validarMesmaCidade(int $lojaRetiradaId, int $lojaDevolucaoId): void
    {
        $stmt = $this->db->prepare("SELECT cidade FROM lojas WHERE id IN (?,?)");
        $stmt->execute([$lojaRetiradaId, $lojaDevolucaoId]);
        $cidades = array_column($stmt->fetchAll(), 'cidade');

        if (count(array_unique($cidades)) > 1) {
            Response::error(
                'A loja de devolução deve ser na mesma cidade da retirada (RN05).',
                422
            );
        }
    }
}
