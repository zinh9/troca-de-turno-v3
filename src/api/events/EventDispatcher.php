<?php

declare(strict_types=1);

namespace trocadeturno\events;

final class EventDispatcher
{
    /** @var array<class-string, callable[]> */
    private array $ouvintes = [];

    public function inscrever(string $classeEvento, callable $ouvinte): void
    {
        $this->ouvintes[$classeEvento][] = $ouvinte;
    }

    public function despachar(object $evento): void
    {
        foreach ($this->ouvintes[$evento::class] ?? [] as $ouvinte) {
            $ouvinte($evento);
        }
    }
}