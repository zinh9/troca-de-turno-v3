<?php

declare(strict_types=1);

namespace trocadeturno\services;

use TrocaDeTurno\Data\Connection;

final class PollingEventoPublisher implements EventoPublisherInterface
{
    public function __construct(private readonly Connection $conn)
    {
    }

    public function publicar(int $idSupervisao, ?int $idLocal): void
    {
        $this->conn->pdo()->prepare(
            'UPDATE empregado SET data_hora_ultima_atualizacao = SYSDATETIME()
            WHERE id_supervisao = :idSupervisao'
        )->execute(['idSupervisao' => $idSupervisao]);
    }

    public function aguardarMudanca(int $idSupervisao, ?int $idLocal, int $timeoutSegundos): bool
    {
        $inicio = time();
        $ultimoCarimbo = $this->buscarCarimbo($idSupervisao);

        while (time() - $inicio < $timeoutSegundos) {
            sleep(1);
            $carimboAtual = $this->buscarCarimbo($idSupervisao);

            if ($carimboAtual !== $ultimoCarimbo) {
                return true;
            }
        }

        return false;
    }

    public function buscarCarimbo(int $idSupervisao): ?string
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT MAX(e.data_hora_ultima_atualizacao) AS carimbo
            FROM empregado e
            JOIN supervisao s ON s.id_supervisao = e.id_supervisao
            WHERE s.id_supervisao = :idSupervisao',
        );
        $stmt->execute(['idSupervisao' => $idSupervisao]);
        return $stmt->fetchColumn() ?: null;
    }
}