<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InskillBuzzer_Shortcodes_Mode4 extends InskillBuzzer_Shortcodes {

    public function __construct() {
        add_shortcode('inskill_buzzer_mode4', array($this, 'render_mode4_participant'));
        add_shortcode('inskill_buzzer_mode4_animateur', array($this, 'render_mode4_animateur'));
    }

    public function render_mode4_participant($atts) {
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
        </style>
        
        <div class="participant-container">
            <h2>Quiz : L'union fait la force - Participant</h2>
            <form id="participant_form_mode4" method="post">
                <label for="participant_name_mode4">Nom Prénom :</label><br>
                <input type="text" name="participant_name" id="participant_name_mode4" required>
                <br>
                <label for="participant_team_mode4">Équipe :</label><br>
                <select name="participant_team" id="participant_team_mode4">
                    <?php
                    $teams = array(
                        get_option('inskill_buzzer_mode4_team1_name', ''),
                        get_option('inskill_buzzer_mode4_team2_name', ''),
                        get_option('inskill_buzzer_mode4_team3_name', ''),
                        get_option('inskill_buzzer_mode4_team4_name', '')
                    );
                    foreach ($teams as $team) {
                        if ($team) {
                            echo '<option value="'.esc_attr($team).'">'.esc_html($team).'</option>';
                        }
                    }
                    ?>
                </select>
                <br>
                <button type="submit" id="validate_button_mode4" name="validate_participant">Valider</button>
            </form>
            <div id="quiz_buttons_mode4" style="display:none;">
                <?php
                foreach (array('A','B','C','D','E','F') as $answer) {
                    echo '<button class="answer_button" style="background-color:red;">'.$answer.'</button> ';
                }
                ?>
            </div>
            <div id="scoreboard_mode4" style="display:none;">
                <h3>Classement</h3>
            </div>
        </div>
        <script>
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        var currentParticipantMode4 = "";
        document.addEventListener('DOMContentLoaded', function(){
            var form = document.getElementById('participant_form_mode4');
            var nameInput = document.getElementById('participant_name_mode4');
            var validateBtn = document.getElementById('validate_button_mode4');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (nameInput.value.trim() === "") {
                    alert("Veuillez saisir un nom.");
                    return;
                }
                currentParticipantMode4 = nameInput.value.trim();
                var params = 'participant_name=' + encodeURIComponent(currentParticipantMode4);
                fetch(ajaxurl + '?action=create_participant_mode4', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: params
                })
                .then(response => response.json())
                .then(data => {
                    nameInput.disabled = true;
                    validateBtn.style.display = 'none';
                    document.getElementById('quiz_buttons_mode4').style.display = 'block';
                    document.getElementById('scoreboard_mode4').style.display = 'block';
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function render_mode4_animateur($atts) {
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
        #good_button_mode4 {
            background-color: green;
            color: #fff;
        }
        #bad_button_mode4 {
            background-color: #8B0000;
            color: #fff;
        }
        </style>
        <div class="animateur-container">
            <h2>Quiz : L'union fait la force<br><span>(Interface Animateur)</span></h2>
            <div>
                <button id="reset_answers_mode4" class="action-btn" style="background-color:#f0ad4e; color:#fff;">Réinitialisation des boutons-réponses</button>
            </div>
            <div>
                <h3>Les 3 plus rapides</h3>
                <div id="top3_mode4"></div>
            </div>
            <div>
                <h3>Résultats</h3>
                <button id="good_button_mode4" class="action-btn result-btn">Goooood !</button>
                <button id="bad_button_mode4" class="action-btn result-btn">No good ...</button>
                <div>
                    <label for="points_ok_mode4">Réponse OK :</label>
                    <input type="number" id="points_ok_mode4" value="1" min="1">
                </div>
                <div>
                    <label for="points_nok_mode4">Réponse NOK :</label>
                    <input type="number" id="points_nok_mode4" value="-1">
                </div>
            </div>
            <div>
                <h3>Classement des équipes</h3>
                <div id="team_scoreboard_mode4"></div>
            </div>
            <div>
                <h3>Participants connectés</h3>
                <div id="participants_list_mode4"></div>
            </div>
            <div>
                <button id="reset_activity_mode4" class="action-btn" style="background-color:#8B0000; color:#fff;">Réinitialisation de l'activité</button>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            var top3Container = document.getElementById('top3_mode4');
            var teamScoreboard = document.getElementById('team_scoreboard_mode4');
            var participantsList = document.getElementById('participants_list_mode4');
            var goodButton = document.getElementById('good_button_mode4');
            var badButton = document.getElementById('bad_button_mode4');

            function updateTop3() {
                fetch(ajaxurl + '?action=get_top3_score_mode4')
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
                fetch(ajaxurl + '?action=get_full_ranking_mode4')
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
                                fetch(ajaxurl + '?action=update_participant_score_mode4', {
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
                                    fetch(ajaxurl + '?action=remove_participant_mode4', {
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
            var resetButton = document.getElementById('reset_answers_mode4');
            if(resetButton) {
                resetButton.addEventListener('click', function(){
                    fetch(ajaxurl + '?action=reset_buzzers_all_mode4', { method: 'POST' })
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
            var resetActivityButton = document.getElementById('reset_activity_mode4');
            if(resetActivityButton) {
                resetActivityButton.addEventListener('click', function(){
                    if(confirm('Supprimer TOUS les participants ?')) {
                        fetch(ajaxurl + '?action=remove_all_participants_mode4', { method: 'POST' })
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
                    var delta = parseInt(document.getElementById('points_ok_mode4').value);
                    if(isNaN(delta) || delta < 1) {
                        alert('La valeur pour Réponse OK doit être un entier >= 1.');
                        return;
                    }
                    var params = 'delta=' + encodeURIComponent(delta);
                    fetch(ajaxurl + '?action=update_first_score_mode4', {
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
                    var delta = parseInt(document.getElementById('points_nok_mode4').value);
                    if(isNaN(delta) || delta > 0) {
                        alert('La valeur pour Réponse NOK doit être un entier <= 0.');
                        return;
                    }
                    var params = 'delta=' + encodeURIComponent(delta);
                    fetch(ajaxurl + '?action=update_first_score_mode4', {
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
