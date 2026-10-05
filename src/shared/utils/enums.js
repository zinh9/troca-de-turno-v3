/**
 * enums.js — nomenclatura única de status. O VBScript hoje usa strings
 * soltas e inconsistentes ("OK", "ok", "1", "ATRASO"); este arquivo é a
 * ÚNICA fonte de verdade no front. Sempre compare contra estas constantes,
 * nunca contra string literal espalhada pelo código.
 */

export const TURNOS = Object.freeze({
  T06X18: '06x18',
  T18X06: '18x06',
  T00X12: '00x12',
  T12X00: '12x00',
});

export const STATUS_APRESENTACAO = Object.freeze({
  OK: 'OK',
  JUSTIFICAR: 'JUSTIFICAR',
  JUSTIFICATIVA_OK: 'JUSTIFICATIVA_OK',
});

export const FASE_PRONTIDAO = Object.freeze({
  TOLERANCIA_INICIAL: 'TOLERANCIA_INICIAL',
  LIBERADO: 'LIBERADO',
  ATRASADO_JUSTIFICAR: 'ATRASADO_JUSTIFICAR',
});

export const STATUS_PRONTIDAO = Object.freeze({
  AGUARDANDO: 'AGUARDANDO',
  PRONTO: 'PRONTO',
  PRONTO_COM_ATRASO: 'PRONTO_COM_ATRASO',
});

// Enum unificado para Lanche e Refeição (mesma máquina de estados).
export const STATUS_INTERVALO = Object.freeze({
  AGUARDANDO_ESCOLHA: 'AGUARDANDO_ESCOLHA', // só lanche (refeição não escolhe janela)
  AGUARDANDO_JANELA: 'AGUARDANDO_JANELA',
  LIBERADO_PARA_ACAO: 'LIBERADO_PARA_ACAO',
  EM_ANDAMENTO: 'EM_ANDAMENTO',
  AGUARDANDO_REFEICAO: 'AGUARDANDO_REFEICAO', // aguardando confirmação de prontidão pós-intervalo
  ATRASADO: 'ATRASADO',
  CONCLUIDO: 'CONCLUIDO',
});

export const INTERVALO_ESCOLHIDO = Object.freeze({
  CEDO: 'CEDO',
  TARDE: 'TARDE',
});

/** Mapa central de cor semântica por status — usado por StatusBadge. */
export const COR_POR_STATUS = Object.freeze({
  [STATUS_APRESENTACAO.OK]: 'ok',
  [STATUS_APRESENTACAO.JUSTIFICAR]: 'critico',
  [STATUS_APRESENTACAO.JUSTIFICATIVA_OK]: 'alerta',

  [STATUS_PRONTIDAO.AGUARDANDO]: 'neutro',
  [STATUS_PRONTIDAO.PRONTO]: 'ok',
  [STATUS_PRONTIDAO.PRONTO_COM_ATRASO]: 'alerta',

  [STATUS_INTERVALO.AGUARDANDO_ESCOLHA]: 'neutro',
  [STATUS_INTERVALO.AGUARDANDO_JANELA]: 'neutro',
  [STATUS_INTERVALO.LIBERADO_PARA_ACAO]: 'info',
  [STATUS_INTERVALO.EM_ANDAMENTO]: 'ambar',
  [STATUS_INTERVALO.AGUARDANDO_REFEICAO]: 'info',
  [STATUS_INTERVALO.ATRASADO]: 'critico',
  [STATUS_INTERVALO.CONCLUIDO]: 'ok',
});

export const ETAPAS_JORNADA = Object.freeze([
  'apresentacao', 'prontidao', 'lanche', 'refeicao', 'fimJornada',
]);
