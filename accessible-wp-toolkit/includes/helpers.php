<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function awpt_capability() : string {
    return 'manage_options';
}

function awpt_admin_state() : array {
    $state = array(
        'submitted_action' => null,
        'errors'           => array(),
        'results'          => array(
            'contrast'  => null,
            'keyboard'  => null,
            'landmarks' => null,
            'media'     => null,
        ),
        'inputs'           => array(
            'contrast'  => array(
                'bg' => '#FFFFFF',
                'fg' => '#000000',
            ),
            'keyboard'  => array( 'html' => '' ),
            'landmarks' => array( 'html' => '' ),
            'media'     => array( 'html' => '' ),
        ),
    );

    if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || empty( $_POST['awpt_action'] ) ) {
        return $state;
    }

    $action = sanitize_key( wp_unslash( $_POST['awpt_action'] ) );
    $state['submitted_action'] = $action;

    $nonce = isset( $_POST['awpt_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['awpt_nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'awpt_' . $action ) ) {
        $state['errors'][] = __( 'La vérification de sécurité a échoué. Merci de réessayer.', 'accessible-wp-toolkit' );
        return $state;
    }

    switch ( $action ) {
        case 'contrast':
            $bg = isset( $_POST['bg'] ) ? awpt_normalize_user_hex( wp_unslash( $_POST['bg'] ) ) : '';
            $fg = isset( $_POST['fg'] ) ? awpt_normalize_user_hex( wp_unslash( $_POST['fg'] ) ) : '';

            $state['inputs']['contrast'] = array(
                'bg' => $bg ?: '#FFFFFF',
                'fg' => $fg ?: '#000000',
            );

            $state['results']['contrast'] = AWPT_Accessibility_API::check_contrast( $bg, $fg );
            break;

        case 'keyboard':
            $html = isset( $_POST['keyboard_html'] ) ? wp_kses_post( wp_unslash( $_POST['keyboard_html'] ) ) : '';
            $state['inputs']['keyboard'] = array( 'html' => $html );
            $state['results']['keyboard'] = AWPT_Accessibility_API::keyboard_audit( $html );
            break;

        case 'landmarks':
            $html = isset( $_POST['landmarks_html'] ) ? wp_kses_post( wp_unslash( $_POST['landmarks_html'] ) ) : '';
            $state['inputs']['landmarks'] = array( 'html' => $html );
            $state['results']['landmarks'] = AWPT_Accessibility_API::analyze_landmarks( $html );
            break;

        case 'media':
            $html = isset( $_POST['media_html'] ) ? wp_kses_post( wp_unslash( $_POST['media_html'] ) ) : '';
            $state['inputs']['media'] = array( 'html' => $html );
            $state['results']['media'] = AWPT_Accessibility_API::analyze_media_alternatives( $html );
            break;

        default:
            $state['errors'][] = __( 'Action inconnue.', 'accessible-wp-toolkit' );
            break;
    }

    return $state;
}

function awpt_normalize_user_hex( string $hex ) : string {
    $hex = trim( $hex );
    if ( '' === $hex ) {
        return '';
    }

    if ( '#' !== $hex[0] ) {
        $hex = '#' . $hex;
    }

    return strtoupper( $hex );
}

function awpt_state_value( array $state, string $tool, string $field, string $default = '' ) : string {
    if ( isset( $state['inputs'][ $tool ][ $field ] ) && '' !== $state['inputs'][ $tool ][ $field ] ) {
        return $state['inputs'][ $tool ][ $field ];
    }

    return $default;
}
