<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'inskill_buzzer_mode3_scores';

/**
 * Crée ou réinitialise un enregistrement pour un participant en Mode 3 (Quiz)
 */
function inskill_buzzer_create_participant_mode3() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode3_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    if ( empty($participant_name) || $activity_id === 0 ) {
        wp_send_json_error( array( 'message' => 'Données manquantes' ) );
    }
    // On ajoute le champ "answer" initialisé à NULL
    $existing = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}inskill_buzzer_mode3_scores WHERE activity_id = %d AND participant_name = %s",
        $activity_id,
        $participant_name
    ) );
    if ($existing) {
        $wpdb->update(
            $wpdb->prefix . 'inskill_buzzer_mode3_scores',
            array( 'buzz_time' => null, 'answer' => null ),
            array( 'activity_id' => $activity_id, 'participant_name' => $participant_name ),
            array( '%f', '%s' ),
            array( '%d','%s' )
        );
    } else {
        $result = $wpdb->insert(
            $wpdb->prefix . 'inskill_buzzer_mode3_scores',
            array(
                'activity_id' => $activity_id,
                'participant_name' => $participant_name,
                'buzz_time' => null,
                'score' => 0,
                'answer' => null
            ),
            array( '%d', '%s', '%f', '%d', '%s' )
        );
        if ( false === $result ) {
            wp_send_json_error( array( 'message' => 'Erreur lors de la création du participant' ) );
        }
    }
    wp_send_json_success( array( 'message' => 'Participant créé ou réinitialisé' ) );
}
add_action('wp_ajax_create_participant_mode3', 'inskill_buzzer_create_participant_mode3');
add_action('wp_ajax_nopriv_create_participant_mode3', 'inskill_buzzer_create_participant_mode3');

/**
 * Enregistre la réponse du participant (avec le temps) en Mode 3 (Quiz)
 */
function inskill_buzzer_record_buzz_score_mode3() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode3_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    $answer = isset($_POST['answer']) ? sanitize_text_field($_POST['answer']) : '';
    if ( empty($participant_name) || $activity_id === 0 ) {
        wp_send_json_error( array( 'message' => 'Données manquantes' ) );
    }
    $existing = $wpdb->get_var( $wpdb->prepare(
        "SELECT id, buzz_time FROM {$wpdb->prefix}inskill_buzzer_mode3_scores WHERE activity_id = %d AND participant_name = %s",
        $activity_id,
        $participant_name
    ) );
    if (!$existing) {
        $wpdb->insert(
            $wpdb->prefix . 'inskill_buzzer_mode3_scores',
            array(
                'activity_id' => $activity_id,
                'participant_name' => $participant_name,
                'buzz_time' => microtime(true),
                'score' => 0,
                'answer' => $answer
            ),
            array( '%d', '%s', '%f', '%d', '%s' )
        );
        wp_send_json_success( array( 'message' => 'Réponse enregistrée' ) );
    } else {
        $buzz_time = $wpdb->get_var( $wpdb->prepare(
            "SELECT buzz_time FROM {$wpdb->prefix}inskill_buzzer_mode3_scores WHERE activity_id = %d AND participant_name = %s",
            $activity_id,
            $participant_name
        ) );
        if ($buzz_time !== null) {
            wp_send_json_error( array( 'message' => 'Participant déjà répondu' ) );
        } else {
            $result = $wpdb->update(
                $wpdb->prefix . 'inskill_buzzer_mode3_scores',
                array( 
                    'buzz_time' => microtime(true),
                    'answer' => $answer
                ),
                array( 'activity_id' => $activity_id, 'participant_name' => $participant_name ),
                array( '%f', '%s' ),
                array( '%d', '%s' )
            );
            if ( false === $result ) {
                wp_send_json_error( array( 'message' => 'Erreur lors de l\'enregistrement de la réponse' ) );
            } else {
                wp_send_json_success( array( 'message' => 'Réponse enregistrée' ) );
            }
        }
    }
}
add_action('wp_ajax_record_buzz_score_mode3', 'inskill_buzzer_record_buzz_score_mode3');
add_action('wp_ajax_nopriv_record_buzz_score_mode3', 'inskill_buzzer_record_buzz_score_mode3');

/**
 * Récupère les 3 premières réponses (les plus rapides) en Mode 3
 */
function inskill_buzzer_get_top3_score_mode3() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode3_activity_id', 0));
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie' ) );
    }
    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT participant_name, answer FROM {$wpdb->prefix}inskill_buzzer_mode3_scores WHERE activity_id = %d AND buzz_time IS NOT NULL ORDER BY buzz_time ASC LIMIT 3",
        $activity_id
    ), ARRAY_A );
    wp_send_json_success( $results );
}
add_action('wp_ajax_get_top3_score_mode3', 'inskill_buzzer_get_top3_score_mode3');
add_action('wp_ajax_nopriv_get_top3_score_mode3', 'inskill_buzzer_get_top3_score_mode3');

/**
 * Met à jour le score du participant ayant répondu le plus rapidement en Mode 3
 */
function inskill_buzzer_update_first_score_mode3() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode3_activity_id', 0));
    $delta = isset($_POST['delta']) ? intval($_POST['delta']) : 0;
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie' ) );
    }
    $participant = $wpdb->get_row( $wpdb->prepare(
        "SELECT id, participant_name, score FROM {$wpdb->prefix}inskill_buzzer_mode3_scores WHERE activity_id = %d AND buzz_time IS NOT NULL ORDER BY buzz_time ASC LIMIT 1",
        $activity_id
    ), ARRAY_A );
    if (!$participant) {
        wp_send_json_error( array( 'message' => 'Aucun participant n\'a répondu' ) );
    }
    $updated = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->prefix}inskill_buzzer_mode3_scores SET score = score + %d WHERE id = %d",
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
add_action('wp_ajax_update_first_score_mode3', 'inskill_buzzer_update_first_score_mode3');
add_action('wp_ajax_nopriv_update_first_score_mode3', 'inskill_buzzer_update_first_score_mode3');

