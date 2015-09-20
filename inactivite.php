<?php

	if (!defined('IN_SPYOGAME')) die("Hacking attempt");

	global $db,$table_prefix;
	$nom_table= $table_prefix."inactivite";
	//**********************************************************************************************************
	//on recupere la liste des inactifs de la table pour enlever les joueurs qui ne jouent plus, ou qui ne sont plus inactif
	//**********************************************************************************************************
	
	//etape 1 suppression des joueurs delete
	$request_ogspy_universe = "SELECT distinct inactivite_nom ".
							  "FROM ".$nom_table." ".
							  "WHERE inactivite_nom not in ( select distinct player from ".TABLE_UNIVERSE." );";
								
	$result_ogspy_universe = $db->sql_query($request_ogspy_universe);
	while(list($inactivite_nom)=$db->sql_fetch_row($result_ogspy_universe)){
		$request = "DELETE FROM $nom_table WHERE inactivite_nom = '$inactivite_nom'";
		$resultat = $db->sql_query($request);
	}
	
	//etape 2 suppression des joueurs qui ne sont plus inactif ou qui sont en mv
	$request_ogspy_universe = "select player, last_update, status from ".TABLE_UNIVERSE." ".
						"where not(player = '') order by player ;";
	$result_ogspy_universe = $db->sql_query($request_ogspy_universe);
	$nom_temp = "";
	$date = 0;
	$old_status = "";
	while(list($player, $last_update, $status)=$db->sql_fetch_row($result_ogspy_universe)){
		if($nom_temp != $player)
		{
			//si non inactif on supprime de la table inactif
			if($old_status != "i")
			{
				$request = "DELETE  FROM $nom_table WHERE inactivite_nom = '$nom_temp'";
				$resultat = $db->sql_query($request);
			}
			$nom_temp = $player;
			$date = 0;
			$old_status = $status;
		}
		else
		{
			if($date < $last_update)
			{
				$date = $last_update;
				$old_status = $status;
			}
		}
	}
	//************************************
	//on recherche les joueurs inactifs
	//************************************
	//il ne faut pas filtrer avec and (status like '%i%')   , un joueur peut ne pas etre inactif mais avoir encore des syst marqué i (syst non à jour, ancien i)
	$request_ogspy_universe_inactif = "select player, last_update, status from ".TABLE_UNIVERSE." where ".
									  "not(player = '') order by player;";
			
	$result_ogspy_universe_inactif = $db->sql_query($request_ogspy_universe_inactif);
	$date = 0;
	$old_status = "";
	$nom_temp = "";
	//trier par player, il faut chercher le systeme le plus à jour pour avoir le dernier status connu
	while(list($player, $last_update, $status)=$db->sql_fetch_row($result_ogspy_universe_inactif)){
		if($nom_temp != $player)
		{
			if($old_status == "i")
			{
				//si joueur n'est pas dans la table inactivite, on l'insere
				$request = "select count(distinct inactivite_nom) from $nom_table where inactivite_nom = '$nom_temp';";
				$result = $db->sql_query($request);
				list($nb) = $db->sql_fetch_row( $result );
				if($nb == 0)
				{
					if($date == 0)
					{
						$request = "INSERT INTO $nom_table ( inactivite_nom, inactivite_date ) VALUES ('$nom_temp', '$last_update')";
					}
					else
					{
						$request = "INSERT INTO $nom_table ( inactivite_nom, inactivite_date ) VALUES ('$nom_temp', '$date')";
					}
					$resultat = $db->sql_query($request);
				}
			}
			$nom_temp = $player;
			$old_status = $status;
			$date = 0;
		}
		else
		{
			if($date < $last_update)
			{
				$date = $last_update;
				$old_status = $status;
			}
		}
	}
	//***************************************
	//on afffiche la table inactivite
	//***************************************
	$request_inactivite = "select inactivite_id, inactivite_nom, inactivite_date from `$nom_table` order by inactivite_date desc LIMIT 150;";
	$result_inactivite = $db->sql_query($request_inactivite);
	$ttttttttt = time();	
	echo "<table style=\"background-color: rgba(0,0,0,0.8);\" cellpudding=0 cellspacing=0 border=1>";
	echo "<th>Nom</th>";
	if(UNITROUVE) echo "<th>War riders</th>";
	echo "<th>G&eacute;n&eacute;ral rank</th><th>G&eacute;n&eacute;ral point</th><th>Militaire rank</th><th>Militaire point</th><th>Date</th><th>nb jours ".help("inactif_nbjours")."</th>";
	while(list($index,$nom, $date_inactivite)=$db->sql_fetch_row($result_inactivite)){
		$trouve = False;
		$ligne = "<tr><td><a href='".SEARCH.$nom."&strict=on'>$nom</a></td>";
		if(UNITROUVE) $ligne .="<td><a href='".WARRIDERS.$nom."'>$nom</a></td>";
		$individual_ranking = galaxy_show_ranking_unique_player($nom);
		//à refaire
		
		while ($ranking = current($individual_ranking)){
		  
			$datadate = strftime("%d %b %Y &eacute; %Hh", key($individual_ranking));
			$general_rank = isset($ranking["general"]) ?  formate_number($ranking["general"]["rank"]) : "&nbsp;";
			$general_points = isset($ranking["general"]) ? formate_number($ranking["general"]["points"]) : "&nbsp;";
			$fleet_rank = isset($ranking["military"]) ?  formate_number($ranking["military"]["rank"]) : "&nbsp;";
			$fleet_points = isset($ranking["military"]) ?  formate_number($ranking["military"]["points"]) : "&nbsp;";
			
			$ligne.="<td>$general_rank</td><td>$general_points</td><td>$fleet_rank</td><td>$fleet_points</td>";
			$trouve = True;
			break;
		}
		if(!$trouve) $ligne.="<td></td><td></td><td></td><td></td>";
		$dif = $ttttttttt - $date_inactivite;
		$ligne .="<td>".strftime("%d %b %Y &agrave; %Hh", $date_inactivite)."</td><td style='text-align:right;'>".(round($dif/(3600*24),1))."</td></tr>";
		echo $ligne;
	}
	echo "</table>"; 

?>