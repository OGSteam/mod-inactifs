<?php

	if (!defined('IN_SPYOGAME')) die("Hacking attempt");

	global $db, $prefixe;
	//rajouter vitesse univers			
	//requete V3
	$request_ogspy_universe_inactif = "SELECT coordinates, max(datere),energie, M, C, D, lower(player)
										FROM ogspy_parsedspy, ".TABLE_UNIVERSE."
										where `coordinates`=CONCAT(`galaxy`,':',`system`,':',`row`)
										and (status = 'i')
										and M > 1
										group by coordinates";

				
	$result_ogspy_universe_inactif = $db->sql_query($request_ogspy_universe_inactif);
	$tab_player = array();
	$index_tab = 0;
	
	while(list($coordinates, $datere ,$energie, $M, $C, $D, $player)=$db->sql_fetch_row($result_ogspy_universe_inactif)){

		//température moyenne
		$temperature = 60;
		$metal_heure = 20 + round(floor(30 * $M * pow(1.1, $M)));
		$cristal_heure = 10 + round(floor(20 * $C * pow(1.1, $C)));
		$deut_heure = round(floor(10 * $D * pow(1.1, $D) * (-0.002 * $temperature + 1.28)));
				
		// nb pt de produit
		$prod_pt = ($metal_heure+$cristal_heure+$deut_heure)*24/5000;
		if(isset($server_config['xtense_universe'])) 
		{
			$prod_pt = $prod_pt * $server_config['speed_uni'];
		}
		$tab_player[$index_tab] = "$player|$coordinates|$M|$C|$D|$prod_pt";
		$index_tab++;
	}	
	
	$nb_colonne = 6;
	if(isset($pub_order_by)) $order_by = $pub_order_by;
	else $order_by = 2;

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
	
	echo "<table cellpudding=0 cellspacing=0 border=1>";
	$link ="index.php?action=".ACTION."&subaction=analyseMI";

	echo "<tr><th><a href='".$link."&order_by=0&sens=1'><img src='".$prefixe."images/asc.png'></a>  Nom  <a href='".$link."&order_by=0&sens=2'><img src='".$prefixe."images/desc.png'></a></th>";
		if(UNITROUVE)	echo "<th>War riders</th>";
		echo "<th><a href='".$link."&order_by=1&sens=1'><img src='".$prefixe."images/asc.png'></a>  Coord ".help("analyseMI_ouvrir_re")." <a href='".$link."&order_by=1&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=2&sens=1'><img src='".$prefixe."images/asc.png'></a>  M  <a href='".$link."&order_by=2&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=3&sens=1'><img src='".$prefixe."images/asc.png'></a>  C  <a href='".$link."&order_by=3&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=4&sens=1'><img src='".$prefixe."images/asc.png'></a>  D  <a href='".$link."&order_by=4&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=5&sens=1'><img src='".$prefixe."images/asc.png'></a>  PT/jour ".help("analyseMI_pt")." <a href='".$link."&order_by=5&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			</tr>";
		
	if(count($tabAlgo) > 0)
	{
		foreach ($a as $k => $v){

				$coords = explode(':',$tabAlgo[1][$k]);
				echo "<tr>";
				echo "<td><a href='".SEARCH.$tabAlgo[0][$k]."&strict=on'>".$tabAlgo[0][$k]."</a></td>";
				if(UNITROUVE)	echo "<td><a href='".WARRIDERS.$tabAlgo[0][$k]."'>".$tabAlgo[0][$k]."</a></td>";
				echo "<td><a href='index.php?action=show_reportspy&galaxy=".$coords[0]."&system=".$coords[1]."&row=".$coords[2]."'>".$tabAlgo[1][$k]."</a></td>";
				echo "<td style='text-align:center;'>".$tabAlgo[2][$k]."</td>";
				echo "<td style='text-align:center;'>".$tabAlgo[3][$k]."</td>";
				echo "<td style='text-align:center;'>".$tabAlgo[4][$k]."</td>";
				echo "<td style='text-align:center;'>".$tabAlgo[5][$k]."</td>";
				echo "</tr>";
		}
	}
	echo "</table>";
	
?>