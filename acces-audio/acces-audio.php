<?php
/**
 * Plugin Name: Accès Audio — Accessibilité Malentendants
 * Description: Outils d’accessibilité axés sur les personnes malentendantes : sous-titres, transcripts, alertes visuelles, et intégration d’une vidéo en LSF. Conçu pour fonctionner en local (LocalWP) comme en prod.
 * Version: 1.0.0
 * Author: Vous + ChatGPT
 * License: GPL-2.0-or-later
 * Text Domain: acces-audio
 */

if (!defined('ABSPATH')) { exit; }

class Acces_Audio_Plugin {
    const OPTION = 'acces_audio_options';

    public function __construct() {
        // Options par défaut
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'add_settings_page']);

        // Front
        add_action('wp_enqueue_scripts', [$this, 'enqueue_front']);
        add_shortcode('transcript', [$this, 'shortcode_transcript']);
        add_shortcode('sign_language', [$this, 'shortcode_sign_language']);

        // Alerte si une vidéo sans sous-titres est détectée en édition
        add_action('admin_notices', [$this, 'admin_video_caption_notice']);

        // Ajout attributs ARIA de base sur <video>
        add_filter('the_content', [$this, 'filter_content_enhance_video'], 20);
    }

    public static function defaults() {
        return [
            'force_cc_button'   => 1,
            'auto_attach_vtt'   => 1,
            'visual_alert'      => 1,
            'transcript_toggle' => 1,
            'lsf_enabled'       => 1,
        ];
    }

    public function get_options() {
        $opts = get_option(self::OPTION, []);
        return wp_parse_args($opts, self::defaults());
    }

    /* --------------------------------------------------
     * Réglages admin
     * -------------------------------------------------- */
    public function register_settings() {
        register_setting('acces_audio_group', self::OPTION, [
            'type' => 'array',
            'sanitize_callback' => function($input){
                $out = self::defaults();
                foreach ($out as $k => $v) {
                    $out[$k] = isset($input[$k]) ? 1 : 0;
                }
                return $out;
            },
            'default' => self::defaults(),
        ]);

        add_settings_section('acces_audio_main', __('Comportement frontal', 'acces-audio'), function(){
            echo '<p>' . esc_html__('Activez/désactivez les fonctions visibles par les visiteurs.', 'acces-audio') . '</p>';
        }, 'acces_audio');

        $fields = [
            'force_cc_button' => __('Bouton CC sur toutes les vidéos', 'acces-audio'),
            'auto_attach_vtt' => __('Joindre automatiquement un .vtt du même nom', 'acces-audio'),
            'visual_alert' => __('Alerte visuelle quand un média joue (sans son)', 'acces-audio'),
            'transcript_toggle' => __('Panneau Transcript repliable', 'acces-audio'),
            'lsf_enabled' => __('Encart vidéo LSF (Langue des Signes)', 'acces-audio'),
        ];

        foreach ($fields as $key => $label) {
            add_settings_field($key, $label, function() use ($key) {
                $opts = $this->get_options();
                printf('<label><input type="checkbox" name="%s[%s]" value="1" %s/> %s</label>',
                    esc_attr(self::OPTION),
                    esc_attr($key),
                    checked(1, (int)$opts[$key], false),
                    esc_html__('Activer', 'acces-audio')
                );
            }, 'acces_audio', 'acces_audio_main');
        }
    }

    public function add_settings_page() {
        add_options_page(
            __('Accès Audio', 'acces-audio'),
            __('Accès Audio', 'acces-audio'),
            'manage_options',
            'acces_audio',
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>🎧 Accès Audio — Accessibilité Malentendants</h1>
            <form method="post" action="options.php">
                <?php settings_fields('acces_audio_group'); ?>
                <?php do_settings_sections('acces_audio'); ?>
                <?php submit_button(); ?>
            </form>
            <hr/>
            <h2>Shortcodes</h2>
            <ul>
                <li><code>[transcript title="Sous-titres"]…texte…[/transcript]</code></li>
                <li><code>[sign_language video="URL-de-la-video-LSF.mp4" for="#id-video"]</code></li>
            </ul>
            <p><em>Astuce :</em> Si <code>auto_attach_vtt</code> est activé, un fichier <code>.vtt</code> portant le même nom que la source vidéo sera associé automatiquement (ex : <code>film.mp4</code> → <code>film.vtt</code> dans la même URL).</p>
        </div>
        <?php
    }

    /* --------------------------------------------------
     * Front
     * -------------------------------------------------- */
    public function enqueue_front() {
        $opts = $this->get_options();
        $data = [
            'forceCC' => (bool)$opts['force_cc_button'],
            'autoVTT' => (bool)$opts['auto_attach_vtt'],
            'visualAlert' => (bool)$opts['visual_alert'],
            'transcriptToggle' => (bool)$opts['transcript_toggle'],
            'lsfEnabled' => (bool)$opts['lsf_enabled'],
        ];

        // CSS minimaliste
        wp_register_style('acces-audio-css', false);
        wp_enqueue_style('acces-audio-css');
        $css = '/* Styles Accès Audio */
        .acces-audio-cc-btn{position:absolute;right:.5rem;bottom:.5rem;padding:.375rem .5rem;border-radius:.5rem;background:#000a;color:#fff;font-size:.875rem;backdrop-filter:blur(6px);}
        .acces-audio-cc-btn:focus{outline:3px solid #99f;outline-offset:2px}
        .acces-audio-wrapper{position:relative;display:inline-block}
        .acces-audio-visual-ring{position:absolute;inset:-6px;border:3px solid transparent;border-radius:12px;pointer-events:none;transition:border-color .2s, box-shadow .2s}
        .acces-audio-visual-ring.is-playing{border-color:#ffd166;box-shadow:0 0 0 6px rgba(255,209,102,.25)}
        .acces-audio-transcript{margin:.5rem 0;padding:.75rem;border:1px dashed #aaa;border-radius:.5rem}
        .acces-audio-transcript[hidden]{display:none}
        .acces-audio-transcript summary{cursor:pointer;font-weight:600}
        .acces-audio-lsf{margin-top:.5rem}
        .acces-audio-lsf video{max-width:260px;border-radius:.5rem}
        ';
        wp_add_inline_style('acces-audio-css', $css);

        // JS
        wp_register_script('acces-audio-js', false, [], false, true);
        wp_enqueue_script('acces-audio-js');
        $js = '(function(){
            const settings = ' . wp_json_encode($data) . ';

            function ensureWrapper(video){
                if(!video.closest(".acces-audio-wrapper")){
                    const wrap = document.createElement("span");
                    wrap.className = "acces-audio-wrapper";
                    video.parentNode.insertBefore(wrap, video);
                    wrap.appendChild(video);
                }
                return video.closest(".acces-audio-wrapper");
            }

            function addCCButton(video){
                const wrap = ensureWrapper(video);
                if(wrap.querySelector(".acces-audio-cc-btn")) return;
                const btn = document.createElement("button");
                btn.type = "button";
                btn.className = "acces-audio-cc-btn";
                btn.setAttribute("aria-label", "Activer/désactiver les sous-titres");
                btn.textContent = "CC";
                btn.addEventListener("click", ()=>{
                    const tracks = Array.from(video.querySelectorAll("track[kind=\'captions\']"));
                    if(!tracks.length){
                        alert("Aucune piste de sous-titres n\'est disponible pour cette vidéo.");
                        return;
                    }
                    tracks.forEach(t=>{ t.track.mode = (t.track.mode === "showing") ? "disabled" : "showing"; });
                });
                wrap.appendChild(btn);
            }

            function autoAttachVTT(video){
                if(video.querySelector("track[kind=\'captions\']")) return; // déjà présent
                const src = video.getAttribute("src") || (video.querySelector("source") && video.querySelector("source").getAttribute("src"));
                if(!src) return;
                try{
                    const url = new URL(src, window.location.href);
                    const vtt = url.pathname.replace(/\.[^.]+$/, ".vtt");
                    const track = document.createElement("track");
                    track.kind = "captions"; track.srclang = document.documentElement.lang || "fr"; track.label = "Sous-titres"; track.src = new URL(vtt, url.origin).toString();
                    // Laisse le navigateur tenter de charger; si 404 ce n\'est pas bloquant
                    video.appendChild(track);
                } catch(e){}
            }

            function addVisualRing(video){
                const wrap = ensureWrapper(video);
                let ring = wrap.querySelector(".acces-audio-visual-ring");
                if(!ring){
                    ring = document.createElement("span");
                    ring.className = "acces-audio-visual-ring";
                    ring.setAttribute("aria-hidden","true");
                    wrap.appendChild(ring);
                }
                const onPlay = ()=> ring.classList.add("is-playing");
                const onPause = ()=> ring.classList.remove("is-playing");
                video.addEventListener("playing", onPlay);
                video.addEventListener("pause", onPause);
                video.addEventListener("ended", onPause);
            }

            function enhanceVideos(){
                const videos = document.querySelectorAll("video");
                videos.forEach(v=>{
                    v.setAttribute("aria-label", v.getAttribute("aria-label") || "Lecteur vidéo");
                    v.setAttribute("role", "application");
                    if(settings.autoVTT) autoAttachVTT(v);
                    if(settings.forceCC) addCCButton(v);
                    if(settings.visualAlert) addVisualRing(v);
                });
            }

            // LSF widget (sign_language shortcode)
            function initLSF(){
                if(!settings.lsfEnabled) return;
                document.querySelectorAll('[data-acces-audio-lsf-for]').forEach(box=>{
                    const target = document.querySelector(box.getAttribute('data-acces-audio-lsf-for'));
                    if(!target) return;
                    // Option : bouton PiP côte à côte
                    const pipBtn = document.createElement('button');
                    pipBtn.type='button';
                    pipBtn.textContent='LSF PiP';
                    pipBtn.setAttribute('aria-label','Afficher la vidéo LSF');
                    pipBtn.className='acces-audio-cc-btn';
                    pipBtn.addEventListener('click',()=>{
                        box.hidden = !box.hidden;
                    });
                    ensureWrapper(target).appendChild(pipBtn);
                });
            }

            // Transcript toggle (utilise <details>)
            function initTranscripts(){
                if(!settings.transcriptToggle) return;
                document.querySelectorAll('.acces-audio-transcript').forEach(panel=>{
                    // rien à faire : <details> gère l\'ouverture/fermeture et l\'accessibilité clavier
                });
            }

            document.addEventListener("DOMContentLoaded", function(){
                enhanceVideos();
                initLSF();
                initTranscripts();
            });
        })();';
        wp_add_inline_script('acces-audio-js', $js);
    }

    /* --------------------------------------------------
     * Shortcodes
     * -------------------------------------------------- */
    public function shortcode_transcript($atts, $content = ''){
        $a = shortcode_atts([
            'title' => __('Transcript / Sous-titres (texte)', 'acces-audio'),
        ], $atts, 'transcript');
        $title = esc_html($a['title']);
        $content = wp_kses_post($content);
        return "<details class=\"acces-audio-transcript\"><summary>{$title}</summary><div role=\"region\" aria-label=\"Transcript\">{$content}</div></details>";
    }

    public function shortcode_sign_language($atts){
        $a = shortcode_atts([
            'video' => '',
            'for'   => '', // sélecteur CSS, ex : #video-id
            'label' => __('Interprétation LSF', 'acces-audio'),
        ], $atts, 'sign_language');
        if(empty($a['video']) || empty($a['for'])) return '';
        $video = esc_url($a['video']);
        $for   = esc_attr($a['for']);
        $label = esc_html($a['label']);
        $html = '<div class="acces-audio-lsf" data-acces-audio-lsf-for="'. $for .'" hidden>';
        $html .= '<p style="margin:.25rem 0 .5rem;font-weight:600">'. $label .'</p>';
        $html .= '<video controls preload="metadata"><source src="'. $video .'" type="video/mp4"/></video>';
        $html .= '</div>';
        return $html;
    }

    /* --------------------------------------------------
     * Améliorations de contenu & admin notice
     * -------------------------------------------------- */
    public function filter_content_enhance_video($content){
        // Ajoute aria-describedby si un [transcript] suit immédiatement
        // (simple heuristique pour associer vidéo et transcript)
        $content = preg_replace_callback('#(<video[^>]*>.*?</video>)(\s*)<details class=\\"acces-audio-transcript\\"#si', function($m){
            $id = 'vid-' . wp_generate_uuid4();
            $video = $m[1];
            if(stripos($video, 'id=') === false){
                $video = preg_replace('/<video/i', '<video id="'. esc_attr($id) .'"', $video, 1);
            }
            return $video . $m[2] . '<details class="acces-audio-transcript" aria-labelledby="'. esc_attr($id) .'"';
        }, $content);
        return $content;
    }

    public function admin_video_caption_notice(){
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if(!$screen || $screen->base !== 'post') return;
        $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
        if(!$post_id) return;
        $post = get_post($post_id);
        if(!$post) return;
        // Détecte une vidéo sans <track kind="captions">
        $hasVideo = (bool) preg_match('#<video[\s\S]*?>[\s\S]*?</video>#i', $post->post_content);
        $hasTrack = (bool) preg_match('#<track[^>]+kind\s*=\s*([\"\']?)captions\1#i', $post->post_content);
        if($hasVideo && !$hasTrack){
            echo '<div class="notice notice-warning is-dismissible"><p>⚠️ <strong>Accessibilité :</strong> cette publication contient une <code>&lt;video&gt;</code> sans sous-titres. Ajoutez un fichier <code>.vtt</code> ou insérez un <code>&lt;track kind="captions"&gt;</code>. Le plugin peut essayer d\'attacher automatiquement un .vtt si l\'option est activée.</p></div>';
        }
    }
}

new Acces_Audio_Plugin();
