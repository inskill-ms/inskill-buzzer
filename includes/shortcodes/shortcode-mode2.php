<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InskillBuzzer_Shortcodes_Mode2 extends InskillBuzzer_Shortcodes {

    public function __construct() {
        add_shortcode('inskill_buzzer_mode2', array($this, 'render_mode2_participant'));
        add_shortcode('inskill_buzzer_mode2_animateur', array($this, 'render_mode2_animateur'));
    }

    // Interface Participant pour "Buzzer – L'union fait la force"
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
        #buzzer_container {
            text-align: center;
            margin-bottom: 20px;
        }
        #buzzer_button {
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
            <h2>Buzzer : L'union fait la force - Participant</h2>
            <form id="participant_form" method="post">
                <label for="participant_name">Nom Prénom :</label><br>
                <input type="text" name="participant_name" id="participant_name" required>
                <br>
                <label for="participant_team">Équipe :</label><br>
                <select name="participant_team" id="participant_team" required>
                    <?php
                    $teams = array(
                        get_option('inskill_buzzer_mode2_team1_name', ''),
                        get_option('inskill_buzzer_mode2_team2_name', ''),
                        get_option('inskill_buzzer_mode2_team3_name', ''),
                        get_option('inskill_buzzer_mode2_team4_name', '')
                    );
                    foreach($teams as $team) {
                        if($team != ''){
                            echo '<option value="'.esc_attr($team).'">'.esc_html($team).'</option>';
                        }
                    }
                    ?>
                </select>
                <br>
                <button type="submit" id="validate_button" name="validate_participant">Valider</button>
            </form>
            <div id="buzzer_container" style="display:none;">
                <button id="buzzer_button">Buzzer</button>
            </div>
            <div id="scoreboard" style="display:none;">
                <h3 style="text-align:left;">Classement</h3>
                <!-- Le classement agrégé sera mis à jour dynamiquement -->
            </div>
        </div>
        <script>
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        var currentParticipant = "";
        var currentTeam = "";
        document.addEventListener('DOMContentLoaded', function(){
            var participantForm = document.getElementById('participant_form');
            var participantNameInput = document.getElementById('participant_name');
            var teamSelect = document.getElementById('participant_team');
            var validateButton = document.getElementById('validate_button');
            var buzzerButton = document.getElementById('buzzer_button');
            var hasBuzzed = false;
            if(participantForm){
                participantForm.addEventListener('submit', function(e){
                    e.preventDefault();
                    if(participantNameInput.value.trim() === ""){
                        alert("Veuillez saisir un nom.");
                        return;
                    }
                    currentParticipant = participantNameInput.value.trim();
                    currentTeam = teamSelect.value;
                    var params = 'participant_name=' + encodeURIComponent(currentParticipant) + '&participant_team=' + encodeURIComponent(currentTeam);
                    fetch(ajaxurl + '?action=create_participant_mode2', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: params
                    })
                    .then(response => response.json())
                    .then(data => {
                        participantNameInput.disabled = true;
                        teamSelect.disabled = true;
                        validateButton.style.display = 'none';
                        document.getElementById('buzzer_container').style.display = 'block';
                        document.getElementById('scoreboard').style.display = 'block';
                    });
                });
            }
            if(buzzerButton){
                buzzerButton.addEventListener('click', function(){
                    if(hasBuzzed) return;
                    if(currentParticipant === "" || currentTeam === ""){
                        alert("Données manquantes.");
                        return;
                    }
                    var params = 'participant_name=' + encodeURIComponent(currentParticipant) + '&participant_team=' + encodeURIComponent(currentTeam);
                    fetch(ajaxurl + '?action=record_buzz_score_mode2', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: params
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success){
                            buzzerButton.style.backgroundColor = 'green';
                            buzzerButton.textContent = 'Buzzed!';
                            hasBuzzed = true;
                        } else {
                            alert('Erreur : ' + (data.message ? data.message : 'Erreur lors de l\'enregistrement du buzz'));
                        }
                    })
                    .catch(function(error){
                        alert('Erreur : ' + error);
                    });
                });
            }
            function updateRanking() {
                if(localStorage.getItem('forceDisconnect_mode2') === "true"){
                    localStorage.removeItem('forceDisconnect_mode2');
                    alert("Votre équipe a été déconnectée.");
                    window.location.reload();
                    return;
                }
                fetch(ajaxurl + '?action=get_full_ranking_mode2')
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        var ranking = data.data;
                        var html = "";
                        if(ranking.length > 0){
                            html += '<table class="participant-ranking-table"><tr><th>#</th><th>Équipe</th><th>Score</th><th>Bonus/Malus</th></tr>';
                            ranking.forEach(function(item, index){
                                html += '<tr>';
                                html += '<td>' + (index + 1) + '</td>';
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
                        document.getElementById('scoreboard').innerHTML = '<h3 style="text-align:left;">Classement</h3>' + html;
                    }
                });
            }
            setInterval(updateRanking, 2000);
            setInterval(function(){
                fetch(ajaxurl + '?action=get_top3_score_mode2')
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        var top3 = data.data;
                        if(top3.length === 0 && hasBuzzed){
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

    // Interface Animateur pour "Buzzer – L'union fait la force"
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
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }
        .animateur-container h2 span {
            font-size: 0.8em;
            font-weight: normal;
            display: block;
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
        .result-btn:disabled {
            background-color: #ccc !important;
            color: #000 !important;
            cursor: default;
        }
        .result-row {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-bottom: 10px;
        }
        .result-row label {
            margin-left: 10px;
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
        /* Tableau de classement agrégé par équipe */
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
        /* Tableau des Participants individuels */
        table.participants-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table.participants-table th, table.participants-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table.participants-table th {
            background-color: #00285B;
            color: #fff;
        }
        </style>
        <div class="animateur-container">
            <h2>Buzzer : L'union fait la force<br><span>(Interface Animateur)</span></h2>
            <div style="text-align:left; margin-bottom:20px;">
                <button id="reset_buzzers_mode2" class="action-btn" style="background-color:#f0ad4e; color:#fff;">Réinitialisation des buzzers</button>
            </div>
            <div>
                <h3 style="text-align:left;">Les 3 plus rapides</h3>
                <div id="top3_mode2">
                    <!-- Mise à jour dynamique -->
                </div>
            </div>
            <!-- Bloc Résultats -->
            <div style="text-align:left; margin-top:20px;">
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
            <!-- Bloc Classement Agrégé -->
            <div>
                <h3 style="text-align:left;">Classement</h3>
                <div id="ranking_container_mode2">
                    <!-- Le classement agrégé par équipe sera mis à jour -->
                </div>
            </div>
            <!-- Nouveau Bloc Participants -->
            <div style="margin-top:30px;">
                <h3 style="text-align:left;">Participants</h3>
                <div id="participants_container_mode2">
                    <!-- Le tableau des participants individuels sera mis à jour ici -->
                </div>
                <div style="text-align:left; margin-top:10px;">
                    <button id="remove_all_mode2" class="remove-all-btn">Supprimer tous les participants</button>
                </div>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            var top3Container = document.getElementById('top3_mode2');
            var rankingContainer = document.getElementById('ranking_container_mode2');
            var participantsContainer = document.getElementById('participants_container_mode2');
            var goodButton = document.getElementById('good_button_mode2');
            var badButton = document.getElementById('bad_button_mode2');

            function updateTop3Mode2() {
                fetch(ajaxurl + '?action=get_top3_score_mode2')
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        var top3 = data.data;
                        var html = "";
                        if(top3.length > 0){
                            html = '<ul style="list-style:none; padding:0;">';
                            top3.forEach(function(item, index){
                                html += '<li>' + (index + 1) + '. ' + item.participant_name + ' (' + item.team + ')</li>';
                            });
                            html += '</ul>';
                        } else {
                            html = 'Aucun participant buzzé.';
                        }
                        top3Container.innerHTML = html;
                    }
                });
            }
            function updateRankingMode2() {
                fetch(ajaxurl + '?action=get_full_ranking_mode2')
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        var ranking = data.data;
                        var html = "";
                        if(ranking.length > 0){
                            html += '<table class="ranking-table"><tr><th>#</th><th>Équipe</th><th>Score</th><th>Bonus/Malus</th></tr>';
                            ranking.forEach(function(item, index){
                                html += '<tr>';
                                html += '<td>' + (index + 1) + '</td>';
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
                        rankingContainer.innerHTML = html;
                        
                        document.querySelectorAll('.bonus-btn').forEach(function(btn){
                            btn.addEventListener('click', function(){
                                var team = this.getAttribute('data-team');
                                var delta = parseInt(this.getAttribute('data-delta'));
                                var params = 'participant_team=' + encodeURIComponent(team) + '&delta=' + encodeURIComponent(delta);
                                fetch(ajaxurl + '?action=update_team_score_mode2', {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                    body: params
                                })
                                .then(response => response.json())
                                .then(data => {
                                    updateRankingMode2();
                                });
                            });
                        });
                    }
                });
            }
            function updateParticipantsMode2() {
                fetch(ajaxurl + '?action=get_all_participants_mode2')
                .then(response => response.json())
                .then(data => {
                    if(data.success){
                        var participants = data.data;
                        var html = "";
                        if(participants.length > 0){
                            html += '<table class="participants-table"><tr><th>#</th><th>NOM Prénom</th><th>Ejecter</th></tr>';
                            participants.forEach(function(item, index){
                                html += '<tr>';
                                html += '<td>' + (index + 1) + '</td>';
                                html += '<td>' + item.participant_name + '</td>';
                                html += '<td><button class="action-btn eject-btn" data-name="'+item.participant_name+'" data-team="'+item.team+'" style="background-color:#8B0000; color:#fff;">Ejecter</button></td>';
                                html += '</tr>';
                            });
                            html += '</table>';
                        } else {
                            html = 'Aucun participant enregistré.';
                        }
                        participantsContainer.innerHTML = html;
                        
                        document.querySelectorAll('.eject-btn').forEach(function(btn){
                            btn.addEventListener('click', function(){
                                var participantName = this.getAttribute('data-name');
                                var team = this.getAttribute('data-team');
                                if(confirm('Ejecter ' + participantName + ' (' + team + ') ?')){
                                    var params = 'participant_name=' + encodeURIComponent(participantName) + '&participant_team=' + encodeURIComponent(team);
                                    fetch(ajaxurl + '?action=remove_participant_mode2', {
                                        method: 'POST',
                                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                        body: params
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if(data.success){
                                            // Forcer la déconnexion du participant ejecté
                                            localStorage.setItem('forceDisconnect_mode2', 'true');
                                            updateParticipantsMode2();
                                        } else {
                                            alert('Erreur lors de l\'éjection : ' + (data.message ? data.message : 'Erreur inconnue'));
                                        }
                                    });
                                }
                            });
                        });
                    }
                });
            }
            setInterval(function(){
                updateTop3Mode2();
                updateRankingMode2();
                updateParticipantsMode2();
            }, 2000);
            var resetButton = document.getElementById('reset_buzzers_mode2');
            if(resetButton){
                resetButton.addEventListener('click', function(){
                    fetch(ajaxurl + '?action=reset_buzzers_all_mode2', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success){
                            updateTop3Mode2();
                            updateRankingMode2();
                            updateParticipantsMode2();
                            goodButton.disabled = false;
                            goodButton.style.backgroundColor = 'green';
                            goodButton.style.color = '#fff';
                            badButton.disabled = false;
                            badButton.style.backgroundColor = '#8B0000';
                            badButton.style.color = '#fff';
                        } else {
                            alert('Erreur lors de la réinitialisation : ' + (data.message ? data.message : 'Erreur inconnue'));
                        }
                    });
                });
            }
            var removeAllButton = document.getElementById('remove_all_mode2');
            if(removeAllButton){
                removeAllButton.addEventListener('click', function(){
                    if(confirm('Supprimer TOUS les participants ?')){
                        fetch(ajaxurl + '?action=remove_all_participants_mode2', { method: 'POST' })
                        .then(response => response.json())
                        .then(data => {
                            if(data.success){
                                updateTop3Mode2();
                                updateRankingMode2();
                                updateParticipantsMode2();
                                goodButton.disabled = false;
                                goodButton.style.backgroundColor = 'green';
                                goodButton.style.color = '#fff';
                                badButton.disabled = false;
                                badButton.style.backgroundColor = '#8B0000';
                                badButton.style.color = '#fff';
                                localStorage.setItem('forceDisconnect_mode2', 'true');
                            } else {
                                alert('Erreur lors de la suppression de tous les participants : ' + (data.message ? data.message : 'Erreur inconnue'));
                            }
                        });
                    }
                });
            }
            if(goodButton){
                goodButton.addEventListener('click', function(){
                    var delta = parseInt(document.getElementById('points_ok_mode2').value);
                    if(isNaN(delta) || delta < 1){
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
                        updateRankingMode2();
                        goodButton.disabled = true;
                        goodButton.style.backgroundColor = '#ccc';
                        goodButton.style.color = '#000';
                        badButton.disabled = true;
                        badButton.style.backgroundColor = '#ccc';
                        badButton.style.color = '#000';
                    });
                });
            }
            if(badButton){
                badButton.addEventListener('click', function(){
                    var delta = parseInt(document.getElementById('points_nok_mode2').value);
                    if(isNaN(delta) || delta > 0){
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
                        updateRankingMode2();
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
