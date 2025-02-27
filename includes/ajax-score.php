<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'inskill_buzzer_scores';

/**
 * Crée ou réinitialise un enregistrement pour un participant.
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
    if ($existing) {
        // Réinitialiser le buzz_time pour permettre une reconnexion
        $wpdb->update(
            $wpdb->prefix . 'inskill_buzzer_scores',
            array( 'buzz_time' => null ),
            array( 'activity_id' => $activity_id, 'participant_name' => $participant_name ),
            array( '%f' ),
            array( '%d','%s' )
        );
    } else {
        $result = $wpdb->insert(
            $wpdb->prefix . 'inskill_buzzer_scores',
            array(
                'activity_id' => $activity_id,
                'participant_name' => $participant_name,
                'buzz_time' => null,
                'score' => 0
            ),
            array( '%d', '%s', '%f', '%d' )
        );
        if ( false === $result ) {
            wp_send_json_error( array( 'message' => 'Erreur lors de la création du participant' ) );
        }
    }
    wp_send_json_success( array( 'message' => 'Participant créé ou réinitialisé' ) );
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
    $existing = $wpdb->get_var( $wpdb->prepare(
        "SELECT id, buzz_time FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d AND participant_name = %s",
        $activity_id,
        $participant_name
    ) );
    if (!$existing) {
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
        $buzz_time = $wpdb->get_var( $wpdb->prepare(
            "SELECT buzz_time FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d AND participant_name = %s",
            $activity_id,
            $participant_name
        ) );
        if ($buzz_time !== null) {
            wp_send_json_error( array( 'message' => 'Participant déjà buzzé' ) );
        } else {
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
 * Récupère les 3 premiers buzz pour le mode 1.
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
 */
function inskill_buzzer_update_first_score() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    $delta = isset($_POST['delta']) ? intval($_POST['delta']) : 0;
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie' ) );
    }
    $participant = $wpdb->get_row( $wpdb->prepare(
        "SELECT id, participant_name, score FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d AND buzz_time IS NOT NULL ORDER BY buzz_time ASC LIMIT 1",
        $activity_id
    ), ARRAY_A );
    if (!$participant) {
        wp_send_json_error( array( 'message' => 'Aucun participant buzzé' ) );
    }
    $updated = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->prefix}inskill_buzzer_scores SET score = score + %d WHERE id = %d",
            $delta,
            $participant['id']
        )
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
 */
function inskill_buzzer_get_full_ranking() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie' ) );
    }
    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT participant_name, score FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d ORDER BY score DESC, CASE WHEN buzz_time IS NULL THEN 1 ELSE 0 END, buzz_time ASC",
        $activity_id
    ), ARRAY_A );
    wp_send_json_success( $results );
}
add_action('wp_ajax_get_full_ranking', 'inskill_buzzer_get_full_ranking');
add_action('wp_ajax_nopriv_get_full_ranking', 'inskill_buzzer_get_full_ranking');

/**
 * Réinitialise tous les buzzers.
 */
function inskill_buzzer_reset_all() {
    delete_transient( 'inskill_buzzer_mode1_buzz' );
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
add_action('wp_ajax_reset_buzzers_all', 'inskill_buzzer_reset_all');
add_action('wp_ajax_nopriv_reset_buzzers_all', 'inskill_buzzer_reset_all');

/**
 * Supprime tous les participants pour l'activité.
 */
function inskill_buzzer_remove_all_participants() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    if($activity_id === 0){
         wp_send_json_error(array('message' => 'Activité non définie'));
    }
    $result = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d", $activity_id));
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la suppression de tous les participants'));
    } else {
         wp_send_json_success(array('message' => 'Tous les participants ont été supprimés'));
    }
}
add_action('wp_ajax_remove_all_participants', 'inskill_buzzer_remove_all_participants');
add_action('wp_ajax_nopriv_remove_all_participants', 'inskill_buzzer_remove_all_participants');

/**
 * Met à jour le nom d'un participant.
 */
function inskill_buzzer_update_participant() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    $old_name = isset($_POST['old_name']) ? sanitize_text_field($_POST['old_name']) : '';
    $new_name = isset($_POST['new_name']) ? sanitize_text_field($_POST['new_name']) : '';
    if(empty($old_name) || empty($new_name) || $activity_id === 0) {
         wp_send_json_error(array('message' => 'Données manquantes'));
    }
    $result = $wpdb->update(
         $wpdb->prefix . 'inskill_buzzer_scores',
         array('participant_name' => $new_name),
         array('activity_id' => $activity_id, 'participant_name' => $old_name),
         array('%s'),
         array('%d','%s')
    );
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la mise à jour'));
    } else {
         wp_send_json_success(array('message' => 'Participant mis à jour', 'new_name' => $new_name));
    }
}
add_action('wp_ajax_update_participant', 'inskill_buzzer_update_participant');
add_action('wp_ajax_nopriv_update_participant', 'inskill_buzzer_update_participant');

/**
 * Supprime un participant.
 */
function inskill_buzzer_remove_participant() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    if(empty($participant_name) || $activity_id === 0) {
         wp_send_json_error(array('message' => 'Données manquantes'));
    }
    $result = $wpdb->delete(
         $wpdb->prefix . 'inskill_buzzer_scores',
         array('activity_id' => $activity_id, 'participant_name' => $participant_name),
         array('%d','%s')
    );
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la suppression'));
    } else {
         wp_send_json_success(array('message' => 'Participant supprimé'));
    }
}
add_action('wp_ajax_remove_participant', 'inskill_buzzer_remove_participant');
add_action('wp_ajax_nopriv_remove_participant', 'inskill_buzzer_remove_participant');

/**
 * Met à jour le score d'un participant.
 */
function inskill_buzzer_update_participant_score() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    $delta = isset($_POST['delta']) ? intval($_POST['delta']) : 0;
    if(empty($participant_name) || $activity_id === 0) {
         wp_send_json_error(array('message' => 'Données manquantes'));
    }
    $result = $wpdb->query(
         $wpdb->prepare(
             "UPDATE {$wpdb->prefix}inskill_buzzer_scores SET score = score + %d WHERE activity_id = %d AND participant_name = %s",
             $delta, $activity_id, $participant_name
         )
    );
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la mise à jour du score'));
    } else {
         wp_send_json_success(array('message' => 'Score mis à jour'));
    }
}
add_action('wp_ajax_update_participant_score', 'inskill_buzzer_update_participant_score');
add_action('wp_ajax_nopriv_update_participant_score', 'inskill_buzzer_update_participant_score');
