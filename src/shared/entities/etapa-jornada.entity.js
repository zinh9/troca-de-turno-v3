/**
 * etapa-jornada.entity.js — classes de domínio para cada etapa da jornada.
 * Estas classes encapsulam a "inteligência" que hoje está espalhada em
 * `if`s de VBScript dentro da página. Um component nunca deve interpretar
 * o JSON cru — ele instancia estas classes e chama métodos de conveniência.
 */
import { STATUS_APRESENTACAO, STATUS_PRONTIDAO, STATUS_INTERVALO } from '../utils/enums.js';
import { formatarDuracaoMin } from '../utils/formatters.js';

export class EtapaJornada {
  constructor(dados = {}) {
    this.dados = dados;
  }

  /** Sobrescrito por subclasse. Rótulo pronto para exibição no stepper/badge. */
  statusLabel() {
    return this.dados.status ?? '—';
  }

  /** Sobrescrito por subclasse. */
  estaConcluida() {
    return false;
  }

  /** Sobrescrito por subclasse quando aplicável (etapas com atraso possível). */
  estaAtrasado() {
    return false;
  }
}

export class Apresentacao extends EtapaJornada {
  estaConcluida() {
    return Boolean(this.dados.dataHora);
  }

  estaAtrasado() {
    return this.dados.status === STATUS_APRESENTACAO.JUSTIFICAR;
  }

  statusLabel() {
    return this.dados.status ?? STATUS_APRESENTACAO.JUSTIFICAR;
  }
}

export class Prontidao extends EtapaJornada {
  estaConcluida() {
    return this.dados.status === STATUS_PRONTIDAO.PRONTO
      || this.dados.status === STATUS_PRONTIDAO.PRONTO_COM_ATRASO;
  }

  estaAtrasado() {
    return this.dados.status === STATUS_PRONTIDAO.PRONTO_COM_ATRASO
      || this.dados.fase === 'ATRASADO_JUSTIFICAR';
  }

  /** Tempo entre apresentação e prontidão, já formatado ("23min"). */
  tempoDesdeApresentacao() {
    return formatarDuracaoMin(this.dados.tempoApresentacaoProntidaoMin);
  }

  /**
   * true quando a tolerância de prontidão estourou e o empregado ainda
   * não confirmou — é o rótulo "EXCEDEU" (âmbar, em negrito) visto no
   * painel de CCP no lugar do horário.
   */
  estaExcedido() {
    return !this.estaConcluida() && this.dados.fase === 'ATRASADO_JUSTIFICAR';
  }

  statusLabel() {
    return this.dados.status ?? STATUS_PRONTIDAO.AGUARDANDO;
  }
}

/** Classe compartilhada por Lanche e Refeição — mesma máquina de estados. */
export class Intervalo extends EtapaJornada {
  estaConcluida() {
    return this.dados.status === STATUS_INTERVALO.CONCLUIDO;
  }

  estaAtrasado() {
    return this.dados.status === STATUS_INTERVALO.ATRASADO;
  }

  estaEmAndamento() {
    return this.dados.status === STATUS_INTERVALO.EM_ANDAMENTO;
  }

  /** Usado pelo componente Cronômetro: null se a etapa não está rodando. */
  inicioParaCronometro() {
    return this.estaEmAndamento() ? this.dados.dataHoraInicio : null;
  }

  statusLabel() {
    return this.dados.status ?? STATUS_INTERVALO.AGUARDANDO_JANELA;
  }
}

export class Lanche extends Intervalo {
  /** "CEDO" | "TARDE" | null */
  intervaloEscolhido() {
    return this.dados.intervaloEscolhido ?? null;
  }
}

export class Refeicao extends Intervalo {}

export class FimJornada extends EtapaJornada {
  estaConcluida() {
    return Boolean(this.dados.dataHora);
  }

  estaAtrasado() {
    return Boolean(this.dados.atrasado);
  }

  statusLabel() {
    if (this.estaConcluida()) return 'CONCLUIDO';
    return this.estaAtrasado() ? 'ATRASADO' : 'AGUARDANDO';
  }
}

/** Fábrica: monta o conjunto de etapas a partir do JSON bruto de um empregado. */
export function criarEtapasJornada(jornadaJson = {}) {
  return {
    apresentacao: new Apresentacao(jornadaJson.apresentacao ?? {}),
    prontidao: new Prontidao(jornadaJson.prontidao ?? {}),
    lanche: new Lanche(jornadaJson.lanche ?? {}),
    refeicao: new Refeicao(jornadaJson.refeicao ?? {}),
    fimJornada: new FimJornada(jornadaJson.fimJornada ?? {}),
  };
}
