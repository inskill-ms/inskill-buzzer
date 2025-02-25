<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'inskill_buzzer_scores';

/**
 * --- MODE 1 ---
 * Crée ou réinitialise un enregistrement pour un participant (mode 1).
 */
function inskill_buzzer_create_participant() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    if ( empty($participant_name) || $activity_id === 0 ) {
        wp_send_json_error( array( 'message' => 'Données manquantes (mode 1)' ) );
    }
    $existing = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d AND participant_name = %s",
        $activity_id,
        $participant_name
    ) );
    if ($existing) {
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
            wp_send_json_error( array( 'message' => 'Erreur lors de la création du participant (mode 1)' ) );
        }
    }
    wp_send_json_success( array( 'message' => 'Participant créé ou réinitialisé (mode 1)' ) );
}
add_action('wp_ajax_create_participant', 'inskill_buzzer_create_participant');
add_action('wp_ajax_nopriv_create_participant', 'inskill_buzzer_create_participant');

/**
 * Enregistre un buzz pour le participant (mode 1).
 */
function inskill_buzzer_record_buzz_score() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    if ( empty($participant_name) || $activity_id === 0 ) {
        wp_send_json_error( array( 'message' => 'Données manquantes (mode 1)' ) );
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
        wp_send_json_success( array( 'message' => 'Buzz enregistré (mode 1)' ) );
    } else {
        $buzz_time = $wpdb->get_var( $wpdb->prepare(
            "SELECT buzz_time FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d AND participant_name = %s",
            $activity_id,
            $participant_name
        ) );
        if ($buzz_time !== null) {
            wp_send_json_error( array( 'message' => 'Participant déjà buzzé (mode 1)' ) );
        } else {
            $result = $wpdb->update(
                $wpdb->prefix . 'inskill_buzzer_scores',
                array( 'buzz_time' => microtime(true) ),
                array( 'activity_id' => $activity_id, 'participant_name' => $participant_name ),
                array( '%f' ),
                array( '%d', '%s' )
            );
            if ( false === $result ) {
                wp_send_json_error( array( 'message' => 'Erreur lors de l\'enregistrement du buzz (mode 1)' ) );
            } else {
                wp_send_json_success( array( 'message' => 'Buzz enregistré (mode 1)' ) );
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
        wp_send_json_error( array( 'message' => 'Activité non définie (mode 1)' ) );
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
 * Met à jour le score du participant ayant buzzé en premier (mode 1).
 */
function inskill_buzzer_update_first_score() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    $delta = isset($_POST['delta']) ? intval($_POST['delta']) : 0;
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie (mode 1)' ) );
    }
    $participant = $wpdb->get_row( $wpdb->prepare(
        "SELECT id, participant_name, score FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d AND buzz_time IS NOT NULL ORDER BY buzz_time ASC LIMIT 1",
        $activity_id
    ), ARRAY_A );
    if (!$participant) {
        wp_send_json_error( array( 'message' => 'Aucun participant buzzé (mode 1)' ) );
    }
    $updated = $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->prefix}inskill_buzzer_scores SET score = score + %d WHERE id = %d",
            $delta,
            $participant['id']
        )
    );
    if ( false === $updated ) {
        wp_send_json_error( array( 'message' => 'Erreur lors de la mise à jour du score (mode 1)' ) );
    } else {
        wp_send_json_success( array( 'message' => 'Score mis à jour (mode 1)', 'participant' => $participant['participant_name'] ) );
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
        wp_send_json_error( array( 'message' => 'Activité non définie (mode 1)' ) );
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
 * Réinitialise tous les buzzers pour le mode 1.
 */
function inskill_buzzer_reset_all() {
    delete_transient( 'inskill_buzzer_mode1_buzz' );
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie (mode 1)' ) );
    }
    $updated = $wpdb->query( $wpdb->prepare(
        "UPDATE {$wpdb->prefix}inskill_buzzer_scores SET buzz_time = NULL WHERE activity_id = %d",
        $activity_id
    ) );
    if ( false === $updated ) {
        wp_send_json_error( array( 'message' => 'Erreur lors de la réinitialisation des buzzers (mode 1)' ) );
    } else {
        wp_send_json_success( array( 'message' => 'Buzzers réinitialisés (mode 1)' ) );
    }
}
add_action('wp_ajax_reset_buzzers_all', 'inskill_buzzer_reset_all');
add_action('wp_ajax_nopriv_reset_buzzers_all', 'inskill_buzzer_reset_all');

/**
 * Réinitialise tous les buzzers pour le mode 2.
 */
