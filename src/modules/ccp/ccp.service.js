/**
 * ccp.service.js — chamadas de rede específicas da tela de supervisão
 * (CCP — Centro de Controle de Pátio). O CCP enxerga todas as torres/
 * guaritas e pode agir em nome do empregado (liberar refeição, acionar
 * chamada via rádio) em vez de o próprio empregado agir no totem.
 */
export class CcpService {
  constructor(apiService) {
    this.apiService = apiService;
  }

  async buscarPainel(contexto) {
    return this.apiService.buscarPainel(contexto);
  }

  /** Aciona o alerta sonoro/rádio para o supervisor chamar o empregado atrasado. */
  async acionarChamadaRadio(matricula) {
    // TODO: integrar com o endpoint real (ver README.md — POST /ccp/chamada)
    console.info('[CcpService] acionarChamadaRadio ->', matricula);
    return { success: true };
  }

  async liberarRefeicao(matricula) {
    return this.apiService.iniciarIntervalo('refeicao', matricula);
  }

  async liberarLanche(matricula) {
    return this.apiService.iniciarIntervalo('lanche', matricula);
  }
}
