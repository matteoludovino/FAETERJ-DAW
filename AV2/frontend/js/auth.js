const Auth = {
  save(token, user) {
    localStorage.setItem('fc_token', token);
    localStorage.setItem('fc_user', JSON.stringify(user));
  },

  getUser() {
    const u = localStorage.getItem('fc_user');
    return u ? JSON.parse(u) : null;
  },

  isAuthenticated() {
    return !!localStorage.getItem('fc_token');
  },

  isAdmin() {
    const u = this.getUser();
    return u && (u.tipo === 'admin' || u.tipo === 'funcionario');
  },

  isCliente() {
    const u = this.getUser();
    return u && u.tipo === 'cliente';
  },

  clear() {
    localStorage.removeItem('fc_token');
    localStorage.removeItem('fc_user');
  },

  requireAuth(redirectTo = null) {
    if (!this.isAuthenticated()) {
      const dest = redirectTo || window.location.href;
      window.location.href = `${getRootPath()}/frontend/login.html?redirect=${encodeURIComponent(dest)}`;
      return null;
    }
    return this.getUser();
  },

  requireAdmin() {
    const user = this.requireAuth();
    if (!user) return null;
    if (!this.isAdmin()) {
      window.location.href = `${getRootPath()}/frontend/cliente/dashboard.html`;
      return null;
    }
    return user;
  },

  requireCliente() {
    const user = this.requireAuth();
    if (!user) return null;
    if (!this.isCliente()) {
      window.location.href = `${getRootPath()}/frontend/admin/dashboard.html`;
      return null;
    }
    return user;
  },

  async logout() {
    try {
      await API.auth.logout();
    } catch (e) {
    }
    this.clear();
    window.location.href = `${getRootPath()}/frontend/login.html`;
  }
};

function getRootPath() {
  const parts = window.location.pathname.split('/');
  const idx   = parts.indexOf('falls-car');
  if (idx === -1) return '';
  return '/' + parts.slice(0, idx + 1).join('/');
}
