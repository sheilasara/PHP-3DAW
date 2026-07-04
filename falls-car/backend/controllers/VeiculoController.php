<?php
require_once __DIR__ . '/../models/Veiculo.php';
require_once __DIR__ . '/../helpers/Response.php';

/**
 * Controlador de consulta de veículos disponíveis.
 */
class VeiculoController
{
    private Veiculo $veiculoModel;

    public function __construct()
    {
        $this->veiculoModel = new Veiculo();
    }

    /** GET /veiculos?cidade=...&categoria=... */
    public function listar(array $query): void
    {
        $cidade = $query['cidade'] ?? null;
        $categoria = $query['categoria'] ?? null;

        $veiculos = $this->veiculoModel->listarDisponiveis($cidade, $categoria);

        // Regra RCL 8: se não houver nenhum veículo na categoria pedida,
        // mas houver na cidade, sugerimos uma categoria superior.
        $sugestao = null;
        if (empty($veiculos) && $cidade && $categoria) {
            $sugestao = $this->veiculoModel->sugerirAlternativa($cidade, $categoria);
        }

        Response::sucesso([
            'veiculos'           => $veiculos,
            'sugestao_categoria_superior' => $sugestao,
        ]);
    }

    /** GET /veiculos/{id} */
    public function detalhar(int $idVeiculo): void
    {
        $veiculo = $this->veiculoModel->buscarPorId($idVeiculo);

        if (!$veiculo) {
            Response::erro('Veículo não encontrado.', 404);
        }

        Response::sucesso($veiculo);
    }
}
