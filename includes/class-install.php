<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InskillBuzzer_Install {

    public static function install() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Liste des tables pour chaque mode
        $table_names = array(
            $wpdb->prefix . 'inskill_buzzer_mode1_scores',
            $wpdb->prefix . 'inskill_buzzer_mode2_scores',
            $wpdb->prefix . 'inskill_buzzer_mode3_scores',
            $wpdb->prefix . 'inskill_buzzer_mode4_scores'
        );

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // Création de chaque table
        foreach ( $table_names as $table_name ) {
            $extra = '';
            if ( strpos( $table_name, 'mode3_scores' ) !== false ) {
                $extra = ", answer VARCHAR(1) DEFAULT NULL";
            } elseif ( strpos( $table_name, 'mode4_scores' ) !== false ) {
                $extra = ", team VARCHAR(255) DEFAULT NULL, answer VARCHAR(1) DEFAULT NULL";
            } elseif ( strpos( $table_name, 'mode2_scores' ) !== false ) {
                $extra = ", team VARCHAR(255) DEFAULT NULL";
            }
            $sql = "CREATE TABLE $table_name (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                activity_id INT NOT NULL,
                participant_name VARCHAR(255) NOT NULL,
                buzz_time DOUBLE DEFAULT NULL,
                score INT NOT NULL DEFAULT 0
                $extra,
                PRIMARY KEY  (id),
                UNIQUE KEY participant (activity_id, participant_name)
            ) $charset_collate;";
            dbDelta( $sql );
        }
    }

    public static function uninstall() {
        // Suppression des options utilisées par le plugin pour chaque mode
        $options = array(
            'inskill_buzzer_mode1_activity_name',
            'inskill_buzzer_mode1_activity_id',
            'inskill_buzzer_mode1_frontend_animateur_url',
            'inskill_buzzer_mode1_animateur_password',
            'inskill_buzzer_mode1_frontend_participant_url',
            'inskill_buzzer_mode2_activity_name',
            'inskill_buzzer_mode2_activity_id',
            'inskill_buzzer_mode2_team1_name',
            'inskill_buzzer_mode2_team2_name',
            'inskill_buzzer_mode2_team3_name',
            'inskill_buzzer_mode2_team4_name',
            'inskill_buzzer_mode2_frontend_animateur_url',
            'inskill_buzzer_mode2_animateur_password',
            'inskill_buzzer_mode2_frontend_participant_url',
            'inskill_buzzer_mode3_activity_name',
            'inskill_buzzer_mode3_activity_id',
            'inskill_buzzer_mode3_frontend_animateur_url',
            'inskill_buzzer_mode3_animateur_password',
            'inskill_buzzer_mode3_frontend_participant_url',
            'inskill_buzzer_mode4_activity_name',
            'inskill_buzzer_mode4_activity_id',
            'inskill_buzzer_mode4_team1_name',
            'inskill_buzzer_mode4_team2_name',
            'inskill_buzzer_mode4_team3_name',
            'inskill_buzzer_mode4_team4_name',
            'inskill_buzzer_mode4_frontend_animateur_url',
            'inskill_buzzer_mode4_animateur_password',
            'inskill_buzzer_mode4_frontend_participant_url'
        );
        foreach( $options as $option ) {
            delete_option( $option );
        }

        // Suppression des 4 tables
        global $wpdb;
        $table_names = array(
            $wpdb->prefix . 'inskill_buzzer_mode1_scores',
            $wpdb->prefix . 'inskill_buzzer_mode2_scores',
            $wpdb->prefix . 'inskill_buzzer_mode3_scores',
            $wpdb->prefix . 'inskill_buzzer_mode4_scores'
        );
        foreach ( $table_names as $table_name ) {
            $wpdb->query("DROP TABLE IF EXISTS $table_name");
        }
    }
}
?>
