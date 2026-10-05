/**
 * Modal — usado pelas telas de "manutenção" e "erro de sistema", entre
 * outros usos futuros (confirmação de justificativa, etc).
 *
 * @param {{tipo: 'manutencao'|'erro'|'info', titulo: string, mensagem: string, acaoRotulo?: string, aoAcionar?: () => void}} opcoes
 */
export function abrirModal({ tipo = 'info', titulo, mensagem, acaoRotulo, aoAcionar }) {
  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay';

  const caixa = document.createElement('div');
  caixa.className = `modal superficie-glass modal--${tipo}`;
  caixa.setAttribute('role', 'alertdialog');
  caixa.setAttribute('aria-modal', 'true');

  const icone = document.createElement('div');
  icone.className = 'modal__icone';
  icone.textContent = { manutencao: '\u{1F527}', erro: '!', info: 'i' }[tipo] ?? 'i';

  const elTitulo = document.createElement('h2');
  elTitulo.className = 'modal__titulo';
  elTitulo.textContent = titulo;

  const elMensagem = document.createElement('p');
  elMensagem.className = 'modal__mensagem texto-secundario';
  elMensagem.textContent = mensagem;

  caixa.append(icone, elTitulo, elMensagem);

  if (acaoRotulo) {
    const botao = document.createElement('button');
    botao.className = 'modal__acao';
    botao.textContent = acaoRotulo;
    botao.addEventListener('click', () => {
      aoAcionar?.();
      fechar();
    });
    caixa.appendChild(botao);
  }

  overlay.appendChild(caixa);
  document.body.appendChild(overlay);

  function fechar() {
    overlay.remove();
  }

  return { fechar };
}
