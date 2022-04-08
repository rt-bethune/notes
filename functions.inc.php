<?php

function retire_accents($str, $charset = 'utf-8')
{
    $str = htmlentities($str, ENT_NOQUOTES, $charset);

    $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

    return $str;
}

function CURL($url)
{
    global $http_options;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);

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

    $output = curl_exec($ch);
    curl_close($ch);

    return $output;
}

function convertir_utf8($texte)
{
    if (is_null($texte))
        return null;
    $texte = str_replace("&apos;", "'", $texte);
    $retour = htmlentities($texte, ENT_NOQUOTES, 'UTF-8');

    return ($retour);
}


function get_dept($nip)
{
    global $sco_url;
    $dept = file_get_contents($sco_url . 'get_etud_dept?code_nip=' . $nip);

    return ($dept);
}


function get_semestre_info($sem, $dept)
{
    global $sco_user;
    global $sco_pw;
    global $sco_url;

    $data = [
        '__ac_name' => $sco_user,
        '__ac_password' => $sco_pw,
        'format' => 'xml',
        'formsemestre_id' => $sem,
    ];
    $query = http_build_query($data);
    $retour = CURL($sco_url . $dept . "/Scolarite/Notes/formsemestre_list?$query");

    return ($retour);
}


function get_bulletinetud_page($nip, $sem, $dept)
{
    global $sco_user;
    global $sco_pw;
    global $sco_url;


    $data = [
        '__ac_name' => $sco_user,
        '__ac_password' => $sco_pw,
        'format' => 'xml',
        'code_nip' => $nip,
        'formsemestre_id' => $sem,
        'version' => 'selectedevals',
    ];
    $query = http_build_query($data);
    $retour = CURL($sco_url . $dept . "/Scolarite/Notes/formsemestre_bulletinetud?$query");

    return ($retour);
}


function get_bulletinetud_page_json($nip, $sem, $dept)
{
    global $sco_user;
    global $sco_pw;
    global $sco_url;


    $data = [
        '__ac_name' => $sco_user,
        '__ac_password' => $sco_pw,
        'format' => 'json',
        'code_nip' => $nip,
        'formsemestre_id' => $sem,
        'version' => 'selectedevals',
    ];
    $query = http_build_query($data);
    $retour = CURL($sco_url . $dept . "/Scolarite/Notes/formsemestre_bulletinetud?$query");

    return ($retour);
}

function get_EtudNonJustAbs_page($etud_id, $dept)
{
    global $sco_user;
    global $sco_pw;
    global $sco_url;

    $data = [
        'etudid' => $etud_id,
        '__ac_name' => $sco_user,
        '__ac_password' => $sco_pw,
        'format' => 'xml',
        'absjust_only' => 0,
    ];
    $query = http_build_query($data);
    $retour = CURL($sco_url . $dept . "/Scolarite/Absences/ListeAbsEtud?$query");

    return ($retour);
}


function get_EtudJustAbs_page($etud_id, $dept)
{
    global $sco_user;
    global $sco_pw;
    global $sco_url;

    $data = [
        'etudid' => $etud_id,
        '__ac_name' => $sco_user,
        '__ac_password' => $sco_pw,
        'format' => 'xml',
        'absjust_only' => 1,
    ];
    $query = http_build_query($data);
    $retour = CURL($sco_url . $dept . "/Scolarite/Absences/ListeAbsEtud?$query");

    return ($retour);
}


function get_EtudInfos_page($nip, $dept)
{
    global $sco_user;
    global $sco_pw;
    global $sco_url;
    $data = [
        '__ac_name' => $sco_user,
        '__ac_password' => $sco_pw,
        'format' => 'xml',
        'code_nip' => $nip,
    ];
    $query = http_build_query($data);
    $retour = CURL($sco_url . $dept . "/Scolarite/etud_info?$query");

    return ($retour);
}


function get_all_semestres($xml_data)
{
    $data = [];
    $xml = simplexml_load_string($xml_data);

    foreach ($xml->insemestre as $s) {
        $sem = (array) $s['formsemestre_id'];
        $data[] = $sem;
    }

    return $data;
}