/**
 * Récupère le classement complet en Mode 3
 */
function inskill_buzzer_get_full_ranking_mode3() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode3_activity_id', 0));
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie' ) );
    }
    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT participant_name, score, answer FROM {$wpdb->prefix}inskill_buzzer_mode3_scores WHERE activity_id = %d ORDER BY score DESC, CASE WHEN buzz_time IS NULL THEN 1 ELSE 0 END, buzz_time ASC",
        $activity_id
    ), ARRAY_A );
    wp_send_json_success( $results );
}
add_action('wp_ajax_get_full_ranking_mode3', 'inskill_buzzer_get_full_ranking_mode3');
add_action('wp_ajax_nopriv_get_full_ranking_mode3', 'inskill_buzzer_get_full_ranking_mode3');

/**
 * Réinitialise tous les enregistrements (boutons réponses) pour le mode 3
 */
function inskill_buzzer_reset_all_mode3() {
    delete_transient( 'inskill_buzzer_mode3_buzz' );
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode3_activity_id', 0));
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie' ) );
    }
    $updated = $wpdb->query( $wpdb->prepare(
        "UPDATE {$wpdb->prefix}inskill_buzzer_mode3_scores SET buzz_time = NULL, answer = NULL WHERE activity_id = %d",
        $activity_id
    ) );
    if ( false === $updated ) {
        wp_send_json_error( array( 'message' => 'Erreur lors de la réinitialisation des réponses' ) );
    } else {
        wp_send_json_success( array( 'message' => 'Boutons réponses réinitialisés' ) );
    }
}
add_action('wp_ajax_reset_buzzers_all_mode3', 'inskill_buzzer_reset_all_mode3');
add_action('wp_ajax_nopriv_reset_buzzers_all_mode3', 'inskill_buzzer_reset_all_mode3');

/**
 * Supprime tous les participants pour l'activité en mode 3.
 */
function inskill_buzzer_remove_all_participants_mode3() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode3_activity_id', 0));
    if($activity_id === 0){
         wp_send_json_error(array('message' => 'Activité non définie'));
    }
    $result = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}inskill_buzzer_mode3_scores WHERE activity_id = %d", $activity_id));
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la suppression de tous les participants'));
    } else {
         wp_send_json_success(array('message' => 'Tous les participants ont été supprimés'));
    }
}
add_action('wp_ajax_remove_all_participants_mode3', 'inskill_buzzer_remove_all_participants_mode3');
add_action('wp_ajax_nopriv_remove_all_participants_mode3', 'inskill_buzzer_remove_all_participants_mode3');

/**
 * Met à jour le nom d'un participant pour le mode 3.
 */
function inskill_buzzer_update_participant_mode3() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode3_activity_id', 0));
    $old_name = isset($_POST['old_name']) ? sanitize_text_field($_POST['old_name']) : '';
    $new_name = isset($_POST['new_name']) ? sanitize_text_field($_POST['new_name']) : '';
    if(empty($old_name) || empty($new_name) || $activity_id === 0) {
         wp_send_json_error(array('message' => 'Données manquantes'));
    }
    $result = $wpdb->update(
         $wpdb->prefix . 'inskill_buzzer_mode3_scores',
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
add_action('wp_ajax_update_participant_mode3', 'inskill_buzzer_update_participant_mode3');
add_action('wp_ajax_nopriv_update_participant_mode3', 'inskill_buzzer_update_participant_mode3');

/**
 * Supprime un participant pour le mode 3.
 */
function inskill_buzzer_remove_participant_mode3() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode3_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    if(empty($participant_name) || $activity_id === 0) {
         wp_send_json_error(array('message' => 'Données manquantes'));
    }
    $result = $wpdb->delete(
         $wpdb->prefix . 'inskill_buzzer_mode3_scores',
         array('activity_id' => $activity_id, 'participant_name' => $participant_name),
         array('%d','%s')
    );
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la suppression'));
    } else {
         wp_send_json_success(array('message' => 'Participant supprimé'));
    }
}
add_action('wp_ajax_remove_participant_mode3', 'inskill_buzzer_remove_participant_mode3');
add_action('wp_ajax_nopriv_remove_participant_mode3', 'inskill_buzzer_remove_participant_mode3');

/**
 * Met à jour le score d'un participant pour le mode 3.
 */
function inskill_buzzer_update_participant_score_mode3() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode3_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    $delta = isset($_POST['delta']) ? intval($_POST['delta']) : 0;
    if(empty($participant_name) || $activity_id === 0) {
         wp_send_json_error(array('message' => 'Données manquantes'));
    }
    $result = $wpdb->query(
         $wpdb->prepare(
             "UPDATE {$wpdb->prefix}inskill_buzzer_mode3_scores SET score = score + %d WHERE activity_id = %d AND participant_name = %s",
             $delta, $activity_id, $participant_name
         )
    );
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la mise à jour du score'));
    } else {
         wp_send_json_success(array('message' => 'Score mis à jour'));
    }
}
add_action('wp_ajax_update_participant_score_mode3', 'inskill_buzzer_update_participant_score_mode3');
add_action('wp_ajax_nopriv_update_participant_score_mode3', 'inskill_buzzer_update_participant_score_mode3');
?>
