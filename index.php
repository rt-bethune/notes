<?php
require_once 'config.php';
require_once 'connexion.php';
require_once 'functions.inc.php';
session_start();
$token = get_token($sco_url, $sco_user, $sco_pw);

?>


<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portail notes et absences - IUT de Béthune</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.2/css/bulma.min.css">
  <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
  <link rel="stylesheet" href="assets/css/app.css">
</head>

<body>
  <?php

  if ($nip) {

    $info = get_student_info($token, $sco_url, $nip);


    if (!$info['dept_id']) {
      echo "Numéro étudiant inconnu : " . $nip . ". Contactez votre Chef de département.";
    } else {

      $formsemestres = get_student_formsemestres($token, $sco_url, $nip);

      foreach ($formsemestres as $formsemestre) {
        if ($formsemestre['etat']) $current_semestre = $formsemestre;
      }
      $sem = "";
      if (isset($_GET['sem']) && $_GET['sem']) {
        $sem = $_GET['sem'];
      } else {

        $sem = $current_semestre ?? "";
      }


      if ($sem != "") {
        foreach ($formsemestres as $formsemestre) {
          if ($formsemestre['id'] == $sem) $sem = $formsemestre;
        }
        print_header($info, $sem);
      }
  ?>
      <div class="container js-tabs-container">

        <div class="tabs is-centered">
          <ul>
            <li class="is-active" data-tab="tab-notes"><a>Bulletin de notes</a></li>
            <li data-tab="tab-absences"><a>Gestion des absences</a></li>
            <li data-tab="tab-semestres"><a>Autres semestres</a></li>

            <li><a href="logout.php">Déconnexion</a></li>
          </ul>
        </div>
        <div class="has-display-none  js-tab-content" id="tab-semestres">
          <article class="message is-info">
            <div class="message-header">Semestres</div>
            <div class="message-body">
              <ul>
                <?php

                foreach ($formsemestres as $semestre) {
                  echo '<li><a href="?sem=' . $semestre['id'] . '">' . $semestre['titre_num'] . '</a></li>';
                }
                ?>
              </ul>

            </div>
          </article>

        </div>
        <div class="js-tab-content" id="tab-notes">
          <?php
          if ($sem === "") {
            echo '<h2> Il n&apos;y a pas de semestre en cours - Choisissez éventuellement dans la liste. (nip = ' . $nip . ') </h2>';
          } else {
            if (isset($current_semestre) && $current_semestre == $sem) {
              echo '
                        <article class="notification is-warning">
                            
                              <p><center>Les informations contenues dans ces tableaux sont provisoires. L&apos;&eacute;tat n&apos;a pas valeur de bulletin de notes<br>Il vous appartient de contacter vos enseignants ou votre département en cas de désaccord</center></p>
                          
                        </article>
                        ';
            }
            $data = get_student_notes($token, $sco_url, $nip, $sem);

            print_notes($data, $sem);
          }


          ?>



        </div>

        <div class="has-display-none  js-tab-content" id="tab-absences">
          <div id="absences">
            <?php
            $data = get_student_absences($token, $sco_url, $nip);
            print_absences($data);
            ?>
          </div>

        </div>
      </div>

  <?php

    }
  }

  ?>
  <footer class="footer">

    <div class="columns is-mobile">
      <div class="column is-4 is-offset-7"> </div>
    </div>



  </footer>

  <script src="assets/js/app.js"></script>

</body>

</html>