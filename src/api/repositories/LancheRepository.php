<?php

declare(strict_types=1);

namespace TrocaDeTurno\Repositories;

use TrocaDeTurno\Data\Connection;
use TrocaDeTurno\Entities\Lanche;

final class LancheRepository
{
    public function __construct(private readonly Connection $conn) {}

    public function buscarPorIdApresentacao(int $idApresentacao): ?Lanche
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT * FROM lanche WHERE id_apresentacao = :idApresentacao'
        );
        $stmt->execute(['idApresentacao' => $idApresentacao]);
        $linha = $stmt->fetch();

        if (!$linha) {
            return null;
        }

        return $this->mapearLinha($linha);
    }

    public function registrarEscolha(int $idApresentacao, string $escolha): void
    {
        $this->conn->pdo()->prepare(
            'INSERT INTO lanche (id_apresentacao, escolha_intervalo_lanche)
            VALUES (:idApresentacao, :escolha)'
        )->execute(['idApresentacao' => $idApresentacao, 'escolha' => $escolha]);
    }

    /** Quantas pessoas do local já escolheram essa janela nas últimas 13h (pra regra "metade"). */
    public function contarEscolhasAtivasPorLocal(int $idLocal, string $escolha): int
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT COUNT(*)
            FROM lanche l
            JOIN apresentacao a ON a.id_apresentacao = l.id_apresentacao
            WHERE a.id_local = :idLocal
            AND l.escolha_intervalo_lanche = :escolha
            AND DATEDIFF(MINUTE, a.data_hora_apresentacao, SYSDATETIME()) <= 780'
        );
        $stmt->execute(['idLocal' => $idLocal, 'escolha' => $escolha]);

        return (int) $stmt->fetchColumn();
    }

    public function registrarInicioPatio(int $idLanche, ?int $idJustificativaInicio): void
    {
        $this->conn->pdo()->prepare(
            'UPDATE lanche
            SET data_hora_lanche_patio = SYSDATETIME(), id_justificativa_inicio = :idJustificativaInicio
            WHERE id_lanche = :idLanche'
        )->execute(['idLanche' => $idLanche, 'idJustificativaInicio' => $idJustificativaInicio]);
    }

    public function mapearLinha(array $linha): Lanche
    {
        return new Lanche(
            idLanche: (int) $linha['id_lanche'],
            dataHoraLanchePatio: $linha['data_hora_lanche_patio'] !== null  
                ? new \DateTimeImmutable($linha['data_hora_lanche_patio']) : null,
            dataHoraLancheCpt: $linha['data_hora_lanche_cpt'] !== null  
                ? new \DateTimeImmutable($linha['data_hora_lanche_cpt']) : null,
            escolhaIntervaloLanche: $linha['escolha_intervalo_lanche'],
            dataHoraProntidaoLanche: $linha['data_hora_prontidao_lanche'] !== null  
                ? new \DateTimeImmutable($linha['data_hora_prontidao_lanche']) : null,
            idApresentacao: (int) $linha['id_apresentacao'],
            idJustificativaInicio: $linha['id_justificativa_inicio'] !== null
                ? (int) $linha['id_justificativa_inicio'] : null,
            idJustificativaProntidao: $linha['id_justificativa_prontidao'] !== null
                ? (int) $linha['id_justificativa_prontidao'] : null,
        );
    }
}