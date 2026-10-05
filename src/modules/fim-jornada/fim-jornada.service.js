/**
 * fim-jornada.service.js — chamadas de rede específicas da etapa "Fim de Jornada".
 * Delegue ao ApiService compartilhado; adicione aqui só a regra que for
 * exclusiva desta etapa (validações, agregações, etc).
 */
export class FimJornadaService {
  constructor(apiService) {
    this.apiService = apiService;
  }

  async buscarPainel(contexto) {
    return this.apiService.buscarPainel(contexto);
  }

  // TODO: métodos de ação específicos de "Fim de Jornada"
  // ex.: escolherIntervalo(), iniciar(), enviarJustificativa()
}
