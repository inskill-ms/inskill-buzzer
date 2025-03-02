<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'inskill_buzzer_mode2_scores';

/**
 * Crée ou réinitialise un enregistrement pour un participant en Mode 2.
 * Ajout du champ 'team'.
 */
function inskill_buzzer_create_participant_mode2() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    $participant_team = isset($_POST['participant_team']) ? sanitize_text_field($_POST['participant_team']) : '';
    if ( empty($participant_name) || $activity_id === 0 || empty($participant_team) ) {
        wp_send_json_error( array( 'message' => 'Données manquantes' ) );
    }
    $existing = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}inskill_buzzer_mode2_scores WHERE activity_id = %d AND participant_name = %s",
        $activity_id,
        $participant_name
    ) );
    if ($existing) {
        $wpdb->update(
            $wpdb->prefix . 'inskill_buzzer_mode2_scores',
            array( 'buzz_time' => null, 'team' => $participant_team ),
            array( 'activity_id' => $activity_id, 'participant_name' => $participant_name ),
            array( '%f', '%s' ),
            array( '%d','%s' )
        );
    } else {
        $result = $wpdb->insert(
            $wpdb->prefix . 'inskill_buzzer_mode2_scores',
            array(
                'activity_id'      => $activity_id,
                'participant_name' => $participant_name,
                'team'             => $participant_team,
                'buzz_time'        => null,
                'score'            => 0
            ),
            array( '%d', '%s', '%s', '%f', '%d' )
        );
        if ( false === $result ) {
            wp_send_json_error( array( 'message' => 'Erreur lors de la création du participant' ) );
        }
    }
    wp_send_json_success( array( 'message' => 'Participant créé ou réinitialisé' ) );
}
add_action('wp_ajax_create_participant_mode2', 'inskill_buzzer_create_participant_mode2');
add_action('wp_ajax_nopriv_create_participant_mode2', 'inskill_buzzer_create_participant_mode2');

/**
 * Enregistre un buzz pour le participant (met à jour buzz_time) en Mode 2.
 */
function inskill_buzzer_record_buzz_score_mode2() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    if ( empty($participant_name) || $activity_id === 0 ) {
        wp_send_json_error( array( 'message' => 'Données manquantes' ) );
    }
    $existing = $wpdb->get_var( $wpdb->prepare(
        "SELECT id, buzz_time FROM {$wpdb->prefix}inskill_buzzer_mode2_scores WHERE activity_id = %d AND participant_name = %s",
        $activity_id,
        $participant_name
    ) );
    if (!$existing) {
        $wpdb->insert(
            $wpdb->prefix . 'inskill_buzzer_mode2_scores',
            array(
                'activity_id'      => $activity_id,
                'participant_name' => $participant_name,
                'buzz_time'        => microtime(true),
                'score'            => 0
            ),
            array( '%d', '%s', '%f', '%d' )
        );
        wp_send_json_success( array( 'message' => 'Buzz enregistré' ) );
    } else {
        $buzz_time = $wpdb->get_var( $wpdb->prepare(
            "SELECT buzz_time FROM {$wpdb->prefix}inskill_buzzer_mode2_scores WHERE activity_id = %d AND participant_name = %s",
            $activity_id,
            $participant_name
        ) );
        if ($buzz_time !== null) {
            wp_send_json_error( array( 'message' => 'Participant déjà buzzé' ) );
        } else {
            $result = $wpdb->update(
                $wpdb->prefix . 'inskill_buzzer_mode2_scores',
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
add_action('wp_ajax_record_buzz_score_mode2', 'inskill_buzzer_record_buzz_score_mode2');
add_action('wp_ajax_nopriv_record_buzz_score_mode2', 'inskill_buzzer_record_buzz_score_mode2');

/**
 * Récupère les 3 premiers buzz pour le mode 2.
 */
function inskill_buzzer_get_top3_score_mode2() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie' ) );
    }
    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT participant_name, team, buzz_time FROM {$wpdb->prefix}inskill_buzzer_mode2_scores WHERE activity_id = %d AND buzz_time IS NOT NULL ORDER BY buzz_time ASC LIMIT 3",
        $activity_id
    ), ARRAY_A );
    wp_send_json_success( $results );
}
add_action('wp_ajax_get_top3_score_mode2', 'inskill_buzzer_get_top3_score_mode2');
add_action('wp_ajax_nopriv_get_top3_score_mode2', 'inskill_buzzer_get_top3_score_mode2');

/**
 * Met à jour le score de l'équipe pour le bonus/malus (pour le mode 2).
 * Correction : mise à jour sur une seule ligne par équipe pour éviter le cumul multiplié.
 */
