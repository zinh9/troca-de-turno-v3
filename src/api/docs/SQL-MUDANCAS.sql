-- Rode no SSMS (banco troca_de_turno). Revise os nomes das colunas antes!

-- 1) CCP "ACIONAR VIA RÁDIO" cria a linha de prontidão ANTES do empregado marcar,
--    então a data da prontidão precisa aceitar NULL.
ALTER TABLE prontidao ALTER COLUMN data_hora_prontidao DATETIME2 NULL;

-- 2) O nome da tabela de horários de referência estava digitado "horario_referecia".
--    Se a sua tabela AINDA tem o nome errado, renomeie:
-- EXEC sp_rename 'horario_referecia', 'horario_referencia';

-- 3) Triggers que "tocam o relógio" (SSE perceber mudança feita por qualquer meio).
--    Opcional: o publisher do PHP já carimba a cada gravação.
