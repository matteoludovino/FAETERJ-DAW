async function apiFetch(method, endpoint, data = null) {
  const token = localStorage.getItem('fc_token');

  const options = {
    method,
    headers: {
      'Content-Type': 'application/json',
      ...(token ? { 'Authorization': `Bearer ${token}` } : {})
    }
  };

  if (data && method !== 'GET') {
    options.body = JSON.stringify(data);
  }

  let url = `${API_BASE}${endpoint}`;
  if (data && method === 'GET') {
    const params = new URLSearchParams(
      Object.fromEntries(Object.entries(data).filter(([, v]) => v !== null && v !== ''))
    );
    if (params.toString()) url += '?' + params.toString();
  }

  let response;
  try {
    response = await fetch(url, options);
  } catch (err) {
    throw new Error('Sem conexão com o servidor. Verifique sua internet.');
  }

  let json;
  try {
    json = await response.json();
  } catch {
    throw new Error('Resposta inválida do servidor.');
  }

  if (!response.ok) {
    const msg = json.message || `Erro ${response.status}`;
    throw Object.assign(new Error(msg), { status: response.status, errors: json.errors });
  }

  return json;
}

const API = {
  get:    (ep, params) => apiFetch('GET',    ep, params),
  post:   (ep, data)   => apiFetch('POST',   ep, data),
  put:    (ep, data)   => apiFetch('PUT',    ep, data),
  delete: (ep)         => apiFetch('DELETE', ep),

  auth: {
    login:    (body)  => API.post('/auth/login',    body),
    register: (body)  => API.post('/auth/register', body),
    logout:   ()      => API.post('/auth/logout',   {}),
    me:       ()      => API.get('/auth/me'),
  },

  veiculos: {
    list:         (q)     => API.get('/veiculos', q),
    get:          (id)    => API.get(`/veiculos/${id}`),
    create:       (body)  => API.post('/veiculos', body),
    update:       (id, b) => API.put(`/veiculos/${id}`, b),
    updateStatus: (id, s) => API.put(`/veiculos/${id}/status`, { status: s }),
    delete:       (id)    => API.delete(`/veiculos/${id}`),
  },

  clientes: {
    list:   (q)     => API.get('/clientes', q),
    get:    (id)    => API.get(`/clientes/${id}`),
    create: (body)  => API.post('/clientes', body),
    update: (id, b) => API.put(`/clientes/${id}`, b),
    delete: (id)    => API.delete(`/clientes/${id}`),
  },

  lojas: {
    list:   (q)     => API.get('/lojas', q),
    get:    (id)    => API.get(`/lojas/${id}`),
    create: (body)  => API.post('/lojas', body),
    update: (id, b) => API.put(`/lojas/${id}`, b),
    delete: (id)    => API.delete(`/lojas/${id}`),
  },

  reservas: {
    list:     (q)     => API.get('/reservas', q),
    get:      (id)    => API.get(`/reservas/${id}`),
    create:   (body)  => API.post('/reservas', body),
    cancelar: (id, b) => API.post(`/reservas/${id}/cancelar`, b),
    retirar:  (id, b) => API.post(`/reservas/${id}/retirar`, b),
    devolver: (id, b) => API.post(`/reservas/${id}/devolver`, b),
  },

  pagamentos: {
    list: (q)  => API.get('/pagamentos', q),
    get:  (id) => API.get(`/pagamentos/${id}`),
  },

  motoristas: {
    list:   (q)     => API.get('/motoristas', q),
    get:    (id)    => API.get(`/motoristas/${id}`),
    create: (body)  => API.post('/motoristas', body),
    update: (id, b) => API.put(`/motoristas/${id}`, b),
    delete: (id)    => API.delete(`/motoristas/${id}`),
  },

  relatorios: {
    dashboard:  ()  => API.get('/relatorios/dashboard'),
    reservas:   (q) => API.get('/relatorios/reservas', q),
    veiculos:   (q) => API.get('/relatorios/veiculos', q),
    financeiro: (q) => API.get('/relatorios/financeiro', q),
  },
};
