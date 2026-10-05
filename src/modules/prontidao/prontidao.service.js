/**
 * prontidao.service.js — chamadas de rede específicas da etapa "Prontidão".
 * Delegue ao ApiService compartilhado; adicione aqui só a regra que for
 * exclusiva desta etapa (validações, agregações, etc).
 */
export class ProntidaoService {
  constructor(apiService) {
    this.apiService = apiService;
  }

  async buscarPainel(contexto) {
    return this.apiService.buscarPainel(contexto);
  }

  // TODO: métodos de ação específicos de "Prontidão"
  // ex.: escolherIntervalo(), iniciar(), enviarJustificativa()
}