function inskill_buzzer_reset_all_mode2() {
    delete_transient( 'inskill_buzzer_mode2_buzz' );
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie (mode 2)' ) );
    }
    $updated = $wpdb->query( $wpdb->prepare(
        "UPDATE {$wpdb->prefix}inskill_buzzer_scores SET buzz_time = NULL WHERE activity_id = %d",
        $activity_id
    ) );
    if ( false === $updated ) {
        wp_send_json_error( array( 'message' => 'Erreur lors de la réinitialisation des buzzers (mode 2)' ) );
    } else {
        wp_send_json_success( array( 'message' => 'Buzzers réinitialisés (mode 2)' ) );
    }
}
add_action('wp_ajax_reset_buzzers_all_mode2', 'inskill_buzzer_reset_all_mode2');
add_action('wp_ajax_nopriv_reset_buzzers_all_mode2', 'inskill_buzzer_reset_all_mode2');

/**
 * Supprime tous les participants pour l'activité (mode 1).
 */
function inskill_buzzer_remove_all_participants() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    if($activity_id === 0){
         wp_send_json_error(array('message' => 'Activité non définie (mode 1)'));
    }
    $result = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d", $activity_id));
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la suppression de tous les participants (mode 1)'));
    } else {
         wp_send_json_success(array('message' => 'Tous les participants ont été supprimés (mode 1)'));
    }
}
add_action('wp_ajax_remove_all_participants', 'inskill_buzzer_remove_all_participants');
add_action('wp_ajax_nopriv_remove_all_participants', 'inskill_buzzer_remove_all_participants');

/**
 * Supprime tous les participants pour l'activité (mode 2).
 */
function inskill_buzzer_remove_all_participants_mode2() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    if($activity_id === 0){
         wp_send_json_error(array('message' => 'Activité non définie (mode 2)'));
    }
    $result = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d", $activity_id));
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la suppression de tous les participants (mode 2)'));
    } else {
         wp_send_json_success(array('message' => 'Tous les participants ont été supprimés (mode 2)'));
    }
}
add_action('wp_ajax_remove_all_participants_mode2', 'inskill_buzzer_remove_all_participants_mode2');
add_action('wp_ajax_nopriv_remove_all_participants_mode2', 'inskill_buzzer_remove_all_participants_mode2');

/**
 * Met à jour le score d'un participant (mode 1).
 */
function inskill_buzzer_update_participant_score() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    $delta = isset($_POST['delta']) ? intval($_POST['delta']) : 0;
    if(empty($participant_name) || $activity_id === 0) {
         wp_send_json_error(array('message' => 'Données manquantes (mode 1)'));
    }
    $result = $wpdb->query(
         $wpdb->prepare(
             "UPDATE {$wpdb->prefix}inskill_buzzer_scores SET score = score + %d WHERE activity_id = %d AND participant_name = %s",
             $delta, $activity_id, $participant_name
         )
    );
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la mise à jour du score (mode 1)'));
    } else {
         wp_send_json_success(array('message' => 'Score mis à jour (mode 1)'));
    }
}
add_action('wp_ajax_update_participant_score', 'inskill_buzzer_update_participant_score');
add_action('wp_ajax_nopriv_update_participant_score', 'inskill_buzzer_update_participant_score');

/**
 * Met à jour le score d'un participant (mode 2).
 */
function inskill_buzzer_update_participant_score_mode2() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    $delta = isset($_POST['delta']) ? intval($_POST['delta']) : 0;
    if(empty($participant_name) || $activity_id === 0) {
         wp_send_json_error(array('message' => 'Données manquantes (mode 2)'));
    }
    $result = $wpdb->query(
         $wpdb->prepare(
             "UPDATE {$wpdb->prefix}inskill_buzzer_scores SET score = score + %d WHERE activity_id = %d AND participant_name = %s",
             $delta, $activity_id, $participant_name
         )
    );
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la mise à jour du score (mode 2)'));
    } else {
         wp_send_json_success(array('message' => 'Score mis à jour (mode 2)'));
    }
}
add_action('wp_ajax_update_participant_score_mode2', 'inskill_buzzer_update_participant_score_mode2');
add_action('wp_ajax_nopriv_update_participant_score_mode2', 'inskill_buzzer_update_participant_score_mode2');

/**
 * Met à jour le score d'une équipe (mode 2) via Bonus/Malus.
 */
