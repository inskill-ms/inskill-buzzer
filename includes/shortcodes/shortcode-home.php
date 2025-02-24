<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InskillBuzzer_Shortcodes_Home extends InskillBuzzer_Shortcodes {

    public function __construct() {
        add_shortcode('inskill_buzzer_home', array($this, 'render_home_page'));
    }

    public function render_home_page($atts) {
        ob_start();

        // Traitement des formulaires si la méthode POST est utilisée
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Traitement du formulaire "Rejoindre une activité"
            if (isset($_POST['join_activity'])) {
                $activity_name = sanitize_text_field($_POST['activity_name']);
                $redirect_url = '';

                if ($activity_name == get_option('inskill_buzzer_mode1_activity_name')) {
                    $frontend_url = get_option('inskill_buzzer_mode1_frontend_participant_url');
                    $activity_id  = get_option('inskill_buzzer_mode1_activity_id');
                    if ($frontend_url && $activity_id)
                        $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode1=' . $activity_id;
                } elseif ($activity_name == get_option('inskill_buzzer_mode2_activity_name')) {
                    $frontend_url = get_option('inskill_buzzer_mode2_frontend_participant_url');
                    $activity_id  = get_option('inskill_buzzer_mode2_activity_id');
                    if ($frontend_url && $activity_id)
                        $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode2=' . $activity_id;
                } elseif ($activity_name == get_option('inskill_buzzer_mode3_activity_name')) {
                    $frontend_url = get_option('inskill_buzzer_mode3_frontend_participant_url');
                    $activity_id  = get_option('inskill_buzzer_mode3_activity_id');
                    if ($frontend_url && $activity_id)
                        $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode3=' . $activity_id;
                } elseif ($activity_name == get_option('inskill_buzzer_mode4_activity_name')) {
                    $frontend_url = get_option('inskill_buzzer_mode4_frontend_participant_url');
                    $activity_id  = get_option('inskill_buzzer_mode4_activity_id');
                    if ($frontend_url && $activity_id)
                        $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode4=' . $activity_id;
                }

                if ($redirect_url) {
                    echo "<script>window.location.href='" . esc_url($redirect_url) . "';</script>";
                    return ob_get_clean();
                } else {
                    echo '<p style="color:red; text-align:center;">Il n\'existe aucune activité de ce nom.</p>';
                }
            }

            // Traitement du formulaire "Animer une activité"
            if (isset($_POST['animate_activity'])) {
                $activity_name = sanitize_text_field($_POST['activity_name']);
                $animate_password = sanitize_text_field($_POST['animate_password']);
                $redirect_url = '';
                $error = '';

                if ($activity_name == get_option('inskill_buzzer_mode1_activity_name')) {
                    if ($animate_password == get_option('inskill_buzzer_mode1_animateur_password')) {
                        $frontend_url = get_option('inskill_buzzer_mode1_frontend_animateur_url');
                        $activity_id  = get_option('inskill_buzzer_mode1_activity_id');
                        if ($frontend_url && $activity_id)
                            $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode1_animateur=' . $activity_id;
                    } else {
                        $error = 'Le mot de passe animateur est incorrect.';
                    }
                } elseif ($activity_name == get_option('inskill_buzzer_mode2_activity_name')) {
                    if ($animate_password == get_option('inskill_buzzer_mode2_animateur_password')) {
                        $frontend_url = get_option('inskill_buzzer_mode2_frontend_animateur_url');
                        $activity_id  = get_option('inskill_buzzer_mode2_activity_id');
                        if ($frontend_url && $activity_id)
                            $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode2_animateur=' . $activity_id;
                    } else {
                        $error = 'Le mot de passe animateur est incorrect.';
                    }
                } elseif ($activity_name == get_option('inskill_buzzer_mode3_activity_name')) {
                    if ($animate_password == get_option('inskill_buzzer_mode3_animateur_password')) {
                        $frontend_url = get_option('inskill_buzzer_mode3_frontend_animateur_url');
                        $activity_id  = get_option('inskill_buzzer_mode3_activity_id');
                        if ($frontend_url && $activity_id)
                            $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode3_animateur=' . $activity_id;
                    } else {
                        $error = 'Le mot de passe animateur est incorrect.';
                    }
                } elseif ($activity_name == get_option('inskill_buzzer_mode4_activity_name')) {
                    if ($animate_password == get_option('inskill_buzzer_mode4_animateur_password')) {
                        $frontend_url = get_option('inskill_buzzer_mode4_frontend_animateur_url');
                        $activity_id  = get_option('inskill_buzzer_mode4_activity_id');
                        if ($frontend_url && $activity_id)
                            $redirect_url = trailingslashit($frontend_url) . '?inskill_buzzer_mode4_animateur=' . $activity_id;
                    } else {
                        $error = 'Le mot de passe animateur est incorrect.';
                    }
                } else {
                    $error = 'Il n\'existe aucune activité de ce nom.';
                }

                if ($redirect_url) {
                    echo "<script>window.location.href='" . esc_url($redirect_url) . "';</script>";
                    return ob_get_clean();
                } else {
                    if ($error)
                        echo '<p style="color:red; text-align:center;">' . esc_html($error) . '</p>';
                }
            }
        }
        ?>
        <style>
        .inskill-home-container {
            display: flex;
            justify-content: center;
            align-items: stretch;
            gap: 20px;
            margin: 20px auto;
            max-width: 1000px;
            padding: 0 20px;
        }
        .inskill-home-block {
            flex: 1;
            padding: 20px;
            box-sizing: border-box;
            color: #fff;
            border-radius: 5px;
            height: 350px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }
        .inskill-home-block p {
            margin: 5px 0;
            color: #fff;
        }
        .inskill-home-block h2 {
            margin: 5px 0;
            font-weight: bold;
            color: #fff;
            line-height: 1.2;
        }
        .inskill-home-block form input[type="text"],
        .inskill-home-block form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
            background-color: #fff;
            color: #000;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .inskill-home-block form button {
            background-color: #0069d9;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 3px;
        }
        .inskill-home-block.left {
            background-color: #00285B;
        }
        .inskill-home-block.right {
            background-color: #50164A;
        }
        @media (max-width: 768px) {
            .inskill-home-container {
                flex-direction: column;
            }
            .inskill-home-block {
                width: 100%;
                margin-bottom: 20px;
                height: auto;
            }
        }
        </style>

        <div class="inskill-home-container">
            <!-- Bloc Joueur : Rejoindre une activité -->
            <div class="inskill-home-block left">
                <p>Joueur</p>
                <h2>Rejoindre<br>une activité</h2>
                <form method="post">
                    <input type="text" name="activity_name" placeholder="Nom de l'activité" required>
                    <button type="submit" name="join_activity">C'est parti !!!</button>
                </form>
            </div>
            <!-- Bloc Organisateur : Animer une activité -->
            <div class="inskill-home-block right">
                <p>Organisateur</p>
                <h2>Animer<br>une activité</h2>
                <form method="post">
                    <input type="text" name="activity_name" placeholder="Nom de l'activité" required>
                    <input type="password" name="animate_password" placeholder="Mot de passe Animateur" required>
                    <button type="submit" name="animate_activity">C'est parti !!!</button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
