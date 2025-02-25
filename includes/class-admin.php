<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InskillBuzzer_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_post_save'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Inskill Buzzer',
            'Inskill Buzzer',
            'manage_options',
            'inskill-buzzer',
            array($this, 'admin_page'),
            'dashicons-games',
            6
        );
    }

    public function admin_page() {
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'mode1';
        ?>
        <div class="wrap">
            <h1>Inskill Buzzer</h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=inskill-buzzer&tab=mode1" class="nav-tab <?php echo ($current_tab=='mode1') ? 'nav-tab-active' : ''; ?>">Buzzer : Chacun pour sa peau</a>
                <a href="?page=inskill-buzzer&tab=mode2" class="nav-tab <?php echo ($current_tab=='mode2') ? 'nav-tab-active' : ''; ?>">Buzzer : L'union fait la force</a>
                <a href="?page=inskill-buzzer&tab=mode3" class="nav-tab <?php echo ($current_tab=='mode3') ? 'nav-tab-active' : ''; ?>">Quiz : Chacun pour sa peau</a>
                <a href="?page=inskill-buzzer&tab=mode4" class="nav-tab <?php echo ($current_tab=='mode4') ? 'nav-tab-active' : ''; ?>">Quiz : L'union fait la force</a>
            </h2>
            <?php $this->render_tab($current_tab); ?>
        </div>
        <?php
    }

    public function handle_post_save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inskill_buzzer_mode'])) {
            $this->save_settings($_POST['inskill_buzzer_mode']);
            add_action('admin_notices', array($this, 'admin_notice'));
        }
    }

    public function admin_notice() {
        $mode = sanitize_text_field($_POST['inskill_buzzer_mode']);
        echo '<div class="updated"><p>Paramètres enregistrés pour ' . esc_html($mode) . '.</p></div>';
    }

    public function save_settings($mode) {
        if ($mode == 'mode1') {
            update_option('inskill_buzzer_mode1_activity_name', sanitize_text_field($_POST['inskill_buzzer_mode1_activity_name']));
            update_option('inskill_buzzer_mode1_activity_id', sanitize_text_field($_POST['inskill_buzzer_mode1_activity_id']));
            update_option('inskill_buzzer_mode1_frontend_animateur_url', esc_url_raw($_POST['inskill_buzzer_mode1_frontend_animateur_url']));
            update_option('inskill_buzzer_mode1_animateur_password', sanitize_text_field($_POST['inskill_buzzer_mode1_animateur_password']));
            update_option('inskill_buzzer_mode1_frontend_participant_url', esc_url_raw($_POST['inskill_buzzer_mode1_frontend_participant_url']));
        }
        elseif ($mode == 'mode2') {
            update_option('inskill_buzzer_mode2_activity_name', sanitize_text_field($_POST['inskill_buzzer_mode2_activity_name']));
            update_option('inskill_buzzer_mode2_activity_id', sanitize_text_field($_POST['inskill_buzzer_mode2_activity_id']));
            update_option('inskill_buzzer_mode2_team1_name', sanitize_text_field($_POST['inskill_buzzer_mode2_team1_name']));
            update_option('inskill_buzzer_mode2_team2_name', sanitize_text_field($_POST['inskill_buzzer_mode2_team2_name']));
            update_option('inskill_buzzer_mode2_team3_name', sanitize_text_field($_POST['inskill_buzzer_mode2_team3_name']));
            update_option('inskill_buzzer_mode2_team4_name', sanitize_text_field($_POST['inskill_buzzer_mode2_team4_name']));
            update_option('inskill_buzzer_mode2_frontend_animateur_url', esc_url_raw($_POST['inskill_buzzer_mode2_frontend_animateur_url']));
            update_option('inskill_buzzer_mode2_animateur_password', sanitize_text_field($_POST['inskill_buzzer_mode2_animateur_password']));
            update_option('inskill_buzzer_mode2_frontend_participant_url', esc_url_raw($_POST['inskill_buzzer_mode2_frontend_participant_url']));
        }
        elseif ($mode == 'mode3') {
            update_option('inskill_buzzer_mode3_activity_name', sanitize_text_field($_POST['inskill_buzzer_mode3_activity_name']));
            update_option('inskill_buzzer_mode3_activity_id', sanitize_text_field($_POST['inskill_buzzer_mode3_activity_id']));
            update_option('inskill_buzzer_mode3_frontend_animateur_url', esc_url_raw($_POST['inskill_buzzer_mode3_frontend_animateur_url']));
            update_option('inskill_buzzer_mode3_animateur_password', sanitize_text_field($_POST['inskill_buzzer_mode3_animateur_password']));
            update_option('inskill_buzzer_mode3_frontend_participant_url', esc_url_raw($_POST['inskill_buzzer_mode3_frontend_participant_url']));
        }
        elseif ($mode == 'mode4') {
            update_option('inskill_buzzer_mode4_activity_name', sanitize_text_field($_POST['inskill_buzzer_mode4_activity_name']));
            update_option('inskill_buzzer_mode4_activity_id', sanitize_text_field($_POST['inskill_buzzer_mode4_activity_id']));
            update_option('inskill_buzzer_mode4_team1_name', sanitize_text_field($_POST['inskill_buzzer_mode4_team1_name']));
            update_option('inskill_buzzer_mode4_team2_name', sanitize_text_field($_POST['inskill_buzzer_mode4_team2_name']));
            update_option('inskill_buzzer_mode4_team3_name', sanitize_text_field($_POST['inskill_buzzer_mode4_team3_name']));
            update_option('inskill_buzzer_mode4_team4_name', sanitize_text_field($_POST['inskill_buzzer_mode4_team4_name']));
            update_option('inskill_buzzer_mode4_frontend_animateur_url', esc_url_raw($_POST['inskill_buzzer_mode4_frontend_animateur_url']));
            update_option('inskill_buzzer_mode4_animateur_password', sanitize_text_field($_POST['inskill_buzzer_mode4_animateur_password']));
            update_option('inskill_buzzer_mode4_frontend_participant_url', esc_url_raw($_POST['inskill_buzzer_mode4_frontend_participant_url']));
        }
    }

    public function render_tab($current_tab) {
        switch($current_tab) {
            case 'mode1':
                $this->render_mode1_form();
                break;
            case 'mode2':
                $this->render_mode2_form();
                break;
            case 'mode3':
                $this->render_mode3_form();
                break;
            case 'mode4':
                $this->render_mode4_form();
                break;
            default:
                $this->render_mode1_form();
                break;
        }
    }

    // --- Formulaire Mode 1 : Buzzer – Chacun pour sa peau ---
    public function render_mode1_form() {
        $activity_name          = get_option('inskill_buzzer_mode1_activity_name', '');
        $activity_id            = get_option('inskill_buzzer_mode1_activity_id', '');
        $frontend_animateur_url = get_option('inskill_buzzer_mode1_frontend_animateur_url', '');
        $animateur_password     = get_option('inskill_buzzer_mode1_animateur_password', '');
        $frontend_participant_url = get_option('inskill_buzzer_mode1_frontend_participant_url', '');
        
        $url_animateur   = ($frontend_animateur_url && $activity_id) ? trailingslashit($frontend_animateur_url) . '?inskill_buzzer_mode1_animateur=' . $activity_id : '';
        $url_participant = ($frontend_participant_url && $activity_id) ? trailingslashit($frontend_participant_url) . '?inskill_buzzer_mode1=' . $activity_id : '';
        ?>
        <form method="post" action="">
            <input type="hidden" name="inskill_buzzer_mode" value="mode1" />
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode1_activity_name">Nom de l'activité</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode1_activity_name" id="inskill_buzzer_mode1_activity_name" value="<?php echo esc_attr($activity_name); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode1_activity_id">ID de l'activité (6 chiffres)</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode1_activity_id" id="inskill_buzzer_mode1_activity_id" value="<?php echo esc_attr($activity_id); ?>" class="regular-text" />
                        <button type="button" id="generate_mode1_id" class="button">Générer un ID aléatoire</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode1_frontend_animateur_url">URL de la page frontend animateur</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode1_frontend_animateur_url" id="inskill_buzzer_mode1_frontend_animateur_url" value="<?php echo esc_attr($frontend_animateur_url); ?>" class="regular-text" />
                        <p class="description">
                            Saisissez l'URL complète de la page frontend utilisée pour afficher la page pour l'animateur (cette page doit contenir le shortcode [inskill_buzzer_mode1_animateur]). Ex : https://buzzer.inskill.net/buzzer_mode1_animateur/
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">URL animateur (automatique)</th>
                    <td>
                        <input type="text" value="<?php echo esc_attr($url_animateur); ?>" class="regular-text" disabled />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode1_animateur_password">Mot de passe animateur</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode1_animateur_password" id="inskill_buzzer_mode1_animateur_password" value="<?php echo esc_attr($animateur_password); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode1_frontend_participant_url">URL de la page frontend participant</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode1_frontend_participant_url" id="inskill_buzzer_mode1_frontend_participant_url" value="<?php echo esc_attr($frontend_participant_url); ?>" class="regular-text" />
                        <p class="description">
                            Saisissez l'URL complète de la page frontend utilisée pour afficher la page pour le participant (cette page doit contenir le shortcode [inskill_buzzer_mode1]). Ex : https://buzzer.inskill.net/buzzer_mode1/
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">URL participant (automatique)</th>
                    <td>
                        <input type="text" value="<?php echo esc_attr($url_participant); ?>" class="regular-text" disabled />
                    </td>
                </tr>
            </table>
            <?php submit_button('Enregistrer les paramètres'); ?>
        </form>
        <script>
            document.getElementById('generate_mode1_id').addEventListener('click', function() {
                var randomId = Math.floor(100000 + Math.random() * 900000);
                document.getElementById('inskill_buzzer_mode1_activity_id').value = randomId;
            });
        </script>
        <?php
    }

    // --- Formulaire Mode 2 : Buzzer – L'union fait la force ---
    public function render_mode2_form() {
        $activity_name          = get_option('inskill_buzzer_mode2_activity_name', '');
        $activity_id            = get_option('inskill_buzzer_mode2_activity_id', '');
        $team1_name             = get_option('inskill_buzzer_mode2_team1_name', '');
        $team2_name             = get_option('inskill_buzzer_mode2_team2_name', '');
        $team3_name             = get_option('inskill_buzzer_mode2_team3_name', '');
        $team4_name             = get_option('inskill_buzzer_mode2_team4_name', '');
        $frontend_animateur_url = get_option('inskill_buzzer_mode2_frontend_animateur_url', '');
        $animateur_password     = get_option('inskill_buzzer_mode2_animateur_password', '');
        $frontend_participant_url = get_option('inskill_buzzer_mode2_frontend_participant_url', '');
        
        $url_animateur   = ($frontend_animateur_url && $activity_id) ? trailingslashit($frontend_animateur_url) . '?inskill_buzzer_mode2_animateur=' . $activity_id : '';
        $url_participant = ($frontend_participant_url && $activity_id) ? trailingslashit($frontend_participant_url) . '?inskill_buzzer_mode2=' . $activity_id : '';
        ?>
        <form method="post" action="">
            <input type="hidden" name="inskill_buzzer_mode" value="mode2" />
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode2_activity_name">Nom de l'activité</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode2_activity_name" id="inskill_buzzer_mode2_activity_name" value="<?php echo esc_attr($activity_name); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode2_activity_id">ID de l'activité (6 chiffres)</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode2_activity_id" id="inskill_buzzer_mode2_activity_id" value="<?php echo esc_attr($activity_id); ?>" class="regular-text" />
                        <button type="button" id="generate_mode2_id" class="button">Générer un ID aléatoire</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode2_team1_name">Nom de l'équipe 1</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode2_team1_name" id="inskill_buzzer_mode2_team1_name" value="<?php echo esc_attr($team1_name); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode2_team2_name">Nom de l'équipe 2</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode2_team2_name" id="inskill_buzzer_mode2_team2_name" value="<?php echo esc_attr($team2_name); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode2_team3_name">Nom de l'équipe 3</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode2_team3_name" id="inskill_buzzer_mode2_team3_name" value="<?php echo esc_attr($team3_name); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode2_team4_name">Nom de l'équipe 4</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode2_team4_name" id="inskill_buzzer_mode2_team4_name" value="<?php echo esc_attr($team4_name); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode2_frontend_animateur_url">URL de la page frontend animateur</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode2_frontend_animateur_url" id="inskill_buzzer_mode2_frontend_animateur_url" value="<?php echo esc_attr($frontend_animateur_url); ?>" class="regular-text" />
                        <p class="description">
                            Saisissez l'URL complète de la page frontend utilisée pour afficher la page pour l'animateur (cette page doit contenir le shortcode [inskill_buzzer_mode2_animateur]). Ex : https://buzzer.inskill.net/buzzer_mode2_animateur/
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">URL animateur (automatique)</th>
                    <td>
                        <input type="text" value="<?php echo esc_attr($url_animateur); ?>" class="regular-text" disabled />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode2_animateur_password">Mot de passe animateur</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode2_animateur_password" id="inskill_buzzer_mode2_animateur_password" value="<?php echo esc_attr($animateur_password); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode2_frontend_participant_url">URL de la page frontend participant</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode2_frontend_participant_url" id="inskill_buzzer_mode2_frontend_participant_url" value="<?php echo esc_attr($frontend_participant_url); ?>" class="regular-text" />
                        <p class="description">
                            Saisissez l'URL complète de la page frontend utilisée pour afficher la page pour le participant (cette page doit contenir le shortcode [inskill_buzzer_mode2]). Ex : https://buzzer.inskill.net/buzzer_mode2/
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">URL participant (automatique)</th>
                    <td>
                        <input type="text" value="<?php echo esc_attr($url_participant); ?>" class="regular-text" disabled />
                    </td>
                </tr>
            </table>
            <?php submit_button('Enregistrer les paramètres'); ?>
        </form>
        <script>
            document.getElementById('generate_mode2_id').addEventListener('click', function() {
                var randomId = Math.floor(100000 + Math.random() * 900000);
                document.getElementById('inskill_buzzer_mode2_activity_id').value = randomId;
            });
        </script>
        <?php
    }

    // --- Formulaire Mode 3 : Quiz – Chacun pour sa peau ---
    public function render_mode3_form() {
        $activity_name          = get_option('inskill_buzzer_mode3_activity_name', '');
        $activity_id            = get_option('inskill_buzzer_mode3_activity_id', '');
        $frontend_animateur_url = get_option('inskill_buzzer_mode3_frontend_animateur_url', '');
        $animateur_password     = get_option('inskill_buzzer_mode3_animateur_password', '');
        $frontend_participant_url = get_option('inskill_buzzer_mode3_frontend_participant_url', '');
        
        $url_animateur   = ($frontend_animateur_url && $activity_id) ? trailingslashit($frontend_animateur_url) . '?inskill_buzzer_mode3_animateur=' . $activity_id : '';
        $url_participant = ($frontend_participant_url && $activity_id) ? trailingslashit($frontend_participant_url) . '?inskill_buzzer_mode3=' . $activity_id : '';
        ?>
        <form method="post" action="">
            <input type="hidden" name="inskill_buzzer_mode" value="mode3" />
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode3_activity_name">Nom de l'activité</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode3_activity_name" id="inskill_buzzer_mode3_activity_name" value="<?php echo esc_attr($activity_name); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode3_activity_id">ID de l'activité (6 chiffres)</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode3_activity_id" id="inskill_buzzer_mode3_activity_id" value="<?php echo esc_attr($activity_id); ?>" class="regular-text" />
                        <button type="button" id="generate_mode3_id" class="button">Générer un ID aléatoire</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode3_frontend_animateur_url">URL de la page frontend animateur</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode3_frontend_animateur_url" id="inskill_buzzer_mode3_frontend_animateur_url" value="<?php echo esc_attr($frontend_animateur_url); ?>" class="regular-text" />
                        <p class="description">
                            Saisissez l'URL complète de la page frontend utilisée pour afficher la page pour l'animateur (cette page doit contenir le shortcode [inskill_buzzer_mode3_animateur]). Ex : https://buzzer.inskill.net/buzzer_mode3_animateur/
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">URL animateur (automatique)</th>
                    <td>
                        <input type="text" value="<?php echo esc_attr($url_animateur); ?>" class="regular-text" disabled />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode3_animateur_password">Mot de passe animateur</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode3_animateur_password" id="inskill_buzzer_mode3_animateur_password" value="<?php echo esc_attr($animateur_password); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode3_frontend_participant_url">URL de la page frontend participant</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode3_frontend_participant_url" id="inskill_buzzer_mode3_frontend_participant_url" value="<?php echo esc_attr($frontend_participant_url); ?>" class="regular-text" />
                        <p class="description">
                            Saisissez l'URL complète de la page frontend utilisée pour afficher la page pour le participant (cette page doit contenir le shortcode [inskill_buzzer_mode3]). Ex : https://buzzer.inskill.net/buzzer_mode3/
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">URL participant (automatique)</th>
                    <td>
                        <input type="text" value="<?php echo esc_attr($url_participant); ?>" class="regular-text" disabled />
                    </td>
                </tr>
            </table>
            <?php submit_button('Enregistrer les paramètres'); ?>
        </form>
        <script>
            document.getElementById('generate_mode3_id').addEventListener('click', function() {
                var randomId = Math.floor(100000 + Math.random() * 900000);
                document.getElementById('inskill_buzzer_mode3_activity_id').value = randomId;
            });
        </script>
        <?php
    }

    // --- Formulaire Mode 4 : Quiz – L'union fait la force ---
    public function render_mode4_form() {
        $activity_name          = get_option('inskill_buzzer_mode4_activity_name', '');
        $activity_id            = get_option('inskill_buzzer_mode4_activity_id', '');
        $team1_name             = get_option('inskill_buzzer_mode4_team1_name', '');
        $team2_name             = get_option('inskill_buzzer_mode4_team2_name', '');
        $team3_name             = get_option('inskill_buzzer_mode4_team3_name', '');
        $team4_name             = get_option('inskill_buzzer_mode4_team4_name', '');
        $frontend_animateur_url = get_option('inskill_buzzer_mode4_frontend_animateur_url', '');
        $animateur_password     = get_option('inskill_buzzer_mode4_animateur_password', '');
        $frontend_participant_url = get_option('inskill_buzzer_mode4_frontend_participant_url', '');
        
        $url_animateur   = ($frontend_animateur_url && $activity_id) ? trailingslashit($frontend_animateur_url) . '?inskill_buzzer_mode4_animateur=' . $activity_id : '';
        $url_participant = ($frontend_participant_url && $activity_id) ? trailingslashit($frontend_participant_url) . '?inskill_buzzer_mode4=' . $activity_id : '';
        ?>
        <form method="post" action="">
            <input type="hidden" name="inskill_buzzer_mode" value="mode4" />
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode4_activity_name">Nom de l'activité</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode4_activity_name" id="inskill_buzzer_mode4_activity_name" value="<?php echo esc_attr($activity_name); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode4_activity_id">ID de l'activité (6 chiffres)</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode4_activity_id" id="inskill_buzzer_mode4_activity_id" value="<?php echo esc_attr($activity_id); ?>" class="regular-text" />
                        <button type="button" id="generate_mode4_id" class="button">Générer un ID aléatoire</button>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode4_team1_name">Nom de l'équipe 1</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode4_team1_name" id="inskill_buzzer_mode4_team1_name" value="<?php echo esc_attr($team1_name); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode4_team2_name">Nom de l'équipe 2</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode4_team2_name" id="inskill_buzzer_mode4_team2_name" value="<?php echo esc_attr($team2_name); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode4_team3_name">Nom de l'équipe 3</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode4_team3_name" id="inskill_buzzer_mode4_team3_name" value="<?php echo esc_attr($team3_name); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode4_team4_name">Nom de l'équipe 4</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode4_team4_name" id="inskill_buzzer_mode4_team4_name" value="<?php echo esc_attr($team4_name); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode4_frontend_animateur_url">URL de la page frontend animateur</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode4_frontend_animateur_url" id="inskill_buzzer_mode4_frontend_animateur_url" value="<?php echo esc_attr($frontend_animateur_url); ?>" class="regular-text" />
                        <p class="description">
                            Saisissez l'URL complète de la page frontend utilisée pour afficher la page pour l'animateur (cette page doit contenir le shortcode [inskill_buzzer_mode4_animateur]). Ex : https://buzzer.inskill.net/buzzer_mode4_animateur/
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">URL animateur (automatique)</th>
                    <td>
                        <input type="text" value="<?php echo esc_attr($url_animateur); ?>" class="regular-text" disabled />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode4_animateur_password">Mot de passe animateur</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode4_animateur_password" id="inskill_buzzer_mode4_animateur_password" value="<?php echo esc_attr($animateur_password); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="inskill_buzzer_mode4_frontend_participant_url">URL de la page frontend participant</label></th>
                    <td>
                        <input type="text" name="inskill_buzzer_mode4_frontend_participant_url" id="inskill_buzzer_mode4_frontend_participant_url" value="<?php echo esc_attr($frontend_participant_url); ?>" class="regular-text" />
                        <p class="description">
                            Saisissez l'URL complète de la page frontend utilisée pour afficher la page pour le participant (cette page doit contenir le shortcode [inskill_buzzer_mode4]). Ex : https://buzzer.inskill.net/buzzer_mode4/
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">URL participant (automatique)</th>
                    <td>
                        <input type="text" value="<?php echo esc_attr($url_participant); ?>" class="regular-text" disabled />
                    </td>
                </tr>
            </table>
            <?php submit_button('Enregistrer les paramètres'); ?>
        </form>
        <script>
            document.getElementById('generate_mode4_id').addEventListener('click', function() {
                var randomId = Math.floor(100000 + Math.random() * 900000);
                document.getElementById('inskill_buzzer_mode4_activity_id').value = randomId;
            });
        </script>
        <?php
    }
}
