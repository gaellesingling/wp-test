<?php
/**
 * Basic slide-in panel markup with accessible controls.
 * Customize freely. Keep IDs/classes to benefit from default JS/CSS.
 */
?>
<button class="fsp-launcher" aria-expanded="false" aria-controls="fsp-panel">
  <?php echo esc_html( isset( $fsp_open_label ) ? $fsp_open_label : ( isset( $GLOBALS['FSP']['openLabel'] ) ? $GLOBALS['FSP']['openLabel'] : 'Open panel' ) ); ?>
</button>

<div class="fsp-overlay" aria-hidden="true"></div>

<aside id="fsp-panel" class="fsp-panel" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="fsp-panel-title">
  <header class="fsp-panel__header">
    <h2 id="fsp-panel-title" class="fsp-title">Accessibilité</h2>
    <button class="fsp-close" aria-label="<?php esc_attr_e('Close panel','faciliti-side-panel'); ?>">✕</button>
  </header>

  <div class="fsp-panel__body">
    <section class="fsp-section" aria-labelledby="fsp-search-title">
      <h3 id="fsp-search-title">Recherche</h3>
      <form class="fsp-form" role="search" data-fsp-search>
        <label class="fsp-label" for="fsp-search-input">Rechercher un mot clé</label>
        <div class="fsp-form__controls">
          <input id="fsp-search-input" class="fsp-input" type="search" name="s" placeholder="Rechercher" autocomplete="off" />
          <button type="submit" class="fsp-btn secondary">Rechercher</button>
        </div>
      </form>
    </section>

    <section class="fsp-section" aria-labelledby="fsp-quick-title">
      <h3 id="fsp-quick-title">Filtres rapides</h3>
      <ul class="fsp-toggle-list" role="list" data-fsp-quick-toggle-group>
        <li>
          <button type="button" class="fsp-toggle" data-fsp-toggle="dyslexia" aria-pressed="false">Mode dyslexie</button>
        </li>
        <li>
          <button type="button" class="fsp-toggle" data-fsp-toggle="eye-strain" aria-pressed="false">Réduire la fatigue visuelle</button>
        </li>
        <li>
          <button type="button" class="fsp-toggle" data-fsp-toggle="night" aria-pressed="false">Mode nuit</button>
        </li>
      </ul>
    </section>

    <section class="fsp-section" aria-labelledby="fsp-categories-title">
      <h3 id="fsp-categories-title">Catégories</h3>
      <label class="fsp-label" for="fsp-category-select">Choisir une catégorie</label>
      <select id="fsp-category-select" class="fsp-select" data-fsp-category>
        <option value="">Toutes les catégories</option>
        <option value="vision">Vision</option>
        <option value="audition">Audition</option>
        <option value="mobilite">Mobilité</option>
        <option value="cognition">Cognition</option>
      </select>
    </section>

    <section class="fsp-section" aria-labelledby="fsp-toggles-title">
      <h3 id="fsp-toggles-title">Réglages</h3>
      <ul class="fsp-settings-list" role="list">
        <li class="fsp-setting">
          <div class="fsp-setting__content">
            <span class="fsp-setting__label">Augmenter le contraste</span>
            <span class="fsp-setting__description">Renforce les contrastes pour un meilleur confort de lecture.</span>
          </div>
          <label class="fsp-switch">
            <input type="checkbox" data-fsp-setting="contrast" />
            <span class="fsp-switch__indicator" aria-hidden="true"></span>
            <span class="fsp-switch__text">Activer</span>
          </label>
        </li>
        <li class="fsp-setting">
          <div class="fsp-setting__content">
            <span class="fsp-setting__label">Augmenter la taille du texte</span>
            <span class="fsp-setting__description">Agrandit la typographie du contenu principal.</span>
          </div>
          <label class="fsp-switch">
            <input type="checkbox" data-fsp-setting="font" />
            <span class="fsp-switch__indicator" aria-hidden="true"></span>
            <span class="fsp-switch__text">Activer</span>
          </label>
        </li>
        <li class="fsp-setting">
          <div class="fsp-setting__content">
            <span class="fsp-setting__label">Réduire les animations</span>
            <span class="fsp-setting__description">Limite les effets visuels et transitions rapides.</span>
          </div>
          <label class="fsp-switch">
            <input type="checkbox" data-fsp-setting="motion" />
            <span class="fsp-switch__indicator" aria-hidden="true"></span>
            <span class="fsp-switch__text">Activer</span>
          </label>
        </li>
      </ul>
    </section>
  </div>

  <div class="fsp-panel__footer">
    <button type="button" class="fsp-btn" data-fsp-reset>Réinitialiser</button>
    <button type="button" class="fsp-btn primary" data-fsp-apply>Appliquer</button>
  </div>
</aside>
