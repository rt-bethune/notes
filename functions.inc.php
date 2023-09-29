<?php

function curl_get($url, $token)
{
    global $http_options;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies_scodoc.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies_scodoc.txt');
    if (isset($http_options['proxy'])) {
        $proxy = preg_replace("(^tcp?://)", "", $http_options['proxy']);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


    $headers = array();
    $headers[] = 'Authorization: Bearer ' . $token;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    return $result;
}
function get_token($sco_url, $user, $password)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $sco_url . 'api/tokens');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);

    $headers = array();
    $headers[] = 'Authorization: Basic ' . base64_encode("$user:$password");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    $decoded_result = json_decode($result, true);
    $token = $decoded_result['token'];
    return $token;
}

function get_student_info($token, $sco_url, $nip)
{
    $result = curl_get($sco_url . 'api/etudiant/nip/' . $nip, $token);
    $decoded_result = json_decode($result, true);
    return $decoded_result;
}

function get_student_formsemestres($token, $sco_url, $nip)
{
    $result = curl_get($sco_url . 'api/etudiant/nip/' . $nip . '/formsemestres', $token);
    $decoded_result = json_decode($result, true);
    return $decoded_result;
}

function get_student_notes($token, $sco_url, $nip, $sem)
{
    $sem_id = $sem['id'];
    $result = curl_get($sco_url . "api/etudiant/nip/$nip/formsemestre/$sem_id/bulletin", $token);
    $decoded_result = json_decode($result, true);
    return $decoded_result;
}


function get_student_absences($token, $sco_url, $nip)
{
    $result = curl_get($sco_url . "api/assiduites/nip/$nip", $token);
    $decoded_result = json_decode($result, true);
    return $decoded_result;
}

function print_header($info, $sem)
{
    if ($sem == "") {
        echo '<h2> Il n&apos;y a pas de semestre courant</h2>';
    } else {
        $prenom = $info['prenom'];
        $nom = $info['nom'];
        $semestre = $sem['titre_num'];
        echo '<section class="hero is-primary">
            <div class="hero-body">
              <div class="container">
                <h1 class="title">
                  ' . convertir_utf8($prenom) . ' ' . convertir_utf8($nom) . '
                </h1>
                <h2 class="subtitle">
                  ' . convertir_utf8($semestre) . '
                </h2>
              </div>
            </div>
          </section>';
    }
}



function retire_accents($str, $charset = 'utf-8')
{
    $str = htmlentities($str, ENT_NOQUOTES, $charset);

    $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

    return $str;
}

function convertir_utf8($texte)
{
    if (is_null($texte))
        return null;
    $texte = str_replace("&apos;", "'", $texte);
    $retour = htmlentities($texte, ENT_NOQUOTES, 'UTF-8');

    return ($retour);
}







function print_absences($data)
{


    echo '   
    <div>
<p class="title"> Liste des absences:</p>
    <table class="notes_bulletin table is-bordered is-striped is-narrow is-hoverable is-fullwidth" style="background-color: background-color: rgb(255,255,240);">

<thead>
<tr> 
  <th class="note_bold" colspan=2 >Date </th>
    <th class="note_bold">Justifiée</th>
    <th class="note_bold">Desc</th>
    </tr></thead>';
    foreach ($data as $abs) {
        if ($abs['etat'] === "ABSENT") {
            if (!$abs['est_just']) {
                $just = "<b class='has-text-danger'>Non</b>";
            } else {
                $just = "Oui";
            }

            echo "<tr>";
            echo "<td>" . pretty_date($abs['date_debut']) . ' </td><td> ' . pretty_date($abs['date_fin']) . '</td>';
            echo "<td>" . $just . '</td>';
            echo "<td>" . $abs['desc'] .  '</td>';
            echo "</tr>";
        }
    }

    echo '</table>
</div>';
}

function pretty_date($dateString)
{
    // Create a DateTime object from the string
    $date = new DateTime($dateString);

    // Reformat the date
    $prettyDate = $date->format('d/m/Y H:i');

    return $prettyDate;
}

