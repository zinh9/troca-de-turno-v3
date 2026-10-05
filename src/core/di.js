/**
 * di.js — injeção de dependência leve, sem decorators/reflection.
 *
 * Uso típico dentro de um *.module.js:
 *
 *   import { container } from '../../core/di.js';
 *   import { ApiService } from '../../shared/services/api.service.js';
 *   import { ApresentacaoService } from './apresentacao.service.js';
 *
 *   container.registerSingleton('apiService', () => new ApiService());
 *   container.registerSingleton('apresentacaoService',
 *     (c) => new ApresentacaoService(c.resolve('apiService')));
 *
 * Cada módulo registra só o que é seu; o container é global e
 * compartilhado (singleton do processo do navegador), evitando
 * reconstruir services a cada troca de rota.
 */
class DiContainer {
  #factories = new Map();
  #instances = new Map();

  registerSingleton(nome, factory) {
    this.#factories.set(nome, factory);
    return this;
  }

  resolve(nome) {
    if (this.#instances.has(nome)) return this.#instances.get(nome);

    const factory = this.#factories.get(nome);
    if (!factory) {
      throw new Error(`[di] Dependência não registrada: "${nome}"`);
    }
    const instancia = factory(this);
    this.#instances.set(nome, instancia);
    return instancia;
  }

  has(nome) {
    return this.#factories.has(nome);
  }
}

export const container = new DiContainer();
