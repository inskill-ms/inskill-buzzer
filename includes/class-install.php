<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InskillBuzzer_Install {

    public static function install() {
        // Vous pouvez initialiser ici des options par défaut si nécessaire.
    }

    public static function uninstall() {
        // Suppression des options du mode 1
        delete_option('inskill_buzzer_mode1_activity_name');
        delete_option('inskill_buzzer_mode1_activity_id');
        delete_option('inskill_buzzer_mode1_frontend_animateur_url');
        delete_option('inskill_buzzer_mode1_animateur_password');
        delete_option('inskill_buzzer_mode1_frontend_participant_url');
        
        // Suppression des options du mode 2
        delete_option('inskill_buzzer_mode2_activity_name');
        delete_option('inskill_buzzer_mode2_activity_id');
        delete_option('inskill_buzzer_mode2_team1_name');
        delete_option('inskill_buzzer_mode2_team2_name');
        delete_option('inskill_buzzer_mode2_team3_name');
        delete_option('inskill_buzzer_mode2_team4_name');
        delete_option('inskill_buzzer_mode2_frontend_animateur_url');
        delete_option('inskill_buzzer_mode2_animateur_password');
        delete_option('inskill_buzzer_mode2_frontend_participant_url');
        
        // Suppression des options du mode 3
        delete_option('inskill_buzzer_mode3_activity_name');
        delete_option('inskill_buzzer_mode3_activity_id');
        delete_option('inskill_buzzer_mode3_frontend_animateur_url');
        delete_option('inskill_buzzer_mode3_animateur_password');
        delete_option('inskill_buzzer_mode3_frontend_participant_url');
        
        // Suppression des options du mode 4
        delete_option('inskill_buzzer_mode4_activity_name');
        delete_option('inskill_buzzer_mode4_activity_id');
        delete_option('inskill_buzzer_mode4_team1_name');
        delete_option('inskill_buzzer_mode4_team2_name');
        delete_option('inskill_buzzer_mode4_team3_name');
        delete_option('inskill_buzzer_mode4_team4_name');
        delete_option('inskill_buzzer_mode4_frontend_animateur_url');
        delete_option('inskill_buzzer_mode4_animateur_password');
        delete_option('inskill_buzzer_mode4_frontend_participant_url');
    }
}