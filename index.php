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

	//------------------------
	// Pieds de page mod/ogspy
	page_footer();
	require_once("views/page_tail.php");
?>