import { mapearEmpregados } from '../entities/empregado.entity.js';

/**
 * ApiService — ÚNICO ponto de acesso à rede no front. Nenhum component
 * ou entity deve chamar `fetch` diretamente; sempre passe por um método
 * daqui. Isso mantém os componentes puros (fáceis de testar) e dá um
 * lugar único para tratar timeout, erro de rede e o modo "manutenção".
 *
 * O shape de resposta esperado é o contrato descrito no README (seção 4)
 * — `success`, `info`, `empregados`. O back-end de destino é uma API em
 * C# .NET (ver README, seção 6); nada aqui precisa mudar por causa disso
 * além do valor de `baseUrl`, contanto que o shape do JSON seja mantido.
 */
export class ApiService {
  #baseUrl;
  #timeoutMs;

  constructor({ baseUrl = '/api', timeoutMs = 8000 } = {}) {
    this.#baseUrl = baseUrl;
    this.#timeoutMs = timeoutMs;
  }

  /** Busca o painel completo de acompanhamento de uma guarita/supervisão. */
  async buscarPainel({ supervisao, local }) {
    const params = new URLSearchParams({ supervisao, local });
    const json = await this.#request(`/painel?${params}`);

    if (json.info?.emManutencao) {
      return { emManutencao: true, info: json.info, empregados: [] };
    }

    return {
      emManutencao: false,
      info: json.info,
      empregados: mapearEmpregados(json.empregados),
    };
  }

  /**
   * Busca a estrutura de supervisões e locais (guaritas) — usada pela
   * tela de Menu para montar os links de cada totem e do CCP.
   * Ver README, seção 4.3 — endpoint novo, ainda não existe no back-end.
   */
  async buscarEstrutura() {
    return this.#request('/estrutura');
  }

  async registrarApresentacao(matricula) {
    return this.#request('/apresentacao', { method: 'POST', body: { matricula } });
  }

  async registrarProntidao(matricula) {
    return this.#request('/prontidao', { method: 'POST', body: { matricula } });
  }

  async escolherIntervaloLanche(matricula, intervaloEscolhido) {
    return this.#request('/lanche/escolha', { method: 'POST', body: { matricula, intervaloEscolhido } });
  }

  async iniciarIntervalo(etapa, matricula) {
    // etapa: 'lanche' | 'refeicao'
    return this.#request(`/${etapa}/iniciar`, { method: 'POST', body: { matricula } });
  }

  async enviarJustificativa(etapa, matricula, texto) {
    return this.#request(`/${etapa}/justificativa`, { method: 'POST', body: { matricula, texto } });
  }

  /** CCP — chamada via rádio para empregado com prontidão excedida. */
  async acionarChamadaRadio(matricula) {
    return this.#request('/ccp/chamada', { method: 'POST', body: { matricula } });
  }

  async finalizarJornada(matricula) {
    return this.#request('/fim-jornada', { method: 'POST', body: { matricula } });
  }

  async #request(path, { method = 'GET', body } = {}) {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), this.#timeoutMs);

    try {
      const resp = await fetch(`${this.#baseUrl}${path}`, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: body ? JSON.stringify(body) : undefined,
        signal: controller.signal,
      });

      const json = await resp.json();
      if (!resp.ok || json.success === false) {
        throw new ApiError(json.mensagem ?? `Falha na requisição: ${path}`, resp.status);
      }
      return json;
    } catch (erro) {
      if (erro.name === 'AbortError') {
        throw new ApiError('Tempo de resposta excedido — verifique a rede do pátio.', 0);
      }
      throw erro;
    } finally {
      clearTimeout(timeoutId);
    }
  }
}

export class ApiError extends Error {
  constructor(mensagem, statusHttp) {
    super(mensagem);
    this.name = 'ApiError';
    this.statusHttp = statusHttp;
  }
}
