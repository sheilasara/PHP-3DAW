<?php
/**
 * Helper para padronizar todas as respostas JSON da API.
 */
class Response
{
    public static function sucesso($dados = null, string $mensagem = 'Operação realizada com sucesso.', int $codigoHttp = 200): void
    {
        http_response_code($codigoHttp);
        echo json_encode([
            'sucesso'  => true,
            'mensagem' => $mensagem,
            'dados'    => $dados,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function erro(string $mensagem, int $codigoHttp = 400, $detalhes = null): void
    {
        http_response_code($codigoHttp);
        echo json_encode([
            'sucesso'  => false,
            'mensagem' => $mensagem,
            'detalhes' => $detalhes,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
