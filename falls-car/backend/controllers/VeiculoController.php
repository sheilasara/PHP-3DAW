<?php
require_once __DIR__ . '/../models/Veiculo.php';
require_once __DIR__ . '/../helpers/Response.php';

class VeiculoController
{
    private Veiculo $veiculoModel;

    public function __construct()
    {
        $this->veiculoModel = new Veiculo();
    }

    
    public function listar(array $query): void
    {
        $cidade = $query['cidade'] ?? null;
        $categoria = $query['categoria'] ?? null;

        $veiculos = $this->veiculoModel->listarDisponiveis($cidade, $categoria);

        $sugestao = null;
        if (empty($veiculos) && $cidade && $categoria) {
            $sugestao = $this->veiculoModel->sugerirAlternativa($cidade, $categoria);
        }

        Response::sucesso([
            'veiculos'           => $veiculos,
            'sugestao_categoria_superior' => $sugestao,
        ]);
    }

    public function detalhar(int $idVeiculo): void
    {
        $veiculo = $this->veiculoModel->buscarPorId($idVeiculo);

        if (!$veiculo) {
            Response::erro('Veículo não encontrado.', 404);
        }

        Response::sucesso($veiculo);
    }
}
