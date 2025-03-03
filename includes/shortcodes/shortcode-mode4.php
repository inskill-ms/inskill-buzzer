<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InskillBuzzer_Shortcodes_Mode4 extends InskillBuzzer_Shortcodes {

    public function __construct() {
        add_shortcode('inskill_buzzer_mode4', array($this, 'render_mode4_participant'));
        add_shortcode('inskill_buzzer_mode4_animateur', array($this, 'render_mode4_animateur'));
    }

    // Interface Participant : Quiz – L'union fait la force
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
        /* Apparence des boutons réponses identique au mode 3 */
        #quiz_buttons_mode4 {
            text-align: center;
            margin-top: 20px;
        }
        #quiz_buttons_mode4 button.answer-btn {
            width: 80px;
            height: 80px;
            margin: 5px;
            border-radius: 10px;
            border: 2px solid #000;
            background-color: #8B0000;
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
            <h2>Prêt à répondre ! (QCM)<br>Mode : "L'union fait la force"</h2>
            <form id="participant_form_mode4" method="post">
                <label for="participant_name_mode4">Nom Prénom :</label><br>
                <input type="text" name="participant_name" id="participant_name_mode4" required>
                <br>
                <label for="participant_team_mode4">Équipe :</label><br>
                <select name="participant_team" id="participant_team_mode4" required>
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
                foreach (array('A','B','C','D','E','F') as $letter) {
                    echo '<button class="answer-btn" data-answer="'.esc_attr($letter).'">'.$letter.'</button> ';
                }
                ?>
            </div>
            <div id="scoreboard_mode4" style="display:none;">
                <h3>Classement des équipes</h3>
                <div id="team_ranking_mode4"></div>
            </div>
        </div>
        <script>
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        var currentParticipantMode4 = "";
        var hasAnswered = false; // Suivi si le participant a déjà répondu
        document.addEventListener('DOMContentLoaded', function(){
            var form = document.getElementById('participant_form_mode4');
            var nameInput = document.getElementById('participant_name_mode4');
            var teamSelect = document.getElementById('participant_team_mode4');
            var validateBtn = document.getElementById('validate_button_mode4');
            var quizButtonsContainer = document.getElementById('quiz_buttons_mode4');
            var answerButtons = document.querySelectorAll('#quiz_buttons_mode4 button.answer-btn');
            var scoreboard = document.getElementById('scoreboard_mode4');
            
            // Enregistrement du participant
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if(nameInput.value.trim() === ""){
                    alert("Veuillez saisir un nom.");
                    return;
                }
                currentParticipantMode4 = nameInput.value.trim();
                var selectedTeam = teamSelect.value;
                var params = 'participant_name=' + encodeURIComponent(currentParticipantMode4) + '&participant_team=' + encodeURIComponent(selectedTeam);
                fetch(ajaxurl + '?action=create_participant_mode4', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: params
                })
                .then(response => response.json())
                .then(data => {
                    nameInput.disabled = true;
                    teamSelect.disabled = true;
                    validateBtn.style.display = 'none';
                    quizButtonsContainer.style.display = 'block';
                    scoreboard.style.display = 'block';
                });
            });
            
            // Gestion du clic sur un bouton réponse
            answerButtons.forEach(function(btn){
                btn.addEventListener('click', function(){
                    if(btn.disabled) return;
                    var answer = btn.getAttribute('data-answer');
                    if(currentParticipantMode4 === ""){
                        alert("Nom manquant.");
                        return;
                    }
                    var params = 'participant_name=' + encodeURIComponent(currentParticipantMode4) + '&answer=' + encodeURIComponent(answer);
                    fetch(ajaxurl + '?action=record_answer_mode4', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: params
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success){
                            btn.style.backgroundColor = 'green';
                            // Désactiver tous les boutons réponses
                            answerButtons.forEach(function(otherBtn){
                                otherBtn.disabled = true;
                            });
                            hasAnswered = true;
                        } else {
                            alert('Erreur : ' + (data.message || 'Erreur inconnue'));
                        }
                    })
                    .catch(function(error){
                        alert('Erreur : ' + error);
                    });
                });
            });
            
            // Polling pour réactiver les boutons réponses si le transient est réinitialisé (comme en mode 3)
            setInterval(function(){
                fetch(ajaxurl + '?action=get_top3_score_mode4')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var top3 = data.data;
                        if(top3.length === 0 && hasAnswered){
                            answerButtons.forEach(function(btn){
                                btn.disabled = false;
                                btn.style.backgroundColor = '#8B0000';
                            });
                            hasAnswered = false;
                        }
                    }
                });
            }, 2000);
            
            // Mise à jour du classement des équipes côté participant
            function updateTeamRankingParticipant() {
                fetch(ajaxurl + '?action=get_full_ranking_mode4')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var ranking = data.data;
                        var html = "";
                        if(ranking.length > 0) {
                            html += '<table class="participant-ranking-table"><tr><th>#</th><th>Équipe</th><th>Score</th></tr>';
                            ranking.forEach(function(item, index) {
                                html += '<tr><td>' + (index+1) + '</td><td>' + item.team + '</td><td>' + item.score + '</td></tr>';
                            });
                            html += '</table>';
                        } else {
                            html = "Aucun score enregistré.";
                        }
                        document.getElementById('team_ranking_mode4').innerHTML = html;
                    }
                });
            }
            setInterval(updateTeamRankingParticipant, 2000);
            
            // Vérification de la présence du participant dans la liste des connectés
            setInterval(function(){
                fetch(ajaxurl + '?action=get_connected_participants_mode4')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var participants = data.data;
                        var found = participants.some(function(item){
                            return item.participant_name === currentParticipantMode4;
                        });
                        if(!found && currentParticipantMode4 !== "") {
                            alert("Vous avez été déconnecté.");
                            window.location.reload();
                        }
                    }
                });
            }, 2000);
            
            // Vérification via localStorage (pour forcer la déconnexion si demandé par l'animateur)
            setInterval(function(){
                if(localStorage.getItem('forceDisconnect') === "true") {
                    localStorage.removeItem('forceDisconnect');
                    alert("Vous avez été déconnecté.");
                    window.location.reload();
                }
            }, 2000);
        });
        </script>
        <?php
        return ob_get_clean();
    }

    // Interface Animateur
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
        #good_button_mode4 {
            background-color: green;
            color: #fff;
        }
        #bad_button_mode4 {
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
            <h2>
                Mode Quiz<br>
                L'union fait la force !<br>
                (Interface Animateur)
            </h2>
            <div style="text-align:left; margin-bottom:20px;">
                <button id="reset_answers_mode4" class="action-btn" style="background-color:#f0ad4e; color:#fff;">Réinitialiser les réponses</button>
            </div>
            <div>
                <h3 style="text-align:left;">Les 3 plus rapides</h3>
                <div id="top3_mode4">
                    <!-- Tableau à 4 colonnes sera inséré ici -->
                </div>
            </div>
            <div>
                <h3 style="text-align:left;">Résultats</h3>
                <div class="result-row">
                    <button id="good_button_mode4" class="result-btn action-btn">Good !</button>
                    <label>Réponse OK : 
                        <input type="number" id="points_ok_mode4" value="1" min="1" style="width:60px;">
                    </label>
                </div>
                <div class="result-row">
                    <button id="bad_button_mode4" class="result-btn action-btn">No good !</button>
                    <label>Réponse NOK : 
                        <input type="number" id="points_nok_mode4" value="-1" style="width:60px;">
                    </label>
                </div>
            </div>
            <div>
                <h3 style="text-align:left;">Classement des équipes</h3>
                <div id="team_scoreboard_mode4"></div>
            </div>
            <div>
                <h3 style="text-align:left;">Participants connectés</h3>
                <div id="participants_list_mode4"></div>
            </div>
            <div>
                <button id="reset_activity_mode4" class="remove-all-btn">Supprimer tous les participants</button>
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

            function updateTop3(){
                fetch(ajaxurl + '?action=get_top3_score_mode4')
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        var top3 = data.data;
                        var html = "";
                        if(top3.length > 0){
                            html += '<table class="ranking-table"><tr><th>#</th><th>Nom Prénom</th><th>Équipe</th><th>Réponse</th></tr>';
                            top3.forEach(function(item, index){
                                html += '<tr>';
                                html += '<td>' + (index+1) + '</td>';
                                html += '<td>' + item.participant_name + '</td>';
                                html += '<td>' + item.team + '</td>';
                                html += '<td>' + (item.answer ? item.answer : '-') + '</td>';
                                html += '</tr>';
                            });
                            html += '</table>';
                        } else {
                            html = 'Aucun participant n\'a répondu.';
                        }
                        top3Container.innerHTML = html;
                    }
                });
            }
            function updateTeamRanking(){
                fetch(ajaxurl + '?action=get_full_ranking_mode4')
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        var ranking = data.data;
                        var html = "";
                        if(ranking.length > 0){
                            html += '<table class="ranking-table"><tr><th>#</th><th>Équipe</th><th>Score</th><th>Bonus/Malus</th></tr>';
                            ranking.forEach(function(item, index){
                                html += '<tr>';
                                html += '<td>' + (index+1) + '</td>';
                                html += '<td>' + item.team + '</td>';
                                html += '<td>' + item.score + '</td>';
                                html += '<td>';
                                for(var i = -3; i <= -1; i++){
                                    html += '<button class="action-btn bonus-btn" data-team="'+item.team+'" data-delta="'+i+'">'+i+'</button> ';
                                }
                                for(var i = 1; i <= 3; i++){
                                    html += '<button class="action-btn bonus-btn" data-team="'+item.team+'" data-delta="'+i+'">'+i+'</button> ';
                                }
                                html += '</td>';
                                html += '</tr>';
                            });
                            html += '</table>';
                        } else {
                            html = 'Aucun score enregistré.';
                        }
                        teamScoreboard.innerHTML = html;
                        var bonusButtons = teamScoreboard.querySelectorAll('.bonus-btn');
                        bonusButtons.forEach(function(btn){
                            btn.addEventListener('click', function(){
                                var team = this.getAttribute('data-team');
                                var delta = parseInt(this.getAttribute('data-delta'));
                                var params = 'team=' + encodeURIComponent(team) + '&delta=' + encodeURIComponent(delta);
                                fetch(ajaxurl + '?action=update_participant_score_mode4', {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                    body: params
                                })
                                .then(response => response.json())
                                .then(data => {
                                    updateTeamRanking();
                                });
                            });
                        });
                    }
                });
            }
            function updateParticipantsList(){
                fetch(ajaxurl + '?action=get_connected_participants_mode4')
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        var participants = data.data;
                        var html = "";
                        if(participants.length > 0){
                            html += '<table class="ranking-table"><tr><th>Nom Prénom</th><th>Équipe</th><th>Action</th></tr>';
                            participants.forEach(function(item){
                                html += '<tr>';
                                html += '<td>' + item.participant_name + '</td>';
                                html += '<td>' + item.team + '</td>';
                                html += '<td><button class="action-btn eject-btn" data-name="'+item.participant_name+'" style="background-color:#8B0000; color:#fff;">Ejecter</button></td>';
                                html += '</tr>';
                            });
                            html += '</table>';
                        } else {
                            html = 'Aucun participant connecté.';
                        }
                        participantsList.innerHTML = html;
                        var ejectButtons = participantsList.querySelectorAll('.eject-btn');
                        ejectButtons.forEach(function(btn){
                            btn.addEventListener('click', function(){
                                var participantName = this.getAttribute('data-name');
                                if(confirm('Ejecter ' + participantName + ' ?')){
                                    var params = 'participant_name=' + encodeURIComponent(participantName);
                                    fetch(ajaxurl + '?action=remove_participant_mode4', {
                                        method: 'POST',
                                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                        body: params
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if(data.success){
                                            updateParticipantsList();
                                            updateTeamRanking();
                                            // Forcer la déconnexion côté participant
                                            localStorage.setItem('forceDisconnect', 'true');
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
                updateTeamRanking();
                updateParticipantsList();
            }, 2000);
            var resetAnswersBtn = document.getElementById('reset_answers_mode4');
            if(resetAnswersBtn){
                resetAnswersBtn.addEventListener('click', function(){
                    fetch(ajaxurl + '?action=reset_buzzers_all_mode4', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success){
                            updateTop3();
                            updateTeamRanking();
                            // Réactive les boutons Good/No good
                            document.getElementById('good_button_mode4').disabled = false;
                            document.getElementById('good_button_mode4').style.backgroundColor = 'green';
                            document.getElementById('bad_button_mode4').disabled = false;
                            document.getElementById('bad_button_mode4').style.backgroundColor = '#8B0000';
                        } else {
                            alert('Erreur lors de la réinitialisation : ' + data.message);
                        }
                    });
                });
            }
            var resetActivityBtn = document.getElementById('reset_activity_mode4');
            if(resetActivityBtn){
                resetActivityBtn.addEventListener('click', function(){
                    if(confirm('Supprimer TOUS les participants ?')){
                        fetch(ajaxurl + '?action=remove_all_participants_mode4', { method: 'POST' })
                        .then(response => response.json())
                        .then(data => {
                            if(data.success){
                                updateTop3();
                                updateTeamRanking();
                                updateParticipantsList();
                                localStorage.setItem('forceDisconnect', 'true');
                            } else {
                                alert('Erreur lors de la suppression de tous les participants : ' + data.message);
                            }
                        });
                    }
                });
            }
            if(document.getElementById('good_button_mode4')){
                document.getElementById('good_button_mode4').addEventListener('click', function(){
                    var delta = parseInt(document.getElementById('points_ok_mode4').value);
                    if(isNaN(delta) || delta < 1){
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
                        updateTeamRanking();
                        document.getElementById('good_button_mode4').disabled = true;
                        document.getElementById('good_button_mode4').style.backgroundColor = '#ccc';
                        document.getElementById('bad_button_mode4').disabled = true;
                        document.getElementById('bad_button_mode4').style.backgroundColor = '#ccc';
                    });
                });
            }
            if(document.getElementById('bad_button_mode4')){
                document.getElementById('bad_button_mode4').addEventListener('click', function(){
                    var delta = parseInt(document.getElementById('points_nok_mode4').value);
                    if(isNaN(delta) || delta > 0){
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
                        updateTeamRanking();
                        document.getElementById('good_button_mode4').disabled = true;
                        document.getElementById('good_button_mode4').style.backgroundColor = '#ccc';
                        document.getElementById('bad_button_mode4').disabled = true;
                        document.getElementById('bad_button_mode4').style.backgroundColor = '#ccc';
                    });
                });
            }
            // Vérification via localStorage pour forcer la déconnexion côté participant
            setInterval(function(){
                if(localStorage.getItem('forceDisconnect') === "true"){
                    localStorage.removeItem('forceDisconnect');
                    alert("Vous avez été déconnecté.");
                    window.location.reload();
                }
            }, 2000);
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
?>
