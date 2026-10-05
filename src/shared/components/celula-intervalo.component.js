import { ICONES } from '../utils/icons.js';
import { STATUS_INTERVALO } from '../utils/enums.js';
import { formatarHora, formatarContagemRegressiva } from '../utils/formatters.js';

/**
 * celula-intervalo.js — renderiza a célula de Lanche/Refeição da tabela
 * de acompanhamento. As duas etapas compartilham a mesma máquina de
 * estados (classe `Intervalo`, ver shared/entities/etapa-jornada.entity.js),
 * então este único renderer cobre as duas colunas.
 *
 * Estados visuais observados no sistema em produção (ver prints anexados):
 *   - AGUARDANDO_JANELA / AGUARDANDO_ESCOLHA → mostra a janela ("02:00 às 04:30")
 *   - ATRASADO (etapa anterior atrasada e pendente de justificativa)
 *          → <select> "Justificativa..." + botão de envio
 *   - LIBERADO_PARA_ACAO → botão verde de ação ("Start Lanche"/"Start Refeição"
 *     no totem, "Liberar Refeição" no CCP — rótulo vem de `rotuloAcao`)
 *   - EM_ANDAMENTO → "início→agora" (hora de início em âmbar, tempo
 *     decorrido em verde), ou contagem regressiva mm:ss no contexto CCP
 *     quando `mostrarContagem` é true
 *   - CONCLUIDO → horário final, texto neutro
 *
 * @param {import('../entities/etapa-jornada.entity.js').Intervalo} etapa
 * @param {{
 *   rotuloAcao: string,
 *   aoAcionar: () => void,
 *   aoEnviarJustificativa: (texto: string) => void,
 *   timeService: import('../services/time.service.js').TimeService,
 *   mostrarContagem?: boolean,
 * }} opcoes
 */
export function renderCelulaIntervalo(etapa, opcoes) {
  const el = document.createElement('div');
  el.className = 'celula-intervalo';

  switch (etapa.statusLabel()) {
    case STATUS_INTERVALO.ATRASADO: {
      el.appendChild(criarJustificativaInline(opcoes.aoEnviarJustificativa));
      break;
    }

    case STATUS_INTERVALO.LIBERADO_PARA_ACAO: {
      const botao = document.createElement('button');
      botao.className = 'celula-intervalo__acao';
      botao.textContent = opcoes.rotuloAcao;
      botao.addEventListener('click', () => opcoes.aoAcionar?.());
      el.appendChild(botao);
      break;
    }

    case STATUS_INTERVALO.EM_ANDAMENTO: {
      el.appendChild(criarIndicadorAndamento(etapa, opcoes));
      break;
    }

    case STATUS_INTERVALO.CONCLUIDO: {
      const span = document.createElement('span');
      span.className = 'celula-intervalo__concluido fonte-mono';
      span.textContent = formatarHora(etapa.dados.dataHoraProntidao ?? etapa.dados.dataHoraInicio);
      el.appendChild(span);
      break;
    }

    case STATUS_INTERVALO.AGUARDANDO_ESCOLHA:
    case STATUS_INTERVALO.AGUARDANDO_JANELA:
    case STATUS_INTERVALO.AGUARDANDO_REFEICAO:
    default: {
      const span = document.createElement('span');
      span.className = 'celula-intervalo__janela fonte-mono';
      span.textContent = etapa.dados.janela ?? '--:-- às --:--';
      el.appendChild(span);
      break;
    }
  }

  return el;
}

function criarJustificativaInline(aoEnviar) {
  const wrap = document.createElement('div');
  wrap.className = 'celula-intervalo__justificativa';
  wrap.innerHTML = `
    <select aria-label="Motivo da justificativa">
      <option value="">Justificativa...</option>
      <option value="AGUARDANDO_LIBERACAO">Aguardando liberação</option>
      <option value="ATIVIDADE_OPERACIONAL">Atividade operacional</option>
      <option value="OUTRO">Outro</option>
    </select>
    <button type="button" class="celula-intervalo__enviar" title="Enviar justificativa">${ICONES.enviar}</button>
  `;
  wrap.querySelector('button').addEventListener('click', () => {
    const valor = wrap.querySelector('select').value;
    if (valor) aoEnviar?.(valor);
  });
  return wrap;
}

function criarIndicadorAndamento(etapa, { timeService, mostrarContagem, duracaoMin }) {
  if (mostrarContagem && timeService) {
    const span = document.createElement('span');
    span.className = 'celula-intervalo__contagem fonte-mono';

    function atualizar(agora) {
      const inicio = new Date(etapa.dados.dataHoraInicio).getTime();
      const fim = inicio + (duracaoMin ?? 15) * 60_000;
      const restante = (fim - agora.getTime()) / 1000;
      span.textContent = formatarContagemRegressiva(restante);
      span.dataset.excedido = String(restante <= 0);
    }
    atualizar(timeService.agora());
    timeService.onTick(atualizar);
    return span;
  }

  const wrap = document.createElement('span');
  wrap.className = 'celula-intervalo__andamento fonte-mono';
  wrap.innerHTML = `
    <span class="celula-intervalo__inicio">${formatarHora(etapa.dados.dataHoraInicio)}</span>
    <span class="celula-intervalo__seta">&rarr;</span>
    <span class="celula-intervalo__agora"></span>
  `;
  const elAgora = wrap.querySelector('.celula-intervalo__agora');
  if (timeService) {
    const atualizar = (agora) => { elAgora.textContent = formatarHora(agora.toISOString()); };
    atualizar(timeService.agora());
    timeService.onTick(atualizar);
  }
  return wrap;
}
