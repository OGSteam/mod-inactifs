<?php

	if (!defined('IN_SPYOGAME')) die("Hacking attempt");

	global $db, $prefixe;
    
     $value = array() ;
     $value = tab_recherche() ;
     
	if(isset($pub_nbjours)) $nb_jour_inactif = $pub_nbjours;
	else $nb_jour_inactif = 3;
	$nb_jour_inactif_sec = $nb_jour_inactif * 24*60*60;

	$request_ogspy_universe_inactif = "select distinct(lower(player)) from ".TABLE_UNIVERSE." where ".
									"not(status like '%v%') ".
									"and not (status like '%i%') ".
									"and not (status like '%d%') ".
                                    "and galaxy <= ".$value["g_max"]." and galaxy >= ".$value["g_min"]."  and system >= ".$value["s_min"]."  and system <= ".$value["s_max"]." ". 
									"and not(player = '');";
				
	$result_ogspy_universe_inactif = $db->sql_query($request_ogspy_universe_inactif);

	$tab_player = array();
	$tab_point = array();
	$tab_vaisseaux = array();
	$index_tab = 0;
	while(list($player)=$db->sql_fetch_row($result_ogspy_universe_inactif)){
	
		$r = galaxy_show_ranking_unique_player($player);
		
		if(empty($r)) $r[0] = "";
		$mark = false;
		$premier_classement_point_trouve = false;
		$premier_classement_point = 0;
		$premiere_date = 0;
		$old_point = 0;
		$old_vaisseaux = 0 ;
		$joueur_absent = true;
		$dernier_classement_point_trouve = 0;
		$dernier_classement_vaisseaux_trouve = 0;
		foreach($r as $d => $v){
		
			$madate = ($d!=0) ? $d : '-' ;
			$point_general  = (isset($v['general']) && isset($v['general']['points'])) ? $v['general']['points']  :  '' ;
			$point_vaisseaux =  (isset($v['fleet']) && isset($v['fleet']['points']))? $v['fleet']['points'] : '';
			
			//stocker la premiere date de l'iteration
			if (!$mark)
			{
				$premiere_date = $madate;
				$mark = true;
			}
			//old point est plus recent vu que le premier classement parcouru est celui d'ajd
			if( $point_general != '' )
			{
				if(!$premier_classement_point_trouve)
				{
					$premier_classement_point_trouve = true;
					$premier_classement_point = $point_general;
				}
				
				$dernier_classement_point_trouve = $point_general ;
				if(!($old_point <= $point_general))
				{
					$joueur_absent = false;
					break;
				}
			}
			if( $point_vaisseaux != '' )
			{
				$dernier_classement_vaisseaux_trouve = $point_vaisseaux;
				if(!($old_vaisseaux <= $point_vaisseaux))
				{
					$joueur_absent = false;
					break;
				}
			}
			//on s'arrete apres avoir parcouru $nb_jour_inactif
			if( $premiere_date - $nb_jour_inactif_sec > $madate) break;
			
			$old_point = $point_general ;
			$old_vaisseaux = $point_vaisseaux ;
		}
		if( $joueur_absent )
		{
			//on regarde la colo la plus recente à jour
			$status = "select status from ".TABLE_UNIVERSE." where player like '$player' order by last_update desc limit 1";
			$result_status = $db->sql_query($status);
			list($status)=$db->sql_fetch_row($result_status);
			//si il n'est pas en mv
			if(substr_count($status, 'v') == 0)
			{
				$tab_player[$index_tab] = $player."|".$dernier_classement_point_trouve."|".$dernier_classement_vaisseaux_trouve."|".($premier_classement_point - $dernier_classement_point_trouve);
				$index_tab++;
			}
		}
	}
	$nb_colonne = 4;
	if(isset($pub_order_by)) $order_by = $pub_order_by;
	else $order_by = 1;

	if(isset($pub_sens)) $sens = $pub_sens;
	else $sens = 1;
	//--------------------------------------
	//algorithme de tri sur la colonne n° order_by
	$tabAlgo = array();
	$sort_by = SORT_REGULAR;
	$tabAlgo = preparationTri($tab_player, $nb_colonne);
	if(count($tabAlgo) > 0)
	{
		$a = $tabAlgo[$order_by];
		if($sens == 1)	arsort($a, $sort_by);
		if($sens == 2)	asort($a, $sort_by);
	}
	//---------------------------------------
	
    
      echo $value["html"];
      
      
	$link ="index.php?action=".ACTION."&subaction=joueur_absent&order_by=$order_by&sens=$sens";

	//en reglant à 0 ou 1 ce mod s apparente à un top flop journalier";
	echo "<table cellpudding=0 cellspacing=0 border=1>";
	echo "<tr><td>".help("joueur_absent_nbjours")." Choix du nombre de jours : </td>";
		for($i=0; $i<11; $i++)
		{
			if($i <> $nb_jour_inactif)
				echo "<td><a href='".$link."&nbjours=$i'>$i</a></td>";
			else
				echo "<td><a style='color:red;' href='".$link."&nbjours=$i'>$i</a></td>";
		}
	echo "</tr>";
	echo "</table>";
	
	$link ="index.php?action=".ACTION."&subaction=joueur_absent&nbjours=$nb_jour_inactif";
	echo "<table cellpudding=0 cellspacing=0 border=1>";
	echo "<tr><th><a href='".$link."&order_by=0&sens=1'><img src='".$prefixe."images/asc.png'></a>  Nom  <a href='".$link."&order_by=0&sens=2'><img src='".$prefixe."images/desc.png'></a></th>";
		if(UNITROUVE) echo "<th>War riders</th>";
		
	echo "<th><a href='".$link."&order_by=1&sens=1'><img src='".$prefixe."images/asc.png'></a>  Points  <a href='".$link."&order_by=1&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
		  <th><a href='".$link."&order_by=2&sens=1'><img src='".$prefixe."images/asc.png'></a>  Vaisseaux  <a href='".$link."&order_by=2&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
		  <th><a href='".$link."&order_by=3&sens=1'><img src='".$prefixe."images/asc.png'></a>  Progression  ".help("joueur_absent_progression")."<a href='".$link."&order_by=3&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
		</tr>";
		
	if(count($tabAlgo) > 0)
	{
		foreach ($a as $k => $v){

			if(strlen($tabAlgo[0][$k]) > 2 )
			{
				echo "<tr>";
				echo "<td><a href='".SEARCH.$tabAlgo[0][$k]."&strict=on'>".$tabAlgo[0][$k]."</a></td>";
				if(UNITROUVE) echo "<td><a href='".WARRIDERS.$tabAlgo[0][$k]."'>".$tabAlgo[0][$k]."</a></td>";
				echo "<td style='text-align:right;'>".$tabAlgo[1][$k]."</td>";
				echo "<td style='text-align:right;'>".$tabAlgo[2][$k]."</td>";
				echo ($tabAlgo[3][$k] == 0) ? "<td style='text-align:right;'>0</td>" : "<td style='color:red;text-align:right;'>".$tabAlgo[3][$k]."</td>";
				echo "</tr>";
			}
		}
	}
	echo "</table>";
		
?>