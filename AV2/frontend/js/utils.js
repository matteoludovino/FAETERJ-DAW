function fmtMoeda(v) {
  return Number(v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function fmtData(s) {
  if (!s) return '—';
  const d = new Date(s + (s.includes('T') ? '' : 'T00:00:00'));
  return d.toLocaleDateString('pt-BR');
}

function fmtDataHora(s) {
  if (!s) return '—';
  return new Date(s).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
}

function toInputDate(s) {
  if (!s) return '';
  return s.split('T')[0];
}

function addDias(dateStr, dias) {
  const d = new Date(dateStr + 'T00:00:00');
  d.setDate(d.getDate() + dias);
  return d.toISOString().split('T')[0];
}

function pluralNome(n, singular, plural) {
  return `${n} ${n === 1 ? singular : plural}`;
}

function capitalize(s) {
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
}

function fmtCPF(v) {
  return v ? v.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4') : '—';
}

function fmtTel(v) {
  if (!v) return '—';
  const d = v.replace(/\D/g, '');
  if (d.length === 11) return d.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
  if (d.length === 10) return d.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
  return v;
}

const Toast = {
  _container: null,

  _getContainer() {
    if (!this._container) {
      this._container = document.getElementById('toast-container');
      if (!this._container) {
        this._container = document.createElement('div');
        this._container.id = 'toast-container';
        this._container.className = 'toast-container';
        document.body.appendChild(this._container);
      }
    }
    return this._container;
  },

  show(msg, type = 'info', duration = 3500) {
    const icons = { success: '✓', error: '✕', info: 'ℹ' };
    const container = this._getContainer();

    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `
      <span class="toast-icon">${icons[type] || icons.info}</span>
      <span class="toast-msg">${msg}</span>
      <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
    `;
    container.appendChild(el);
    requestAnimationFrame(() => el.style.opacity = '1');

    setTimeout(() => {
      el.style.opacity = '0';
      el.style.transform = 'translateX(110%)';
      setTimeout(() => el.remove(), 300);
    }, duration);
  },

  success(msg) { this.show(msg, 'success'); },
  error(msg)   { this.show(msg, 'error', 5000); },
  info(msg)    { this.show(msg, 'info'); },
};


const Modal = {
  open(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('open');
    document.body.style.overflow = 'hidden';

    this._onKey = (e) => { if (e.key === 'Escape') this.close(id); };
    document.addEventListener('keydown', this._onKey);

    el.onclick = (e) => { if (e.target === el) this.close(id); };
  },

  close(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('open');
    document.body.style.overflow = '';
    if (this._onKey) document.removeEventListener('keydown', this._onKey);
  },

  closeAll() {
    document.querySelectorAll('.modal-overlay.open').forEach(el => {
      el.classList.remove('open');
    });
    document.body.style.overflow = '';
  },
};

function tableEmpty(tbody, msg = 'Nenhum registro encontrado', cols = 5) {
  tbody.innerHTML = `
    <tr>
      <td colspan="${cols}" style="text-align:center; padding: 48px 0; color: var(--fc-text-muted);">
        <div style="font-size:2rem;margin-bottom:8px;opacity:.3">📭</div>
        <div>${msg}</div>
      </td>
    </tr>
  `;
}

function tableLoading(tbody, cols = 5) {
  tbody.innerHTML = `
    <tr>
      <td colspan="${cols}" style="text-align:center; padding: 48px 0;">
        <div class="spinner" style="margin: 0 auto;"></div>
      </td>
    </tr>
  `;
}

function confirmar(msg) {
  return new Promise(resolve => {
    resolve(window.confirm(msg));
  });
}

function formClearErrors(form) {
  form.querySelectorAll('.form-error').forEach(el => el.remove());
  form.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
}

function formShowErrors(form, errors) {
  if (!errors) return;
  Object.entries(errors).forEach(([field, msg]) => {
    const input = form.querySelector(`[name="${field}"]`);
    if (!input) return;
    input.classList.add('is-invalid');
    const err = document.createElement('span');
    err.className = 'form-error';
    err.textContent = msg;
    input.parentNode.appendChild(err);
  });
}

function formData(form) {
  const fd = new FormData(form);
  const obj = {};
  fd.forEach((v, k) => { obj[k] = v; });
  return obj;
}

function renderAdminSidebar(activePage) {
  const user = Auth.getUser();
  const root = getRootPath();

  const nav = [
    { id: 'dashboard',  icon: '📊', label: 'Dashboard',   href: `${root}/frontend/admin/dashboard.html` },
    { id: 'reservas',   icon: '📋', label: 'Reservas',     href: `${root}/frontend/admin/reservas.html` },
    { id: 'veiculos',   icon: '🚗', label: 'Veículos',     href: `${root}/frontend/admin/veiculos.html` },
    { id: 'clientes',   icon: '👥', label: 'Clientes',     href: `${root}/frontend/admin/clientes.html` },
    { id: 'lojas',      icon: '🏪', label: 'Lojas',        href: `${root}/frontend/admin/lojas.html` },
    { id: 'motoristas', icon: '🧑‍✈️', label: 'Motoristas', href: `${root}/frontend/admin/motoristas.html` },
    { id: 'relatorios', icon: '📈', label: 'Relatórios',   href: `${root}/frontend/admin/relatorios.html` },
  ];

  const navHTML = nav.map(item => `
    <a href="${item.href}" class="nav-item${activePage === item.id ? ' active' : ''}">
      <span class="nav-icon">${item.icon}</span>
      ${item.label}
    </a>
  `).join('');

  const el = document.getElementById('sidebar');
  if (!el) return;
  el.innerHTML = `
    <div class="sidebar-logo">
      <div class="logo-mark"><span class="logo-icon">🚗</span></div>
      <div>
        <div class="logo-text">Falls Car</div>
        <div class="logo-sub">Admin</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Menu Principal</div>
      ${navHTML}
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="user-avatar">${(user?.nome || 'A').charAt(0).toUpperCase()}</div>
        <div class="user-info">
          <div class="user-name">${user?.nome || 'Usuário'}</div>
          <div class="user-role">${capitalize(user?.tipo || 'admin')}</div>
        </div>
        <button class="btn-logout" title="Sair" onclick="Auth.logout()">⏏</button>
      </div>
    </div>
  `;
}

function renderClienteSidebar(activePage) {
  const user = Auth.getUser();
  const root = getRootPath();

  const nav = [
    { id: 'dashboard', icon: '🏠', label: 'Início',        href: `${root}/frontend/cliente/dashboard.html` },
    { id: 'veiculos',  icon: '🚗', label: 'Veículos',       href: `${root}/frontend/cliente/veiculos.html` },
    { id: 'reservar',  icon: '➕', label: 'Nova Reserva',   href: `${root}/frontend/cliente/reservar.html` },
    { id: 'reservas',  icon: '📋', label: 'Minhas Reservas',href: `${root}/frontend/cliente/minhas-reservas.html` },
    { id: 'perfil',    icon: '👤', label: 'Meu Perfil',     href: `${root}/frontend/cliente/perfil.html` },
  ];

  const navHTML = nav.map(item => `
    <a href="${item.href}" class="nav-item${activePage === item.id ? ' active' : ''}">
      <span class="nav-icon">${item.icon}</span>
      ${item.label}
    </a>
  `).join('');

  const el = document.getElementById('sidebar');
  if (!el) return;
  el.innerHTML = `
    <div class="sidebar-logo">
      <div class="logo-mark"><span class="logo-icon">🚗</span></div>
      <div>
        <div class="logo-text">Falls Car</div>
        <div class="logo-sub">Área do Cliente</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Navegação</div>
      ${navHTML}
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="user-avatar">${(user?.nome || 'C').charAt(0).toUpperCase()}</div>
        <div class="user-info">
          <div class="user-name">${user?.nome || 'Cliente'}</div>
          <div class="user-role">Cliente</div>
        </div>
        <button class="btn-logout" title="Sair" onclick="Auth.logout()">⏏</button>
      </div>
    </div>
  `;
}
