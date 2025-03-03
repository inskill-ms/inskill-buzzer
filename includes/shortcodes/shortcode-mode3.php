<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class InskillBuzzer_Shortcodes_Mode3 extends InskillBuzzer_Shortcodes {

    public function __construct() {
        add_shortcode('inskill_buzzer_mode3', array($this, 'render_mode3_participant'));
        add_shortcode('inskill_buzzer_mode3_animateur', array($this, 'render_mode3_animateur'));
    }

    // Interface participant pour le quiz (Mode 3)
    public function render_mode3_participant($atts) {
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
        /* Titre sur 2 lignes */
        .participant-container h2 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            line-height: 1.2;
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
        /* Styles pour le conteneur des boutons réponse */
        #answer_container_mode3 {
            text-align: center;
            margin-top: 20px;
        }
        #answer_container_mode3 button.answer-btn {
            width: 80px;
            height: 80px;
            margin: 5px;
            border-radius: 10px;
            border: 2px solid #000;
            background-color: #8B0000; /* même couleur que le buzzer du mode 1 */
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
            <h2>Prêt à répondre ! (QCM)<br>Mode : "Chacun pour sa peau !"</h2>
            <form id="participant_form_mode3" method="post">
                <label for="participant_name_mode3">Nom Prénom :</label><br>
                <input type="text" name="participant_name" id="participant_name_mode3" required>
                <br>
                <button type="submit" id="validate_button_mode3" name="validate_participant">Valider</button>
            </form>
            <!-- Conteneur des boutons de réponses (initialement caché) -->
            <div id="answer_container_mode3" style="display:none;">
                <?php
                foreach (array('A','B','C','D','E','F') as $letter) {
                    echo '<button class="answer-btn" data-answer="' . esc_attr($letter) . '">' . $letter . '</button> ';
                }
                ?>
            </div>
            <div id="scoreboard_mode3" style="display:none;">
                <h3 style="text-align:left;">Classement</h3>
            </div>
        </div>
        <script>
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        var currentParticipantMode3 = "";
        var hasAnswered = false; // Flag pour indiquer que le participant a répondu
        document.addEventListener('DOMContentLoaded', function(){
            var participantForm = document.getElementById('participant_form_mode3');
            var participantNameInput = document.getElementById('participant_name_mode3');
            var validateButton = document.getElementById('validate_button_mode3');
            var answerContainer = document.getElementById('answer_container_mode3');
            var answerButtons = document.querySelectorAll('#answer_container_mode3 button.answer-btn');

            // Enregistrement du participant
            participantForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (participantNameInput.value.trim() === "") {
                    alert("Veuillez saisir un nom.");
                    return;
                }
                currentParticipantMode3 = participantNameInput.value.trim();
                var params = 'participant_name=' + encodeURIComponent(currentParticipantMode3);
                fetch(ajaxurl + '?action=create_participant_mode3', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: params
                })
                .then(response => response.json())
                .then(data => {
                    participantNameInput.disabled = true;
                    validateButton.style.display = 'none';
                    answerContainer.style.display = 'block';
                    document.getElementById('scoreboard_mode3').style.display = 'block';
                });
            });

            // Gestion des boutons de réponses
            answerButtons.forEach(function(btn) {
                btn.addEventListener('click', function(){
                    if(hasAnswered) return;
                    var answer = this.getAttribute('data-answer');
                    if(currentParticipantMode3 === "") {
                        alert("Nom manquant.");
                        return;
                    }
                    var params = 'participant_name=' + encodeURIComponent(currentParticipantMode3) + '&answer=' + encodeURIComponent(answer);
                    fetch(ajaxurl + '?action=record_buzz_score_mode3', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: params
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            // Marquer le bouton cliqué en vert et désactiver les autres
                            btn.style.backgroundColor = 'green';
                            hasAnswered = true;
                            answerButtons.forEach(function(otherBtn) {
                                otherBtn.disabled = true;
                            });
                        } else {
                            alert('Erreur : ' + (data.message || 'Erreur inconnue'));
                        }
                    })
                    .catch(function(error){
                        alert('Erreur : ' + error);
                    });
                });
            });

            // Mise à jour du classement côté participant
            function updateRankingParticipant() {
                fetch(ajaxurl + '?action=get_full_ranking_mode3')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var ranking = data.data;
                        var html = "";
                        var stillConnected = false;
                        if(ranking.length > 0) {
                            html += '<table class="participant-ranking-table"><tr><th>Nom Prénom</th><th>Score</th></tr>';
                            ranking.forEach(function(item) {
                                html += '<tr><td>' + item.participant_name + '</td><td>' + item.score + '</td></tr>';
                                if(item.participant_name === currentParticipantMode3) {
                                    stillConnected = true;
                                }
                            });
                            html += '</table>';
                        } else {
                            html = "Aucun score enregistré.";
                        }
                        document.getElementById('scoreboard_mode3').innerHTML = '<h3 style="text-align:left;">Classement</h3>' + html;
                        if(!stillConnected && currentParticipantMode3 !== "") {
                            alert("Vous avez été déconnecté.");
                            window.location.reload();
                        }
                    }
                });
            }
            setInterval(updateRankingParticipant, 2000);

            // Nouvelle logique de réactivation des boutons de réponses
            setInterval(function(){
                fetch(ajaxurl + '?action=get_top3_score_mode3')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var top3 = data.data;
                        // Si aucun participant n'a répondu (reset effectué) et que le participant avait déjà répondu
                        if(top3.length === 0 && hasAnswered) {
                            answerButtons.forEach(function(btn) {
                                btn.disabled = false;
                                btn.style.backgroundColor = '#8B0000'; // couleur initiale
                            });
                            hasAnswered = false;
                        }
                    }
                });
            }, 2000);
        });
        </script>
        <?php
        return ob_get_clean();
    }

    // Interface animateur pour le quiz (Mode 3)
    public function render_mode3_animateur($atts) {
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
        /* Titre sur 3 lignes */
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
        #good_button_mode3 {
            background-color: green;
            color: #fff;
        }
        #bad_button_mode3 {
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
        .classement-heading {
            margin-top: 20px;
        }
        </style>
        <div class="animateur-container">
            <h2>Mode Quiz<br>Chacun pour sa peau !<br>(Interface Animateur)</h2>
            <div style="text-align:left; margin-bottom:20px;">
                <button id="reset_answers_mode3" class="action-btn" style="background-color:#f0ad4e; color:#fff;">Réinitialisation des boutons-réponses</button>
            </div>
            <div>
                <h3 style="text-align:left;">Les 3 plus rapides</h3>
                <div id="top3_mode3">
                    <!-- Affichage des 3 premiers -->
                </div>
            </div>
            <div>
                <h3 style="text-align:left;">Résultats</h3>
                <div class="result-row">
                    <button id="good_button_mode3" class="action-btn result-btn">Good !</button>
                    <label>Réponse OK : 
                        <select id="points_ok_mode3" style="width:60px;">
                            <?php for ($i=1;$i<=10;$i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </label>
                </div>
                <div class="result-row">
                    <button id="bad_button_mode3" class="action-btn result-btn">No good !</button>
                    <label>Réponse NOK : 
                        <select id="points_nok_mode3" style="width:60px;">
                            <?php for ($i=1;$i<=10;$i++):
                                $val = -$i; ?>
                                <option value="<?php echo $val; ?>"><?php echo $val; ?></option>
                            <?php endfor; ?>
                        </select>
                    </label>
                </div>
            </div>
            <div>
                <h3 class="classement-heading" style="text-align:left;">Classement</h3>
                <div id="ranking_container_mode3">
                    <!-- Le classement complet sera affiché sous forme de tableau -->
                </div>
                <div style="text-align:left; margin-top:10px;">
                    <button id="remove_all_mode3" class="remove-all-btn">Supprimer tous les participants</button>
                </div>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            var top3Container = document.getElementById('top3_mode3');
            var rankingContainer = document.getElementById('ranking_container_mode3');
            var goodButton = document.getElementById('good_button_mode3');
            var badButton = document.getElementById('bad_button_mode3');

            function updateTop3() {
                fetch(ajaxurl + '?action=get_top3_score_mode3')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var top3 = data.data;
                        var html = "";
                        if(top3.length > 0) {
                            html = '<ul style="list-style:none; padding:0;">';
                            top3.forEach(function(item, index) {
                                html += '<li>' + (index + 1) + '. ' + item.participant_name + ' (' + (item.answer ? item.answer : '-') + ')</li>';
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
                fetch(ajaxurl + '?action=get_full_ranking_mode3')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        var ranking = data.data;
                        var html = "";
                        if(ranking.length > 0) {
                            // Suppression de la colonne "Réponse"
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
                        rankingContainer.innerHTML = html;
                        attachBonusAndEjectListeners();
                    }
                });
            }
            
            function attachBonusAndEjectListeners() {
                var bonusButtons = rankingContainer.querySelectorAll('.bonus-btn');
                bonusButtons.forEach(function(btn) {
                    btn.addEventListener('click', function(){
                        var participantName = this.getAttribute('data-name');
                        var delta = parseInt(this.getAttribute('data-delta'));
                        var params = 'participant_name=' + encodeURIComponent(participantName) + '&delta=' + encodeURIComponent(delta);
                        fetch(ajaxurl + '?action=update_participant_score_mode3', {
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
                var ejectButtons = rankingContainer.querySelectorAll('.eject-btn');
                ejectButtons.forEach(function(btn) {
                    btn.addEventListener('click', function(){
                        var participantName = this.getAttribute('data-name');
                        if(confirm('Ejecter ' + participantName + ' ?')) {
                            var params = 'participant_name=' + encodeURIComponent(participantName);
                            fetch(ajaxurl + '?action=remove_participant_mode3', {
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
            
            setInterval(function(){
                updateTop3();
                updateRanking();
            }, 2000);
            
            var resetButton = document.getElementById('reset_answers_mode3');
            if(resetButton) {
                resetButton.addEventListener('click', function(){
                    fetch(ajaxurl + '?action=reset_buzzers_all_mode3', { method: 'POST' })
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
            
            var removeAllButton = document.getElementById('remove_all_mode3');
            if(removeAllButton) {
                removeAllButton.addEventListener('click', function(){
                    if(confirm('Supprimer TOUS les participants ?')) {
                        fetch(ajaxurl + '?action=remove_all_participants_mode3', { method: 'POST' })
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
                                localStorage.setItem('forceDisconnect', 'true');
                            } else {
                                alert('Erreur lors de la suppression de tous les participants : ' + data.message);
                            }
                        });
                    }
                });
            }
            
            if(goodButton) {
                goodButton.addEventListener('click', function(){
                    var delta = parseInt(document.getElementById('points_ok_mode3').value);
                    if(isNaN(delta) || delta < 1) {
                        alert('La valeur pour Réponse OK doit être un entier >= 1.');
                        return;
                    }
                    var params = 'delta=' + encodeURIComponent(delta);
                    fetch(ajaxurl + '?action=update_first_score_mode3', {
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
                    var delta = parseInt(document.getElementById('points_nok_mode3').value);
                    if(isNaN(delta) || delta > 0) {
                        alert('La valeur pour Réponse NOK doit être un entier <= 0.');
                        return;
                    }
                    var params = 'delta=' + encodeURIComponent(delta);
                    fetch(ajaxurl + '?action=update_first_score_mode3', {
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
            
            if(localStorage.getItem('forceDisconnect') === "true") {
                localStorage.removeItem('forceDisconnect');
                alert("Vous avez été déconnecté.");
                window.location.reload();
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
}
