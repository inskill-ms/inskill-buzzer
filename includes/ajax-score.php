<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'inskill_buzzer_scores';

/**
 * Crée un enregistrement pour un participant s'il n'existe pas déjà.
 */
function inskill_buzzer_create_participant() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    if ( empty($participant_name) || $activity_id === 0 ) {
        wp_send_json_error( array( 'message' => 'Données manquantes' ) );
    }
    // Vérifier si le participant existe déjà
    $existing = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d AND participant_name = %s",
        $activity_id,
        $participant_name
    ) );
    if (!$existing) {
        $result = $wpdb->insert(
            $wpdb->prefix . 'inskill_buzzer_scores',
            array(
                'activity_id' => $activity_id,
                'participant_name' => $participant_name,
                'buzz_time' => NULL,
                'score' => 0
            ),
            array( '%d', '%s', '%f', '%d' )
        );
        if ( false === $result ) {
            wp_send_json_error( array( 'message' => 'Erreur lors de la création du participant' ) );
        }
    }
    wp_send_json_success( array( 'message' => 'Participant créé ou déjà existant' ) );
}
add_action('wp_ajax_create_participant', 'inskill_buzzer_create_participant');
add_action('wp_ajax_nopriv_create_participant', 'inskill_buzzer_create_participant');

/**
 * Enregistre un buzz pour le participant (met à jour buzz_time).
 */
function inskill_buzzer_record_buzz_score() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    if ( empty($participant_name) || $activity_id === 0 ) {
        wp_send_json_error( array( 'message' => 'Données manquantes' ) );
    }
    // Vérifier si ce participant existe, sinon créer un enregistrement
    $existing = $wpdb->get_var( $wpdb->prepare(
        "SELECT id, buzz_time FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d AND participant_name = %s",
        $activity_id,
        $participant_name
    ) );
    if (!$existing) {
        // Créer le participant
        $wpdb->insert(
            $wpdb->prefix . 'inskill_buzzer_scores',
            array(
                'activity_id' => $activity_id,
                'participant_name' => $participant_name,
                'buzz_time' => microtime(true),
                'score' => 0
            ),
            array( '%d', '%s', '%f', '%d' )
        );
        wp_send_json_success( array( 'message' => 'Buzz enregistré' ) );
    } else {
        // Si buzz_time est déjà défini, empêcher le buzz multiple
        $buzz_time = $wpdb->get_var( $wpdb->prepare(
            "SELECT buzz_time FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d AND participant_name = %s",
            $activity_id,
            $participant_name
        ) );
        if ($buzz_time !== null) {
            wp_send_json_error( array( 'message' => 'Participant déjà buzzé' ) );
        } else {
            // Mettre à jour le buzz_time
            $result = $wpdb->update(
                $wpdb->prefix . 'inskill_buzzer_scores',
                array( 'buzz_time' => microtime(true) ),
                array( 'activity_id' => $activity_id, 'participant_name' => $participant_name ),
                array( '%f' ),
                array( '%d', '%s' )
            );
            if ( false === $result ) {
                wp_send_json_error( array( 'message' => 'Erreur lors de l\'enregistrement du buzz' ) );
            } else {
                wp_send_json_success( array( 'message' => 'Buzz enregistré' ) );
            }
        }
    }
}
add_action('wp_ajax_record_buzz_score', 'inskill_buzzer_record_buzz_score');
add_action('wp_ajax_nopriv_record_buzz_score', 'inskill_buzzer_record_buzz_score');

/**
 * Récupère les 3 premiers buzz (ceux dont buzz_time n'est pas NULL) pour le mode 1.
 */
function inskill_buzzer_get_top3_score() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie' ) );
    }
    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT participant_name FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d AND buzz_time IS NOT NULL ORDER BY buzz_time ASC LIMIT 3",
        $activity_id
    ), ARRAY_A );
    wp_send_json_success( $results );
}
add_action('wp_ajax_get_top3_score', 'inskill_buzzer_get_top3_score');
add_action('wp_ajax_nopriv_get_top3_score', 'inskill_buzzer_get_top3_score');

/**
 * Met à jour le score du participant ayant buzzé en premier.
 * Pour simplifier, on met à jour le participant avec le buzz_time minimal.
 * L'animateur envoie un delta (points à ajouter, positif ou négatif).
 */
function inskill_buzzer_update_first_score() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    $delta = isset($_POST['delta']) ? intval($_POST['delta']) : 0;
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie' ) );
    }
    // Récupérer le participant avec buzz_time non null le plus rapide
    $participant = $wpdb->get_row( $wpdb->prepare(
        "SELECT id, participant_name FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d AND buzz_time IS NOT NULL ORDER BY buzz_time ASC LIMIT 1",
        $activity_id
    ), ARRAY_A );
    if (!$participant) {
        wp_send_json_error( array( 'message' => 'Aucun participant buzzé' ) );
    }
    // Mettre à jour le score
    $updated = $wpdb->update(
        $wpdb->prefix . 'inskill_buzzer_scores',
        array( 'score' => $wpdb->prepare("score + %d", $delta) ),
        array( 'id' => $participant['id'] ),
        array( '%d' ),
        array( '%d' )
    );
    if ( false === $updated ) {
        wp_send_json_error( array( 'message' => 'Erreur lors de la mise à jour du score' ) );
    } else {
        wp_send_json_success( array( 'message' => 'Score mis à jour', 'participant' => $participant['participant_name'] ) );
    }
}
add_action('wp_ajax_update_first_score', 'inskill_buzzer_update_first_score');
add_action('wp_ajax_nopriv_update_first_score', 'inskill_buzzer_update_first_score');

/**
 * Récupère le classement complet pour le mode 1.
 * Tous les participants (même ceux sans buzz_time) sont listés.
 * Le tri se fait par score décroissant, puis par buzz_time ASC (les NULL en fin).
 */
function inskill_buzzer_get_full_ranking() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie' ) );
    }
    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT participant_name, score FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d ORDER BY score DESC, 
         CASE WHEN buzz_time IS NULL THEN 1 ELSE 0 END, buzz_time ASC",
        $activity_id
    ), ARRAY_A );
    wp_send_json_success( $results );
}
add_action('wp_ajax_get_full_ranking', 'inskill_buzzer_get_full_ranking');
add_action('wp_ajax_nopriv_get_full_ranking', 'inskill_buzzer_get_full_ranking');

/**
 * Réinitialise uniquement le buzz (remet buzz_time à NULL) pour tous les participants de l'activité,
 * sans affecter les scores.
 */
function inskill_buzzer_reset_buzzers() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie' ) );
    }
    $updated = $wpdb->query( $wpdb->prepare(
        "UPDATE {$wpdb->prefix}inskill_buzzer_scores SET buzz_time = NULL WHERE activity_id = %d",
        $activity_id
    ) );
    if ( false === $updated ) {
        wp_send_json_error( array( 'message' => 'Erreur lors de la réinitialisation des buzzers' ) );
    } else {
        wp_send_json_success( array( 'message' => 'Buzzers réinitialisés' ) );
    }
}
add_action('wp_ajax_reset_buzz', 'inskill_buzzer_reset_buzzers');
add_action('wp_ajax_nopriv_reset_buzz', 'inskill_buzzer_reset_buzzers');