function inskill_buzzer_update_team_score_mode2() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    $team = isset($_POST['participant_team']) ? sanitize_text_field($_POST['participant_team']) : '';
    $delta = isset($_POST['delta']) ? intval($_POST['delta']) : 0;
    if(empty($team) || $activity_id === 0) {
         wp_send_json_error(array('message' => 'Données manquantes (team update mode 2)'));
    }
    $result = $wpdb->query(
         $wpdb->prepare(
             "UPDATE {$wpdb->prefix}inskill_buzzer_scores SET score = score + %d WHERE activity_id = %d AND team = %s",
             $delta, $activity_id, $team
         )
    );
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la mise à jour du score de l\'équipe'));
    } else {
         wp_send_json_success(array('message' => 'Score de l\'équipe mis à jour'));
    }
}
add_action('wp_ajax_update_team_score_mode2', 'inskill_buzzer_update_team_score_mode2');
add_action('wp_ajax_nopriv_update_team_score_mode2', 'inskill_buzzer_update_team_score_mode2');

/**
 * Met à jour le nom d'un participant (mode 1).
 */
function inskill_buzzer_update_participant() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    $old_name = isset($_POST['old_name']) ? sanitize_text_field($_POST['old_name']) : '';
    $new_name = isset($_POST['new_name']) ? sanitize_text_field($_POST['new_name']) : '';
    if(empty($old_name) || empty($new_name) || $activity_id === 0) {
         wp_send_json_error(array('message' => 'Données manquantes (mode 1)'));
    }
    $result = $wpdb->update(
         $wpdb->prefix . 'inskill_buzzer_scores',
         array('participant_name' => $new_name),
         array('activity_id' => $activity_id, 'participant_name' => $old_name),
         array('%s'),
         array('%d','%s')
    );
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la mise à jour (mode 1)'));
    } else {
         wp_send_json_success(array('message' => 'Participant mis à jour (mode 1)', 'new_name' => $new_name));
    }
}
add_action('wp_ajax_update_participant', 'inskill_buzzer_update_participant');
add_action('wp_ajax_nopriv_update_participant', 'inskill_buzzer_update_participant');

/**
 * Supprime un participant (mode 1).
 */
function inskill_buzzer_remove_participant() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode1_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    if(empty($participant_name) || $activity_id === 0) {
         wp_send_json_error(array('message' => 'Données manquantes (mode 1)'));
    }
    $result = $wpdb->delete(
         $wpdb->prefix . 'inskill_buzzer_scores',
         array('activity_id' => $activity_id, 'participant_name' => $participant_name),
         array('%d','%s')
    );
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la suppression (mode 1)'));
    } else {
         wp_send_json_success(array('message' => 'Participant supprimé (mode 1)'));
    }
}
add_action('wp_ajax_remove_participant', 'inskill_buzzer_remove_participant');
add_action('wp_ajax_nopriv_remove_participant', 'inskill_buzzer_remove_participant');

/**
 * Supprime un participant (mode 2).
 */
function inskill_buzzer_remove_participant_mode2() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    $participant_team = isset($_POST['participant_team']) ? sanitize_text_field($_POST['participant_team']) : '';
    if(empty($participant_name) || empty($participant_team) || $activity_id === 0) {
         wp_send_json_error(array('message' => 'Données manquantes (mode 2)'));
    }
    $result = $wpdb->delete(
         $wpdb->prefix . 'inskill_buzzer_scores',
         array('activity_id' => $activity_id, 'participant_name' => $participant_name, 'team' => $participant_team),
         array('%d','%s','%s')
    );
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la suppression (mode 2)'));
    } else {
         wp_send_json_success(array('message' => 'Participant supprimé (mode 2)'));
    }
}
add_action('wp_ajax_remove_participant_mode2', 'inskill_buzzer_remove_participant_mode2');
add_action('wp_ajax_nopriv_remove_participant_mode2', 'inskill_buzzer_remove_participant_mode2');

/**
 * --- Nouvel endpoint : Récupère tous les participants pour le mode 2 ---
 */
function inskill_buzzer_get_all_participants_mode2() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    if ($activity_id === 0) {
         wp_send_json_error(array('message' => 'Activité non définie (mode 2)'));
    }
    $results = $wpdb->get_results(
         $wpdb->prepare(
             "SELECT participant_name, team FROM {$wpdb->prefix}inskill_buzzer_scores WHERE activity_id = %d ORDER BY team ASC, participant_name ASC",
             $activity_id
         ),
         ARRAY_A
    );
    wp_send_json_success($results);
}
add_action('wp_ajax_get_all_participants_mode2', 'inskill_buzzer_get_all_participants_mode2');
add_action('wp_ajax_nopriv_get_all_participants_mode2', 'inskill_buzzer_get_all_participants_mode2');

/**
 * --- Fin des endpoints ---
 */
