<?php
require_once __DIR__ . '/../config/database.php';

class Veiculo
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function listarDisponiveis(?string $cidade = null, ?string $categoria = null): array
    {
        $sql = 'SELECT v.id_veiculo, v.placa, v.marca, v.modelo, v.ano, v.cor, v.categoria,
                       v.quilometragem, v.valor_diaria, v.status,
                       l.id_loja, l.nome AS loja_nome, l.cidade AS loja_cidade
                FROM veiculos v
                INNER JOIN lojas l ON l.id_loja = v.id_loja
                WHERE v.status = "disponivel" AND v.necessita_revisao = 0';

        $params = [];

        if ($cidade !== null && $cidade !== '') {
            $sql .= ' AND l.cidade = :cidade';
            $params['cidade'] = $cidade;
        }

        if ($categoria !== null && $categoria !== '') {
            $sql .= ' AND v.categoria = :categoria';
            $params['categoria'] = $categoria;
        }

        $sql .= ' ORDER BY l.cidade, v.valor_diaria ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $idVeiculo): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT v.*, l.nome AS loja_nome, l.cidade AS loja_cidade
             FROM veiculos v
             INNER JOIN lojas l ON l.id_loja = v.id_loja
             WHERE v.id_veiculo = :id'
        );
        $stmt->execute(['id' => $idVeiculo]);
        $veiculo = $stmt->fetch();
        return $veiculo ?: null;
    }

    public function atualizarStatus(int $idVeiculo, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE veiculos SET status = :status WHERE id_veiculo = :id');
        $stmt->execute(['status' => $status, 'id' => $idVeiculo]);
    }

    public function sugerirAlternativa(string $cidade, string $categoria): ?array
    {
        $ordemCategorias = ['economico', 'intermediario', 'suv', 'luxo'];
        $indiceAtual = array_search($categoria, $ordemCategorias, true);

        if ($indiceAtual === false) {
            return null;
        }

        for ($i = $indiceAtual + 1; $i < count($ordemCategorias); $i++) {
            $disponiveis = $this->listarDisponiveis($cidade, $ordemCategorias[$i]);
            if (!empty($disponiveis)) {
                return $disponiveis[0];
            }
        }

        return null;
    }
}
