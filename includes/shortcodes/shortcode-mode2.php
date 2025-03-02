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
        }
        .participant-container form {
            text-align: center;
            margin-bottom: 20px;
        }
        .participant-container form input[type="text"],
        .participant-container form select {
            width: 80%;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .participant-container form button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
        /* Bouton buzzer identique à celui du mode 1 */
        #buzzer_container_mode2 {
            text-align: center;
            margin-bottom: 20px;
        }
        #buzzer_button_mode2 {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background-color: #8B0000;
            border: 5px solid #000;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
        }
        table.participant-ranking-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table.participant-ranking-table th, table.participant-ranking-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table.participant-ranking-table th {
            background-color: #00285B;
            color: #fff;
        }
        </style>
        
        <div class="participant-container">
            <!-- Titre en 2 lignes -->
            <h2>Prêt à buzzer !<br>Mode : "L'union fait la force"</h2>
            <form id="participant_form_mode2" method="post">
                <label for="participant_name_mode2">Nom Prénom :</label><br>
                <input type="text" name="participant_name" id="participant_name_mode2" required>
                <br>
                <label for="participant_team_mode2">Équipe :</label><br>
                <select name="participant_team" id="participant_team_mode2">
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
                <br>
                <button type="submit" id="validate_button_mode2" name="validate_participant">Valider</button>
            </form>
            <!-- Bouton buzzer et scoreboard initialement cachés -->
            <div id="buzzer_container_mode2" style="display:none;">
                <button id="buzzer_button_mode2">Buzzer</button>
            </div>
            <div id="scoreboard_mode2" style="display:none;">
                <h3>Classement</h3>
            </div>
        </div>
        <script>
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        var currentParticipantMode2 = "";
        document.addEventListener('DOMContentLoaded', function(){
            var form = document.getElementById('participant_form_mode2');
            var nameInput = document.getElementById('participant_name_mode2');
            var validateBtn = document.getElementById('validate_button_mode2');
            var buzzerBtn = document.getElementById('buzzer_button_mode2');
            var hasBuzzed = false;

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (nameInput.value.trim() === "") {
                    alert("Veuillez saisir un nom.");
                    return;
                }
                currentParticipantMode2 = nameInput.value.trim();
                var params = 'participant_name=' + encodeURIComponent(currentParticipantMode2);
                fetch(ajaxurl + '?action=create_participant_mode2', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: params
                })
                .then(response => response.json())
                .then(data => {
                    nameInput.disabled = true;
                    validateBtn.style.display = 'none';
                    document.getElementById('buzzer_container_mode2').style.display = 'block';
                    document.getElementById('scoreboard_mode2').style.display = 'block';
                });
            });

            buzzerBtn.addEventListener('click', function(){
                if(hasBuzzed) return;
                if (currentParticipantMode2 === "") {
                    alert("Nom manquant.");
                    return;
                }
                var params = 'participant_name=' + encodeURIComponent(currentParticipantMode2);
                fetch(ajaxurl + '?action=record_buzz_score_mode2', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: params
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        buzzerBtn.style.backgroundColor = 'green';
                        buzzerBtn.textContent = 'Buzzed!';
                        hasBuzzed = true;
                    } else {
                        alert('Erreur : ' + (data.message || 'Erreur inconnue'));
                    }
                })
                .catch(function(error){
                    alert('Erreur : ' + error);
                });
            });

            function updateRanking() {
                fetch(ajaxurl + '?action=get_full_ranking_mode2')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var ranking = data.data;
                        var html = "";
                        if(ranking.length > 0) {
                            html += '<table class="participant-ranking-table"><tr><th>Nom Prénom</th><th>Score</th></tr>';
                            ranking.forEach(function(item) {
                                html += '<tr><td>' + item.participant_name + '</td><td>' + item.score + '</td></tr>';
                            });
                            html += '</table>';
                        } else {
                            html = "Aucun score enregistré.";
                        }
                        document.getElementById('scoreboard_mode2').innerHTML = '<h3>Classement</h3>' + html;
                    }
                });
            }
            setInterval(updateRanking, 2000);
            setInterval(function(){
                fetch(ajaxurl + '?action=get_top3_score_mode2')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var top3 = data.data;
                        if(top3.length === 0 && hasBuzzed) {
                            buzzerBtn.style.backgroundColor = '#8B0000';
                            buzzerBtn.textContent = 'Buzzer';
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

    public function render_mode2_animateur($atts) {
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
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            line-height: 1.2;
        }
        .result-row {
            margin-bottom: 20px;
        }
        table.ranking-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table.ranking-table th, table.ranking-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table.ranking-table th {
            background-color: #00285B;
            color: #fff;
        }
        .action-btn {
            cursor: pointer;
            padding: 10px 20px;
            font-size: 18px;
            height: 50px;
            border: none;
            border-radius: 3px;
        }
        .result-btn {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid #000;
            font-size: 24px;
            cursor: pointer;
        }
        #good_button_mode2 {
            background-color: green;
            color: #fff;
        }
        #bad_button_mode2 {
            background-color: #8B0000;
            color: #fff;
        }
        .remove-all-btn {
            background-color: #8B0000;
            color: #fff;
            height: 50px;
            font-size: 18px;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        </style>
        <div class="animateur-container">
            <!-- Titre en 3 lignes identique au mode 1 -->
            <h2>
                Mode Buzzer<br>
                "L'union fait la force"<br>
                (Interface Animateur)
            </h2>
            <div style="text-align:left; margin-bottom:20px;">
                <button id="reset_buzzers_mode2" class="action-btn" style="background-color:#f0ad4e; color:#fff;">Réinitialisation des buzzers</button>
            </div>
            <div>
                <h3 style="text-align:left;">Les 3 plus rapides</h3>
                <div id="top3_mode2"></div>
            </div>
            <div>
                <h3 style="text-align:left;">Résultats</h3>
                <div class="result-row">
                    <button id="good_button_mode2" class="action-btn result-btn">Good !</button>
                    <label>Réponse OK : 
                        <select id="points_ok_mode2" style="width:60px;">
                            <?php for ($i=1;$i<=10;$i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </label>
                </div>
                <div class="result-row">
                    <button id="bad_button_mode2" class="action-btn result-btn">No good !</button>
                    <label>Réponse NOK : 
                        <select id="points_nok_mode2" style="width:60px;">
                            <?php for ($i=1;$i<=10;$i++):
                                $val = -$i; ?>
                                <option value="<?php echo $val; ?>"><?php echo $val; ?></option>
                            <?php endfor; ?>
                        </select>
                    </label>
                </div>
            </div>
            <div>
                <h3 style="text-align:left;">Classement des équipes</h3>
                <div id="team_scoreboard_mode2"></div>
            </div>
            <div>
                <h3 style="text-align:left;">Participants connectés</h3>
                <div id="participants_list_mode2"></div>
            </div>
            <div>
                <!-- Bouton renommé en "Supprimer tous les participants" -->
                <button id="reset_activity_mode2" class="remove-all-btn">Supprimer tous les participants</button>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            var top3Container = document.getElementById('top3_mode2');
            var teamScoreboard = document.getElementById('team_scoreboard_mode2');
            var participantsList = document.getElementById('participants_list_mode2');
            var goodButton = document.getElementById('good_button_mode2');
            var badButton = document.getElementById('bad_button_mode2');

            function updateTop3() {
                fetch(ajaxurl + '?action=get_top3_score_mode2')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var top3 = data.data;
                        var html = "";
                        if(top3.length > 0) {
                            html = '<ul style="list-style:none; padding:0;">';
                            top3.forEach(function(item, index) {
                                html += '<li>' + (index + 1) + '. ' + item.participant_name + '</li>';
                            });
                            html += '</ul>';
                        } else {
                            html = 'Aucun participant buzzé.';
                        }
                        top3Container.innerHTML = html;
                    }
                });
            }
            function updateRanking() {
                fetch(ajaxurl + '?action=get_full_ranking_mode2')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var ranking = data.data;
                        var html = "";
                        if(ranking.length > 0) {
                            html += '<table class="ranking-table"><tr><th>#</th><th>Nom Prénom</th><th>Score</th><th>Bonus/Malus</th><th>Ejecter</th></tr>';
                            ranking.forEach(function(item, index) {
                                html += '<tr>';
                                html += '<td>' + (index + 1) + '</td>';
                                html += '<td>' + item.participant_name + '</td>';
                                html += '<td>' + item.score + '</td>';
                                html += '<td>';
                                for(var i = -3; i <= -1; i++){
                                    html += '<button class="action-btn bonus-btn" data-name="'+item.participant_name+'" data-delta="'+i+'">'+i+'</button> ';
                                }
                                for(var i = 1; i <= 3; i++){
                                    html += '<button class="action-btn bonus-btn" data-name="'+item.participant_name+'" data-delta="'+i+'">'+i+'</button> ';
                                }
                                html += '</td>';
                                html += '<td><button class="action-btn eject-btn" data-name="'+item.participant_name+'" style="background-color:#8B0000; color:#fff;">Ejecter</button></td>';
                                html += '</tr>';
                            });
                            html += '</table>';
                        } else {
                            html = 'Aucun score enregistré.';
                        }
                        teamScoreboard.innerHTML = html;
                        
                        document.querySelectorAll('.bonus-btn').forEach(function(btn) {
                            btn.addEventListener('click', function(){
                                var participantName = this.getAttribute('data-name');
                                var delta = parseInt(this.getAttribute('data-delta'));
                                var params = 'participant_name=' + encodeURIComponent(participantName) + '&delta=' + encodeURIComponent(delta);
                                fetch(ajaxurl + '?action=update_participant_score_mode2', {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                    body: params
                                })
                                .then(response => response.json())
                                .then(data => {
                                    updateRanking();
                                });
                            });
                        });
                        
                        document.querySelectorAll('.eject-btn').forEach(function(btn) {
                            btn.addEventListener('click', function(){
                                var participantName = this.getAttribute('data-name');
                                if(confirm('Ejecter ' + participantName + ' ?')) {
                                    var params = 'participant_name=' + encodeURIComponent(participantName);
                                    fetch(ajaxurl + '?action=remove_participant_mode2', {
                                        method: 'POST',
                                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                        body: params
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if(data.success) {
                                            updateRanking();
                                        } else {
                                            alert('Erreur lors de l\'éjection : ' + data.message);
                                        }
                                    });
                                }
                            });
                        });
                    }
                });
            }
            setInterval(function(){
                updateTop3();
                updateRanking();
            }, 2000);
            var resetButton = document.getElementById('reset_buzzers_mode2');
            if(resetButton) {
                resetButton.addEventListener('click', function(){
                    fetch(ajaxurl + '?action=reset_buzzers_all_mode2', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            updateTop3();
                            updateRanking();
                            goodButton.disabled = false;
                            goodButton.style.backgroundColor = 'green';
                            goodButton.style.color = '#fff';
                            badButton.disabled = false;
                            badButton.style.backgroundColor = '#8B0000';
                            badButton.style.color = '#fff';
                        } else {
                            alert('Erreur lors de la réinitialisation : ' + data.message);
                        }
                    });
                });
            }
            var resetActivityButton = document.getElementById('reset_activity_mode2');
            if(resetActivityButton) {
                resetActivityButton.addEventListener('click', function(){
                    if(confirm('Supprimer TOUS les participants ?')) {
                        fetch(ajaxurl + '?action=remove_all_participants_mode2', { method: 'POST' })
                        .then(response => response.json())
                        .then(data => {
                            if(data.success) {
                                updateTop3();
                                updateRanking();
                                goodButton.disabled = false;
                                goodButton.style.backgroundColor = 'green';
                                goodButton.style.color = '#fff';
                                badButton.disabled = false;
                                badButton.style.backgroundColor = '#8B0000';
                                badButton.style.color = '#fff';
                            } else {
                                alert('Erreur lors de la suppression de tous les participants : ' + data.message);
                            }
                        });
                    }
                });
            }
            if(goodButton) {
                goodButton.addEventListener('click', function(){
                    var delta = parseInt(document.getElementById('points_ok_mode2').value);
                    if(isNaN(delta) || delta < 1) {
                        alert('La valeur pour Réponse OK doit être un entier >= 1.');
                        return;
                    }
                    var params = 'delta=' + encodeURIComponent(delta);
                    fetch(ajaxurl + '?action=update_first_score_mode2', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: params
                    })
                    .then(response => response.json())
                    .then(data => {
                        updateRanking();
                        goodButton.disabled = true;
                        goodButton.style.backgroundColor = '#ccc';
                        goodButton.style.color = '#000';
                        badButton.disabled = true;
                        badButton.style.backgroundColor = '#ccc';
                        badButton.style.color = '#000';
                    });
                });
            }
            if(badButton) {
                badButton.addEventListener('click', function(){
                    var delta = parseInt(document.getElementById('points_nok_mode2').value);
                    if(isNaN(delta) || delta > 0) {
                        alert('La valeur pour Réponse NOK doit être un entier <= 0.');
                        return;
                    }
                    var params = 'delta=' + encodeURIComponent(delta);
                    fetch(ajaxurl + '?action=update_first_score_mode2', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: params
                    })
                    .then(response => response.json())
                    .then(data => {
                        updateRanking();
                        goodButton.disabled = true;
                        goodButton.style.backgroundColor = '#ccc';
                        goodButton.style.color = '#000';
                        badButton.disabled = true;
                        badButton.style.backgroundColor = '#ccc';
                        badButton.style.color = '#000';
                    });
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
?>
