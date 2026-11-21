/* =========================================================================
   Socle « Plume » — interactions et animations du site public
   GSAP (ScrollTrigger, SplitText, Flip, DrawSVG) · Lenis · WebGL
   Tout est désactivé si l'utilisateur demande à réduire les animations.
   ========================================================================= */
(() => {
  'use strict';
  const P = window.PLUME || {};
  const doc = document.documentElement;
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));
  const reduce = !!P.reduce;
  const fine = matchMedia('(hover: hover) and (pointer: fine)').matches;
  const hasGsap = typeof window.gsap !== 'undefined';
  const show = (els) => (Array.isArray(els) ? els : [els]).forEach((el) => el && (el.style.visibility = 'visible', el.style.animation = 'none'));
  const css = (name) => getComputedStyle(doc).getPropertyValue(name).trim();

  /* --------------------------------------------------------------- outils */
  const track = (event, ref) => {
    try {
      const body = JSON.stringify({ event, ref });
      if (navigator.sendBeacon) navigator.sendBeacon((P.base || '') + '/api/track', new Blob([body], { type: 'application/json' }));
      else fetch((P.base || '') + '/api/track', { method: 'POST', body, headers: { 'Content-Type': 'application/json' }, keepalive: true });
    } catch (e) { /* silencieux */ }
  };
  document.addEventListener('click', (e) => {
    const a = e.target.closest('[data-track]');
    if (a) track(a.dataset.track, a.dataset.ref || '');
    const c = e.target.closest('[data-copy]');
    if (c) {
      navigator.clipboard?.writeText(c.dataset.copy).then(() => {
        c.classList.add('is-copied'); c.setAttribute('aria-label', 'Lien copié');
        setTimeout(() => c.classList.remove('is-copied'), 1600);
      });
    }
  });

  /* ------------------------------------------------- consentement cookies */
  const consentKey = 'plume-consent';
  const getConsent = () => { try { return localStorage.getItem(consentKey); } catch (e) { return null; } };
  const setConsent = (v) => { try { localStorage.setItem(consentKey, v); } catch (e) { /* navigation privée */ } };
  const banner = $('[data-cookie]');
  const applyConsent = () => {
    const c = getConsent();
    const tpl = $('template[data-analytics]');
    if (tpl && !tpl.dataset.done && (tpl.dataset.consent === '0' || c === 'accept')) {
      tpl.dataset.done = '1';
      const frag = tpl.content.cloneNode(true);
      frag.querySelectorAll('script').forEach((old) => {
        const s = document.createElement('script');
        [...old.attributes].forEach((a) => s.setAttribute(a.name, a.value));
        s.textContent = old.textContent; old.replaceWith(s);
      });
      document.body.appendChild(frag);
    }
    if (c === 'accept') $$('[data-embed]').forEach(loadEmbed);
  };
  if (banner) {
    if (!getConsent()) setTimeout(() => { banner.hidden = false; if (hasGsap && !reduce) gsap.from(banner, { y: 40, autoAlpha: 0, duration: .8, ease: 'expo.out' }); }, 1800);
    banner.addEventListener('click', (e) => {
      const b = e.target.closest('[data-cookie-choice]');
      if (!b) return;
      setConsent(b.dataset.cookieChoice); banner.hidden = true; applyConsent();
    });
    $$('[data-cookie-open]').forEach((b) => b.addEventListener('click', () => { banner.hidden = false; }));
  }

  /* --------------------------------------- vidéos / audios externes */
  function loadEmbed(box) {
    if (box.dataset.loaded) return;
    box.dataset.loaded = '1';
    const f = document.createElement('iframe');
    f.src = box.dataset.embed; f.title = box.dataset.embedTitle || 'Contenu externe';
    f.loading = 'lazy'; f.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen'; f.allowFullscreen = true;
    box.replaceChildren(f);
  }
  document.addEventListener('click', (e) => { const b = e.target.closest('.embed__load'); if (b) loadEmbed(b.parentElement); });
  applyConsent();

  /* ------------------------------------------------------- visionneuse */
  const lb = $('[data-lightbox]');
  let lbItems = [], lbIndex = 0, lbReturn = null;
  const lbShow = () => {
    const [src, cap] = lbItems[lbIndex];
    $('img', lb).src = src; $('img', lb).alt = cap || '';
    $('figcaption', lb).textContent = cap || '';
    lb.classList.toggle('is-single', lbItems.length < 2);
  };
  const lbOpen = (items, i = 0, from = null) => {
    lbItems = items; lbIndex = i; lbReturn = from; lbShow(); lb.hidden = false;
    window.__lenis?.stop();
    if (hasGsap && !reduce) gsap.fromTo($('figure', lb), { scale: .94, autoAlpha: 0 }, { scale: 1, autoAlpha: 1, duration: .6, ease: 'expo.out' });
    $('[data-lightbox-close]', lb).focus();
  };
  const lbClose = () => { lb.hidden = true; window.__lenis?.start(); lbReturn?.focus(); };
  if (lb) {
    document.addEventListener('click', (e) => {
      const one = e.target.closest('[data-lightbox-src]');
      if (one) { lbOpen([[one.dataset.lightboxSrc, one.dataset.lightboxCaption]], 0, one); return; }
      const grp = e.target.closest('[data-lightbox-group]');
      if (grp) lbOpen(JSON.parse(grp.dataset.lightboxGroup), 0, grp);
    });
    $('[data-lightbox-close]', lb).addEventListener('click', lbClose);
    $('[data-lightbox-prev]', lb).addEventListener('click', () => { lbIndex = (lbIndex - 1 + lbItems.length) % lbItems.length; lbShow(); });
    $('[data-lightbox-next]', lb).addEventListener('click', () => { lbIndex = (lbIndex + 1) % lbItems.length; lbShow(); });
    lb.addEventListener('click', (e) => { if (e.target === lb) lbClose(); });
    document.addEventListener('keydown', (e) => {
      if (lb.hidden) return;
      if (e.key === 'Escape') lbClose();
      if (e.key === 'ArrowRight') $('[data-lightbox-next]', lb).click();
      if (e.key === 'ArrowLeft') $('[data-lightbox-prev]', lb).click();
    });
  }

  /* ======================================================================
     Livres en volume : inclinaison, coin qui se soulève, pages feuilletées
     par le coin (jamais d'ouverture), retournement recto / verso.
     ====================================================================== */
  function tomes() {
    $$('[data-tome]').forEach((tome, idx) => {
      const st = { ry: 0, rx: 0, c: 0, lift: 0, sy: 0, flip: 0 };
      const leaves = $$('.tome__leaf:not(.tome__leaf--base)', tome);
      const lv = leaves.map(() => ({ v: 0 }));
      const render = () => {
        const s = tome.style;
        s.setProperty('--ry', st.ry.toFixed(2)); s.setProperty('--rx', st.rx.toFixed(2));
        s.setProperty('--c', st.c.toFixed(2)); s.setProperty('--lift', st.lift.toFixed(2));
        s.setProperty('--sy', st.sy.toFixed(2)); s.setProperty('--flip', st.flip.toFixed(2));
      };
      const renderLeaves = () => leaves.forEach((l, i) => l.style.setProperty('--lc', lv[i].v.toFixed(2)));
      const stage = tome.closest('[data-tome-stage]');
      const host = tome.closest('.book-card__link') || stage || tome;
      let riffle = null, isOpen = false;

      // Retourner le livre (fiche détaillée)
      const flipBtn = stage ? $('[data-flip]') : null;
      const flip = () => {
        close();
        gsap.to(st, { flip: st.flip ? 0 : 180, duration: reduce ? 0 : 1.5, ease: 'expo.inOut', onUpdate: render });
        flipBtn?.setAttribute('aria-pressed', String(!st.flip));
      };
      if (stage && tome.hasAttribute('data-tome-back') && !tome.closest('.featured')) {
        flipBtn?.addEventListener('click', flip);
        tome.addEventListener('click', flip);
      }
      if (reduce) return;

      // Feuilletage : les pages remontent sous le pouce, puis retombent une à une par le coin
      const riffleTl = (cycles) => {
        riffle?.kill();
        riffle = gsap.timeline({ repeat: cycles < 0 ? -1 : cycles - 1, repeatDelay: .12, onUpdate: renderLeaves })
          .to(lv, { v: 12.5, duration: .24, ease: 'power2.out', stagger: { each: .02, from: 'start' } })
          .to(lv, { v: 0, duration: .3, ease: 'power2.in', stagger: { each: .07, from: 'end' } }, '+=.06');
        return riffle;
      };
      const open = (cycles = -1) => {
        if (st.flip > 90) return;
        isOpen = true;
        gsap.to(st, { c: 14, lift: 10, duration: .8, ease: 'expo.out', onUpdate: render, overwrite: 'auto' });
        gsap.delayedCall(.18, () => isOpen && riffleTl(cycles).eventCallback('onComplete', () => cycles > 0 && close()));
      };
      function close() {
        isOpen = false;
        riffle?.kill();
        gsap.to(lv, { v: 0, duration: .35, ease: 'power2.out', onUpdate: renderLeaves, overwrite: 'auto' });
        gsap.to(st, { c: 0, lift: 0, ry: 0, rx: 0, duration: 1.1, ease: 'expo.out', onUpdate: render, overwrite: 'auto' });
      }

      if (fine && tome.hasAttribute('data-tome-hover')) {
        const qy = gsap.quickTo(st, 'ry', { duration: .7, ease: 'power3', onUpdate: render });
        const qx = gsap.quickTo(st, 'rx', { duration: .7, ease: 'power3', onUpdate: render });
        host.addEventListener('pointerenter', () => open());
        host.addEventListener('pointermove', (e) => {
          const r = tome.getBoundingClientRect();
          qy(gsap.utils.clamp(-1, 1, (e.clientX - r.left) / r.width - .5) * -22 - 6);
          qx(gsap.utils.clamp(-1, 1, (e.clientY - r.top) / r.height - .5) * -12);
        });
        host.addEventListener('pointerleave', close);
        host.addEventListener('focusin', () => open(2));
        host.addEventListener('focusout', close);
      } else if (window.ScrollTrigger) {
        // Écran tactile : un feuilletage à l'apparition du livre
        ScrollTrigger.create({ trigger: tome, start: 'top 80%', once: true, onEnter: () => gsap.delayedCall((idx % 4) * .14, () => open(1)) });
        host.addEventListener('touchstart', () => !isOpen && open(1), { passive: true });
      }

      // Le livre mis en avant tourne doucement au défilement
      if (tome.closest('.featured') && window.ScrollTrigger) {
        gsap.fromTo(st, { sy: -26 }, { sy: 18, ease: 'none', onUpdate: render, scrollTrigger: { trigger: tome, start: 'top bottom', end: 'bottom top', scrub: 1 } });
      }
    });
  }

  /* ---------------------------------------------------- menu mobile */
  const menu = $('[data-menu]'), menuBtn = $('[data-menu-btn]');
  let menuTl = null;
  const setMenu = (open) => {
    menuBtn.setAttribute('aria-expanded', String(open));
    doc.classList.toggle('menu-open', open);
    if (open) {
      menu.hidden = false; window.__lenis?.stop();
      if (hasGsap && !reduce) {
        menuTl?.kill();
        menuTl = gsap.timeline()
          .fromTo(menu, { clipPath: 'inset(0 0 100% 0)' }, { clipPath: 'inset(0 0 0% 0)', duration: .9, ease: 'expo.inOut' })
          .fromTo($$('nav li span, nav li small', menu), { yPercent: 130 }, { yPercent: 0, duration: .9, stagger: .05, ease: 'expo.out' }, '-=.35')
          .fromTo($('.menu__curve path', menu), { drawSVG: '0%' }, { drawSVG: '100%', duration: 1.6, ease: 'power2.inOut' }, '<')
          .fromTo($$('.menu__foot li', menu), { autoAlpha: 0, y: 20, scale: .8 }, { autoAlpha: 1, y: 0, scale: 1, duration: .6, stagger: .06, ease: 'back.out(2)' }, '-=1');
      } else menu.style.clipPath = 'none';
      $('a', menu)?.focus();
    } else {
      window.__lenis?.start();
      const done = () => { menu.hidden = true; };
      if (hasGsap && !reduce) gsap.to(menu, { clipPath: 'inset(0 0 100% 0)', duration: .7, ease: 'expo.inOut', onComplete: done });
      else done();
      menuBtn.focus();
    }
  };
  menuBtn?.addEventListener('click', () => setMenu(menuBtn.getAttribute('aria-expanded') !== 'true'));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && doc.classList.contains('menu-open')) setMenu(false); });
  menu?.addEventListener('keydown', (e) => { // piège de focus
    if (e.key !== 'Tab') return;
    const f = $$('a, button', menu).concat(menuBtn); const i = f.indexOf(document.activeElement);
    if (e.shiftKey && i <= 0) { e.preventDefault(); f[f.length - 1].focus(); }
    else if (!e.shiftKey && i === f.length - 1) { e.preventDefault(); f[0].focus(); }
  });

  /* --------------------------------------------------- en-tête au défilement */
  const header = $('[data-header]');
  let lastY = 0;
  const onScroll = (y) => {
    header.classList.toggle('is-scrolled', y > 40);
    if (!doc.classList.contains('menu-open')) header.classList.toggle('is-hidden', y > 300 && y > lastY + 2);
    if (y < lastY - 2) header.classList.remove('is-hidden');
    lastY = y;
  };
  addEventListener('scroll', () => onScroll(scrollY), { passive: true });
  onScroll(scrollY);

  /* ---------------------------------------- catalogue : filtres et recherche */
  const catalog = $('[data-catalog]');
  if (catalog) {
    const cards = $$('[data-book]', catalog);
    const search = $('[data-search]', catalog), pubSel = $('[data-filter-publisher]', catalog);
    const count = $('[data-count-label]', catalog), empty = $('[data-empty]', catalog);
    let genre = '';
    const norm = (s) => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
    const apply = () => {
      const state = hasGsap && !reduce && window.Flip ? Flip.getState(cards) : null;
      const q = norm(search.value.trim()), pub = pubSel.value;
      let n = 0;
      cards.forEach((c) => {
        const ok = (!genre || c.dataset.genre === genre) && (!pub || c.dataset.publisher === pub) && (!q || norm(c.dataset.search).includes(q));
        c.hidden = !ok; if (ok) n++;
      });
      count.textContent = count.dataset.tpl.replace('{n}', n);
      empty.hidden = n > 0;
      if (state) Flip.from(state, { duration: .7, ease: 'expo.out', absolute: true, scale: true, stagger: .015,
        onEnter: (els) => gsap.fromTo(els, { autoAlpha: 0, y: 30 }, { autoAlpha: 1, y: 0, duration: .6, ease: 'expo.out' }),
        onLeave: (els) => gsap.to(els, { autoAlpha: 0, scale: .9, duration: .35 }), onComplete: () => window.ScrollTrigger?.refresh() });
    };
    $$('[data-filter-genre]', catalog).forEach((b) => b.addEventListener('click', () => {
      b.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'nearest', inline: 'center' });
      genre = b.dataset.filterGenre;
      $$('[data-filter-genre]', catalog).forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
      apply();
    }));
    pubSel.addEventListener('change', apply);
    let t; search.addEventListener('input', () => { clearTimeout(t); t = setTimeout(apply, 180); });
  }

  /* ------------------------------------------ bandes coulissantes (filtres) */
  $$('[data-swipe]').forEach((el) => {
    const edges = () => {
      el.classList.toggle('has-more-start', el.scrollLeft > 4);
      el.classList.toggle('has-more-end', el.scrollLeft < el.scrollWidth - el.clientWidth - 4);
    };
    el.addEventListener('scroll', edges, { passive: true });
    addEventListener('resize', edges);
    edges();
    // Glisser à la souris (le doigt fait défiler nativement)
    let down = false, sx = 0, sl = 0, moved = false;
    el.addEventListener('pointerdown', (e) => { if (e.pointerType !== 'mouse' || el.scrollWidth <= el.clientWidth) return; down = true; moved = false; sx = e.clientX; sl = el.scrollLeft; });
    addEventListener('pointermove', (e) => {
      if (!down) return;
      const dx = e.clientX - sx;
      if (Math.abs(dx) > 5) { moved = true; el.classList.add('is-dragging'); }
      el.scrollLeft = sl - dx;
    });
    addEventListener('pointerup', () => { if (!down) return; down = false; setTimeout(() => el.classList.remove('is-dragging'), 0); });
    el.addEventListener('click', (e) => { if (moved) { e.stopPropagation(); e.preventDefault(); moved = false; } }, true);
  });

  /* ======================================================================
     Shader « encre et ocre » du hero (WebGL natif, aucun chargement externe)
     ====================================================================== */
  function heroInk() {
    const canvas = $('[data-ink]');
    if (!canvas) return;
    const gl = canvas.getContext('webgl', { antialias: false, premultipliedAlpha: false, powerPreference: 'low-power' });
    if (!gl) { canvas.remove(); return; }
    const vs = 'attribute vec2 p;void main(){gl_Position=vec4(p,0.,1.);}';
    const fs = `precision mediump float;
uniform vec2 uRes;uniform float uTime;uniform vec2 uMouse;uniform vec3 uCream,uOcre,uRed;uniform vec4 uHole;uniform float uMobile;
vec2 h(vec2 p){p=vec2(dot(p,vec2(127.1,311.7)),dot(p,vec2(269.5,183.3)));return -1.+2.*fract(sin(p)*43758.5453);}
float n(vec2 p){vec2 i=floor(p),f=fract(p),u=f*f*(3.-2.*f);
return mix(mix(dot(h(i),f),dot(h(i+vec2(1,0)),f-vec2(1,0)),u.x),mix(dot(h(i+vec2(0,1)),f-vec2(0,1)),dot(h(i+vec2(1,1)),f-vec2(1,1)),u.x),u.y);}
float fbm(vec2 p){float v=0.,a=.5;mat2 r=mat2(.8,.6,-.6,.8);for(int i=0;i<5;i++){v+=a*n(p);p=r*p*2.03;a*=.5;}return v;}
void main(){
  vec2 uv=gl_FragCoord.xy/uRes;float asp=uRes.x/uRes.y;vec2 p=uv*vec2(asp,1.)*1.35;
  float t=uTime*.035;vec2 m=(uMouse-.5)*.35;
  vec2 q=vec2(fbm(p+vec2(0.,t)+m),fbm(p+vec2(5.2,1.3)-t));
  vec2 r=vec2(fbm(p+3.*q+vec2(1.7,9.2)+.6*t),fbm(p+3.*q+vec2(8.3,2.8)-.5*t));
  float f=fbm(p+2.4*r);
  float wash=smoothstep(.02,.55,f+.35*length(q));
  float lines=smoothstep(.93,1.,abs(sin(f*22.+t*3.)));
  vec3 col=mix(uCream,uOcre,wash*.62);
  col=mix(col,uRed,lines*wash*.35);
  float mask=smoothstep(.95,.05,uv.y+(.45-uv.x)*.25*(1.-uMobile))*.95;
  mask+=smoothstep(.35,.0,uv.x)*.25*(1.-uMobile);
  vec2 d=(gl_FragCoord.xy-uHole.xy)/uHole.zw;float hole=smoothstep(.55,1.05,length(d));
  mask*=mix(.18,1.,hole);
  gl_FragColor=vec4(mix(uCream,col,clamp(mask,0.,1.)),1.);
}`;
    const sh = (type, src) => { const s = gl.createShader(type); gl.shaderSource(s, src); gl.compileShader(s); return s; };
    const prog = gl.createProgram();
    gl.attachShader(prog, sh(gl.VERTEX_SHADER, vs)); gl.attachShader(prog, sh(gl.FRAGMENT_SHADER, fs));
    gl.linkProgram(prog);
    if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) { canvas.remove(); return; }
    gl.useProgram(prog);
    const buf = gl.createBuffer(); gl.bindBuffer(gl.ARRAY_BUFFER, buf);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);
    const loc = gl.getAttribLocation(prog, 'p'); gl.enableVertexAttribArray(loc); gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);
    const U = (k) => gl.getUniformLocation(prog, k);
    const rgb = (hex) => { const v = parseInt(hex.replace('#', ''), 16); return [(v >> 16 & 255) / 255, (v >> 8 & 255) / 255, (v & 255) / 255]; };
    gl.uniform3fv(U('uCream'), rgb(css('--cream') || '#fbfaf6'));
    gl.uniform3fv(U('uOcre'), rgb(css('--ocre') || '#b8860b'));
    gl.uniform3fv(U('uRed'), rgb(css('--red') || '#cc0000'));
    const portrait = $('[data-hero-portrait]');
    const scale = Math.min(devicePixelRatio, 1.5) * 0.5; // demi-résolution : le flou est voulu et léger
    const size = () => {
      const r = canvas.getBoundingClientRect();
      canvas.width = Math.max(1, r.width * scale); canvas.height = Math.max(1, r.height * scale);
      gl.viewport(0, 0, canvas.width, canvas.height);
      gl.uniform2f(U('uRes'), canvas.width, canvas.height);
      gl.uniform1f(U('uMobile'), innerWidth < 900 ? 1 : 0);
      if (portrait) {
        const pr = portrait.getBoundingClientRect();
        gl.uniform4f(U('uHole'), (pr.left - r.left + pr.width / 2) * scale, (r.bottom - pr.bottom + pr.height * .55) * scale, pr.width * .62 * scale, pr.height * .6 * scale);
      } else gl.uniform4f(U('uHole'), -999, -999, 1, 1);
    };
    size(); addEventListener('resize', size);
    let mx = .5, my = .5, tx = .5, ty = .5, visible = true, t0 = performance.now();
    addEventListener('pointermove', (e) => { tx = e.clientX / innerWidth; ty = 1 - e.clientY / innerHeight; }, { passive: true });
    new IntersectionObserver(([en]) => { visible = en.isIntersecting; if (visible && !reduce) requestAnimationFrame(loop); }).observe(canvas);
    function frame(now) {
      mx += (tx - mx) * .04; my += (ty - my) * .04;
      gl.uniform1f(U('uTime'), (now - t0) / 1000 + 12);
      gl.uniform2f(U('uMouse'), mx, my);
      gl.drawArrays(gl.TRIANGLES, 0, 3);
    }
    function loop(now) { if (!visible) return; frame(now); requestAnimationFrame(loop); }
    if (reduce) frame(performance.now()); else requestAnimationFrame(loop);
    if (hasGsap && !reduce) gsap.from(canvas, { autoAlpha: 0, duration: 2.2, ease: 'power2.out', delay: .2 });
  }

  /* ======================================================================
     Bibliothèque 3D (Three.js), chargée seulement à l'approche de la section
     ====================================================================== */
  function library() {
    const sec = $('[data-library]');
    if (!sec) return;
    const slow = navigator.connection && (navigator.connection.saveData || /2g/.test(navigator.connection.effectiveType || ''));
    const gl = (() => { try { return !!document.createElement('canvas').getContext('webgl2'); } catch (e) { return false; } })();
    if (reduce || slow || !gl) { sec.classList.add('is-fallback'); return; }
    const io = new IntersectionObserver(([en]) => {
      if (!en.isIntersecting) return;
      io.disconnect();
      import(P.assets + 'js/library3d.js').then((m) => m.default(sec)).catch(() => sec.classList.add('is-fallback'));
    }, { rootMargin: '1400px 0px' });
    io.observe(sec);
  }

  /* ======================================================================
     Animations GSAP
     ====================================================================== */
  function animations() {
    if (!hasGsap) { show($$('[data-hero-chars], [data-hero-fade], [data-hero-portrait], .page-head__title, [data-split-chars]')); return; }
    gsap.registerPlugin(ScrollTrigger, SplitText, Flip, DrawSVGPlugin, CustomEase);
    CustomEase.create('plume', '0.16,1,0.3,1');
    gsap.defaults({ ease: 'plume', duration: 1.1 });

    if (reduce) { show($$('[data-hero-chars], [data-hero-fade], [data-hero-portrait], .page-head__title, [data-split-chars]')); $('.loader')?.classList.add('is-done'); tomes(); return; }

    /* Défilement doux (Lenis) synchronisé avec ScrollTrigger */
    if (window.Lenis) {
      const lenis = new Lenis({ lerp: .1, smoothWheel: true, wheelMultiplier: 1 });
      window.__lenis = lenis;
      lenis.on('scroll', (e) => { ScrollTrigger.update(); onScroll(e.scroll); });
      gsap.ticker.add((t) => lenis.raf(t * 1000));
      gsap.ticker.lagSmoothing(0);
      $$('a[href^="#"]').forEach((a) => a.addEventListener('click', (e) => {
        const target = $(a.getAttribute('href'));
        if (target) { e.preventDefault(); lenis.scrollTo(target, { offset: -20, duration: 1.4 }); }
      }));
    }

    /* Intro : rideau d'ouverture (une fois par session) puis hero */
    const loader = $('.loader');
    const hero = $('[data-hero]');
    const introDone = (() => { try { return sessionStorage.getItem('plume-intro'); } catch (e) { return '1'; } })();
    const intro = gsap.timeline();
    if (loader && !introDone) {
      try { sessionStorage.setItem('plume-intro', '1'); } catch (e) { /* */ }
      const split = new SplitText($('.loader__name', loader), { type: 'chars', charsClass: 'char' });
      const yr = $('[data-count-from]', loader); const end = parseInt(yr?.parentElement.textContent.split('—')[1], 10);
      const from = parseInt(yr?.dataset.countFrom, 10);
      intro.from(split.chars, { yPercent: 120, autoAlpha: 0, stagger: .025, duration: .9 })
        .to($('.loader__line', loader), { scaleX: 1, duration: 1, ease: 'expo.inOut' }, '-=.6')
        .from($('.loader__years', loader), { autoAlpha: 0, y: 10, duration: .6 }, '<');
      if (yr && from && end) { const o = { v: from }; intro.to(o, { v: end, duration: .9, ease: 'power2.inOut', onUpdate: () => { yr.textContent = Math.round(o.v); } }, '<'); }
      intro.to(loader, { clipPath: 'inset(0 0 100% 0)', duration: 1, ease: 'expo.inOut' }, '+=.15')
        .add(() => loader.classList.add('is-done'));
    } else loader?.classList.add('is-done');

    if (hero) {
      const chars = $$('[data-hero-chars]', hero).map((el) => new SplitText(el, { type: 'chars', charsClass: 'char' }).chars).flat();
      const portrait = $('[data-hero-portrait]', hero);
      const pImg = $('img', portrait) || portrait.firstElementChild;
      show($$('[data-hero-chars], [data-hero-fade], [data-hero-portrait]', hero));
      intro.addLabel('hero', loader && !introDone ? '-=.55' : 0)
        .fromTo(portrait, { clipPath: 'inset(100% 0% 0% 0%)' }, { clipPath: 'inset(0% 0% 0% 0%)', duration: 1.5, ease: 'expo.inOut' }, 'hero')
        .from(pImg, { scale: 1.3, duration: 2.2, ease: 'expo.out' }, 'hero')
        .from(chars, { yPercent: 115, rotate: 8, autoAlpha: 0, stagger: .035, duration: 1.3 }, 'hero+=.35')
        .from($$('[data-hero-fade]', hero), { y: 30, autoAlpha: 0, stagger: .12, duration: 1 }, 'hero+=.8')
        .from($$('.hero__curves path', hero), { drawSVG: '0%', duration: 2.4, stagger: .2, ease: 'power2.inOut' }, 'hero+=.4')
        .from($('.hero__scroll', hero), { autoAlpha: 0, duration: .8 }, '-=1');

      gsap.timeline({ scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true } })
        .to($('.hero__name', hero), { yPercent: -30, ease: 'none' }, 0)
        .to(portrait, { yPercent: 12, ease: 'none' }, 0)
        .to($('.hero__curves', hero), { yPercent: 20, ease: 'none' }, 0)
        .to($('.hero__text', hero), { autoAlpha: .2, ease: 'none' }, 0);
    }

    /* Titres de pages intérieures */
    $$('[data-split-chars]').forEach((el) => {
      const s = new SplitText(el, { type: 'words,chars', charsClass: 'char', wordsClass: 'word', mask: 'words' });
      show(el);
      gsap.from(s.chars, { yPercent: 130, autoAlpha: 0, stagger: .025, duration: 1.2, delay: .1 });
    });

    /* Titres de section : révélation ligne par ligne sous masque */
    $$('[data-split-lines]').forEach((el) => {
      SplitText.create(el, { type: 'lines', mask: 'lines', autoSplit: true, linesClass: 'line',
        onSplit: (self) => gsap.from(self.lines, { yPercent: 125, duration: 1.2, stagger: .1, scrollTrigger: { trigger: el, start: 'top 88%' } }) });
    });

    /* Apparitions simples */
    $$('[data-fade]').forEach((el) => gsap.from(el, { y: 36, autoAlpha: 0, duration: 1.1, scrollTrigger: { trigger: el, start: 'top 90%' } }));
    $$('[data-fade-children]').forEach((el) => gsap.from(el.children, { y: 30, autoAlpha: 0, stagger: .08, duration: 1, scrollTrigger: { trigger: el, start: 'top 85%' } }));
    $$('[data-reveal]').forEach((el) => gsap.from(el, { y: 60, autoAlpha: 0, duration: 1.2, scrollTrigger: { trigger: el, start: 'top 90%' } }));
    $$('[data-stagger]').forEach((el) => {
      ScrollTrigger.batch(el.children, { start: 'top 92%', once: true,
        onEnter: (b) => gsap.fromTo(b, { y: 60, autoAlpha: 0 }, { y: 0, autoAlpha: 1, stagger: .09, duration: 1.1, overwrite: true }) });
      gsap.set(el.children, { autoAlpha: 0 });
    });

    /* Courbes ocre tracées au défilement */
    $$('[data-draw]').forEach((p) => {
      if (p.closest('.hero__curves')) return;
      gsap.from(p, { drawSVG: '0%', duration: 2.4, ease: 'power2.inOut', scrollTrigger: { trigger: p.closest('section, header, li') || p, start: 'top 85%' } });
    });

    /* Compteurs */
    $$('[data-count]').forEach((el) => {
      const end = parseInt(el.dataset.count, 10);
      if (!end) return;
      const o = { v: 0 }; const txt = el.dataset.countText;
      gsap.to(o, { v: end, duration: 2.2, ease: 'power3.out', scrollTrigger: { trigger: el, start: 'top 88%' },
        onUpdate: () => { el.textContent = txt ? txt.replace(/\d+/, Math.round(o.v)) : Math.round(o.v); } });
    });

    /* Parallaxe des images */
    $$('[data-parallax-img]').forEach((fig) => {
      const img = $('img', fig); if (!img) return;
      gsap.fromTo(img, { yPercent: -6, scale: 1.12 }, { yPercent: 6, ease: 'none', scrollTrigger: { trigger: fig, scrub: true } });
    });

    tomes();

    /* Bandeau presse : défilement infini qui accélère avec la vitesse de scroll */
    $$('[data-marquee]').forEach((m) => {
      const track = $('.marquee__track', m);
      const loop = gsap.to(track, { xPercent: -50, duration: 60, ease: 'none', repeat: -1 });
      ScrollTrigger.create({ trigger: m, onUpdate: (self) => {
        const v = gsap.utils.clamp(-6, 6, self.getVelocity() / 250);
        gsap.to(loop, { timeScale: (v < 0 ? -1 : 1) * (1 + Math.abs(v)), duration: .2, overwrite: true });
        gsap.to(loop, { timeScale: v < 0 ? -1 : 1, duration: 1.2, delay: .2 });
      } });
    });

    parcours();

    /* Signature du pied de page */
    $$('[data-split-reveal]').forEach((el) => {
      const s = new SplitText(el, { type: 'words,chars', charsClass: 'char', wordsClass: 'word', mask: 'words' });
      gsap.from(s.chars, { yPercent: 130, stagger: .03, duration: 1.2, scrollTrigger: { trigger: el, start: 'top 95%' } });
    });

    /* Boutons magnétiques et curseur (souris uniquement) */
    if (fine) {
      $$('[data-magnetic]').forEach((el) => {
        const xTo = gsap.quickTo(el, 'x', { duration: .6, ease: 'elastic.out(1, .4)' });
        const yTo = gsap.quickTo(el, 'y', { duration: .6, ease: 'elastic.out(1, .4)' });
        el.addEventListener('pointermove', (e) => { const r = el.getBoundingClientRect(); xTo((e.clientX - r.left - r.width / 2) * .28); yTo((e.clientY - r.top - r.height / 2) * .35); });
        el.addEventListener('pointerleave', () => { xTo(0); yTo(0); });
      });
      cursor();
    }

    addEventListener('load', () => ScrollTrigger.refresh());
    document.fonts?.ready.then(() => ScrollTrigger.refresh());
  }

  /* ======================================================================
     Parcours immersif : lecture au fil du défilement, plongée dans les mots,
     voyage dans le temps (frise horizontale, courbe tracée, années en parallaxe)
     ====================================================================== */
  function parcours() {
    // 1. La biographie s'allume mot à mot
    $$('[data-bio-read] > p').forEach((p) => {
      const s = new SplitText(p, { type: 'words', wordsClass: 'w' });
      gsap.fromTo(s.words, { opacity: .14 }, { opacity: 1, ease: 'none', stagger: .08,
        scrollTrigger: { trigger: p, start: 'top 82%', end: 'bottom 52%', scrub: .6 } });
    });
    const bp = $('[data-bio-portrait]');
    if (bp) {
      const m = $('picture, .photo-soon', bp);
      gsap.fromTo(m, { clipPath: 'inset(100% 0% 0% 0%)' }, { clipPath: 'inset(0% 0% 0% 0%)', duration: 1.6, ease: 'expo.inOut', scrollTrigger: { trigger: bp, start: 'top 85%' } });
      gsap.fromTo($('.bio__frame', bp), { x: -8, y: -8, autoAlpha: 0 }, { x: 16, y: 16, autoAlpha: 1, duration: 1.4, delay: .7, scrollTrigger: { trigger: bp, start: 'top 85%' } });
      const img = $('img', bp);
      if (img) gsap.fromTo(img, { scale: 1.18 }, { scale: 1, ease: 'none', scrollTrigger: { trigger: bp.closest('section'), start: 'top bottom', end: 'bottom top', scrub: true } });
    }

    // 2. Plongée : la phrase grandit jusqu'à ce qu'on traverse une lettre
    const dive = $('[data-dive]');
    if (dive) {
      const txt = $('[data-dive-text]', dive);
      const s = new SplitText(txt, { type: 'words,chars', wordsClass: 'word', charsClass: 'char' });
      const last = s.words[s.words.length - 1];
      const hole = $$('.char', last).find((c) => /[oaedOAEDq]/.test(c.textContent)) || s.chars[s.chars.length - 1];
      const origin = () => {
        const r = hole.getBoundingClientRect(), t = txt.getBoundingClientRect();
        return `${r.left - t.left + r.width / 2}px ${r.top - t.top + r.height * .56}px`;
      };
      gsap.timeline({ scrollTrigger: { trigger: dive, start: 'top top', end: '+=190%', pin: true, scrub: 1, invalidateOnRefresh: true, anticipatePin: 1 } })
        .fromTo(s.words, { opacity: .08, yPercent: 40, rotateX: -50 }, { opacity: 1, yPercent: 0, rotateX: 0, stagger: .14, duration: .7, ease: 'power2.out' })
        .to({}, { duration: .25 })
        .to(txt, { color: css('--ocre'), duration: .35 })
        .to(dive, { backgroundColor: css('--ink'), duration: .5 }, '<')
        .fromTo(txt, { scale: 1 }, { scale: 70, transformOrigin: origin, duration: 1.1, ease: 'power3.in', immediateRender: false }, '<+.1')
        .to(txt, { autoAlpha: 0, duration: .12 }, '>-.12');
    }

    // 3. Voyage dans le temps
    const jr = $('[data-journey]');
    if (!jr) return;
    const track = $('[data-journey-track]', jr), stops = $$('.stop', jr);
    const yearEl = $('[data-journey-year]', jr), idxEl = $('[data-journey-index]', jr);
    let active = -1;
    const setActive = (i) => {
      if (i === active) return;
      active = i;
      stops.forEach((s, k) => s.classList.toggle('is-active', k === i));
      if (idxEl) idxEl.textContent = String(i + 1).padStart(2, '0');
      if (yearEl) {
        yearEl.textContent = stops[i].dataset.year;
        gsap.fromTo(yearEl, { yPercent: 45, autoAlpha: 0 }, { yPercent: 0, autoAlpha: 1, duration: .55, ease: 'expo.out', overwrite: true });
      }
    };
    dust(jr);
    gsap.from($$('.journey__head > *', jr), { y: 40, autoAlpha: 0, stagger: .12, duration: 1.1, scrollTrigger: { trigger: jr, start: 'top 75%' } });

    const mm = gsap.matchMedia();
    mm.add('(min-width: 1024px)', () => {
      jr.classList.add('is-horizontal');
      const svg = $('.journey__path', jr), pBg = $('.journey__path-bg', jr), pFg = $('.journey__path-fg', jr);
      let dots = [], W = 1;
      // Courbe lisse passant par chaque repère (Catmull-Rom → Bézier)
      const build = () => {
        const tr = track.getBoundingClientRect(), x0 = gsap.getProperty(track, 'x');
        dots = stops.map((s) => { const r = $('.stop__dot', s).getBoundingClientRect(); return [r.left - tr.left + r.width / 2, r.top - tr.top + r.height / 2]; });
        W = track.scrollWidth; const H = track.offsetHeight;
        svg.setAttribute('width', W); svg.setAttribute('height', H); svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
        const pts = [[0, dots[0][1] + 80], ...dots, [W, dots[dots.length - 1][1] - 60]];
        let d = `M${pts[0][0]},${pts[0][1]}`;
        for (let i = 0; i < pts.length - 1; i++) {
          const p0 = pts[i - 1] || pts[i], p1 = pts[i], p2 = pts[i + 1], p3 = pts[i + 2] || p2;
          d += ` C${(p1[0] + (p2[0] - p0[0]) / 6).toFixed(1)},${(p1[1] + (p2[1] - p0[1]) / 6).toFixed(1)} ${(p2[0] - (p3[0] - p1[0]) / 6).toFixed(1)},${(p2[1] - (p3[1] - p1[1]) / 6).toFixed(1)} ${p2[0].toFixed(1)},${p2[1].toFixed(1)}`;
        }
        pBg.setAttribute('d', d); pFg.setAttribute('d', d);
        return x0;
      };
      build();
      const dist = () => track.scrollWidth - innerWidth;
      const render = () => {
        const x = gsap.getProperty(track, 'x'), vw = innerWidth, c = vw * .5;
        const prog = dist() > 0 ? gsap.utils.clamp(0, 1, -x / dist()) : 0;
        jr.style.setProperty('--jp', prog.toFixed(4));
        gsap.set(pFg, { drawSVG: `0% ${gsap.utils.clamp(0, 100, ((c - x) / W) * 100)}%` });
        let best = 0, bi = 0;
        stops.forEach((s, i) => {
          const sx = dots[i][0] + x, off = (sx - c) / (vw * .5);
          const p = gsap.utils.clamp(0, 1, 1 - Math.abs(off) * 1.15);
          s.style.setProperty('--p', p.toFixed(3));
          s.style.setProperty('--gx', (-(sx - c) * .28).toFixed(1));
          if (p > best) { best = p; bi = i; }
        });
        setActive(bi);
      };
      gsap.to(track, { x: () => -dist(), ease: 'none', onUpdate: render,
        scrollTrigger: { trigger: jr, start: 'top top', end: () => '+=' + dist() * 1.2, pin: true, scrub: 1.2, invalidateOnRefresh: true, anticipatePin: 1,
          onRefresh: () => { build(); render(); } } });
      render();
      return () => { jr.classList.remove('is-horizontal'); gsap.set(track, { clearProps: 'transform' }); };
    });
    mm.add('(max-width: 1023px)', () => {
      gsap.fromTo(track, { '--fill': 0 }, { '--fill': 1, ease: 'none', scrollTrigger: { trigger: track, start: 'top 60%', end: 'bottom 60%', scrub: true } });
      const st = ScrollTrigger.create({ trigger: track, start: 'top bottom', end: 'bottom top', onUpdate: () => {
        const c = innerHeight * .55; let best = 0, bi = 0;
        stops.forEach((s, i) => {
          const r = s.getBoundingClientRect();
          const p = gsap.utils.clamp(0, 1, 1 - Math.abs((r.top + 30 - c) / (innerHeight * .55)));
          s.style.setProperty('--p', p.toFixed(3));
          if (p > best) { best = p; bi = i; }
        });
        setActive(bi);
      } });
      st.update?.();
      return () => stops.forEach((s) => s.style.removeProperty('--p'));
    });
    // Les déclencheurs créés avant les sections épinglées doivent tenir compte de leur durée
    ScrollTrigger.sort();
  }

  /* Poussière dorée : particules qui dérivent, accélérées par la vitesse de défilement */
  function dust(jr) {
    const cv = $('.journey__dust', jr);
    if (!cv) return;
    const ctx = cv.getContext('2d');
    const ocre = css('--ocre') || '#b8860b';
    let W = 0, H = 0, vel = 0, run = false, t = 0;
    const parts = [];
    const size = () => {
      const horiz = jr.classList.contains('is-horizontal');
      const dpr = horiz ? Math.min(devicePixelRatio, 1.5) : 1;
      W = cv.offsetWidth; H = cv.offsetHeight;
      cv.width = Math.max(1, W * dpr); cv.height = Math.max(1, H * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      while (parts.length < (innerWidth < 700 ? 45 : 120)) parts.push({ x: Math.random() * W, y: Math.random() * H, r: Math.random() * 1.7 + .3, s: Math.random() * .45 + .1, a: Math.random() * .6 + .2, ph: Math.random() * 6 });
      parts.forEach((p) => { p.x = Math.random() * W; p.y = Math.random() * H; });
    };
    size();
    ScrollTrigger.addEventListener('refresh', size);
    ScrollTrigger.create({ trigger: jr, start: 'top bottom', end: 'bottom top',
      onToggle: (s) => { run = s.isActive; if (run) requestAnimationFrame(loop); },
      onUpdate: (s) => { vel = gsap.utils.clamp(-30, 30, s.getVelocity() / 80); } });
    function loop() {
      if (!run) return;
      t += .016; vel *= .95;
      const horiz = jr.classList.contains('is-horizontal');
      ctx.clearRect(0, 0, W, H);
      ctx.fillStyle = ocre;
      for (const p of parts) {
        if (horiz) { p.x -= p.s * (1 + Math.abs(vel)); p.y += Math.sin(t + p.ph) * .2; }
        else { p.y -= p.s * (.6 + Math.abs(vel) * .4); p.x += Math.sin(t + p.ph) * .2; }
        if (p.x < -5) p.x = W + 5; if (p.x > W + 5) p.x = -5; if (p.y < -5) p.y = H + 5; if (p.y > H + 5) p.y = -5;
        ctx.globalAlpha = p.a * (.55 + .45 * Math.sin(t * 1.7 + p.ph));
        ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, 6.283); ctx.fill();
      }
      requestAnimationFrame(loop);
    }
  }

  function cursor() {
    const c = $('.cursor'); if (!c) return;
    doc.classList.add('has-cursor');
    const dot = $('.cursor__dot', c), ring = $('.cursor__ring', c), label = $('em', ring);
    gsap.set([dot, ring], { x: innerWidth / 2, y: innerHeight / 2 });
    const dx = gsap.quickTo(dot, 'x', { duration: .12 }), dy = gsap.quickTo(dot, 'y', { duration: .12 });
    const rx = gsap.quickTo(ring, 'x', { duration: .5, ease: 'power3' }), ry = gsap.quickTo(ring, 'y', { duration: .5, ease: 'power3' });
    addEventListener('pointermove', (e) => {
      dx(e.clientX); dy(e.clientY); rx(e.clientX); ry(e.clientY);
      const t = e.target;
      const lab = t.closest?.('[data-cursor]');
      const link = t.closest?.('a, button, input, select, textarea, label');
      c.classList.toggle('is-label', !!lab); c.classList.toggle('is-link', !lab && !!link);
      if (lab) label.textContent = lab.dataset.cursor;
      c.classList.toggle('is-dark', !!t.closest?.('.section--ink, .site-footer, .menu, .section--red'));
    }, { passive: true });
    document.addEventListener('pointerleave', () => c.classList.add('is-hidden'));
    document.addEventListener('pointerenter', () => c.classList.remove('is-hidden'));
    window.__cursor = c;
  }

  /* ---------------------------------------------------------- démarrage */
  heroInk();
  animations();
  library();
})();