function get_current_semestre($xml_data)
{
    $xml = simplexml_load_string($xml_data);
    foreach ($xml->insemestre as $s) {
        $finsemestre = $s['date_fin'];
        $fin = strtotime($finsemestre) + 3000000;
        if ($fin > strtotime('now')) {
            $sem = (array) $s['formsemestre_id'];

            return ($sem[0]);
        }

    }
    $sem = "";

    return ($sem);
}


function print_header($xml_data, $sem, $dept)
{
    if ($sem == "") {
        echo '<h2> Il n&apos;y a pas de semestre courant</h2>';
    } else {
        $xml = simplexml_load_string($xml_data);
        $retour = get_semestre_info($sem, $dept);
        $xml2 = simplexml_load_string($retour);
        $sexe = $xml->etudiant['sexe'];
        $prenom = $xml->etudiant['prenom'];
        $nom = $xml->etudiant['nom'];
        $semestre = $xml2->formsemestre['titre_num'];
        echo '<section class="hero is-primary">
            <div class="hero-body">
              <div class="container">
                <h1 class="title">
                  ' . convertir_utf8($sexe) . ' ' . convertir_utf8($prenom) . ' ' . convertir_utf8($nom) . '
                </h1>
                <h2 class="subtitle">
                  ' . convertir_utf8($semestre) . '
                </h2>
              </div>
            </div>
          </section>';
    }
}


function print_absences($xml_data, $sem, $dept)
{
    if ($sem == "") {
        echo '<h2> Il n&apos;y a pas de semestre courant</h2>';
    } else {
        $xml = simplexml_load_string($xml_data);
        $etudid = ((array) $xml->etudiant['etudid'])[0];
        $retour_non_just_abs = get_EtudNonJustAbs_page($etudid, $dept);
        $xml_non_just_abs = simplexml_load_string($retour_non_just_abs);


        $retour_just_abs = get_EtudJustAbs_page($etudid, $dept);
        $xml_just_abs = simplexml_load_string($retour_just_abs);


        echo '   
    <div>
<p class="title"> Liste des absences:</p>
    <table class="notes_bulletin table is-bordered is-striped is-narrow is-hoverable is-fullwidth" style="background-color: background-color: rgb(255,255,240);">

<thead>
<tr> 
  <th class="note_bold">Date </th>
    <th class="note_bold">Justifiée</th>
    <th class="note_bold">Desc</th>
    <th class="note_bold">Motif</th>
    </tr></thead>';
        echo "<tr><td></td><td></td><td></td><td></td></tr>";
        foreach ($xml_non_just_abs->row as $abs) {
            $just = "<b class='has-text-danger'>Non</b>";
            echo "<tr>";
            echo "<td>" . $abs->datedmy->attributes()->value . ' ' . $abs->matin->attributes()->value . '</td>';
            echo "<td>" . $just . '</td>';
            echo "<td>" . $abs->description->attributes()->value . '  ' . $abs->motif->attributes()->value . '</td>';
            echo "<td></td>";
            echo "</tr>";
        }
        echo "<tr><td></td><td></td><td></td><td></td></tr>";

        foreach ($xml_just_abs->row as $abs) {
            $just = "Oui";
            echo "<tr>";
            echo "<td>" . $abs->datedmy->attributes()->value . ' ' . $abs->matin->attributes()->value . '</td>';
            echo "<td>" . $just . '</td>';
            echo "<td>" . $abs->description->attributes()->value . '</td>';
            echo "<td>" . $abs->motif->attributes()->value . '</td>';
            echo "</tr>";
        }
        echo '</table>
</div>';
    } // else sem
}

