/* Remplace le stockage de Claude par api.php : mêmes appels (collection, where, limit, onSnapshot, doc().set/update/delete),
   synchronisés toutes les 5 secondes entre tous les ordinateurs connectés. */
(() => {
  const API = 'api.php';
  const cache = { dossiers: new Map(), collaborateurs: new Map(), regles: new Map() };
  const revs = new Map();
  const listeners = new Set();
  let rev = 0, ready = false, polling = false, offline = false;

  async function call(method, url, body) {
    const r = await fetch(url, {
      method, credentials: 'same-origin', cache: 'no-store',
      headers: { 'X-Echeancier': '1', ...(body ? { 'Content-Type': 'application/json' } : {}) },
      body: body ? JSON.stringify(body) : undefined,
    });
    if (r.status === 401) { location.reload(); throw Object.assign(new Error('Session expirée'), { code: 'unauthenticated' }); }
    const j = await r.json().catch(() => ({}));
    if (!r.ok) throw Object.assign(new Error(j.error || 'Erreur ' + r.status), { code: r.status === 404 || r.status === 400 ? 'invalid_argument' : 'unavailable', status: r.status });
    return j;
  }

  // Un document n'est remplacé que par une version plus récente (évite qu'une lecture lente écrase une écriture).
  function apply(c, id, data, r) {
    const k = c + '/' + id;
    if ((revs.get(k) || 0) > r) return false;
    revs.set(k, r);
    if (data === null) cache[c].delete(id); else cache[c].set(id, data);
    return true;
  }
  const snap = l => ({
    docs: [...cache[l.c]].filter(([, d]) => !l.w || d[l.w[0]] === l.w[2]).slice(0, l.n).map(([id, d]) => ({ id, data: () => d })),
  });
  const notify = cs => listeners.forEach(l => { if (cs.has(l.c)) l.cb(snap(l)); });

  async function poll() {
    if (polling) return;
    polling = true;
    try {
      const changed = new Set();
      for (let more = true; more;) {
        const r = await call('GET', `${API}?since=${rev}`);
        r.docs.forEach(x => { if (cache[x.c] && apply(x.c, x.id, x.data, x.rev)) changed.add(x.c); });
        rev = r.rev; more = r.more;
      }
      setOffline(false);
      if (!ready) { ready = true; listeners.forEach(l => l.cb(snap(l))); } else if (changed.size) notify(changed);
    } catch (e) { setOffline(true); }
    finally { polling = false; }
  }

  async function write(op, c, id, data) {
    const r = await call('POST', API, { op, c, id, data });
    if (apply(c, id, r.data, r.rev)) notify(new Set([c]));
  }

  const db = {
    collection(c) {
      if (!cache[c]) throw Object.assign(new Error('Collection inconnue'), { code: 'invalid_argument' });
      const q = (w, n) => ({
        where: (f, op, v) => q([f, op, v], n),
        limit: k => q(w, k),
        onSnapshot(cb) { const l = { c, w, n: n || Infinity, cb }; listeners.add(l); if (ready) cb(snap(l)); return () => listeners.delete(l); },
        doc: id => ({ set: d => write('set', c, id, d), update: d => write('update', c, id, d), delete: () => write('delete', c, id) }),
      });
      return q(null, 0);
    },
  };

  window.claude = {
    async use(name) {
      if (name === 'db') return db;
      if (name === 'user') return { can: async () => true };
      throw Object.assign(new Error('Indisponible hors Claude'), { code: 'unsupported' }); // l'export Excel passe alors par le téléchargement du navigateur
    },
  };

  // Bandeau discret en cas de coupure réseau, et lien de déconnexion dans l'en-tête.
  function setOffline(v) {
    if (v === offline) return;
    offline = v;
    let el = document.getElementById('partage-etat');
    if (!el) { el = document.createElement('div'); el.id = 'partage-etat'; el.setAttribute('role', 'status'); document.body.appendChild(el); }
    el.textContent = v ? 'Connexion au serveur perdue, nouvelle tentative…' : '';
    el.style.cssText = v ? 'position:fixed;left:16px;bottom:16px;z-index:99;background:#b3261e;color:#fff;padding:8px 12px;border-radius:8px;font:14px sans-serif' : 'display:none';
  }
  document.addEventListener('DOMContentLoaded', () => {
    const top = document.querySelector('.bar-top');
    if (top) { const a = document.createElement('a'); a.href = '?deconnexion'; a.className = 'btn ghost'; a.textContent = 'Déconnexion'; a.style.cssText = 'text-decoration:none;color:var(--muted)'; top.appendChild(a); }
  });

  poll();
  setInterval(() => { if (!document.hidden) poll(); }, 5000);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
})();
