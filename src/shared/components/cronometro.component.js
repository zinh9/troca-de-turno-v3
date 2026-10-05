import { formatarContagemRegressiva } from '../utils/formatters.js';

/**
 * Cronometro — cronômetro regressivo reutilizável.
 * Usado para: 15min lanche, 60min refeição, 5min tolerância de prontidão.
 *
 * Uso:
 *   const crono = criarCronometro({
 *     dataHoraInicio: '2026-09-04T08:14:00',
 *     duracaoMin: 15,
 *     toleranciaMin: 5,
 *     timeService,
 *     aoMudarEstado: (estado) => { ... atualizar cor do card ... },
 *   });
 *   container.appendChild(crono.elemento);
 *   // ao desmontar a tela:
 *   crono.destruir();
 *
 * Estados emitidos via `aoMudarEstado`: 'rodando' | 'tolerancia' | 'excedido'
 */
export function criarCronometro({ dataHoraInicio, duracaoMin, toleranciaMin = 0, timeService, aoMudarEstado }) {
  const elemento = document.createElement('div');
  elemento.className = 'cronometro';
  elemento.setAttribute('role', 'timer');

  const elValor = document.createElement('span');
  elValor.className = 'cronometro__valor fonte-mono';
  const elRotulo = document.createElement('span');
  elRotulo.className = 'cronometro__rotulo';
  elRotulo.textContent = `${duracaoMin}min`;

  elemento.append(elValor, elRotulo);

  let estadoAnterior = null;

  function atualizar(agora) {
    if (!dataHoraInicio) {
      elValor.textContent = '--:--';
      elemento.dataset.estado = 'ocioso';
      return;
    }

    const inicio = new Date(dataHoraInicio).getTime();
    const fimPrevisto = inicio + duracaoMin * 60_000;
    const fimTolerancia = fimPrevisto + toleranciaMin * 60_000;
    const agoraMs = agora.getTime();

    let estado;
    let segundosRestantes;

    if (agoraMs <= fimPrevisto) {
      estado = 'rodando';
      segundosRestantes = (fimPrevisto - agoraMs) / 1000;
    } else if (agoraMs <= fimTolerancia) {
      estado = 'tolerancia';
      segundosRestantes = (fimTolerancia - agoraMs) / 1000;
    } else {
      estado = 'excedido';
      segundosRestantes = 0;
    }

    elValor.textContent = estado === 'excedido'
      ? formatarContagemRegressiva(0)
      : formatarContagemRegressiva(segundosRestantes);
    elemento.dataset.estado = estado;

    if (estado !== estadoAnterior) {
      estadoAnterior = estado;
      aoMudarEstado?.(estado);
    }
  }

  atualizar(timeService.agora());
  const cancelarInscricao = timeService.onTick(atualizar);

  return {
    elemento,
    destruir: () => cancelarInscricao(),
  };
}
