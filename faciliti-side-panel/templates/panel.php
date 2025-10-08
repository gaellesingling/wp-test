<?php
/**
 * Basic slide-in panel markup with placeholders only.
 * Customize freely. Keep IDs/classes to benefit from default JS/CSS.
 */
?>
<button class="fsp-launcher" aria-expanded="false" aria-controls="fsp-panel">
  <?php echo esc_html( isset( $fsp_open_label ) ? $fsp_open_label : ( isset( $GLOBALS['FSP']['openLabel'] ) ? $GLOBALS['FSP']['openLabel'] : 'Open panel' ) ); ?>
</button>

<div class="fsp-overlay" aria-hidden="true"></div>

<aside id="fsp-panel" class="fsp-panel" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="fsp-panel-title">
  <header class="fsp-panel__header">
    <h2 id="fsp-panel-title" class="fsp-title">Accessibilité — Placeholder</h2>
    <button class="fsp-close" aria-label="<?php esc_attr_e('Close panel','faciliti-side-panel'); ?>">✕</button>
  </header>

  <div class="fsp-panel__body">
    <section class="fsp-section" aria-labelledby="fsp-search-title">
      <h3 id="fsp-search-title">Recherche (placeholder)</h3>
      <div class="fsp-placeholder">[ champ de recherche à implémenter ]</div>
    </section>

    <section class="fsp-section" aria-labelledby="fsp-quick-title">
      <h3 id="fsp-quick-title">Filtres rapides (placeholder)</h3>
      <ul class="fsp-placeholder" role="list">
        <li>• Dyslexie — (switch à ajouter)</li>
        <li>• Fatigue visuelle — (switch à ajouter)</li>
        <li>• Mode nuit — (switch à ajouter)</li>
      </ul>
    </section>

    <section class="fsp-section" aria-labelledby="fsp-categories-title">
      <h3 id="fsp-categories-title">Catégories (placeholder)</h3>
      <div class="fsp-placeholder">[ sélecteur de catégorie, options dynamiques à venir ]</div>
    </section>

    <section class="fsp-section" aria-labelledby="fsp-toggles-title">
      <h3 id="fsp-toggles-title">Réglages (placeholder)</h3>
      <div class="fsp-placeholder">[ liste de réglages avec interrupteurs, sliders, etc. ]</div>
    </section>
  </div>

  <div class="fsp-panel__footer">
    <button class="fsp-btn">Réinitialiser (placeholder)</button>
    <button class="fsp-btn primary">Appliquer (placeholder)</button>
  </div>
</aside>
