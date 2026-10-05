<?php

declare(strict_types=1);

namespace trocadeturno\services;

require_once '../data/Connection.php';
require_once 'EventoPublisherInterface.php';

final class PollingEventoPublisher implements EventoPublisherInterface
{
    public function __construct(private readonly Connection $conn)
    {
    }

    public function publicar(string $supervisao, ?string $local): void
    {
        $this->conn->pdo()->prepare(
            'UPDATE empregado SET data_hora_ultima_atualizacao = SYSDATETIME()
            WHERE id_supervisao = (SELECT id_supervisao FROM supervisao WHERE supervisao = :supervisao)'
        )->execute(['supervisao' => $supervisao]);
    }

    public function aguardarMudanca(string $supervisao, ?string $local, int $timeoutSegundos): bool
    {
        $inicio = time();
        $ultimoCarimbo = $this->buscarCarimbo($supervisao);

        while (time() - $inicio < $timeoutSegundos) {
            sleep(1);
            $carimboAtual = $this->buscarCarimbo($supervisao);

            if ($carimboAtual !== $ultimoCarimbo) {
                return true;
            }
        }

        return false;
    }

    public function buscarCarimbo(string $supervisao): ?string
    {
        $stmt = $this->conn->pdo()->prepare(
            'SELECT MAX(e.data_hora_ultima_atualizacao) AS carimbo
            FROM empregado e
            JOIN supervisao s ON s.id_supervisao = e.id_supervisao
            WHERE s.supervisao = :supervisao',
        );
        $stmt->execute(['supervisao' => $supervisao]);
        return $stmt->fetchColumn() ?: null;
    }
}