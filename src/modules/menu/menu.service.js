/**
 * menu.service.js — busca a estrutura de supervisões/locais usada para
 * montar os links da tela de Menu. Ver README seção 4.3 (GET /api/estrutura
 * — endpoint novo, ainda não existe no back-end).
 */
export class MenuService {
  constructor(apiService) {
    this.apiService = apiService;
  }

  async buscarEstrutura() {
    const json = await this.apiService.buscarEstrutura();
    return json.supervisoes ?? [];
  }
}
