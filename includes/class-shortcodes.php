<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InskillBuzzer_Shortcodes {

    public function __construct() {
        add_shortcode('inskill_buzzer_home', array($this, 'render_home_page'));
        add_shortcode('inskill_buzzer_mode1', array($this, 'render_mode1_participant'));
        add_shortcode('inskill_buzzer_mode1_animateur', array($this, 'render_mode1_animateur'));
        add_shortcode('inskill_buzzer_mode2', array($this, 'render_mode2_participant'));
        add_shortcode('inskill_buzzer_mode2_animateur', array($this, 'render_mode2_animateur'));
        add_shortcode('inskill_buzzer_mode3', array($this, 'render_mode3_participant'));
        add_shortcode('inskill_buzzer_mode3_animateur', array($this, 'render_mode3_animateur'));
        add_shortcode('inskill_buzzer_mode4', array($this, 'render_mode4_participant'));
        add_shortcode('inskill_buzzer_mode4_animateur', array($this, 'render_mode4_animateur'));
    }

    // PAGE D'ACCUEIL : Formulaire de connexion pour rejoindre ou animer une activité.
    public function render_home_page($atts) {
        ob_start();

        // Traitement du formulaire "Rejoindre une activité"
        if ( isset($_POST['join_activity']) ) {
            $activity_name = sanitize_text_field($_POST['activity_name']);
            $redirect_url = '';

            // Recherche dans chacun des modes
            if ( $activity_name == get_option('inskill_buzzer_mode1_activity_name') ) {
                $frontend_url = get_option('inskill_buzzer_mode1_frontend_participant_url');
                $activity_id  = get_option('inskill_buzzer_mode1_activity_id');
                if ( $frontend_url && $activity_id )
                    $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode1=' . $activity_id;
            }
            elseif ( $activity_name == get_option('inskill_buzzer_mode2_activity_name') ) {
                $frontend_url = get_option('inskill_buzzer_mode2_frontend_participant_url');
                $activity_id  = get_option('inskill_buzzer_mode2_activity_id');
                if ( $frontend_url && $activity_id )
                    $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode2=' . $activity_id;
            }
            elseif ( $activity_name == get_option('inskill_buzzer_mode3_activity_name') ) {
                $frontend_url = get_option('inskill_buzzer_mode3_frontend_participant_url');
                $activity_id  = get_option('inskill_buzzer_mode3_activity_id');
                if ( $frontend_url && $activity_id )
                    $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode3=' . $activity_id;
            }
            elseif ( $activity_name == get_option('inskill_buzzer_mode4_activity_name') ) {
                $frontend_url = get_option('inskill_buzzer_mode4_frontend_participant_url');
                $activity_id  = get_option('inskill_buzzer_mode4_activity_id');
                if ( $frontend_url && $activity_id )
                    $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode4=' . $activity_id;
            }

            if ( $redirect_url ) {
                wp_redirect($redirect_url);
                exit;
            } else {
                echo '<p style="color:red;">Il n\'existe aucune activité de ce nom.</p>';
            }
        }

        // Traitement du formulaire "Assurer l’animation d'une activité"
        if ( isset($_POST['animate_activity']) ) {
            $activity_name   = sanitize_text_field($_POST['activity_name']);
            $animate_password = sanitize_text_field($_POST['animate_password']);
            $redirect_url    = '';
            $error           = '';

            if ( $activity_name == get_option('inskill_buzzer_mode1_activity_name') ) {
                if ( $animate_password == get_option('inskill_buzzer_mode1_animateur_password') ) {
                    $frontend_url = get_option('inskill_buzzer_mode1_frontend_animateur_url');
                    $activity_id  = get_option('inskill_buzzer_mode1_activity_id');
                    if ( $frontend_url && $activity_id )
                        $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode1_animateur=' . $activity_id;
                } else {
                    $error = 'Le mot de passe animateur est incorrect.';
                }
            }
            elseif ( $activity_name == get_option('inskill_buzzer_mode2_activity_name') ) {
                if ( $animate_password == get_option('inskill_buzzer_mode2_animateur_password') ) {
                    $frontend_url = get_option('inskill_buzzer_mode2_frontend_animateur_url');
                    $activity_id  = get_option('inskill_buzzer_mode2_activity_id');
                    if ( $frontend_url && $activity_id )
                        $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode2_animateur=' . $activity_id;
                } else {
                    $error = 'Le mot de passe animateur est incorrect.';
                }
            }
            elseif ( $activity_name == get_option('inskill_buzzer_mode3_activity_name') ) {
                if ( $animate_password == get_option('inskill_buzzer_mode3_animateur_password') ) {
                    $frontend_url = get_option('inskill_buzzer_mode3_frontend_animateur_url');
                    $activity_id  = get_option('inskill_buzzer_mode3_activity_id');
                    if ( $frontend_url && $activity_id )
                        $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode3_animateur=' . $activity_id;
                } else {
                    $error = 'Le mot de passe animateur est incorrect.';
                }
            }
            elseif ( $activity_name == get_option('inskill_buzzer_mode4_activity_name') ) {
                if ( $animate_password == get_option('inskill_buzzer_mode4_animateur_password') ) {
                    $frontend_url = get_option('inskill_buzzer_mode4_frontend_animateur_url');
                    $activity_id  = get_option('inskill_buzzer_mode4_activity_id');
                    if ( $frontend_url && $activity_id )
                        $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode4_animateur=' . $activity_id;
                } else {
                    $error = 'Le mot de passe animateur est incorrect.';
                }
            } else {
                $error = 'Il n\'existe aucune activité de ce nom.';
            }

            if ( $redirect_url ) {
                wp_redirect($redirect_url);
                exit;
            } else {
                if ( $error )
                    echo '<p style="color:red;">' . esc_html($error) . '</p>';
            }
        }
        ?>
        <h2>Rejoindre une activité</h2>
        <form method="post">
            <label for="activity_name">Nom de l'activité :</label>
            <input type="text" name="activity_name" id="activity_name" required>
            <button type="submit" name="join_activity">C'est parti !</button>
        </form>
        <hr>
        <h2>Assurer l'animation d'une activité</h2>
        <form method="post">
            <label for="activity_name_animate">Nom de l'activité :</label>
            <input type="text" name="activity_name" id="activity_name_animate" required>
            <br>
            <label for="animate_password">Mot de passe animateur :</label>
            <input type="password" name="animate_password" id="animate_password" required>
            <br>
            <button type="submit" name="animate_activity">C'est parti !</button>
        </form>
        <?php
        return ob_get_clean();
    }

    // ---------------------------
    // PAGES PARTICIPANTS ET ANIMATEURS
    // ---------------------------

    // Mode 1 – Buzzer : Chacun pour sa peau (Participant)
    public function render_mode1_participant($atts) {
        ob_start();
        ?>
        <h2>Buzzer : Chacun pour sa peau - Participant</h2>
        <form method="post">
            <label for="participant_name">Nom Prénom :</label>
            <input type="text" name="participant_name" id="participant_name" required>
            <button type="submit" name="validate_participant">Valider</button>
        </form>
        <!-- Une fois validé, le bouton buzzer s'affichera -->
        <div id="buzzer_container" style="display:none;">
            <button id="buzzer_button" style="background-color:red;">Buzzer</button>
        </div>
        <!-- Tableau de classement -->
        <div id="scoreboard">
            <h3>Classement</h3>
            <!-- Classement mis à jour en temps réel -->
        </div>
        <script>
        // Exemple de script : après validation, affichage du buzzer
        document.querySelector('form').addEventListener('submit', function(e){
            e.preventDefault();
            document.getElementById('buzzer_container').style.display = 'block';
        });
        </script>
        <?php
        return ob_get_clean();
    }

    // Mode 1 – Buzzer : Chacun pour sa peau (Animateur)
    public function render_mode1_animateur($atts) {
        ob_start();
        ?>
        <h2>Buzzer : Chacun pour sa peau - Animateur</h2>
        <div>
            <button id="reset_buzzers">Réinitialisation des buzzers</button>
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

    // Mode 2 – Buzzer : L'union fait la force (Participant)
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
                foreach ( $teams as $team ) {
                    if ( $team ) {
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

    // Mode 2 – Buzzer : L'union fait la force (Animateur)
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

    // Mode 3 – Quiz : Chacun pour sa peau (Participant)
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

    // Mode 3 – Quiz : Chacun pour sa peau (Animateur)
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

    // Mode 4 – Quiz : L'union fait la force (Participant)
    public function render_mode4_participant($atts) {
        ob_start();
        ?>
        <h2>Quiz : L'union fait la force - Participant</h2>
        <form method="post">
            <label for="participant_name">Nom Prénom :</label>
            <input type="text" name="participant_name" id="participant_name" required>
            <br>
            <label for="participant_team">Équipe :</label>
            <select name="participant_team" id="participant_team">
                <?php
                $teams = array(
                    get_option('inskill_buzzer_mode4_team1_name', ''),
                    get_option('inskill_buzzer_mode4_team2_name', ''),
                    get_option('inskill_buzzer_mode4_team3_name', ''),
                    get_option('inskill_buzzer_mode4_team4_name', '')
                );
                foreach ( $teams as $team ) {
                    if ( $team ) {
                        echo '<option value="'.esc_attr($team).'">'.esc_html($team).'</option>';
                    }
                }
                ?>
            </select>
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

    // Mode 4 – Quiz : L'union fait la force (Animateur)
    public function render_mode4_animateur($atts) {
        ob_start();
        ?>
        <h2>Quiz : L'union fait la force - Animateur</h2>
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

// Initialisation des shortcodes
new InskillBuzzer_Shortcodes();
