// Comportements du site : curseurs avant/après, formulaire en étapes, photos, code postal.
(function () {
  document.documentElement.classList.add('js');

  // Curseurs avant / après
  document.querySelectorAll('.compare').forEach(function (box) {
    var range = box.querySelector('.compare-range');
    range.addEventListener('input', function () { box.style.setProperty('--pos', range.value + '%'); });
  });

  var form = document.getElementById('quote');
  if (!form) return;

  var steps = [].slice.call(form.querySelectorAll('.step'));
  var progress = [].slice.call(form.querySelectorAll('.progress li'));
  var nav = form.querySelector('.form-nav');
  var prev = form.querySelector('[data-prev]');
  var next = form.querySelector('[data-next]');
  var submit = form.querySelector('[data-submit]');
  var current = 0;
  var MAX = 5;
  var files = [];   // photos retenues (redimensionnées si possible)

  // Si le serveur a renvoyé des erreurs, ouvrir l'étape concernée
  var firstErr = form.querySelector('.field-error:not([hidden])');
  if (firstErr) current = steps.indexOf(firstErr.closest('.step'));

  function show(i) {
    current = i;
    steps.forEach(function (s, k) { s.classList.toggle('active', k === i); });
    progress.forEach(function (p, k) { p.classList.toggle('on', k === i); p.classList.toggle('done', k < i); });
    prev.hidden = i === 0;
    nav.classList.toggle('last', i === steps.length - 1);
  }

  function setError(name, msg) {
    var el = document.getElementById('err-' + name);
    var inputs = form.querySelectorAll('[name="' + name + '"]');
    inputs.forEach(function (inp) { if (msg) inp.setAttribute('aria-invalid', 'true'); else inp.removeAttribute('aria-invalid'); });
    if (el) { el.textContent = msg || ''; el.hidden = !msg; }
  }

  function checkStep(i) {
    var errs = {};
    var val = function (n) { var f = form.elements[n]; return f ? (f.value || '').trim() : ''; };
    if (i === 0 && !form.querySelector('[name="travaux"]:checked')) errs.travaux = 'Choisissez le type de travaux.';
    if (i === 2 && files.length > MAX) errs.photos = 'Vous pouvez envoyer 5 photos au maximum.';
    if (i === 3) {
      if (!val('nom')) errs.nom = 'Indiquez votre nom.';
      var tel = val('telephone').replace(/[^0-9+]/g, '');
      if (!tel) errs.telephone = 'Indiquez votre numéro de téléphone pour que nous puissions vous rappeler.';
      else if (!/^(0[1-9]\d{8}|\+33[1-9]\d{8}|\+\d{8,15})$/.test(tel)) errs.telephone = 'Ce numéro de téléphone semble incomplet. Exemple : 06 12 34 56 78.';
      var mail = val('email');
      if (mail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mail)) errs.email = 'Cette adresse e-mail n’est pas valide.';
      if (!/^\d{5}$/.test(val('code_postal').replace(/\s/g, ''))) errs.code_postal = 'Indiquez le code postal du chantier (5 chiffres).';
      if (!form.elements.rgpd.checked) errs.rgpd = 'Cochez la case pour accepter que nous utilisions vos coordonnées afin de vous recontacter.';
    }
    var names = { 0: ['travaux'], 2: ['photos'], 3: ['nom', 'telephone', 'email', 'code_postal', 'rgpd'] }[i] || [];
    names.forEach(function (n) { setError(n, errs[n]); });
    var first = names.filter(function (n) { return errs[n]; })[0];
    if (first) { var f = form.querySelector('[name="' + first + '"]'); if (f) f.focus(); }
    return !first;
  }

  next.addEventListener('click', function () {
    if (!checkStep(current)) return;
    show(current + 1);
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
  prev.addEventListener('click', function () { show(current - 1); });

  // Urgence : rappel du numéro
  form.querySelectorAll('[name="urgence"]').forEach(function (r) {
    r.addEventListener('change', function () { form.querySelector('.urgent-hint').hidden = form.elements.urgence.value !== 'oui'; });
  });
  form.querySelector('.urgent-hint').hidden = form.elements.urgence.value !== 'oui';

  // Code postal hors zone : message poli, l'envoi reste possible
  var cp = form.elements.code_postal, zoneHint = document.getElementById('zone-hint');
  function checkZone() {
    var v = cp.value.replace(/\D/g, '');
    var zone = (window.SITE && window.SITE.zone) || [];
    var out = v.length === 5 && zone.length && !zone.some(function (p) { return v.indexOf(String(p).replace(/\D/g, '')) === 0; });
    zoneHint.hidden = !out;
  }
  cp.addEventListener('input', checkZone);
  cp.addEventListener('blur', checkZone);

  // Photos : 5 au maximum, aperçu, réduction de taille avant envoi
  var input = document.getElementById('photos'), thumbs = document.getElementById('thumbs');
  function renderThumbs() {
    thumbs.innerHTML = '';
    files.forEach(function (f, i) {
      var d = document.createElement('div'); d.className = 'thumb';
      var img = document.createElement('img'); img.alt = 'Photo ' + (i + 1); img.src = URL.createObjectURL(f);
      var b = document.createElement('button'); b.type = 'button'; b.textContent = '×'; b.setAttribute('aria-label', 'Retirer la photo ' + (i + 1));
      b.onclick = function () { files.splice(i, 1); renderThumbs(); setError('photos', ''); };
      d.appendChild(img); d.appendChild(b); thumbs.appendChild(d);
    });
    form.querySelector('.drop b').textContent = files.length ? 'Ajouter des photos (' + files.length + '/5)' : 'Ajouter des photos';
  }
  function shrink(file) {
    return new Promise(function (resolve) {
      if (!/^image\/(jpeg|png|webp)$/.test(file.type)) return resolve(file);
      var img = new Image(), url = URL.createObjectURL(file);
      img.onload = function () {
        var k = Math.min(1, 1800 / Math.max(img.width, img.height));
        if (k === 1 && file.size < 1.5e6) return resolve(file);
        var c = document.createElement('canvas'); c.width = Math.round(img.width * k); c.height = Math.round(img.height * k);
        c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
        c.toBlob(function (b) { resolve(b ? new File([b], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }) : file); }, 'image/jpeg', 0.82);
      };
      img.onerror = function () { resolve(file); };
      img.src = url;
    });
  }
  input.addEventListener('change', function () {
    var picked = [].slice.call(input.files);
    input.value = '';
    var room = MAX - files.length;
    if (picked.length > room) setError('photos', 'Vous pouvez envoyer 5 photos au maximum. ' + (room > 0 ? 'Seules les ' + room + ' premières ont été ajoutées.' : 'Retirez une photo pour en ajouter une autre.'));
    else setError('photos', '');
    Promise.all(picked.slice(0, Math.max(0, room)).map(shrink)).then(function (list) { files = files.concat(list); renderThumbs(); });
  });

  // Envoi
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    for (var i = 0; i < steps.length; i++) if (!checkStep(i)) { show(i); return; }
    var data = new FormData(form);
    data.delete('photos[]');
    files.forEach(function (f) { data.append('photos[]', f, f.name); });
    submit.disabled = true; submit.textContent = 'Envoi en cours…';
    var urgent = form.elements.urgence.value === 'oui';
    if (window.SITE && window.SITE.static) { location.href = 'merci.html' + (urgent ? '?urgence=1' : ''); return; }
    fetch(form.action, { method: 'POST', body: data, headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json().then(function (j) { return { status: r.status, body: j }; }); })
      .then(function (res) {
        if (res.body.ok) { location.href = res.body.redirect; return; }
        var errs = res.body.errors || {}, firstStep = null;
        Object.keys(errs).forEach(function (n) {
          setError(n, errs[n]);
          var el = form.querySelector('[name="' + n + '"]') || document.getElementById('err-' + n);
          var s = el && el.closest('.step'); if (s && firstStep === null) firstStep = steps.indexOf(s);
        });
        if (firstStep !== null) show(firstStep);
        submit.disabled = false; submit.textContent = 'Envoyer ma demande';
      })
      .catch(function () {
        submit.disabled = false; submit.textContent = 'Envoyer ma demande';
        alertBox('L’envoi a échoué (connexion interrompue ?). Réessayez, ou appelez-nous directement.');
      });
  });
  function alertBox(msg) {
    var a = form.querySelector('.alert') || document.createElement('div');
    a.className = 'alert'; a.setAttribute('role', 'alert'); a.textContent = msg; form.prepend(a);
  }

  show(current);
})();
