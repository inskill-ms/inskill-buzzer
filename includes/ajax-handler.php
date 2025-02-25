<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * --- MODE 1 ---
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
 * Récupère les 3 premiers buzz (les plus rapides) pour le mode 1.
 */
function inskill_buzzer_get_top3_buzz() {
    $buzzes = get_transient( 'inskill_buzzer_mode1_buzz' );
    if ( ! is_array( $buzzes ) ) {
        $buzzes = array();
    }
    usort( $buzzes, function( $a, $b ) {
        return $a['time'] - $b['time'];
    } );
    $top3 = array_slice( $buzzes, 0, 3 );
    wp_send_json_success( $top3 );
}
add_action( 'wp_ajax_get_top3_buzz', 'inskill_buzzer_get_top3_buzz' );
add_action( 'wp_ajax_nopriv_get_top3_buzz', 'inskill_buzzer_get_top3_buzz' );

/**
 * Réinitialise les buzz pour le mode 1.
 */
function inskill_buzzer_reset_buzz() {
    delete_transient( 'inskill_buzzer_mode1_buzz' );
    wp_send_json_success( array( 'message' => 'Buzz réinitialisés' ) );
}
add_action( 'wp_ajax_reset_buzz', 'inskill_buzzer_reset_buzz' );
add_action( 'wp_ajax_nopriv_reset_buzz', 'inskill_buzzer_reset_buzz' );

/* ----------------- MODE 2 ----------------- */
/**
 * Enregistre un buzz pour le mode 2.
 */
function inskill_buzzer_record_buzz_mode2() {
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    // Pour le mode2, on peut aussi recevoir le nom de l'équipe
    $participant_team = isset($_POST['participant_team']) ? sanitize_text_field($_POST['participant_team']) : '';
    if ( empty( $participant_name ) || empty( $participant_team ) ) {
        wp_send_json_error( array( 'message' => 'Données manquantes' ) );
    }
    $time = microtime(true);
    $buzzes = get_transient( 'inskill_buzzer_mode2_buzz' );
    if ( ! is_array( $buzzes ) ) {
        $buzzes = array();
    }
    // Ajouter l'événement de buzz avec équipe
    $buzzes[] = array(
        'name' => $participant_name,
        'team' => $participant_team,
        'time' => $time
    );
    set_transient( 'inskill_buzzer_mode2_buzz', $buzzes, 10 * MINUTE_IN_SECONDS );
    wp_send_json_success( array( 'message' => 'Buzz enregistré' ) );
}
add_action( 'wp_ajax_record_buzz_mode2', 'inskill_buzzer_record_buzz_mode2' );
add_action( 'wp_ajax_nopriv_record_buzz_mode2', 'inskill_buzzer_record_buzz_mode2' );

/**
 * Récupère les 3 premiers buzz pour le mode 2.
 */
function inskill_buzzer_get_top3_buzz_mode2() {
    $buzzes = get_transient( 'inskill_buzzer_mode2_buzz' );
    if ( ! is_array( $buzzes ) ) {
        $buzzes = array();
    }
    usort( $buzzes, function( $a, $b ) {
        return $a['time'] - $b['time'];
    } );
    $top3 = array_slice( $buzzes, 0, 3 );
    wp_send_json_success( $top3 );
}
add_action( 'wp_ajax_get_top3_buzz_mode2', 'inskill_buzzer_get_top3_buzz_mode2' );
add_action( 'wp_ajax_nopriv_get_top3_buzz_mode2', 'inskill_buzzer_get_top3_buzz_mode2' );

/**
 * Réinitialise les buzz pour le mode 2.
 */
function inskill_buzzer_reset_buzz_mode2() {
    delete_transient( 'inskill_buzzer_mode2_buzz' );
    wp_send_json_success( array( 'message' => 'Buzz réinitialisés' ) );
}
add_action( 'wp_ajax_reset_buzz_mode2', 'inskill_buzzer_reset_buzz_mode2' );
add_action( 'wp_ajax_nopriv_reset_buzz_mode2', 'inskill_buzzer_reset_buzz_mode2' );
