<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class AWPT_Accessibility_API {
    /**
     * Calcule le contraste entre deux couleurs selon la formule WCAG 2.1.
     */
    public static function check_contrast( string $hex_bg, string $hex_fg ) : array {
        try {
            $bg = self::normalise_hex_color( $hex_bg );
            $fg = self::normalise_hex_color( $hex_fg );
        } catch ( InvalidArgumentException $exception ) {
            return array(
                'ratio'   => null,
                'levels'  => array(),
                'message' => $exception->getMessage(),
            );
        }

        $bg_luminance = self::relative_luminance( $bg );
        $fg_luminance = self::relative_luminance( $fg );

        $lighter = max( $bg_luminance, $fg_luminance );
        $darker  = min( $bg_luminance, $fg_luminance );
        $ratio   = round( ( $lighter + 0.05 ) / ( $darker + 0.05 ), 2 );

        $levels = array(
            'normal' => array(
                'AA'  => $ratio >= 4.5,
                'AAA' => $ratio >= 7.0,
            ),
            'large'  => array(
                'AA'  => $ratio >= 3.0,
                'AAA' => $ratio >= 4.5,
            ),
            'ui'     => array(
                'AA' => $ratio >= 3.0,
            ),
        );

        /* translators: %s: contraste calculé (ex: 4.75:1). */
        $message = sprintf(
            __( 'Contraste calculé : %s.', 'accessible-wp-toolkit' ),
            number_format_i18n( $ratio, 2 ) . ':1'
        );

        return array(
            'ratio'   => $ratio,
            'levels'  => $levels,
            'message' => $levels['normal']['AA']
                ? sprintf( __( '✅ Lisible pour le texte normal. %s', 'accessible-wp-toolkit' ), $message )
                : sprintf( __( '⚠️ Contraste insuffisant pour du texte normal. %s', 'accessible-wp-toolkit' ), $message ),
        );
    }

    /**
     * Analyse rapide de la navigation clavier.
     */
    public static function keyboard_audit( string $html ) : array {
        $dom = self::create_dom_from_html( $html );

        if ( ! $dom ) {
            return array(
                'findings' => array(),
                'message'  => __( 'Impossible d’analyser le code fourni.', 'accessible-wp-toolkit' ),
            );
        }

        $xpath      = new DOMXPath( $dom );
        $findings   = array();
        $labels_map = self::map_labelled_controls( $dom );

        $focusable_query = '//*[self::a or self::button or self::input or self::select or self::textarea or @tabindex]';
        $nodes            = $xpath->query( $focusable_query );

        if ( $nodes instanceof DOMNodeList ) {
            foreach ( $nodes as $node ) {
                $tag_name = strtolower( $node->nodeName );

                // Skip hidden inputs.
                if ( 'input' === $tag_name ) {
                    $type = strtolower( $node->getAttribute( 'type' ) );
                    if ( in_array( $type, array( 'hidden', 'submit', 'button', 'image', 'reset' ), true ) ) {
                        continue;
                    }
                }

                if ( 'a' === $tag_name ) {
                    $href = trim( $node->getAttribute( 'href' ) );
                    if ( '' === $href || '#' === $href ) {
                        $findings[] = __( 'Lien focusable sans destination. Ajoutez un attribut href valide ou transformez-le en bouton.', 'accessible-wp-toolkit' );
                    }
                }

                if ( $node->hasAttribute( 'tabindex' ) ) {
                    $tabindex = (int) $node->getAttribute( 'tabindex' );
                    if ( $tabindex > 0 ) {
                        $findings[] = __( 'Un tabindex positif a été détecté. Préférez l’ordre naturel du DOM ou tabindex="0".', 'accessible-wp-toolkit' );
                    }

                    $semantics = in_array( $tag_name, array( 'a', 'button', 'input', 'select', 'textarea' ), true );
                    if ( ! $semantics ) {
                        $findings[] = __( 'Un élément non interactif utilise tabindex. Vérifiez qu’un rôle ARIA adapté est présent.', 'accessible-wp-toolkit' );
                    }
                }

                if ( ! self::has_accessible_name( $node, $labels_map ) ) {
                    /* translators: %s: nom de la balise HTML (ex: button). */
                    $findings[] = sprintf(
                        __( 'L’élément <%s> semble dépourvu de nom accessible.', 'accessible-wp-toolkit' ),
                        $tag_name
                    );
                }
            }
        }

        return array(
            'findings' => $findings,
            'message'  => $findings
                ? __( 'Vérifiez les points relevés ci-dessous.', 'accessible-wp-toolkit' )
                : __( '✅ Aucun problème clavier détecté dans cet extrait.', 'accessible-wp-toolkit' ),
        );
    }

    /**
     * Vérifie la présence des repères ARIA/WAI.
     */
    public static function analyze_landmarks( string $html ) : array {
        $dom = self::create_dom_from_html( $html );

        if ( ! $dom ) {
            return array(
                'present' => array(),
                'missing' => array(),
                'message' => __( 'Impossible d’analyser le code fourni.', 'accessible-wp-toolkit' ),
            );
        }

        $landmarks = array(
            'banner'     => array( 'header', '[role="banner"]' ),
            'navigation' => array( 'nav', '[role="navigation"]' ),
            'main'       => array( 'main', '[role="main"]' ),
            'complementary' => array( 'aside', '[role="complementary"]' ),
            'contentinfo'   => array( 'footer', '[role="contentinfo"]' ),
        );

        $xpath   = new DOMXPath( $dom );
        $present = array();

        foreach ( $landmarks as $name => $selectors ) {
            $query = array();
            foreach ( $selectors as $selector ) {
                if ( '[' === $selector[0] ) {
                    $query[] = '//*' . $selector;
                } else {
                    $query[] = '//' . $selector;
                }
            }

            $expression = implode( ' | ', $query );
            $nodes      = $xpath->query( $expression );

            if ( $nodes instanceof DOMNodeList && $nodes->length > 0 ) {
                $present[] = $name;
            }
        }

        $missing = array_diff( array_keys( $landmarks ), $present );

        return array(
            'present' => $present,
            'missing' => array_values( $missing ),
            'message' => $missing
                ? __( '⚠️ Certains repères manquent.', 'accessible-wp-toolkit' )
                : __( '✅ Tous les repères essentiels sont présents.', 'accessible-wp-toolkit' ),
        );
    }

    /**
     * Vérifie la présence de sous-titres ou alternatives pour les médias.
     */
    public static function analyze_media_alternatives( string $html ) : array {
        $dom = self::create_dom_from_html( $html );

        if ( ! $dom ) {
            return array(
                'findings' => array(),
                'message'  => __( 'Impossible d’analyser le code fourni.', 'accessible-wp-toolkit' ),
            );
        }

        $xpath    = new DOMXPath( $dom );
        $findings = array();

        $videos = $xpath->query( '//video' );
        if ( $videos instanceof DOMNodeList ) {
            foreach ( $videos as $video ) {
                $tracks = 0;
                foreach ( $video->childNodes as $child ) {
                    if ( $child instanceof DOMElement && 'track' === strtolower( $child->tagName ) ) {
                        $kind = strtolower( $child->getAttribute( 'kind' ) );
                        if ( in_array( $kind, array( 'captions', 'subtitles' ), true ) ) {
                            $tracks++;
                        }
                    }
                }

                if ( 0 === $tracks ) {
                    $findings[] = __( 'Une balise <video> ne contient pas de piste de sous-titres (`<track kind="captions">`).', 'accessible-wp-toolkit' );
                }
            }
        }

        $audios = $xpath->query( '//audio' );
        if ( $audios instanceof DOMNodeList ) {
            foreach ( $audios as $audio ) {
                $has_description = $audio->hasAttribute( 'aria-describedby' ) || $audio->hasAttribute( 'aria-label' );
                $has_transcript  = false;

                foreach ( $audio->childNodes as $child ) {
                    if ( $child instanceof DOMElement && 'track' === strtolower( $child->tagName ) ) {
                        $kind = strtolower( $child->getAttribute( 'kind' ) );
                        if ( in_array( $kind, array( 'descriptions', 'captions' ), true ) ) {
                            $has_transcript = true;
                        }
                    }
                }

                if ( ! $has_transcript && ! $has_description ) {
                    $findings[] = __( 'Une balise <audio> ne propose pas de transcription ou description accessible.', 'accessible-wp-toolkit' );
                }
            }
        }

        return array(
            'findings' => $findings,
            'message'  => $findings
                ? __( '⚠️ Ajoutez des alternatives pour les médias listés ci-dessous.', 'accessible-wp-toolkit' )
                : __( '✅ Tous les médias analysés possèdent une alternative.', 'accessible-wp-toolkit' ),
        );
    }

    private static function normalise_hex_color( string $color ) : string {
        $color = trim( $color );

        if ( '' === $color ) {
            throw new InvalidArgumentException( __( 'Merci de fournir une couleur hexadécimale valide.', 'accessible-wp-toolkit' ) );
        }

        if ( '#' === $color[0] ) {
            $color = substr( $color, 1 );
        }

        if ( 3 === strlen( $color ) ) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }

        $color = strtoupper( $color );

        if ( ! preg_match( '/^[0-9A-F]{6}$/', $color ) ) {
            throw new InvalidArgumentException( __( 'Format de couleur incorrect. Utilisez des valeurs hexadécimales (ex: #1A1A1A).', 'accessible-wp-toolkit' ) );
        }

        return $color;
    }

    private static function relative_luminance( string $color ) : float {
        $r = hexdec( substr( $color, 0, 2 ) ) / 255;
        $g = hexdec( substr( $color, 2, 2 ) ) / 255;
        $b = hexdec( substr( $color, 4, 2 ) ) / 255;

        $channels = array( $r, $g, $b );
        foreach ( $channels as &$channel ) {
            $channel = ( $channel <= 0.03928 )
                ? $channel / 12.92
                : pow( ( $channel + 0.055 ) / 1.055, 2.4 );
        }

        list( $r_lin, $g_lin, $b_lin ) = $channels;

        return ( 0.2126 * $r_lin ) + ( 0.7152 * $g_lin ) + ( 0.0722 * $b_lin );
    }

    private static function create_dom_from_html( string $html ) : ?DOMDocument {
        $html = trim( $html );
        if ( '' === $html ) {
            return null;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors( true );
        $loaded = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        libxml_clear_errors();

        if ( ! $loaded ) {
            return null;
        }

        return $dom;
    }

    private static function has_accessible_name( DOMElement $node, array $labels_map ) : bool {
        $tag_name = strtolower( $node->tagName );

        $text = trim( preg_replace( '/\s+/u', ' ', $node->textContent ) );
        if ( '' !== $text ) {
            return true;
        }

        if ( $node->hasAttribute( 'aria-label' ) && '' !== trim( $node->getAttribute( 'aria-label' ) ) ) {
            return true;
        }

        if ( $node->hasAttribute( 'aria-labelledby' ) ) {
            $ids = preg_split( '/\s+/', trim( $node->getAttribute( 'aria-labelledby' ) ) );
            if ( $ids ) {
                foreach ( $ids as $id ) {
                    if ( isset( $labels_map['ids'][ $id ] ) ) {
                        return true;
                    }
                }
            }
        }

        if ( $node->hasAttribute( 'id' ) ) {
            $id = $node->getAttribute( 'id' );
            if ( isset( $labels_map['for'][ $id ] ) ) {
                return true;
            }
        }

        // Placeholder text inputs with aria-describedby/placeholder are acceptable.
        if ( 'input' === $tag_name && $node->hasAttribute( 'placeholder' ) ) {
            return true;
        }

        return false;
    }

    private static function map_labelled_controls( DOMDocument $dom ) : array {
        $map = array(
            'for' => array(),
            'ids' => array(),
        );

        foreach ( $dom->getElementsByTagName( 'label' ) as $label ) {
            if ( ! $label instanceof DOMElement ) {
                continue;
            }

            $text = trim( preg_replace( '/\s+/u', ' ', $label->textContent ) );
            if ( '' === $text ) {
                continue;
            }

            if ( $label->hasAttribute( 'for' ) ) {
                $map['for'][ $label->getAttribute( 'for' ) ] = $text;
            }

            if ( $label->hasAttribute( 'id' ) ) {
                $map['ids'][ $label->getAttribute( 'id' ) ] = $text;
            }
        }

        return $map;
    }
}
