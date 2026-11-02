/**
 * LU3G Ajax Search — frontend
 *
 * - Clic sulla lente: apre il pannello e un velo con blur leggero. Solo mentre
 *   è aperto il pannello viene spostato nel <body> (per non subire z-index,
 *   transform o backdrop-filter dell'header); alla chiusura torna nel widget.
 *   Ogni copia del widget è indipendente e rispetta la visibilità responsive.
 * - La posizione si sceglie per dispositivo (data-pos) e non esce mai dallo schermo.
 *   Barra di input (inline): nessuna lente, il campo resta nel widget; solo la tendina dei
 *   risultati va nel <body> mentre è aperta, così l'header non la taglia né la copre.
 * - Dai caratteri minimi in poi: blur pieno, barra di caricamento + skeleton,
 *   poi i risultati. Clic sul risultato → pagina di dettaglio.
 * - Tastiera: frecce su/giù, Invio, Esc.
 */
(function () {
  'use strict';

  const SETTINGS = window.LU3G_SEARCH || {};
  const DEBOUNCE = 250;      // ms tra l'ultimo tasto e la richiesta
  const MIN_LOADING = 350;   // ms minimi di animazione, per evitare flash
  const VARS = [
    '--lu3g-accent',
    '--lu3g-panel-bg',
    '--lu3g-panel-text',
    '--lu3g-panel-muted',
    '--lu3g-panel-radius',
    '--lu3g-panel-width',
    '--lu3g-inline-align',
    '--lu3g-panel-offset-x',
    '--lu3g-panel-offset-y',
    '--lu3g-panel-max',
    '--lu3g-panel-font',
    '--lu3g-panel-border-width',
    '--lu3g-panel-border-color',
    '--lu3g-panel-shadow',
    '--lu3g-panel-glass',
    '--lu3g-body-padding',
    '--lu3g-anim-duration',
    '--lu3g-anim-ease',
    '--lu3g-results-stagger',
    '--lu3g-bar-bg',
    '--lu3g-bar-padding',
    '--lu3g-bar-divider-width',
    '--lu3g-bar-divider-color',
    '--lu3g-bar-icon-color',
    '--lu3g-bar-icon-size',
    '--lu3g-input-color',
    '--lu3g-input-size',
    '--lu3g-placeholder-color',
    '--lu3g-close-color',
    '--lu3g-close-hover',
    '--lu3g-message-size',
    '--lu3g-sub-size',
    '--lu3g-thumb-size',
    '--lu3g-thumb-radius',
    '--lu3g-item-padding',
    '--lu3g-item-gap',
    '--lu3g-item-radius',
    '--lu3g-hover-bg',
    '--lu3g-item-bg',
    '--lu3g-overlay-color',
    '--lu3g-overlay-blur',
    '--lu3g-title-color',
    '--lu3g-title-hover',
    '--lu3g-title-size',
    '--lu3g-title-weight',
    '--lu3g-title-transform',
    '--lu3g-title-spacing',
    '--lu3g-title-deco-hover',
    '--lu3g-sub-color',
    '--lu3g-sub-hover',
    '--lu3g-mark-color',
    '--lu3g-mark-hover'
  ];
  const STATI = ['is-idle', 'is-loading', 'has-results', 'is-empty', 'is-error'];

  const POSIZIONI = ['icon', 'center', 'left', 'right', 'full', 'top', 'middle', 'bottom', 'fullscreen', 'inline'];
  const SU_TUTTO_LO_SCHERMO = ['top', 'middle', 'bottom', 'fullscreen']; // il velo copre anche l'header
  const RITORNO_EXTRA = 100; // ms oltre la durata dell'animazione: poi il pannello torna dentro il widget

  let istanzaAperta = null;
  const istanze = new Set();

  // Ultima interazione: tastiera o mouse/touch (serve per il contorno di focus)
  let ultimoDaTastiera = false;
  document.addEventListener('keydown', function () { ultimoDaTastiera = true; }, true);
  document.addEventListener('pointerdown', function () { ultimoDaTastiera = false; }, true);

  function attendi(ms) {
    return new Promise(function (resolve) { setTimeout(resolve, ms); });
  }

  // Evidenzia la parte cercata nel titolo, senza usare innerHTML
  function evidenzia(testo, q) {
    const frag = document.createDocumentFragment();
    const basso = testo.toLowerCase();
    const cercato = q.toLowerCase();
    let da = 0;
    let pos = cercato ? basso.indexOf(cercato, da) : -1;

    while (pos !== -1) {
      if (pos > da) frag.append(testo.slice(da, pos));
      const mark = document.createElement('mark');
      mark.textContent = testo.slice(pos, pos + cercato.length);
      frag.append(mark);
      da = pos + cercato.length;
      pos = basso.indexOf(cercato, da);
    }
    frag.append(testo.slice(da));
    return frag;
  }

  class Lu3gSearch {
    constructor(root) {
      this.root = root;
      this.toggle = root.querySelector('.lu3g-search__toggle');
      this.overlay = root.querySelector('.lu3g-search__overlay');
      this.panel = root.querySelector('.lu3g-search__panel');
      this.input = this.panel.querySelector('.lu3g-search__input');
      this.chiudi = this.panel.querySelector('.lu3g-search__close');
      this.lista = this.panel.querySelector('.lu3g-search__results');
      this.messaggio = this.panel.querySelector('.lu3g-search__message');
      this.live = this.panel.querySelector('.lu3g-search__live');
      this.corpo = this.panel.querySelector('.lu3g-search__body');
      this.tendina = null;

      this.min = parseInt(root.dataset.min, 10) || 2;
      this.posizioni = {};
      this.bp = { mobile: 767, tablet: 1024 };
      try { this.posizioni = JSON.parse(root.dataset.pos || '{}'); } catch (e) { this.posizioni = {}; }
      try { this.bp = Object.assign(this.bp, JSON.parse(root.dataset.bp || '{}')); } catch (e) { /* valori predefiniti */ }
      this.conVelo = root.dataset.overlay !== 'no';
      this.apertoInEditor = root.dataset.editorOpen === 'yes';
      this.inline = false;
      this.testi = {};
      try { this.testi = JSON.parse(root.dataset.i18n || '{}'); } catch (e) { this.testi = {}; }

      this.cache = new Map();
      this.controller = null;
      this.timer = null;
      this.voci = [];
      this.attiva = -1;
      this.aperto = false;
      this.inAttesa = false;
      this.timerRitorno = null;

      istanze.add(this);
      this.eventi();
      this.applicaModo();

      if (this.apertoInEditor) {
        // Editor: il box resta aperto e segue lo stile mentre lo si cambia
        const self = this;
        this.overlay.style.pointerEvents = 'none';
        this.panel.style.pointerEvents = 'none';
        this.timerEditor = setInterval(function () {
          if (!self.root.isConnected) return;
          if (!self.aperto) self.open();
          self.applicaModo();
          self.copiaVariabili();
          if (self.inline) self.posizionaTendina();
          else self.posiziona();
        }, 400);
        this.open();
      }
    }

    dispositivo() {
      const w = window.innerWidth;
      if (w <= this.bp.mobile) return 'mobile';
      if (w <= this.bp.tablet) return 'tablet';
      return 'desktop';
    }

    posizione() {
      const p = this.posizioni[this.dispositivo()];
      return POSIZIONI.indexOf(p) !== -1 ? p : 'icon';
    }

    // "Nella pagina" oppure no: cambia con il dispositivo, quindi si ricontrolla al resize
    applicaModo() {
      const inline = this.posizione() === 'inline';
      if (inline === this.inline) return;
      if (this.aperto) this.close(false);
      clearTimeout(this.timerRitorno);
      if (this.overlay.parentNode !== this.root) this.root.append(this.overlay, this.panel);
      this.inline = inline;
      const panel = this.panel;
      POSIZIONI.forEach(function (p) {
        if (p !== 'inline') panel.classList.remove('lu3g-pos-' + p);
      });
      panel.style.removeProperty('--lu3g-left');
      this.root.classList.toggle('is-inline', inline);
      this.panel.classList.toggle('lu3g-pos-inline', inline);
      if (!inline) {
        this.panel.classList.remove('is-open');
        void this.panel.offsetWidth;
      }
    }

    // Tendina della barra di input: un contenitore nel <body> che riceve i risultati
    apriTendina() {
      if (!this.tendina) {
        this.tendina = document.createElement('div');
        this.tendina.className = 'lu3g-search__panel lu3g-search__dropdown';
        this.tendina.dataset.anim = this.panel.dataset.anim || '';
        this.tendina.dataset.resultsAnim = this.panel.dataset.resultsAnim || '';
        if (this.apertoInEditor) this.tendina.style.pointerEvents = 'none';
      }
      this.sincronizzaTendina();
      this.tendina.append(this.corpo);
      document.body.append(this.tendina);
      this.copiaVariabili();
      this.posizionaTendina();
    }

    chiudiTendina() {
      if (!this.tendina || !this.tendina.isConnected) return;
      this.panel.insertBefore(this.corpo, this.live);
      this.tendina.remove();
    }

    // Gli stati (caricamento, risultati, vuoto...) decidono cosa si vede dentro la tendina
    sincronizzaTendina() {
      if (!this.tendina) return;
      const tendina = this.tendina;
      const panel = this.panel;
      STATI.forEach(function (nome) { tendina.classList.toggle(nome, panel.classList.contains(nome)); });
    }

    posizionaTendina() {
      if (!this.tendina || !this.tendina.isConnected) return;
      const r = this.panel.getBoundingClientRect();
      const spazio = Math.max(120, window.innerHeight - r.bottom - 22);
      const massimo = parseFloat(getComputedStyle(this.panel).getPropertyValue('--lu3g-panel-max'));
      const unita = getComputedStyle(this.panel).getPropertyValue('--lu3g-panel-max').trim().slice(-2);
      const scelto = isNaN(massimo) ? window.innerHeight * 0.6 : (unita === 'vh' ? window.innerHeight * massimo / 100 : massimo);
      const s = this.tendina.style;
      s.setProperty('--lu3g-dd-top', Math.round(r.bottom + 6) + 'px');
      s.setProperty('--lu3g-dd-left', Math.round(r.left) + 'px');
      s.setProperty('--lu3g-dd-width', Math.round(r.width) + 'px');
      s.setProperty('--lu3g-dd-max', Math.round(Math.min(scelto, spazio)) + 'px');
    }

    durataAnimazione() {
      const v = getComputedStyle(this.panel).getPropertyValue('--_dur').trim();
      const n = parseFloat(v);
      if (isNaN(n)) return 320;
      return v.slice(-2) === 'ms' ? n : n * 1000;
    }

    // Il widget è visibile? (false se nascosto dalle impostazioni responsive di Elementor)
    visibile() {
      if (!this.root.isConnected) return false;
      const el = this.inline ? this.root : this.toggle;
      return el.getClientRects().length > 0 && getComputedStyle(el).visibility !== 'hidden';
    }

    // Aperto: pannello e velo vanno nel <body> (niente z-index/transform dell'header)
    portaNelBody() {
      clearTimeout(this.timerRitorno);
      if (this.panel.parentNode !== document.body) {
        document.body.append(this.overlay, this.panel);
        void this.panel.offsetWidth; // riflusso: fa partire la transizione di apertura
      }
    }

    // Chiuso: tornano dentro il proprio widget, che così resta indipendente dagli altri
    riportaNelWidget() {
      const self = this;
      clearTimeout(this.timerRitorno);
      if (this.inline) return;
      this.timerRitorno = setTimeout(function () {
        if (self.aperto) return;
        if (self.root.isConnected) self.root.append(self.overlay, self.panel);
        else self.distruggi();
      }, this.durataAnimazione() + RITORNO_EXTRA);
    }

    // Widget rimosso (es. ri-render nell'editor): pulizia completa
    distruggi() {
      clearTimeout(this.timer);
      clearTimeout(this.timerRitorno);
      clearInterval(this.timerEditor);
      if (this.controller) this.controller.abort();
      window.removeEventListener('resize', this.alRidimensionamento);
      window.removeEventListener('scroll', this.alRidimensionamento);
      document.removeEventListener('pointerdown', this.alClicFuori, true);
      this.chiudiTendina();
      this.overlay.remove();
      this.panel.remove();
      if (istanzaAperta === this) {
        istanzaAperta = null;
        document.documentElement.classList.remove('lu3g-search-lock');
      }
      istanze.delete(this);
    }

    eventi() {
      const self = this;

      this.toggle.addEventListener('click', function () {
        if (self.aperto) self.close(true);
        else self.open();
      });

      this.chiudi.addEventListener('click', function () {
        if (self.inline) {
          // Nella pagina il campo resta: la X lo svuota
          self.input.value = '';
          self.onInput();
          self.input.focus({ preventScroll: true });
          return;
        }
        self.close(true);
      });
      this.overlay.addEventListener('click', function () { self.close(true); });

      this.input.addEventListener('focus', function () {
        if (self.inline && !self.aperto) self.open();
      });
      this.input.addEventListener('input', function () { self.onInput(); });
      this.input.addEventListener('keydown', function (e) { self.onKeydown(e); });

      this.panel.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
          e.preventDefault();
          self.close(true);
        }
      });

      // Clic fuori dal box: chiude (serve quando non c'è il velo e nella modalità "Nella pagina")
      this.alClicFuori = function (e) {
        if (!self.aperto || self.apertoInEditor) return;
        if (self.panel.contains(e.target) || self.toggle.contains(e.target)) return;
        if (self.tendina && self.tendina.contains(e.target)) return;
        self.close(false);
      };
      document.addEventListener('pointerdown', this.alClicFuori, true);

      const riposiziona = function () {
        self.applicaModo();
        if (!self.aperto || self.inAttesa) return;
        // Se cambiando dimensione il widget viene nascosto (es. desktop → mobile), si chiude
        if (!self.visibile()) {
          self.close(false);
          return;
        }
        self.inAttesa = true;
        requestAnimationFrame(function () {
          self.inAttesa = false;
          self.copiaVariabili(); // i valori responsive possono cambiare col breakpoint
          if (self.inline) self.posizionaTendina();
          else self.posiziona();
        });
      };
      this.alRidimensionamento = riposiziona;
      window.addEventListener('resize', riposiziona);
      window.addEventListener('scroll', riposiziona, { passive: true });
    }

    /* ---------------- Apertura / chiusura ---------------- */

    open() {
      if (!this.visibile()) return;
      if (istanzaAperta && istanzaAperta !== this) istanzaAperta.close(false);
      istanzaAperta = this;
      this.aperto = true;

      this.copiaVariabili();
      if (!this.inline) {
        // Posizione applicata senza transizione: anima solo l'apertura, non la centratura
        this.panel.style.transition = 'none';
        this.portaNelBody();
        this.posiziona();
        void this.panel.offsetWidth;
        this.panel.style.transition = '';
      }

      this.root.classList.add('is-open');
      this.panel.classList.add('is-open');
      if (this.conVelo && !this.inline) {
        this.overlay.classList.add('is-open');
        this.overlay.classList.toggle('is-active', this.input.value.trim().length >= this.min);
      }
      this.toggle.setAttribute('aria-expanded', 'true');
      this.toggle.setAttribute('aria-label', this.testi.close || 'Chiudi la ricerca');

      if (this.inline) {
        this.apriTendina(); // il focus è già nel campo
        return;
      }

      if (this.dispositivo() !== 'desktop' && !this.apertoInEditor) {
        document.documentElement.classList.add('lu3g-search-lock');
      }

      // Il focus va dato subito, dentro il clic: su iOS apre la tastiera
      if (!this.apertoInEditor) {
        this.input.focus({ preventScroll: true });
        if (this.input.value) this.input.select();
      }
    }

    close(restituisciFocus) {
      if (!this.aperto) return;
      this.aperto = false;
      if (istanzaAperta === this) istanzaAperta = null;

      if (this.controller) this.controller.abort();
      clearTimeout(this.timer);

      this.chiudiTendina();
      this.root.classList.remove('is-open');
      this.panel.classList.remove('is-open');
      this.overlay.classList.remove('is-open', 'is-active');
      this.toggle.setAttribute('aria-expanded', 'false');
      this.toggle.setAttribute('aria-label', this.testi.open || 'Apri la ricerca');
      document.documentElement.classList.remove('lu3g-search-lock');

      this.riportaNelWidget();

      // Focus sulla lente solo se si usa la tastiera (Esc, Tab): con mouse/touch niente contorno
      if (restituisciFocus && ultimoDaTastiera && this.visibile()) {
        this.toggle.focus({ preventScroll: true });
      } else if (this.panel.contains(document.activeElement)) {
        document.activeElement.blur();
      }
    }

    // Le impostazioni di stile di Elementor vivono sul wrapper del widget: le copio sul pannello
    copiaVariabili() {
      const cs = getComputedStyle(this.root);
      const destinazioni = [this.panel, this.overlay];
      if (this.tendina) destinazioni.push(this.tendina);

      VARS.forEach(function (nome) {
        const valore = cs.getPropertyValue(nome).trim();
        destinazioni.forEach(function (el) {
          if (valore) el.style.setProperty(nome, valore);
          else el.style.removeProperty(nome);
        });
      });
    }

    // Pannello e velo partono dal fondo dell'header, così l'header resta nitido
    posiziona() {
      const pos = this.posizione();
      const panel = this.panel;

      POSIZIONI.forEach(function (p) {
        if (p !== 'inline') panel.classList.remove('lu3g-pos-' + p);
      });
      panel.classList.add('lu3g-pos-' + pos);

      const header = this.root.closest('.elementor-location-header') || this.root.closest('header');
      const rif = (header || this.root).getBoundingClientRect();
      // Widget in fondo alla pagina: il box non deve finire sotto il bordo dello schermo
      const top = Math.min(Math.max(0, Math.round(rif.bottom)), Math.round(window.innerHeight * 0.5));
      const barra = document.getElementById('wpadminbar');
      const schermo = barra ? Math.max(0, Math.round(barra.getBoundingClientRect().bottom)) : 0;

      panel.style.setProperty('--lu3g-top', top + 'px');
      panel.style.setProperty('--lu3g-screen-top', schermo + 'px');
      this.overlay.style.setProperty('--lu3g-top', (SU_TUTTO_LO_SCHERMO.indexOf(pos) !== -1 ? schermo : top) + 'px');

      if (pos === 'icon') panel.style.setProperty('--lu3g-left', this.sinistraIcona() + 'px');
      else panel.style.removeProperty('--lu3g-left');
    }

    // Allineato all'icona: bordo destro sull'icona; se non c'è spazio, bordo sinistro sull'icona.
    // In ogni caso resta dentro lo schermo, alla distanza scelta dai bordi.
    sinistraIcona() {
      const icona = this.toggle.getBoundingClientRect();
      const margine = parseFloat(getComputedStyle(this.panel).getPropertyValue('--_ox')) || 16;
      const larghezza = this.panel.offsetWidth;
      const vw = document.documentElement.clientWidth;
      let sinistra = icona.right + 8 - larghezza;

      if (sinistra < margine) sinistra = icona.left - 8;
      if (sinistra + larghezza > vw - margine) sinistra = vw - margine - larghezza;
      return Math.round(Math.max(margine, sinistra));
    }

    /* ---------------- Ricerca ---------------- */

    onInput() {
      const q = this.input.value.trim();
      clearTimeout(this.timer);

      if (this.inline && !this.aperto) this.open();

      if (q.length < this.min) {
        if (this.controller) this.controller.abort();
        this.overlay.classList.remove('is-active');
        this.svuota();
        this.stato('is-idle');
        return;
      }

      if (this.conVelo && !this.inline) this.overlay.classList.add('is-active');

      if (this.cache.has(q)) {
        this.mostra(q, this.cache.get(q));
        return;
      }

      this.stato('is-loading');
      const self = this;
      this.timer = setTimeout(function () { self.cerca(q); }, DEBOUNCE);
    }

    async cerca(q) {
      if (q !== this.input.value.trim()) return;
      if (this.controller) this.controller.abort();
      this.controller = new AbortController();

      const inizio = performance.now();
      const url = new URL(SETTINGS.restUrl, window.location.origin);
      url.searchParams.set('q', q);
      url.searchParams.set('c', this.root.dataset.config || '');
      url.searchParams.set('s', this.root.dataset.sig || '');

      try {
        const risposta = await fetch(url.toString(), {
          signal: this.controller.signal,
          headers: { Accept: 'application/json' },
          credentials: 'same-origin'
        });
        if (!risposta.ok) throw new Error('HTTP ' + risposta.status);

        const dati = await risposta.json();
        const risultati = Array.isArray(dati.results) ? dati.results : [];
        this.cache.set(q, risultati);

        // L'animazione resta visibile un minimo, per non "lampeggiare"
        const trascorso = performance.now() - inizio;
        if (trascorso < MIN_LOADING) await attendi(MIN_LOADING - trascorso);

        if (q === this.input.value.trim()) this.mostra(q, risultati);
      } catch (errore) {
        if (errore.name === 'AbortError') return;
        if (q !== this.input.value.trim()) return;
        this.svuota();
        this.messaggio.textContent = this.testi.error || '';
        this.stato('is-error');
        this.annuncia(this.testi.error || '');
      }
    }

    mostra(q, risultati) {
      this.svuota();

      if (!risultati.length) {
        this.messaggio.textContent = this.testi.empty || '';
        this.stato('is-empty');
        this.annuncia(this.testi.empty || '');
        return;
      }

      const self = this;
      const base = this.lista.id;

      risultati.forEach(function (r, i) {
        const li = document.createElement('li');
        li.style.setProperty('--i', i);
        li.setAttribute('role', 'presentation');

        const a = document.createElement('a');
        a.className = 'lu3g-search__item';
        a.href = r.url;
        a.id = base + '-' + i;
        a.setAttribute('role', 'option');
        a.setAttribute('aria-selected', 'false');
        a.tabIndex = -1;

        if (r.thumb) {
          const img = document.createElement('img');
          img.className = 'lu3g-search__thumb';
          img.src = r.thumb;
          img.alt = '';
          img.loading = 'lazy';
          img.decoding = 'async';
          a.append(img);
        }

        const testo = document.createElement('span');
        testo.className = 'lu3g-search__text';

        const titolo = document.createElement('span');
        titolo.className = 'lu3g-search__title';
        titolo.append(evidenzia(r.title || '', q));
        testo.append(titolo);

        if (r.sub) {
          const sub = document.createElement('span');
          sub.className = 'lu3g-search__sub';
          sub.textContent = r.sub;
          testo.append(sub);
        }

        a.append(testo);
        a.addEventListener('mouseenter', function () { self.evidenziaVoce(i); });

        li.append(a);
        self.lista.append(li);
        self.voci.push(a);
      });

      this.stato('has-results');
      this.input.setAttribute('aria-expanded', 'true');

      const n = risultati.length;
      this.annuncia(n === 1 ? (this.testi.result || '1 risultato') : (this.testi.results || '%d risultati').replace('%d', n));
    }

    svuota() {
      this.lista.textContent = '';
      this.messaggio.textContent = '';
      this.voci = [];
      this.attiva = -1;
      this.input.setAttribute('aria-expanded', 'false');
      this.input.removeAttribute('aria-activedescendant');
    }

    stato(nome) {
      this.panel.classList.remove.apply(this.panel.classList, STATI);
      this.panel.classList.add(nome);
      this.sincronizzaTendina();
    }

    annuncia(testo) {
      this.live.textContent = '';
      const live = this.live;
      setTimeout(function () { live.textContent = testo; }, 50);
    }

    /* ---------------- Tastiera ---------------- */

    onKeydown(e) {
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        this.muovi(1);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        this.muovi(-1);
      } else if (e.key === 'Enter') {
        const voce = this.voci[this.attiva] || (this.voci.length === 1 ? this.voci[0] : null);
        if (voce) {
          e.preventDefault();
          window.location.href = voce.href;
        }
      }
    }

    muovi(direzione) {
      if (!this.voci.length) return;
      const n = this.voci.length;
      const prossima = this.attiva < 0
        ? (direzione > 0 ? 0 : n - 1)
        : (this.attiva + direzione + n) % n;
      this.evidenziaVoce(prossima);
      this.voci[prossima].scrollIntoView({ block: 'nearest' });
    }

    evidenziaVoce(indice) {
      this.voci.forEach(function (voce, i) {
        const attiva = i === indice;
        voce.classList.toggle('is-active', attiva);
        voce.setAttribute('aria-selected', attiva ? 'true' : 'false');
      });
      this.attiva = indice;
      if (this.voci[indice]) this.input.setAttribute('aria-activedescendant', this.voci[indice].id);
    }
  }

  /* ---------------- Avvio ---------------- */

  // Istanze il cui widget non è più nella pagina (editor): rimuove i pannelli rimasti
  function pulisci() {
    istanze.forEach(function (istanza) {
      if (!istanza.root.isConnected) istanza.distruggi();
    });
  }

  function avvia(contesto) {
    pulisci();
    const scope = contesto || document;
    scope.querySelectorAll('.lu3g-search').forEach(function (el) {
      if (el.dataset.lu3gInit) return;
      el.dataset.lu3gInit = '1';
      new Lu3gSearch(el);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { avvia(); });
  } else {
    avvia();
  }

  // Editor di Elementor: il widget viene ri-renderizzato a ogni modifica
  function agganciaElementor() {
    if (!window.elementorFrontend || !window.elementorFrontend.hooks) return false;
    window.elementorFrontend.hooks.addAction('frontend/element_ready/lu3g-ajax-search.default', function ($scope) {
      avvia($scope[0]);
    });
    return true;
  }

  if (!agganciaElementor() && window.jQuery) {
    window.jQuery(window).on('elementor/frontend/init', agganciaElementor);
  }
})();
