<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="wrap awpt">
    <h1><?php echo esc_html__( 'Accessible WP Toolkit', 'accessible-wp-toolkit' ); ?></h1>
    <p class="description">
        <?php echo esc_html__( 'Analyses rapides pour améliorer le contraste, la navigation clavier, les landmarks ARIA et l’accessibilité des médias.', 'accessible-wp-toolkit' ); ?>
    </p>

    <?php if ( ! empty( $awpt_state['errors'] ) ) : ?>
        <div class="notice notice-error">
            <ul>
                <?php foreach ( $awpt_state['errors'] as $error ) : ?>
                    <li><?php echo esc_html( $error ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="awpt-grid">
        <section class="awpt-card" aria-labelledby="awpt-contrast-title">
            <h2 id="awpt-contrast-title"><?php echo esc_html__( 'Contraste des couleurs', 'accessible-wp-toolkit' ); ?></h2>
            <p><?php echo esc_html__( 'Calculez le ratio de contraste WCAG pour deux couleurs et voyez les niveaux atteints.', 'accessible-wp-toolkit' ); ?></p>

            <form method="post" class="awpt-form awpt-form--contrast">
                <?php wp_nonce_field( 'awpt_contrast', 'awpt_nonce' ); ?>
                <input type="hidden" name="awpt_action" value="contrast" />
                <div class="awpt-field">
                    <label for="awpt-bg"><?php echo esc_html__( 'Couleur de fond', 'accessible-wp-toolkit' ); ?></label>
                    <input
                        type="text"
                        id="awpt-bg"
                        name="bg"
                        value="<?php echo esc_attr( awpt_state_value( $awpt_state, 'contrast', 'bg', '#FFFFFF' ) ); ?>"
                        class="regular-text awpt-color-input"
                        data-preview-target="awpt-preview-bg"
                    />
                </div>
                <div class="awpt-field">
                    <label for="awpt-fg"><?php echo esc_html__( 'Couleur du texte', 'accessible-wp-toolkit' ); ?></label>
                    <input
                        type="text"
                        id="awpt-fg"
                        name="fg"
                        value="<?php echo esc_attr( awpt_state_value( $awpt_state, 'contrast', 'fg', '#000000' ) ); ?>"
                        class="regular-text awpt-color-input"
                        data-preview-target="awpt-preview-fg"
                    />
                </div>

                <div class="awpt-contrast-preview" aria-hidden="true">
                    <span class="awpt-swatch awpt-swatch--bg" id="awpt-preview-bg" style="background-color: <?php echo esc_attr( awpt_state_value( $awpt_state, 'contrast', 'bg', '#FFFFFF' ) ); ?>"></span>
                    <span class="awpt-swatch awpt-swatch--fg" id="awpt-preview-fg" style="color: <?php echo esc_attr( awpt_state_value( $awpt_state, 'contrast', 'fg', '#000000' ) ); ?>; background-color: <?php echo esc_attr( awpt_state_value( $awpt_state, 'contrast', 'bg', '#FFFFFF' ) ); ?>">Aa</span>
                </div>

                <p>
                    <button type="submit" class="button button-primary">
                        <?php echo esc_html__( 'Calculer le contraste', 'accessible-wp-toolkit' ); ?>
                    </button>
                </p>
            </form>

            <?php $contrast_result = $awpt_state['results']['contrast']; ?>
            <?php if ( ! empty( $contrast_result ) ) : ?>
                <div class="awpt-results" aria-live="polite">
                    <p class="awpt-results__message"><?php echo esc_html( $contrast_result['message'] ); ?></p>
                    <?php if ( isset( $contrast_result['ratio'] ) ) : ?>
                        <p class="awpt-results__ratio">
                            <strong><?php echo esc_html__( 'Ratio', 'accessible-wp-toolkit' ); ?>:</strong>
                            <?php echo esc_html( number_format_i18n( (float) $contrast_result['ratio'], 2 ) . ':1' ); ?>
                        </p>
                    <?php endif; ?>
                    <?php if ( ! empty( $contrast_result['levels'] ) ) : ?>
                        <?php
                        $labels = array(
                            'normal' => __( 'Texte normal', 'accessible-wp-toolkit' ),
                            'large'  => __( 'Texte large (≥ 18px ou 14px bold)', 'accessible-wp-toolkit' ),
                            'ui'     => __( 'Interface utilisateur et graphismes', 'accessible-wp-toolkit' ),
                        );
                        ?>
                        <ul class="awpt-list awpt-list--status">
                            <?php foreach ( $contrast_result['levels'] as $context => $grades ) : ?>
                                <?php foreach ( $grades as $level => $passes ) : ?>
                                    <li class="<?php echo $passes ? 'is-pass' : 'is-fail'; ?>">
                                        <span class="awpt-list__label"><?php echo esc_html( sprintf( '%s – %s', $labels[ $context ] ?? ucfirst( $context ), $level ) ); ?></span>
                                        <span class="awpt-list__status" aria-hidden="true"><?php echo $passes ? '✓' : '✕'; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="awpt-card" aria-labelledby="awpt-keyboard-title">
            <h2 id="awpt-keyboard-title"><?php echo esc_html__( 'Audit navigation clavier', 'accessible-wp-toolkit' ); ?></h2>
            <p><?php echo esc_html__( 'Collez un extrait HTML pour repérer les pièges courants : tabindex, liens muets, nom accessible…', 'accessible-wp-toolkit' ); ?></p>

            <form method="post" class="awpt-form">
                <?php wp_nonce_field( 'awpt_keyboard', 'awpt_nonce' ); ?>
                <input type="hidden" name="awpt_action" value="keyboard" />
                <label for="awpt-keyboard-html" class="screen-reader-text"><?php echo esc_html__( 'Extrait HTML à analyser', 'accessible-wp-toolkit' ); ?></label>
                <textarea
                    id="awpt-keyboard-html"
                    name="keyboard_html"
                    rows="6"
                    class="large-text code"
                    placeholder="&lt;button&gt;...&lt;/button&gt;"
                ><?php echo esc_textarea( awpt_state_value( $awpt_state, 'keyboard', 'html' ) ); ?></textarea>
                <p>
                    <button type="submit" class="button button-primary">
                        <?php echo esc_html__( 'Analyser l’extrait', 'accessible-wp-toolkit' ); ?>
                    </button>
                </p>
            </form>

            <?php $keyboard_result = $awpt_state['results']['keyboard']; ?>
            <?php if ( ! empty( $keyboard_result ) ) : ?>
                <div class="awpt-results" aria-live="polite">
                    <p class="awpt-results__message"><?php echo esc_html( $keyboard_result['message'] ); ?></p>
                    <?php if ( ! empty( $keyboard_result['findings'] ) ) : ?>
                        <ul class="awpt-list">
                            <?php foreach ( $keyboard_result['findings'] as $finding ) : ?>
                                <li><?php echo esc_html( $finding ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="awpt-card" aria-labelledby="awpt-landmarks-title">
            <h2 id="awpt-landmarks-title"><?php echo esc_html__( 'Repères ARIA', 'accessible-wp-toolkit' ); ?></h2>
            <p><?php echo esc_html__( 'Vérifiez la présence des repères principaux (header/banner, nav, main, aside, footer…).', 'accessible-wp-toolkit' ); ?></p>

            <form method="post" class="awpt-form">
                <?php wp_nonce_field( 'awpt_landmarks', 'awpt_nonce' ); ?>
                <input type="hidden" name="awpt_action" value="landmarks" />
                <label for="awpt-landmarks-html" class="screen-reader-text"><?php echo esc_html__( 'Structure HTML à analyser', 'accessible-wp-toolkit' ); ?></label>
                <textarea
                    id="awpt-landmarks-html"
                    name="landmarks_html"
                    rows="6"
                    class="large-text code"
                    placeholder="&lt;main role=&quot;main&quot;&gt;...&lt;/main&gt;"
                ><?php echo esc_textarea( awpt_state_value( $awpt_state, 'landmarks', 'html' ) ); ?></textarea>
                <p>
                    <button type="submit" class="button button-primary">
                        <?php echo esc_html__( 'Vérifier les repères', 'accessible-wp-toolkit' ); ?>
                    </button>
                </p>
            </form>

            <?php $landmarks_result = $awpt_state['results']['landmarks']; ?>
            <?php if ( ! empty( $landmarks_result ) ) : ?>
                <div class="awpt-results" aria-live="polite">
                    <p class="awpt-results__message"><?php echo esc_html( $landmarks_result['message'] ); ?></p>
                    <div class="awpt-landmarks-grid">
                        <div>
                            <h3><?php echo esc_html__( 'Présents', 'accessible-wp-toolkit' ); ?></h3>
                            <?php if ( empty( $landmarks_result['present'] ) ) : ?>
                                <p><?php echo esc_html__( 'Aucun repère détecté pour le moment.', 'accessible-wp-toolkit' ); ?></p>
                            <?php else : ?>
                                <ul class="awpt-list">
                                    <?php foreach ( $landmarks_result['present'] as $landmark ) : ?>
                                        <li><?php echo esc_html( ucfirst( $landmark ) ); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h3><?php echo esc_html__( 'À ajouter', 'accessible-wp-toolkit' ); ?></h3>
                            <?php if ( empty( $landmarks_result['missing'] ) ) : ?>
                                <p><?php echo esc_html__( 'Tous les repères principaux sont présents 🎉', 'accessible-wp-toolkit' ); ?></p>
                            <?php else : ?>
                                <ul class="awpt-list">
                                    <?php foreach ( $landmarks_result['missing'] as $landmark ) : ?>
                                        <li><?php echo esc_html( ucfirst( $landmark ) ); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <section class="awpt-card" aria-labelledby="awpt-media-title">
            <h2 id="awpt-media-title"><?php echo esc_html__( 'Sous-titres & médias', 'accessible-wp-toolkit' ); ?></h2>
            <p><?php echo esc_html__( 'Détectez les vidéos sans piste de sous-titres et les audios sans transcription.', 'accessible-wp-toolkit' ); ?></p>

            <form method="post" class="awpt-form">
                <?php wp_nonce_field( 'awpt_media', 'awpt_nonce' ); ?>
                <input type="hidden" name="awpt_action" value="media" />
                <label for="awpt-media-html" class="screen-reader-text"><?php echo esc_html__( 'Extrait HTML des médias', 'accessible-wp-toolkit' ); ?></label>
                <textarea
                    id="awpt-media-html"
                    name="media_html"
                    rows="6"
                    class="large-text code"
                    placeholder="&lt;video&gt;&lt;source src=\"...\"&gt;&lt;track kind=\"captions\" ...&gt;&lt;/video&gt;"
                ><?php echo esc_textarea( awpt_state_value( $awpt_state, 'media', 'html' ) ); ?></textarea>
                <p>
                    <button type="submit" class="button button-primary">
                        <?php echo esc_html__( 'Analyser les médias', 'accessible-wp-toolkit' ); ?>
                    </button>
                </p>
            </form>

            <?php $media_result = $awpt_state['results']['media']; ?>
            <?php if ( ! empty( $media_result ) ) : ?>
                <div class="awpt-results" aria-live="polite">
                    <p class="awpt-results__message"><?php echo esc_html( $media_result['message'] ); ?></p>
                    <?php if ( ! empty( $media_result['findings'] ) ) : ?>
                        <ul class="awpt-list">
                            <?php foreach ( $media_result['findings'] as $finding ) : ?>
                                <li><?php echo esc_html( $finding ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
