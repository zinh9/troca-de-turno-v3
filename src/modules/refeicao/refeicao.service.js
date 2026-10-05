/**
 * refeicao.service.js — chamadas de rede específicas da etapa "Refeição".
 * Delegue ao ApiService compartilhado; adicione aqui só a regra que for
 * exclusiva desta etapa (validações, agregações, etc).
 */
export class RefeicaoService {
  constructor(apiService) {
    this.apiService = apiService;
  }

  async buscarPainel(contexto) {
    return this.apiService.buscarPainel(contexto);
  }

  // TODO: métodos de ação específicos de "Refeição"
  // ex.: escolherIntervalo(), iniciar(), enviarJustificativa()
}
