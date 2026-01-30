<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Retorna uma resposta de sucesso padronizada
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function success($data = null, string $message = 'Operação realizada com sucesso', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Retorna uma resposta de erro padronizada
     *
     * @param string $message
     * @param mixed $errors
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function error(string $message = 'Erro ao processar a requisição', $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Retorna uma resposta de validação padronizada
     *
     * @param mixed $errors
     * @param string $message
     * @return JsonResponse
     */
    public static function validationError($errors, string $message = 'Dados de validação inválidos'): JsonResponse
    {
        return self::error($message, $errors, 422);
    }

    /**
     * Retorna uma resposta de não encontrado padronizada
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Recurso não encontrado'): JsonResponse
    {
        return self::error($message, null, 404);
    }

    /**
     * Retorna uma resposta de não autorizado padronizada
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function unauthorized(string $message = 'Não autorizado'): JsonResponse
    {
        return self::error($message, null, 401);
    }

    /**
     * Retorna uma resposta de servidor interno padronizada
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function serverError(string $message = 'Erro interno do servidor'): JsonResponse
    {
        return self::error($message, null, 500);
    }
}