function print_notes_json($json_data, $sem, $dept, $show_moy = false)
{
    $modules = [];
    $codesmodules = [];
    $data = json_decode($json_data);
    $i = 0;
    if ($sem == "" || (isset($data->publie) && $data->publie != "1")) {
        echo '<h2> Il n&apos;y a pas de semestre courant</h2>';

        return;
    }
    if (isset($data->ues)) {
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
        foreach ($data->ues as $name => $ue) {
            echo '<tr class="notes_bulletin_row_ue is-selected">
        <td class="note_bold" colspan=2><span onclick="toggle_vis_ue(this);" class="toggle_ue"><img src="imgs/minus_img.png" alt="-" title="" height="13" width="13" border="0" /></span>' . $name . '</td>
        <td colspan="2"><b>' . $ue->moyenne->value . '</b></td>
        
        </tr>';
            foreach ($ue->ressources as $rname => $resource) {
                echo '<tr>
          <td></td>
          <td >' . $rname . '</td>
          <td >' . $resource->moyenne . '</td>
          <td >' . $resource->coef . '</td>
         
        </tr>';
            }
            foreach ($ue->saes as $sname => $sae) {
                echo '<tr>
          <td></td>
          <td >' . $sname . '</td>
          <td >' . $sae->moyenne . '</td>
          <td >' . $sae->coef . '</td>
         
        </tr>';
            }
        }
        echo '</tbody></table>';
        echo '<article class="notification is-warning"><p><center>' . convertir_utf8($data->semestre->situation) . '</center></p></article>';
    }
    if (isset($data->ressources)) {
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
        foreach ($data->ressources as $rname => $ressource) {
            echo '<tr class="notes_bulletin_row_ue">
        <td class="note_bold  is-info" colspan=5><span onclick="toggle_vis_ue(this);" class="toggle_ue"><img src="imgs/minus_img.png" alt="-" title="" height="13" width="13" border="0" /></span>' . $rname . ' : ' . $ressource->titre . '</td>

      </tr>';
            foreach ($ressource->evaluations as $evaluation) {
                echo '<tr>
        
        <td></td>    
        <td >' . $evaluation->description . '</td>
        <td >' . $evaluation->date . '</td>
        <td >' . $evaluation->note->value . '</td>
        <td >' . $evaluation->coef . '</td>
       
      </tr>';
            }
        }

        echo '</tbody></table>';
    }
    if (isset($data->saes)) {
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
        foreach ($data->saes as $sname => $sae) {
            echo '<tr class="notes_bulletin_row_ue">
        <td class="note_bold is-danger" colspan=5><span onclick="toggle_vis_ue(this);" class="toggle_ue"><img src="imgs/minus_img.png" alt="-" title="" height="13" width="13" border="0" /></span>' . $sname . ' : ' . $sae->titre . '</td>
      </tr>';
            foreach ($sae->evaluations as $evaluation) {
                echo '<tr>
        
        <td></td>    
        <td >' . $evaluation->description . '</td>
        <td >' . $evaluation->date . '</td>
        <td >' . $evaluation->note->value . '</td>
        <td >' . $evaluation->coef . '</td>
       
      </tr>';
            }
        }


        echo '</tbody></table>';
    }
    if (isset($data->ue)) {

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
        echo '<tr class="gt_hl notes_bulletin_row_gen" ><td  class="titre" colspan="4" >Moyenne générale:</td><td  class="note">' . $data->note->value . '/20</td><td  class="coef"></td></tr>';


        foreach ($data->ue as $ue) {
            $coef = 0;
            foreach ($ue->module as $mod) {
                $i = $i + 1;
                $coef = $coef + strval($mod->coefficient);
                $modules[$i] = retire_accents($mod->titre, 'UTF-8');
                $codesmodules[$i] = retire_accents($mod->code, 'UTF-8');
            }
            echo '<tr class="notes_bulletin_row_ue is-selected">
      <td class="note_bold"><span onclick="toggle_vis_ue(this);" class="toggle_ue"><img src="imgs/minus_img.png" alt="-" title="" height="13" width="13" border="0" /></span>' . $ue->acronyme . '</td>
      <td></td>
      <td></td>
    ';

            echo '  <td></td><td>' . $ue->note->value . '</td>
    ';

            echo '  <td>' . $coef . '</td>
    </tr>';
            foreach ($ue->module as $mod) {
                echo '<tr class="notes_bulletin_row_mod">
      <td></td>
      <td>' . $mod->code . '</td>
       <td>' . convertir_utf8($mod->titre) . '</td>
      <td></td>
    ';


                echo '  <td>' . $mod->note->value . '</td>
    ';


                echo '  <td>' . $mod->coefficient . '</td>
    </tr>';


                foreach ($mod->evaluation as $eval) {


                    echo '<tr class="toggle4" >
      <td></td>
      <td></td>
        <td></td>
      <td class="bull_nom_eval is-size-6  has-text-grey-light ">' . convertir_utf8($eval->description) . '</td>
      <td class="note is-size-6  has-text-grey-light " >' . $eval->note . '</td>
      <td class="max is-size-6  has-text-grey-light ">(' . $eval->coefficient . ')</td>
    </tr>';
                }

            }
        }
        echo '<tbody></table>
    ';

        if(isset($data->situation)) {
            echo '<article class="notification is-warning"><p><center>' . convertir_utf8($data->situation) . '</center></p></article>';
        }


    }
}


