<?php

declare(strict_types=1);

/**
 * Runner "vanilla": roda os MESMOS testes de test/*Test.php sem instalar PHPUnit.
 *
 *   php test/rodar.php
 *
 * Como funciona: se a classe PHPUnit\Framework\TestCase não existir, a gente cria
 * uma versão mínima aqui (só os assert* que usamos). Cada método test* vira um teste.
 * Saída: um ✔ ou ✘ por teste + resumo. Exit code 0 = tudo certo, 1 = algo falhou.
 */

namespace PHPUnit\Framework {
    if (!class_exists(TestCase::class)) {
        class TestCase
        {
            protected function assertSame(mixed $esperado, mixed $atual): void
            {
                if ($esperado !== $atual) {
                    throw new \Exception('esperado ' . var_export($esperado, true) . ' mas veio ' . var_export($atual, true));
                }
            }

            protected function assertTrue(mixed $v): void { $this->assertSame(true, $v); }
            protected function assertFalse(mixed $v): void { $this->assertSame(false, $v); }
        }
    }
}

namespace {
    require __DIR__ . '/bootstrap.php';

    $ok = 0;
    $falhas = 0;

    foreach (glob(__DIR__ . '/*Test.php') as $arquivo) {
        $antes = get_declared_classes();
        require_once $arquivo;

        foreach (array_diff(get_declared_classes(), $antes) as $classe) {
            foreach (get_class_methods($classe) as $metodo) {
                if (!str_starts_with($metodo, 'test')) {
                    continue;
                }
                try {
                    (new $classe())->$metodo();
                    $ok++;
                    echo "  ✔ $classe::$metodo\n";
                } catch (\Throwable $e) {
                    $falhas++;
                    echo "  ✘ $classe::$metodo -> {$e->getMessage()}\n";
                }
            }
        }
    }

    echo "\n$ok passaram, $falhas falharam\n";
    exit($falhas > 0 ? 1 : 0);
}
