<?php
require_once 'config.php';
require_once 'connexion.php';
require_once 'functions.inc.php';
session_start ();
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
  $dept = get_dept($nip);
  if ($dept) {
      $retour = get_EtudInfos_page($nip, $dept);

      $sems = get_all_semestres($retour);
      $sem = get_current_semestre($retour);
    

      if($sem==""){
        echo '<h2> Il n&apos;y a pas de semestre en cours - Choisissez éventuellement dans la liste.</h2>';
      } 
      else{       
        $retour = get_bulletinetud_page($nip, $sem, $dept);
        $data = get_bulletinetud_page_json($nip, $sem, $dept);
        print_header($retour, $sem, $dept, False);
  ?>
<div class="container js-tabs-container">

<div class="tabs is-centered">
  <ul>
    <li class="is-active" data-tab="tab-notes"><a>Bulletin de notes</a></li>
    <li data-tab="tab-absences"><a>Gestion des absences</a></li>
    <!-- <li data-tab="tab-infos"><a>Infos</a></li> -->
    <li><a href="logout.php">Déconnexion</a></li>
  </ul>
</div>
        <div class="js-tab-content" id="tab-notes">
                    <article class="notification is-warning">
                        
                          <p><center>Les informations contenues dans ces tableaux sont provisoires. L&apos;&eacute;tat n&apos;a pas valeur de bulletin de notes<br>Il vous appartient de contacter vos enseignants ou votre département en cas de désaccord</center></p>
                       
                    </article>
                    
                    <?php print_notes_json($data, $sem, $dept, TRUE); ?>


                        
        </div>

        <div class="has-display-none js-tab-content" id="tab-absences">
              <div id="absences">
                     <?php print_absences($retour, $sem, $dept, TRUE); ?>
              </div>
              
        </div> 

        <!-- <div class="has-display-none js-tab-content" id="tab-infos">
                    <article class="message is-info">
                        <div class="message-header">Infos</div>
                        <div class="message-body">
                            

                        </div>
                    </article>
        </div> -->
</div>

<?php
  }
}
else {
    echo "Numéro étudiant inconnu : " . $nip . ". Contactez votre Chef de département.";

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