const API_BASE = window.location.origin + '/falls-car/backend/api';

const PERIODOS = [7, 15, 30];

const CATEGORIAS = ['economico','compacto','sedan','suv','luxo','pickup'];
const CATEGORIAS_LABEL = {
  economico: 'Econômico', compacto: 'Compacto', sedan: 'Sedan',
  suv: 'SUV', luxo: 'Luxo', pickup: 'Pickup'
};

const STATUS_VEICULO = {
  livre: 'Disponível', alugado: 'Alugado',
  reservado: 'Reservado', manutencao: 'Manutenção'
};
const STATUS_RESERVA = {
  pendente: 'Pendente', confirmada: 'Confirmada', ativa: 'Em Andamento',
  concluida: 'Concluída', cancelada: 'Cancelada'
};
