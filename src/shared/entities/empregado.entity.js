import { criarEtapasJornada } from './etapa-jornada.entity.js';
import { ETAPAS_JORNADA } from '../utils/enums.js';

/**
 * Empregado — entidade de domínio construída a partir de um item do
 * array `empregados` do novo contrato JSON (ver seção 3 do briefing).
 */
export class Empregado {
  constructor(json) {
    this.matricula = json.matricula;
    this.nome = json.nome;
    this.cargo = json.cargo;
    this.supervisao = json.supervisao;
    this.local = json.local;
    this.turno = json.turno;
    this.etapas = criarEtapasJornada(json.jornada);
  }

  /** Etapa atual = primeira etapa, na ordem do fluxo, que ainda não concluiu. */
  etapaAtual() {
    const chave = ETAPAS_JORNADA.find((k) => !this.etapas[k].estaConcluida());
    return chave ? this.etapas[chave] : this.etapas.fimJornada;
  }

  /** true se qualquer etapa está com atraso — usado para destacar a linha na tabela. */
  estaAtrasado() {
    return ETAPAS_JORNADA.some((k) => this.etapas[k].estaAtrasado());
  }

  /** true se todas as etapas foram concluídas — jornada fechada. */
  jornadaCompleta() {
    return this.etapas.fimJornada.estaConcluida();
  }
}

/** Converte o array bruto `empregados` da API em instâncias de domínio. */
export function mapearEmpregados(empregadosJson = []) {
  return empregadosJson.map((e) => new Empregado(e));
}
