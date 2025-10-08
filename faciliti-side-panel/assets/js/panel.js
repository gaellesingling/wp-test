(function() {
  const q = (sel, ctx=document) => ctx.querySelector(sel);
  const qa = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));

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
      // Focus the first focusable or the close button
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
