<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enregistre un buzz pour le mode 1.
 */
function inskill_buzzer_record_buzz() {
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    if ( empty( $participant_name ) ) {
        wp_send_json_error( array( 'message' => 'Nom de participant manquant' ) );
    }
    $time = microtime(true);
    $buzzes = get_transient( 'inskill_buzzer_mode1_buzz' );
    if ( ! is_array( $buzzes ) ) {
        $buzzes = array();
    }
    // Ajouter l'événement de buzz
    $buzzes[] = array(
        'name' => $participant_name,
        'time' => $time
    );
    // Conserver pendant 10 minutes
    set_transient( 'inskill_buzzer_mode1_buzz', $buzzes, 10 * MINUTE_IN_SECONDS );
    wp_send_json_success( array( 'message' => 'Buzz enregistré' ) );
}
add_action( 'wp_ajax_record_buzz', 'inskill_buzzer_record_buzz' );
add_action( 'wp_ajax_nopriv_record_buzz', 'inskill_buzzer_record_buzz' );

/**
 * Récupère les 3 premiers buzz (les plus rapides).
 */
function inskill_buzzer_get_top3_buzz() {
    $buzzes = get_transient( 'inskill_buzzer_mode1_buzz' );
    if ( ! is_array( $buzzes ) ) {
        $buzzes = array();
    }
    // Trier par temps d'enregistrement (le plus rapide en premier)
    usort( $buzzes, function( $a, $b ) {
        return $a['time'] - $b['time'];
    } );
    $top3 = array_slice( $buzzes, 0, 3 );
    wp_send_json_success( $top3 );
}
add_action( 'wp_ajax_get_top3_buzz', 'inskill_buzzer_get_top3_buzz' );
add_action( 'wp_ajax_nopriv_get_top3_buzz', 'inskill_buzzer_get_top3_buzz' );

/**
 * Réinitialise les buzz.
 */
function inskill_buzzer_reset_buzz() {
    delete_transient( 'inskill_buzzer_mode1_buzz' );
    wp_send_json_success( array( 'message' => 'Buzz réinitialisés' ) );
}
add_action( 'wp_ajax_reset_buzz', 'inskill_buzzer_reset_buzz' );
add_action( 'wp_ajax_nopriv_reset_buzz', 'inskill_buzzer_reset_buzz' );
