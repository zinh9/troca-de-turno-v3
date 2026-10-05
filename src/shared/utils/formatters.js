/**
 * formatters.js — utilidades puras de formatação. Nenhuma função aqui
 * toca DOM ou rede; só transforma dado -> string.
 */

/** "2026-09-04T06:33:00" -> "06:33" */
export function formatarHora(isoString) {
  if (!isoString) return '--:--';
  const d = new Date(isoString);
  return d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}

/** "2026-09-04T06:33:00" -> "04/09/2026" */
export function formatarData(isoString) {
  if (!isoString) return '--/--/----';
  const d = new Date(isoString);
  return d.toLocaleDateString('pt-BR');
}

/** minutos totais -> "1h 23min" ou "23min" ou "-05min" (negativo = adiantado) */
export function formatarDuracaoMin(minutosTotais) {
  if (minutosTotais === null || minutosTotais === undefined) return '--';
  const sinal = minutosTotais < 0 ? '-' : '';
  const abs = Math.abs(Math.round(minutosTotais));
  const horas = Math.floor(abs / 60);
  const min = abs % 60;
  return horas > 0 ? `${sinal}${horas}h ${String(min).padStart(2, '0')}min` : `${sinal}${min}min`;
}

/** segundos restantes -> "14:59" (mm:ss), negativo vira "00:00" */
export function formatarContagemRegressiva(segundosRestantes) {
  const s = Math.max(0, Math.round(segundosRestantes));
  const mm = String(Math.floor(s / 60)).padStart(2, '0');
  const ss = String(s % 60).padStart(2, '0');
  return `${mm}:${ss}`;
}

/** "JUSTIFICATIVA_OK" -> "Justificativa OK" — fallback humanizado genérico */
export function humanizarEnum(valor) {
  if (!valor) return '—';
  return valor
    .toLowerCase()
    .split('_')
    .map((palavra) => palavra.charAt(0).toUpperCase() + palavra.slice(1))
    .join(' ');
}

/** Matrícula "81053394" -> "8105.8394" no padrão de exibição do pátio, se aplicável.
 *  Ajuste esta função caso o padrão real de exibição seja diferente. */
export function formatarMatricula(matricula) {
  if (!matricula) return '';
  return matricula;
}