function inskill_buzzer_update_participant_score_mode2() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    if($activity_id === 0){
         wp_send_json_error(array('message' => 'Activité non définie'));
    }
    $delta = isset($_POST['delta']) ? intval($_POST['delta']) : 0;
    if(isset($_POST['team']) && !empty($_POST['team'])){
         $team = sanitize_text_field($_POST['team']);
    } else {
         $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
         if(empty($participant_name)){
             wp_send_json_error(array('message' => 'Données manquantes'));
         }
         $team = $wpdb->get_var( $wpdb->prepare(
             "SELECT team FROM {$wpdb->prefix}inskill_buzzer_mode2_scores WHERE activity_id = %d AND participant_name = %s",
             $activity_id,
             $participant_name
         ) );
         if(!$team) {
              wp_send_json_error(array('message' => 'Équipe non trouvée'));
         }
    }
    // Sélectionner une seule ligne de l'équipe (par exemple, la première par ID)
    $id = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}inskill_buzzer_mode2_scores WHERE activity_id = %d AND team = %s ORDER BY id ASC LIMIT 1",
        $activity_id,
        $team
    ) );
    if(!$id) {
         wp_send_json_error(array('message' => 'Aucun participant trouvé pour cette équipe'));
    }
    // Récupérer le score actuel de cette ligne et mettre à jour uniquement cette ligne
    $currentScore = $wpdb->get_var( $wpdb->prepare(
        "SELECT score FROM {$wpdb->prefix}inskill_buzzer_mode2_scores WHERE id = %d",
        $id
    ) );
    $newScore = $currentScore + $delta;
    $result = $wpdb->update(
         $wpdb->prefix . 'inskill_buzzer_mode2_scores',
         array('score' => $newScore),
         array('id' => $id),
         array('%d'),
         array('%d')
    );
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la mise à jour du score'));
    } else {
         wp_send_json_success(array('message' => 'Score mis à jour pour l\'équipe'));
    }
}
add_action('wp_ajax_update_participant_score_mode2', 'inskill_buzzer_update_participant_score_mode2');
add_action('wp_ajax_nopriv_update_participant_score_mode2', 'inskill_buzzer_update_participant_score_mode2');

/**
 * Récupère le classement complet pour le mode 2 (agrégé par équipe).
 */
function inskill_buzzer_get_full_ranking_mode2() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie' ) );
    }
    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT team, SUM(score) as score FROM {$wpdb->prefix}inskill_buzzer_mode2_scores WHERE activity_id = %d AND team != '' GROUP BY team ORDER BY score DESC",
        $activity_id
    ), ARRAY_A );
    wp_send_json_success( $results );
}
add_action('wp_ajax_get_full_ranking_mode2', 'inskill_buzzer_get_full_ranking_mode2');
add_action('wp_ajax_nopriv_get_full_ranking_mode2', 'inskill_buzzer_get_full_ranking_mode2');

/**
 * Réinitialise tous les buzzers pour le mode 2.
 */
function inskill_buzzer_reset_all_mode2() {
    delete_transient( 'inskill_buzzer_mode2_buzz' );
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    if ($activity_id === 0) {
        wp_send_json_error( array( 'message' => 'Activité non définie' ) );
    }
    $updated = $wpdb->query( $wpdb->prepare(
        "UPDATE {$wpdb->prefix}inskill_buzzer_mode2_scores SET buzz_time = NULL WHERE activity_id = %d",
        $activity_id
    ) );
    if ( false === $updated ) {
        wp_send_json_error( array( 'message' => 'Erreur lors de la réinitialisation des buzzers' ) );
    } else {
        wp_send_json_success( array( 'message' => 'Buzzers réinitialisés' ) );
    }
}
add_action('wp_ajax_reset_buzzers_all_mode2', 'inskill_buzzer_reset_all_mode2');
add_action('wp_ajax_nopriv_reset_buzzers_all_mode2', 'inskill_buzzer_reset_all_mode2');

/**
 * Supprime tous les participants pour l'activité en mode 2.
 */
function inskill_buzzer_remove_all_participants_mode2() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    if($activity_id === 0){
         wp_send_json_error(array('message' => 'Activité non définie'));
    }
    $result = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}inskill_buzzer_mode2_scores WHERE activity_id = %d", $activity_id));
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la suppression de tous les participants'));
    } else {
         wp_send_json_success(array('message' => 'Tous les participants ont été supprimés'));
    }
}
add_action('wp_ajax_remove_all_participants_mode2', 'inskill_buzzer_remove_all_participants_mode2');
add_action('wp_ajax_nopriv_remove_all_participants_mode2', 'inskill_buzzer_remove_all_participants_mode2');

/**
 * Met à jour le nom d'un participant pour le mode 2.
 */
function inskill_buzzer_update_participant_mode2() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    $old_name = isset($_POST['old_name']) ? sanitize_text_field($_POST['old_name']) : '';
    $new_name = isset($_POST['new_name']) ? sanitize_text_field($_POST['new_name']) : '';
    if(empty($old_name) || empty($new_name) || $activity_id === 0) {
         wp_send_json_error(array('message' => 'Données manquantes'));
    }
    $result = $wpdb->update(
         $wpdb->prefix . 'inskill_buzzer_mode2_scores',
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
add_action('wp_ajax_update_participant_mode2', 'inskill_buzzer_update_participant_mode2');
add_action('wp_ajax_nopriv_update_participant_mode2', 'inskill_buzzer_update_participant_mode2');

/**
 * Supprime un participant pour le mode 2.
 */
function inskill_buzzer_remove_participant_mode2() {
    global $wpdb;
    $activity_id = intval(get_option('inskill_buzzer_mode2_activity_id', 0));
    $participant_name = isset($_POST['participant_name']) ? sanitize_text_field($_POST['participant_name']) : '';
    if(empty($participant_name) || $activity_id === 0) {
         wp_send_json_error(array('message' => 'Données manquantes'));
    }
    $result = $wpdb->delete(
         $wpdb->prefix . 'inskill_buzzer_mode2_scores',
         array('activity_id' => $activity_id, 'participant_name' => $participant_name),
         array('%d','%s')
    );
    if(false === $result) {
         wp_send_json_error(array('message' => 'Erreur lors de la suppression'));
    } else {
         wp_send_json_success(array('message' => 'Participant supprimé'));
    }
}
add_action('wp_ajax_remove_participant_mode2', 'inskill_buzzer_remove_participant_mode2');
add_action('wp_ajax_nopriv_remove_participant_mode2', 'inskill_buzzer_remove_participant_mode2');
?>
