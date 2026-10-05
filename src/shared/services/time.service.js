/**
 * time.service.js — relógio único da aplicação. Em vez de cada
 * componente usar `new Date()` do navegador (que pode estar
 * dessincronizado do relógio do servidor/pátio), tudo lê daqui.
 *
 * Estratégia: guarda o "offset" entre o serverTime recebido em
 * `info.serverTime` e o clock local, e recalcula a cada resposta da
 * API. Componentes assinam `onTick` para atualizar relógios/cronômetros
 * sem cada um criar seu próprio setInterval.
 */
export class TimeService {
  #offsetMs = 0;
  #listeners = new Set();
  #intervalId = null;

  constructor(apiService) {
    this.apiService = apiService;
  }

  /** Chame sempre que uma resposta da API trouxer `info.serverTime`. */
  sincronizarComServidor(serverTimeIso) {
    if (!serverTimeIso) return;
    this.#offsetMs = new Date(serverTimeIso).getTime() - Date.now();
  }

  agora() {
    return new Date(Date.now() + this.#offsetMs);
  }

  iniciarSincronizacao(intervaloMs = 1000) {
    if (this.#intervalId) return;
    this.#intervalId = setInterval(() => {
      const agora = this.agora();
      this.#listeners.forEach((cb) => cb(agora));
    }, intervaloMs);
  }

  pararSincronizacao() {
    clearInterval(this.#intervalId);
    this.#intervalId = null;
  }

  onTick(callback) {
    this.#listeners.add(callback);
    return () => this.#listeners.delete(callback); // função de "unsubscribe"
  }
}
