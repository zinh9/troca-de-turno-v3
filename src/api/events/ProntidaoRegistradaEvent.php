<?php

declare(strict_types=1);

namespace trocadeturno\events;

final readonly class ProntidaoRegistradaEvent
{
    public function __construct(
        public string $matricula,
        public string $supervisao,
        public string $local,
    ) {}
}