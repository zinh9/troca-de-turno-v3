/**
 * apresentacao.service.js — regra de acesso a dados específica da etapa
 * de Apresentação. Delega a chamada de rede real ao ApiService
 * compartilhado; aqui entra qualquer transformação/regra que seja
 * exclusiva desta etapa (ex.: validação de matrícula antes de enviar).
 */
export class ApresentacaoService {
  constructor(apiService) {
    this.apiService = apiService;
  }

  async buscarPainel(contexto) {
    return this.apiService.buscarPainel(contexto);
  }

  async apresentar(matricula) {
    // TODO: validar formato de matrícula do pátio antes de enviar, se necessário
    return this.apiService.registrarApresentacao(matricula);
  }
}
