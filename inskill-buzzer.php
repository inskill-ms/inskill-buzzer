<?php
/**
 * Plugin Name: Inskill Buzzer
 * Plugin URI: https://buzzer.inskill.net
 * Description: Plugin pour mettre en place des activités pédagogiques sous forme de jeux avec des buzzers et des quiz.
 * Version: 1.0
 * Author: Votre Nom
 * Author URI: https://votresite.com
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Définition du chemin du plugin
define( 'INSKILL_BUZZER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'INSKILL_BUZZER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Inclusion des fichiers nécessaires
require_once INSKILL_BUZZER_PLUGIN_DIR . 'includes/class-install.php';
require_once INSKILL_BUZZER_PLUGIN_DIR . 'includes/class-admin.php';
require_once INSKILL_BUZZER_PLUGIN_DIR . 'includes/class-shortcodes.php';

// Activation et désinstallation
register_activation_hook( __FILE__, array( 'InskillBuzzer_Install', 'install' ) );
register_uninstall_hook( __FILE__, array( 'InskillBuzzer_Install', 'uninstall' ) );

// Initialisation du panneau d'administration (uniquement en back-office)
if ( is_admin() ) {
    new InskillBuzzer_Admin();
}
