<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InskillBuzzer_Shortcodes_Mode2 extends InskillBuzzer_Shortcodes {

    public function __construct() {
        add_shortcode('inskill_buzzer_mode2', array($this, 'render_mode2_participant'));
        add_shortcode('inskill_buzzer_mode2_animateur', array($this, 'render_mode2_animateur'));
    }

    public function render_mode2_participant($atts) {
        ob_start();
        ?>
        <h2>Buzzer : L'union fait la force - Participant</h2>
        <form method="post">
            <label for="participant_name">Nom Prénom :</label>
            <input type="text" name="participant_name" id="participant_name" required>
            <br>
            <label for="participant_team">Équipe :</label>
            <select name="participant_team" id="participant_team">
                <?php
                $teams = array(
                    get_option('inskill_buzzer_mode2_team1_name', ''),
                    get_option('inskill_buzzer_mode2_team2_name', ''),
                    get_option('inskill_buzzer_mode2_team3_name', ''),
                    get_option('inskill_buzzer_mode2_team4_name', '')
                );
                foreach ($teams as $team) {
                    if ($team) {
                        echo '<option value="'.esc_attr($team).'">'.esc_html($team).'</option>';
                    }
                }
                ?>
            </select>
            <button type="submit" name="validate_participant">Valider</button>
        </form>
        <div id="buzzer_container" style="display:none;">
            <button id="buzzer_button" style="background-color:red;">Buzzer</button>
        </div>
        <div id="scoreboard">
            <h3>Classement</h3>
        </div>
        <script>
        document.querySelector('form').addEventListener('submit', function(e){
            e.preventDefault();
            document.getElementById('buzzer_container').style.display = 'block';
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function render_mode2_animateur($atts) {
        ob_start();
        ?>
        <h2>Buzzer : L'union fait la force - Animateur</h2>
        <div>
            <button id="reset_buzzers">Réinitialisation des buzzers</button>
        </div>
        <div>
            <h3>Les 3 plus rapides</h3>
            <div id="top3">
                <!-- Affichage des 3 premiers avec nom d'équipe -->
            </div>
        </div>
        <div>
            <h3>Résultats</h3>
            <button id="good_button" style="background-color:green;">Goooood !</button>
            <button id="bad_button" style="background-color:red;">No good ...</button>
            <div>
                <label for="points_ok">Réponse OK :</label>
                <input type="number" id="points_ok" value="1" min="1">
            </div>
            <div>
                <label for="points_nok">Réponse NOK :</label>
                <input type="number" id="points_nok" value="-1">
            </div>
        </div>
        <div>
            <h3>Classement des équipes</h3>
            <div id="team_scoreboard">
                <!-- Classement par équipe -->
            </div>
        </div>
        <div>
            <h3>Participants connectés</h3>
            <div id="participants_list">
                <!-- Liste des participants -->
            </div>
        </div>
        <div>
            <button id="reset_activity">Réinitialisation de l'activité</button>
        </div>
        <script>
        // Code à ajouter pour la logique d'animation
        </script>
        <?php
        return ob_get_clean();
    }
}