function print_notes($xml_data, $sem, $dept, $show_moy = false)
{
    $modules = [];
    $codesmodules = [];
    $i = 0;
    if ($sem == "") {
        echo '<h2> Il n&apos;y a pas de semestre courant</h2>';
    } else {
        $xml = simplexml_load_string($xml_data);

        $retour = get_semestre_info($sem, $dept);
        $xml2 = simplexml_load_string($retour);
        $finsemestre = $xml2->formsemestre['date_fin_iso'];
        $fin = strtotime($finsemestre) + 3000000;
        $day = strtotime(date("d-m-Y"));
        $publie = $xml2->formsemestre['bul_hide_xml'];


        if ($publie == "0") {
            //        if (!$show_moy) {

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
            echo '<tr class="gt_hl notes_bulletin_row_gen" ><td  class="titre" colspan="4" >Moyenne générale:</td><td  class="note">' . $xml->note['value'] . '/20</td><td  class="coef"></td></tr>';

            foreach ($xml->ue as $ue) {
                $coef = 0;
                foreach ($ue->module as $mod) {
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

                echo '  <td></td><td>' . $ue->note['value'] . '</td>
';

                echo '  <td>' . $coef . '</td>
</tr>';
                foreach ($ue->module as $mod) {
                    echo '<tr class="notes_bulletin_row_mod">
  <td></td>
  <td>' . $mod['code'] . '</td>
   <td>' . convertir_utf8($mod['titre']) . '</td>
  <td></td>
';


                    echo '  <td>' . $mod->note['value'] . '</td>
';


                    echo '  <td>' . $mod['coefficient'] . '</td>
</tr>';

                    if (! $show_moy or $fin > $day) {
                        foreach ($mod->evaluation as $eval) {
                            if (is_numeric(strval($eval->note['value']))) {
                                $note_eval = round((strval($eval->note['value'])) / 20 * strval($eval['note_max_origin']), 2);
                            } else {
                                $note_eval = $eval->note['value'];
                            }

                            echo '<tr class="toggle4" >
  <td></td>
  <td></td>
    <td></td>
  <td class="bull_nom_eval is-size-6  has-text-grey-light ">' . convertir_utf8($eval['description']) . '</td>
  <td class="note is-size-6  has-text-grey-light " >' . $note_eval . ' / ' . strval($eval['note_max_origin']) . '</td>
  <td class="max is-size-6  has-text-grey-light ">(' . $eval['coefficient'] . ')</td>
</tr>';
                        }
                    }
                }
            }
            echo '<tbody></table>
';
            $code = $xml->decision['code'];

            // Affichage décision seulement aprés 45 jours de la fin du semestre
            if ($show_moy and $fin < $day) {
                echo "<br>" . convertir_utf8($xml->situation);
            } else {
                if ($code != "" and $fin < $day) {
                    echo "<br>" . convertir_utf8($xml->situation);
                }
            }
        }
    }
}
