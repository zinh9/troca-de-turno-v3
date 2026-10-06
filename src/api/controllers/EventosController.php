<?php

declare(strict_types=1);

namespace TrocaDeTurno\Controllers;

use TrocaDeTurno\Services\EventoPublisherInterface;

final class EventosController
{
    public function __construct(private readonly EventoPublisherInterface $eventoPublisher)
    {}

    public function atender(string $supervisao, ?string $local): void
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');

        set_time_limit(0);
        ignore_user_abort(false);

        while (!connection_aborted()) {
            $mudou = $this->eventoPublisher->aguardarMudanca($supervisao, $local, timeoutSegundos: 30);

            if ($mudou) {
                echo "event: jornada-atualizada\n";
                echo "data: {}\n\n";
            } else {
                echo ": ping\n\n";
            }

            if(ob_get_level() > 0) {
                ob_flush();
            }

            flush();
        }
    }
}