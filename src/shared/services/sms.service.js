/**
 * sms.service.js — envio de alerta (SMS/chamada CCP) quando um empregado
 * está atrasado em alguma etapa. Hoje isso provavelmente é feito por um
 * endpoint ASP separado; encapsule a chamada real aqui quando integrar.
 */
export class SmsService {
  async notificarAtraso({ matricula, etapa, minutosAtraso }) {
    // TODO: integrar com endpoint real de notificação (ex.: /asp/cpt_alerta.asp)
    console.info(`[SmsService] Alerta de atraso — matrícula ${matricula}, etapa ${etapa}, ${minutosAtraso}min`);
  }
}
