/**
 * lanche.service.js — chamadas de rede específicas da etapa "Lanche".
 * Delegue ao ApiService compartilhado; adicione aqui só a regra que for
 * exclusiva desta etapa (validações, agregações, etc).
 */
export class LancheService {
  constructor(apiService) {
    this.apiService = apiService;
  }

  async buscarPainel(contexto) {
    return this.apiService.buscarPainel(contexto);
  }

  // TODO: métodos de ação específicos de "Lanche"
  // ex.: escolherIntervalo(), iniciar(), enviarJustificativa()
}
