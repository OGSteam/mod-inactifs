<?php
	/**
	 *	index.php Page d'accès au module analyseI
	 *	@package	analyseI
	 *	@author	benneb 
	 *	created	 : 09/02/2010  
	 *	modified : 10/02/2010
	 */
	if (!defined('IN_SPYOGAME')) die("Hacking attempt");
	
	function page_footer(){
		global $db;

		//Récupérer le numéro de version du mod
		$request = "SELECT version from ".TABLE_MOD." WHERE root='analyseI'";
		$result = $db->sql_query($request,false);
		list($version)=$db->sql_fetch_row($result);
		echo "<br/><B>analyseI v$version</B> - benneb&copy;2010<br/>";
	}
		function since($timestamp){
	$now = time();
    $since = (int)($timestamp * 24 *60 *60) ;
    $difference = (int) ($now - $since);
    return $difference;
	}
    // retourn le tableau de recherche utilisable sur toutes les pages
    function tab_recherche(){
        global $pub_g_max , $pub_g_min , $pub_s_min , $pub_s_max ,$server_config , $pub_subaction , $pub_since ,$pub_action;
    
    if(isset($pub_g_max)) $g_max = (int)$pub_g_max;
	else $g_max = $server_config['num_of_galaxies']; 
    if(isset($pub_g_min)) $g_min = (int)$pub_g_min;
	else $g_min = $server_config['num_of_galaxies']; 
	if(isset($pub_s_max)) $s_max = (int)$pub_s_max;
	else $s_max = $server_config['num_of_systems']; 
    if(isset($pub_s_min)) $s_min = (int)$pub_s_min;
	else $s_min = $server_config['num_of_systems']; 
	if(isset($pub_since)) $since = (int)$pub_since;
	else $since = 30;
   
	$retour = '';
    $retour .= ' <table width="100%">';
    $retour .= '<form method="POST" action="index.php?action='.$pub_action.'&subaction='.$pub_subaction.'">';
    $retour .= '<input type="hidden" name="t" value="t">';
    $retour .= '<tbody><tr>';
    $retour .= '<td class="c_recherche" colspan="4">Recherche Secteur</td>';
    $retour .= '</tr>';
    $retour .= '<tr><th colspan="2"></th><th>Minimum</th><th>Maximum</th></tr><tr><th>&nbsp;</th>';
    $retour .= '<th>Galaxie</th>';
    $retour .= '<th><input name="g_min" type="text" maxlength="2" size="3" value="'.$g_min.'"></th>';
    $retour .= '<th><input name="g_max" type="text" maxlength="2" size="3" value="'.$g_max.'"></th>';
    $retour .= '</tr>';
    $retour .= '<tr>';
    $retour .= '<th>&nbsp;</th>';
    $retour .= '<th>Syst&egrave;me solaire</th>';
    $retour .= '<th><input name="s_min" type="text" maxlength="3" size="3" value="'.$s_min.'"></th>';
    $retour .= '<th><input name="s_max" type="text" maxlength="3" size="3" value="'.$s_max.'"></th>';
    $retour .= '</tr>';
    $retour .= '<th>Non scann&eacute; depuis</th>';
    $retour .= '<th><input name="since" type="text" maxlength="3" size="3" value="'.$since.'"> jour(s)</th>';
    $retour .= '<th> </th><th> </th>';
    $retour .= '</tr>';
    $retour .= '';
    $retour .= '<tr><th colspan="4"><input type="submit" value="Chercher"></th>		</tr></tbody></form></table><br />';
  
   $tab_retour = array();
   $tab_retour["html"]=$retour;
   $tab_retour["g_max"]=$g_max;
   $tab_retour["g_min"]=$g_min;
   $tab_retour["s_max"]=$s_max;
   $tab_retour["s_min"]=$s_min;
    $tab_retour["since"]=$since;

	
    return $tab_retour ;
    
    
	}
	function preparationTri($tab_player, $nb_colonne){
	
		$tabAlgo = array();
		for ($x=0; $x<count($tab_player); $x++){
			$line = explode('|',$tab_player[$x]);

			for ($i=0; $i<$nb_colonne; $i++){
				  $tabAlgo[$i][$x] = $line[$i];
			}
		}
		return $tabAlgo;
	}
	function montd($arg){
		$rowWidth = 14;
		return $tdlime = "<th class='c' width='".$rowWidth."%'><a href='index.php?action=inactifs&subaction=$arg' style='color: lime;'>";
	}
	require_once("views/page_header.php");

	$query = "SELECT `active`,`root` FROM `".TABLE_MOD."` WHERE `action`='inactifs' AND `active`='1' LIMIT 1";
	if (!$db->sql_numrows($db->sql_query($query))) die('Mod désactivé !');
	$result = $db->sql_query($query);
	list($active,$root) = $db->sql_fetch_row($result);
	
	$uni_trouve = true;
	global $server_config;
	if(isset($server_config['xtense_universe']))
	{
		$url = $server_config['xtense_universe'];
		if(strlen($url) >= 20)
		{
			if( strstr($url, "uni") != "") $uni = substr($url,10,strlen($url)-19);
			else 			               $uni = substr($url,7,strlen($url)-16);
			
			define('WARRIDERS','http://www.war-riders.de/fr/'.$uni.'/details/player/');
		}
		else $uni_trouve = false;
		
	}
	else $uni_trouve = false;
		
	define('ACTION','inactifs');
	//define('UNITROUVE',false);
	define('UNITROUVE',$uni_trouve);
	define('SEARCH','index.php?action=search&type_search=player&string_search=');
	define('FOLDER_ANALYSEI','mod/'.$root);
	include(FOLDER_ANALYSEI."/help.php");

	// Menu
	$menu1 = "joueur_absent"; $menulabel1 = "Joueurs Absents";
	$menu2 = "inactivite";    $menulabel2 = "Inactivite inactif";
	$menu3 = "analyseMI";     $menulabel3 = "Analyse mine inactif";
	$menu4 = "non_sonde";     $menulabel4 = "Inactif non sond&eacute;";
	if (!isset($pub_subaction)) $pub_subaction = $menu1;
	echo "<table style='width : 100%; text-align : center; margin-bottom : 20px;'><tr>";
	
	if ($pub_subaction != $menu1) echo montd($menu1);
	else	echo "<th><a>";
	echo $menulabel1."</a></th>";
	
	if ($pub_subaction != $menu2) echo montd($menu2);
	else	echo "<th><a>";
	echo $menulabel2."</a></th>";
	
	if ($pub_subaction != $menu3) echo montd($menu3);
	else	echo "<th><a>";
	echo $menulabel3."</a></th>";
	
	if ($pub_subaction != $menu4) echo montd($menu4);
	else	echo "<th><a>";
	echo $menulabel4."</a></th>";
	
	echo "</tr></table>";
    
	
     
	    //On  affiche de la page demandée
	    if ($pub_subaction == $menu1) include("$menu1.php");
	elseif ($pub_subaction == $menu2) include("$menu2.php");
	elseif ($pub_subaction == $menu3) include("$menu3.php");
	elseif ($pub_subaction == $menu4) include("$menu4.php");
	//Si la page a afficher n'est pas définie, on affiche la première
	else include("$menu1.php");
    
    
   
    
	
	//ecriture dans les logs lors de l'utilisation du mod
	$line = "[mod] ".$user_data['user_name']." consulte le module ".ACTION;
	$fichier = "log_".date("ymd").'.log';
	$line = "/*".date("d/m/Y H:i:s").'*/ '.$line;
	write_file(PATH_LOG_TODAY.$fichier, "a", $line);


	
	page_footer();
	require_once("views/page_tail.php");
