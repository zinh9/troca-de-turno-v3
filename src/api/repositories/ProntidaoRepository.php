<?php

declare(strict_types=1);

namespace TrocaDeTurno\Repositories;

use TrocaDeTurno\Data\Connection;
use TrocaDeTurno\Entities\Prontidao;

final class ProntidaoRepository
{
    public function __construct(private readonly Connection $conn)
    {}

    /**
     * Marca a prontidão. Se o CCP já tinha "acionado via rádio" (linha criada com
     * status AGUARDANDO), ATUALIZA a linha; senão cria uma nova.
     */
    public function registrar(int $idApresentacao, ?int $idJustificativa, string $status): void
    {
        if ($this->buscarPorIdApresentacao($idApresentacao) !== null) {
            $this->conn->pdo()->prepare(
                'UPDATE prontidao
                SET data_hora_prontidao = SYSDATETIME(), status = :status, id_justificativa = :idJustificativa
                WHERE id_apresentacao = :idApresentacao'
            )->execute([
                'status' => $status,
                'idJustificativa' => $idJustificativa,
                'idApresentacao' => $idApresentacao,
            ]);
            return;
        }

        $this->conn->pdo()->prepare(
            'INSERT INTO prontidao (id_apresentacao, data_hora_prontidao, status, id_justificativa)
            VALUES (:idApresentacao, SYSDATETIME(), :status, :idJustificativa)'
        )->execute([
            'idApresentacao' => $idApresentacao,
            'status' => $status,
            'idJustificativa' => $idJustificativa,
        ]);
    }

    /**
     * CCP clicou "ACIONAR VIA RÁDIO" (empregado passou de 15 min sem marcar).
     * TODO(Zenzo): isto exige a coluna prontidao.data_hora_prontidao ser NULLABLE
     * (veja docs/SQL-MUDANCAS.sql). Se preferir outra modelagem (ex.: coluna
     * data_hora_chamada_cpt dentro de apresentacao), é só mexer neste método.
     */
    public function registrarChamadaCpt(int $idApresentacao): void
    {
        if ($this->buscarPorIdApresentacao($idApresentacao) !== null) {
            $this->conn->pdo()->prepare(
                'UPDATE prontidao SET data_hora_chamada_cpt = SYSDATETIME() WHERE id_apresentacao = :id'
            )->execute(['id' => $idApresentacao]);
            return;
        }

        $this->conn->pdo()->prepare(
            "INSERT INTO prontidao (id_apresentacao, data_hora_prontidao, data_hora_chamada_cpt, status)
            VALUES (:id, NULL, SYSDATETIME(), 'AGUARDANDO')"
        )->execute(['id' => $idApresentacao]);
    }

    /** @return array<Prontidao> */
    public function listarTodos(): array
    {
        $linhas = $this->conn->pdo()->query('SELECT * FROM prontidao')->fetchAll();

        return array_map(fn(array $l) => $this->mapear($l), $linhas);
    }

    public function buscarPorId(int $idProntidao): ?Prontidao
    {
        $stmt = $this->conn->pdo()->prepare('SELECT * FROM prontidao WHERE id_prontidao = :idProntidao');
        $stmt->execute(['idProntidao' => $idProntidao]);
        $linha = $stmt->fetch();

        return $linha ? $this->mapear($linha) : null;
    }

    public function buscarPorIdApresentacao(int $idApresentacao): ?Prontidao
    {
        $stmt = $this->conn->pdo()->prepare('SELECT * FROM prontidao WHERE id_apresentacao = :idApresentacao');
        $stmt->execute(['idApresentacao' => $idApresentacao]);
        $linha = $stmt->fetch();

        return $linha ? $this->mapear($linha) : null;
    }

    /** UM mapper só (antes eram dois idênticos) e null-safe. */
    private function mapear(array $linha): Prontidao
    {
        return new Prontidao(
            idProntidao: (int) $linha['id_prontidao'],
            dataHoraProntidao: $linha['data_hora_prontidao'] !== null
                ? new \DateTimeImmutable($linha['data_hora_prontidao']) : null,
            dataHoraChamadaCpt: $linha['data_hora_chamada_cpt'] !== null
                ? new \DateTimeImmutable($linha['data_hora_chamada_cpt']) : null,
            status: $linha['status'],
            idApresentacao: (int) $linha['id_apresentacao'],
            idJustificativa: $linha['id_justificativa'] !== null ? (int) $linha['id_justificativa'] : null,
        );
    }
}
