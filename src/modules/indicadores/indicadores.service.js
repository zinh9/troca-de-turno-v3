/**
 * indicadores.service.js — hoje o back-end ainda não expõe um endpoint
 * agregado; a ideia é criar um `/api/indicadores?supervisao=...&periodo=...`
 * que devolva séries já prontas (evita recalcular no front a partir de
 * milhares de jornadas). Enquanto isso não existe, este método busca o
 * painel e agrega no cliente — troque por uma chamada direta assim que
 * o endpoint agregado existir no ASP.
 */
export class IndicadoresService {
  constructor(apiService) {
    this.apiService = apiService;
  }

  async buscarIndicadores(contexto) {
    const { empregados } = await this.apiService.buscarPainel(contexto);

    const totalAtrasados = empregados.filter((e) => e.estaAtrasado()).length;
    const totalConcluidos = empregados.filter((e) => e.jornadaCompleta()).length;

    return {
      totalEmpregados: empregados.length,
      totalAtrasados,
      totalConcluidos,
      taxaConformidade: empregados.length
        ? Math.round(((empregados.length - totalAtrasados) / empregados.length) * 100)
        : 100,
    };
  }
}
