<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InskillBuzzer_Shortcodes_Mode3 extends InskillBuzzer_Shortcodes {

    public function __construct() {
        add_shortcode('inskill_buzzer_mode3', array($this, 'render_mode3_participant'));
        add_shortcode('inskill_buzzer_mode3_animateur', array($this, 'render_mode3_animateur'));
    }

    public function render_mode3_participant($atts) {
        ob_start();
        ?>
        <h2>Quiz : Chacun pour sa peau - Participant</h2>
        <form method="post">
            <label for="participant_name">Nom Prénom :</label>
            <input type="text" name="participant_name" id="participant_name" required>
            <button type="submit" name="validate_participant">Valider</button>
        </form>
        <div id="quiz_buttons" style="display:none;">
            <?php
            foreach (array('A','B','C','D','E','F') as $answer) {
                echo '<button class="answer_button" style="background-color:red;">'.$answer.'</button> ';
            }
            ?>
        </div>
        <div id="scoreboard">
            <h3>Classement</h3>
        </div>
        <script>
        document.querySelector('form').addEventListener('submit', function(e){
            e.preventDefault();
            document.getElementById('quiz_buttons').style.display = 'block';
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function render_mode3_animateur($atts) {
        ob_start();
        ?>
        <h2>Quiz : Chacun pour sa peau - Animateur</h2>
        <div>
            <button id="reset_answers">Réinitialisation des boutons-réponses</button>
        </div>
        <div>
            <h3>Les 3 plus rapides</h3>
            <div id="top3">
                <!-- Affichage des 3 premiers -->
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
            <h3>Classement</h3>
            <div id="scoreboard">
                <!-- Classement des participants -->
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
