<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InskillBuzzer_Install {

    public static function install() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'inskill_buzzer_scores';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            activity_id INT NOT NULL,
            participant_name VARCHAR(255) NOT NULL,
            buzz_time DOUBLE DEFAULT NULL,
            score INT NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY participant (activity_id, participant_name)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    public static function uninstall() {
        // Supprimer les options utilisées par le plugin
        delete_option('inskill_buzzer_mode1_activity_name');
        delete_option('inskill_buzzer_mode1_activity_id');
        delete_option('inskill_buzzer_mode1_frontend_animateur_url');
        delete_option('inskill_buzzer_mode1_animateur_password');
        delete_option('inskill_buzzer_mode1_frontend_participant_url');
        // … idem pour les autres modes

        // Supprimer la table personnalisée
        global $wpdb;
        $table_name = $wpdb->prefix . 'inskill_buzzer_scores';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
}
