(function() {
  const q = (sel, ctx = document) => ctx.querySelector(sel);
  const qa = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
  const STORAGE_KEY = 'fsp-panel-state';

  const defaultState = {
    search: '',
    quickFilters: {
      dyslexia: false,
      'eye-strain': false,
      night: false
    },
    category: '',
    settings: {
      contrast: false,
      font: false,
      motion: false
    }
  };

  function trapFocus(container) {
    const focusableSelectors = [
      'a[href]','area[href]','input:not([disabled])','select:not([disabled])',
      'textarea:not([disabled])','button:not([disabled])','iframe','object','embed',
      '[contenteditable]','[tabindex]:not([tabindex="-1"])'
    ];
    const focusables = () => qa(focusableSelectors.join(','), container).filter(el => !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length));
    function handle(e){
      if (e.key !== 'Tab') return;
      const nodes = focusables();
      if (!nodes.length) return;
      const first = nodes[0], last = nodes[nodes.length-1];
      if (e.shiftKey && document.activeElement === first){ last.focus(); e.preventDefault(); }
      else if (!e.shiftKey && document.activeElement === last){ first.focus(); e.preventDefault(); }
    }
    return {
      enable(){ container.addEventListener('keydown', handle); },
      disable(){ container.removeEventListener('keydown', handle); }
    }
  }

  function storageAvailable(){
    try {
      const test = '__fsp__';
      localStorage.setItem(test, test);
      localStorage.removeItem(test);
      return true;
    } catch (err) {
      return false;
    }
  }

  const HAS_STORAGE = storageAvailable();

  function loadState(){
    if (!HAS_STORAGE) return JSON.parse(JSON.stringify(defaultState));
    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      if (!stored) return JSON.parse(JSON.stringify(defaultState));
      const parsed = JSON.parse(stored);
      return {
        ...JSON.parse(JSON.stringify(defaultState)),
        ...parsed,
        quickFilters: { ...defaultState.quickFilters, ...(parsed.quickFilters || {}) },
        settings: { ...defaultState.settings, ...(parsed.settings || {}) }
      };
    } catch (err) {
      return JSON.parse(JSON.stringify(defaultState));
    }
  }

  function saveState(state){
    if (!HAS_STORAGE) return;
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (err) {
      // ignore
    }
  }

  function clearState(){
    if (!HAS_STORAGE) return;
    try {
      localStorage.removeItem(STORAGE_KEY);
    } catch (err) {
      // ignore
    }
  }

  function setupControls(panel){
    const searchForm = q('[data-fsp-search]', panel);
    const searchInput = searchForm ? q('input[type="search"]', searchForm) : null;
    const quickToggles = qa('[data-fsp-toggle]', panel);
    const categorySelect = q('[data-fsp-category]', panel);
    const settingInputs = qa('[data-fsp-setting]', panel);
    const resetBtn = q('[data-fsp-reset]', panel);
    const applyBtn = q('[data-fsp-apply]', panel);

    let state = loadState();

    function syncQuickButtons(){
      quickToggles.forEach(btn => {
        const key = btn.getAttribute('data-fsp-toggle');
        const active = !!state.quickFilters[key];
        btn.setAttribute('aria-pressed', String(active));
        btn.classList.toggle('is-active', active);
      });
    }

    function syncSettings(){
      settingInputs.forEach(input => {
        const key = input.getAttribute('data-fsp-setting');
        input.checked = !!state.settings[key];
      });
    }

    function syncSearch(){
      if (searchInput) searchInput.value = state.search || '';
    }

    function syncCategory(){
      if (categorySelect) categorySelect.value = state.category || '';
    }

    function syncAll(){
      syncQuickButtons();
      syncSettings();
      syncSearch();
      syncCategory();
    }

    function dispatchApply(){
      const detail = JSON.parse(JSON.stringify(state));
      panel.dispatchEvent(new CustomEvent('fsp:apply', { detail }));
    }

    syncAll();

    quickToggles.forEach(btn => {
      btn.addEventListener('click', () => {
        const key = btn.getAttribute('data-fsp-toggle');
        state.quickFilters[key] = !state.quickFilters[key];
        saveState(state);
        syncQuickButtons();
      });
    });

    settingInputs.forEach(input => {
      input.addEventListener('change', () => {
        const key = input.getAttribute('data-fsp-setting');
        state.settings[key] = input.checked;
        saveState(state);
      });
    });

    if (categorySelect){
      categorySelect.addEventListener('change', () => {
        state.category = categorySelect.value;
        saveState(state);
      });
    }

    if (searchForm && searchInput){
      searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        state.search = searchInput.value.trim();
        saveState(state);
        dispatchApply();
      });
      searchInput.addEventListener('input', () => {
        state.search = searchInput.value;
        saveState(state);
      });
    }

    if (resetBtn){
      resetBtn.addEventListener('click', () => {
        state = JSON.parse(JSON.stringify(defaultState));
        clearState();
        syncAll();
        panel.dispatchEvent(new CustomEvent('fsp:reset'));
      });
    }

    if (applyBtn){
      applyBtn.addEventListener('click', () => {
        dispatchApply();
      });
    }

    return {
      getState: () => JSON.parse(JSON.stringify(state)),
      apply: dispatchApply
    };
  }

  function setup(){
    const launcher = q('.fsp-launcher');
    const overlay = q('.fsp-overlay');
    const panel = q('.fsp-panel');
    const closeBtn = q('.fsp-close', panel);
    if (!launcher || !overlay || !panel || !closeBtn) return;

    const tf = trapFocus(panel);
    let prevFocused = null;

    function open(){
      prevFocused = document.activeElement;
      panel.setAttribute('aria-hidden','false');
      overlay.setAttribute('aria-hidden','false');
      launcher.setAttribute('aria-expanded','true');
      tf.enable();
      const firstFocusable = panel.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
      (firstFocusable || closeBtn).focus();
      document.body.style.overflow = 'hidden';
    }
    function close(){
      panel.setAttribute('aria-hidden','true');
      overlay.setAttribute('aria-hidden','true');
      launcher.setAttribute('aria-expanded','false');
      tf.disable();
      document.body.style.overflow = '';
      if (prevFocused) prevFocused.focus();
    }

    setupControls(panel);

    launcher.addEventListener('click', open);
    closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', close);
    document.addEventListener('keydown', (e)=>{
      if (e.key === 'Escape' && panel.getAttribute('aria-hidden') === 'false'){ close(); }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setup);
  } else {
    setup();
  }
})();
