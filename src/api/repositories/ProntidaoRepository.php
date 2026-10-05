<?php

declare(strict_types=1);

namespace TrocaDeTurno\Repositories;

use TrocaDeTurno\Data\Connection;
use TrocaDeTurno\Entities\Prontidao;

final class ProntidaoRepository
{
    public function __construct(private readonly Connection $conn)
    {}

    public function registrar(int $idApresentacao, ?int $idJustificativa, string $status): void
    {
        $this->conn->pdo()->prepare(
            'INSERT INTO prontidao (id_apresentacao, data_hora_prontidao, status, id_justificativa)
            VALUES (:idApresentacao, SYSDATETIME(), :status, :idJustificativa)',
        )->execute([
            'idApresentacao' => $idApresentacao,
            'status' => $status,
            'idJustificativa' => $idJustificativa,
        ]);
    }

    public function listarTodos(): array
    {
        $stmt = $this->conn->pdo()->query(
            'SELECT * FROM prontidao'
        );
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $idProntidao): ?Prontidao
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT * FROM prontidao WHERE id_prontidao = :idProntidao'
        );
        $stmt->execute(['idProntidao' => $idProntidao]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        $prontidao = new Prontidao(
            idProntidao: (int) $result['id_prontidao'],
            dataHoraProntidao: new \DateTimeImmutable($result['data_hora_prontidao']),
            status: $result['status'],
            dataHoraChamadaCpt: $result['data_hora_chamada_cpt'] !== null 
                ? new \DateTimeImmutable($result['data_hora_chamada_cpt'])
                : null,
            idApresentacao: (int) $result['id_apresentacao'],
            idJustificativa: (int) $result['id_justificativa']
        );

        return $prontidao;
    }

    public function buscarPorIdApresentacao(int $idApresentacao): ?Prontidao
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT * FROM prontidao WHERE id_apresentacao = :idApresentacao'
        );
        $stmt->execute(['idApresentacao' => $idApresentacao]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        $prontidao = new Prontidao(
            idProntidao: (int) $result['id_prontidao'],
            dataHoraProntidao: new \DateTimeImmutable($result['data_hora_prontidao']),
            status: $result['status'],
            dataHoraChamadaCpt: $result['data_hora_chamada_cpt'] !== null 
                ? new \DateTimeImmutable($result['data_hora_chamada_cpt'])
                : null,
            idApresentacao: (int) $result['id_apresentacao'],
            idJustificativa: (int) $result['id_justificativa']
        );

        return $prontidao;
    }
}