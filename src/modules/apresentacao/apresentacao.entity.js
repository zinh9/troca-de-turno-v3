/**
 * apresentacao.entity.js
 * A entidade de domínio "Empregado" e a etapa "Apresentacao" são
 * compartilhadas entre módulos (ver /shared/entities), pois o mesmo
 * empregado atravessa todas as etapas da jornada. Este arquivo existe
 * para manter a convenção de pasta por módulo e é o lugar certo para
 * adicionar, no futuro, qualquer regra de domínio que seja EXCLUSIVA
 * da tela de apresentação (ex.: uma classe de ViewModel local).
 */
export { Empregado } from '../../shared/entities/empregado.entity.js';
export { Apresentacao } from '../../shared/entities/etapa-jornada.entity.js';
