<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InskillBuzzer_Shortcodes_Mode1 extends InskillBuzzer_Shortcodes {

    public function __construct() {
        add_shortcode('inskill_buzzer_mode1', array($this, 'render_mode1_participant'));
        add_shortcode('inskill_buzzer_mode1_animateur', array($this, 'render_mode1_animateur'));
    }

    public function render_mode1_participant($atts) {
        ob_start();
        ?>
        <style>
        .participant-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f0f0f0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .participant-container h2 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            line-height: 1.2;
        }
        .participant-container h2 span {
            display: block;
            font-size: 0.8em;
            font-weight: normal;
        }
        .participant-container form {
            text-align: center;
            margin-bottom: 20px;
        }
        .participant-container form input[type="text"] {
            width: 80%;
            padding: 10px;
            font-size: 16px;
        }
        .participant-container form button {
            padding: 10px 20px;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
        }
        #buzzer_container {
            text-align: center;
            margin-bottom: 20px;
        }
        #buzzer_button {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background-color: #8B0000; /* blood red */
            border: 5px solid #000;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
        }
        #scoreboard {
            text-align: center;
        }
        </style>
        
        <div class="participant-container">
            <h2>Prêt à buzzer !<br><span>Mode : "Chacun pour sa peau !"</span></h2>
            <form id="participant_form" method="post">
                <label for="participant_name">Nom Prénom :</label><br>
                <input type="text" name="participant_name" id="participant_name" required>
                <br>
                <button type="submit" id="validate_button" name="validate_participant">Valider</button>
            </form>
            <!-- Le bouton buzzer et le classement sont cachés initialement -->
            <div id="buzzer_container" style="display:none;">
                <button id="buzzer_button">Buzzer</button>
            </div>
            <div id="scoreboard" style="display:none;">
                <h3>Classement</h3>
                <!-- Le classement complet sera mis à jour dynamiquement -->
            </div>
        </div>
        <script>
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        document.addEventListener('DOMContentLoaded', function(){
            var participantForm = document.getElementById('participant_form');
            var participantNameInput = document.getElementById('participant_name');
            var validateButton = document.getElementById('validate_button');
            var buzzerButton = document.getElementById('buzzer_button');
            var hasBuzzed = false;

            // Lors de la validation du nom, créer le participant dans la table des scores
            if (participantForm) {
                participantForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (participantNameInput.value.trim() === "") {
                        alert("Veuillez saisir un nom.");
                        return;
                    }
                    // Appeler l'endpoint pour créer le participant s'il n'existe pas
                    var params = 'participant_name=' + encodeURIComponent(participantNameInput.value);
                    fetch(ajaxurl + '?action=create_participant', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: params
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Indépendamment de la réponse, désactiver le champ et masquer le bouton de validation
                        participantNameInput.disabled = true;
                        validateButton.style.display = 'none';
                        document.getElementById('buzzer_container').style.display = 'block';
                        document.getElementById('scoreboard').style.display = 'block';
                    });
                });
            }
            if(buzzerButton) {
                buzzerButton.addEventListener('click', function(){
                    if(hasBuzzed) return;
                    var participantName = participantNameInput.value;
                    if (participantName.trim() === "") {
                        alert("Nom manquant.");
                        return;
                    }
                    var params = 'participant_name=' + encodeURIComponent(participantName);
                    fetch(ajaxurl + '?action=record_buzz_score', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: params
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            buzzerButton.style.backgroundColor = 'green';
                            buzzerButton.textContent = 'Buzzed!';
                            hasBuzzed = true;
                        } else {
                            alert('Erreur : ' + (data.message || 'Erreur inconnue'));
                        }
                    })
                    .catch(function(error){
                        alert('Erreur : ' + error);
                    });
                });
            }
            // Mise à jour dynamique du classement complet
            function updateRanking() {
                fetch(ajaxurl + '?action=get_full_ranking')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var ranking = data.data;
                        var html = "";
                        if(ranking.length > 0) {
                            html = ranking.map(function(item, index) {
                                return (index + 1) + '. ' + item.participant_name + ' - ' + item.score + ' pts';
                            }).join('<br>');
                        } else {
                            html = "Aucun score enregistré.";
                        }
                        document.getElementById('scoreboard').innerHTML = "<h3>Classement</h3>" + html;
                    }
                });
            }
            setInterval(updateRanking, 2000);
            // Réinitialisation des buzzers (ne touche pas aux scores)
            setInterval(function(){
                fetch(ajaxurl + '?action=get_top3_score')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var top3 = data.data;
                        if(top3.length === 0 && hasBuzzed) {
                            buzzerButton.style.backgroundColor = '#8B0000';
                            buzzerButton.textContent = 'Buzzer';
                            hasBuzzed = false;
                        }
                    }
                });
            }, 2000);
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function render_mode1_animateur($atts) {
        ob_start();
        ?>
        <style>
        .animateur-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f0f0f0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .animateur-container h2 {
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
            line-height: 1.2;
        }
        .animateur-container h2 span {
            font-size: 0.8em;
            font-weight: normal;
            display: block;
        }
        .animateur-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .animateur-content > div {
            padding: 10px;
        }
        </style>
        <div class="animateur-container">
            <h2>
                Buzzer : Chacun pour sa peau<br>
                <span>(Interface Animateur)</span>
            </h2>
            <div class="animateur-content">
                <div style="text-align:center;">
                    <button id="reset_buzzers">Réinitialisation des buzzers</button>
                </div>
                <div>
                    <h3 style="text-align:center;">Les 3 plus rapides</h3>
                    <div id="top3">
                        <!-- Mise à jour dynamique avec positions -->
                    </div>
                </div>
                <div>
                    <h3 style="text-align:center;">Résultats</h3>
                    <div style="text-align:center;">
                        <button id="good_button" style="background-color:green;">Goooood !</button>
                        <button id="bad_button" style="background-color:red;">No good ...</button>
                    </div>
                    <div style="display:flex; justify-content: center; gap: 20px; margin-top: 10px;">
                        <div>
                            <label for="points_ok">Réponse OK :</label>
                            <input type="number" id="points_ok" value="1" min="1">
                        </div>
                        <div>
                            <label for="points_nok">Réponse NOK :</label>
                            <input type="number" id="points_nok" value="-1">
                        </div>
                    </div>
                </div>
                <div>
                    <h3 style="text-align:center;">Classement</h3>
                    <div id="scoreboard">
                        <!-- Le classement complet sera mis à jour dynamiquement -->
                    </div>
                </div>
                <div style="text-align:center;">
                    <button id="reset_activity">Réinitialisation de l'activité</button>
                </div>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            var top3Container = document.getElementById('top3');
            var scoreboard = document.getElementById('scoreboard');
            function updateTop3() {
                fetch(ajaxurl + '?action=get_top3_score')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var top3 = data.data;
                        var html = "";
                        if(top3.length > 0) {
                            html = top3.map(function(item, index) {
                                return (index + 1) + '. ' + item.participant_name;
                            }).join('<br>');
                        } else {
                            html = 'Aucun participant buzzé.';
                        }
                        top3Container.innerHTML = html;
                    }
                });
            }
            function updateRanking() {
                fetch(ajaxurl + '?action=get_full_ranking')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var ranking = data.data;
                        var html = "";
                        if(ranking.length > 0) {
                            html = ranking.map(function(item, index) {
                                return (index + 1) + '. ' + item.participant_name + ' - ' + item.score + ' pts';
                            }).join('<br>');
                        } else {
                            html = 'Aucun score enregistré.';
                        }
                        scoreboard.innerHTML = "<h3>Classement</h3>" + html;
                    }
                });
            }
            setInterval(function(){
                updateTop3();
                updateRanking();
            }, 2000);
            var resetButton = document.getElementById('reset_buzzers');
            if(resetButton) {
                resetButton.addEventListener('click', function(){
                    fetch(ajaxurl + '?action=reset_buzz', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            updateTop3();
                        }
                    });
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
