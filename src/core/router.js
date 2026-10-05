/**
 * router.js — roteador baseado em hash (#/rota?query=...), sem dependências.
 * Cada módulo se registra com um path e uma função `montar(outlet, params)`
 * — `params` é um `URLSearchParams` já pronto com a querystring da rota
 * (ex.: `#/apresentacao?supervisao=VPN&local=Guarita_2` → params.get('local')
 * === 'Guarita_2'). A função pode devolver um `desmontar()` opcional para
 * limpar listeners/timers ao trocar de tela.
 *
 * Por que hash e não History API: o servidor IIS só serve estático puro,
 * então não há como configurar fallback de rota no servidor — hash evita
 * 404 em refresh (#/apresentacao?... funciona sem nenhuma config extra no IIS).
 *
 * Por que querystring em vez de rota por segmento (`/apresentacao/VPN/Guarita_2`):
 * cada totem/CPT é fixado numa URL única (ver modules/menu) — querystring
 * nomeada deixa explícito, ao olhar o link, qual chave é supervisão e qual
 * é local, o que ajuda quando alguém for configurar um totem novo copiando
 * o link da tela de Menu.
 */
class Router {
  #rotas = new Map();
  #outlet = null;
  #desmontarAtual = null;
  #rotaPadrao = '/menu';

  init(outletSelector) {
    this.#outlet = document.querySelector(outletSelector);
    window.addEventListener('hashchange', () => this.#resolver());
    this.#resolver();
  }

  registrar(path, montar) {
    this.#rotas.set(path, montar);
    return this;
  }

  navegar(path) {
    window.location.hash = path;
  }

  #resolver() {
    const bruto = window.location.hash.replace(/^#/, '') || this.#rotaPadrao;
    const [path, querystring = ''] = bruto.split('?');
    const params = new URLSearchParams(querystring);
    const montar = this.#rotas.get(path) ?? this.#rotas.get('/nao-encontrado');

    if (this.#desmontarAtual) {
      this.#desmontarAtual();
      this.#desmontarAtual = null;
    }
    if (!this.#outlet) return;

    this.#outlet.innerHTML = '';
    if (montar) {
      const resultado = montar(this.#outlet, params);
      if (typeof resultado === 'function') this.#desmontarAtual = resultado;
    }
  }
}

export const router = new Router();
