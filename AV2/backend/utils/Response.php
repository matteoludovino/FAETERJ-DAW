<?php

class Response
{
    /**
     * @param mixed
     * @param string
     * @param int
     */
    public static function success($data = null, string $message = 'OK', int $code = 200): void
    {
        self::send([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * @param string
     * @param int
     * @param array
     */
    public static function error(string $message, int $code = 400, array $errors = []): void
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];
        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }
        self::send($payload, $code);
    }

    public static function created($data = null, string $message = 'Criado com sucesso'): void
    {
        self::success($data, $message, 201);
    }

    public static function noContent(): void
    {
        http_response_code(204);
        exit;
    }

    public static function paginated(array $items, int $total, int $page, int $perPage): void
    {
        self::send([
            'success'    => true,
            'message'    => 'OK',
            'data'       => $items,
            'pagination' => [
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => ceil($total / $perPage),
            ],
        ], 200);
    }

    private static function send(array $payload, int $code): void
    {
        http_response_code($code);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