function print_notes($data, $sem)
{
    $modules = [];
    $codesmodules = [];
    $i = 0;
    if ($sem == "" || (isset($data['publie']) && $data['publie'] != "1")) {
        echo '<h2> Il n&apos;y a pas de semestre courant</h2>';

        return;
    }
    if (isset($data['ues'])) {
        echo '<p class="title"> Synthèse :</p>';
        echo ' <HR noshade size="5" width="100%" align="left" style="color: blue;">
        </center>  
            
    
        <table class="notes_bulletin table is-bordered is-striped is-narrow is-hoverable is-fullwidth" style="background-color: background-color: rgb(255,255,240);">
    <thead>
    <tr>
      <th class="note_bold">UE</th>
      <th class="note_bold">Code Ressource/SAÉ</th>
      <th class="note_bold">Note</th>
      <th class="note_bold">Coef</th>
    </tr>
    </thead>
    <tbody>
    ';
        foreach ($data['ues'] as $name => $ue) {
            echo '<tr class="notes_bulletin_row_ue is-selected">
        <td class="note_bold" colspan=2><span onclick="toggle_vis_ue(this);" class="toggle_ue"><img src="imgs/minus_img.png" alt="-" title="" height="13" width="13" border="0" /></span>' . $name . '</td>
        <td colspan="2"><b>' . $ue['moyenne']['value'] . '</b></td>
        
        </tr>';
            foreach ($ue['ressources'] as $rname => $resource) {
                echo '<tr>
          <td></td>
          <td >' . $rname . '</td>
          <td >' . $resource['moyenne'] . '</td>
          <td >' . $resource['coef'] . '</td>
         
        </tr>';
            }
            foreach ($ue['saes'] as $sname => $sae) {
                echo '<tr>
          <td></td>
          <td >' . $sname . '</td>
          <td >' . $sae['moyenne'] . '</td>
          <td >' . $sae['coef'] . '</td>
         
        </tr>';
            }
        }
        echo '</tbody></table>';
        echo '<article class="notification is-warning"><p><center>' . convertir_utf8($data['semestre']['situation']) . '</center></p></article>';
    }
    if (isset($data['ressources'])) {
        echo '<p class="title"> Ressources :</p>';
        echo ' <HR noshade size="5" width="100%" align="left" style="color: blue;">
        </center>  
            
    
        <table class="notes_bulletin table is-bordered is-striped is-narrow is-hoverable is-fullwidth" style="background-color: background-color: rgb(255,255,240);">
    <thead>
    <tr>
    <th></th>
    <th class="note_bold">Evaluation</th>
    <th class="note_bold">Date</th>
    <th class="note_bold">Note</th>
      <th class="note_bold">Coef</th>
    </tr>
    </thead>
    <tbody>
    ';
        foreach ($data['ressources'] as $rname => $ressource) {
            echo '<tr class="notes_bulletin_row_ue">
        <td class="note_bold  is-info" colspan=5><span onclick="toggle_vis_ue(this);" class="toggle_ue"><img src="imgs/minus_img.png" alt="-" title="" height="13" width="13" border="0" /></span>' . $rname . ' : ' . $ressource['titre'] . '</td>

      </tr>';
            foreach ($ressource['evaluations'] as $evaluation) {
                echo '<tr>
        
        <td></td>    
        <td >' . $evaluation['description'] . '</td>
        <td >' . pretty_date($evaluation['date']) . '</td>
        <td >' . $evaluation['note']['value'] . '</td>
        <td >' . $evaluation['coef'] . '</td>
       
      </tr>';
            }
        }

        echo '</tbody></table>';
    }
    if (isset($data['saes'])) {
        echo '<p class="title"> SAÉs :</p>';
        echo ' <HR noshade size="5" width="100%" align="left" style="color: blue;">
        </center>  
            
    
        <table class="notes_bulletin table is-bordered is-striped is-narrow is-hoverable is-fullwidth" style="background-color: background-color: rgb(255,255,240);">
    <thead>
    <tr>
    <th></th>
    <th class="note_bold">Evaluation</th>
    <th class="note_bold">Date</th>
    <th class="note_bold">Note</th>
      <th class="note_bold">Coef</th>
    </tr>
    </thead>
    <tbody>
    ';
        foreach ($data['saes'] as $sname => $sae) {
            echo '<tr class="notes_bulletin_row_ue">
        <td class="note_bold is-danger" colspan=5><span onclick="toggle_vis_ue(this);" class="toggle_ue"><img src="imgs/minus_img.png" alt="-" title="" height="13" width="13" border="0" /></span>' . $sname . ' : ' . $sae['titre'] . '</td>
      </tr>';
            foreach ($sae['evaluations'] as $evaluation) {
                echo '<tr>
        
        <td></td>    
        <td >' . $evaluation['description'] . '</td>
        <td >' . pretty_date($evaluation['date']) . '</td>
        <td >' . $evaluation['note']['value'] . '</td>
        <td >' . $evaluation['coef'] . '</td>
       
      </tr>';
            }
        }


        echo '</tbody></table>';
    }
    if (isset($data['ue'])) {

        echo ' <HR noshade size="5" width="100%" align="left" style="color: blue;">
        </center>  
            
    
        <table class="notes_bulletin table is-bordered is-striped is-narrow is-hoverable is-fullwidth" style="background-color: background-color: rgb(255,255,240);">
    <thead>
    <tr>
      <th class="note_bold">UE</th>
      <th class="note_bold">Code Module</th>
        <th class="note_bold">Module</th>
      <th class="note_bold"><a href="#" id="toggler4">Evaluation</a></th>
      <th class="note_bold">Note</th>
      <th class="note_bold">Coef</th>
    </tr>
    </thead>
    <tbody>
    ';
        echo '<tr class="gt_hl notes_bulletin_row_gen" ><td  class="titre" colspan="4" >Moyenne générale:</td><td  class="note">' . $data['note']['value'] . '/20</td><td  class="coef"></td></tr>';


        foreach ($data['ue'] as $ue) {
            $coef = 0;
            foreach ($ue['module'] as $mod) {
                $i = $i + 1;
                $coef = $coef + strval($mod['coefficient']);
                $modules[$i] = retire_accents($mod['titre'], 'UTF-8');
                $codesmodules[$i] = retire_accents($mod['code'], 'UTF-8');
            }
            echo '<tr class="notes_bulletin_row_ue is-selected">
      <td class="note_bold"><span onclick="toggle_vis_ue(this);" class="toggle_ue"><img src="imgs/minus_img.png" alt="-" title="" height="13" width="13" border="0" /></span>' . $ue['acronyme'] . '</td>
      <td></td>
      <td></td>
    ';

            echo '  <td></td><td>' . $ue['note']['value'] . '</td>
    ';

            echo '  <td>' . $coef . '</td>
    </tr>';
            foreach ($ue['module'] as $mod) {
                echo '<tr class="notes_bulletin_row_mod">
      <td></td>
      <td>' . $mod['code'] . '</td>
       <td>' . convertir_utf8($mod['titre']) . '</td>
      <td></td>
    ';


                echo '  <td>' . $mod['note']['value'] . '</td>
    ';


                echo '  <td>' . $mod['coefficient'] . '</td>
    </tr>';


                foreach ($mod['evaluation'] as $eval) {


                    echo '<tr class="toggle4" >
      <td></td>
      <td></td>
        <td></td>
      <td class="bull_nom_eval is-size-6  has-text-grey-light ">' . convertir_utf8($eval['description']) . '</td>
      <td class="note is-size-6  has-text-grey-light " >' . $eval['note'] . '</td>
      <td class="max is-size-6  has-text-grey-light ">(' . $eval['coefficient'] . ')</td>
    </tr>';
                }
            }
        }
        echo '<tbody></table>
    ';
    if (isset($data['semestre']['situation'])) {
        echo '<article class="notification is-warning"><p><center>' . convertir_utf8($data['semestre']['situation']) . '</center></p></article>';
    }
    }
        
    
}
