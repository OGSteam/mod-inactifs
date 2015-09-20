<?php

	if (!defined('IN_SPYOGAME')) die("Hacking attempt");

	global $db, $prefixe;
    
    
     $value = array() ;
     $value = tab_recherche() ;
     
	//rajouter vitesse univers			
	//requete V3
     
     $sub_request = ""; // uniquemen t si on demande des I sans moyen de defense
     if (isset($pub_notdef))
     {
     	$sub_request = " and LM 	= 0
     	and LLE 	= 0
     	and LLO 	= 0
     	and CG 	= 0
     	and AI 	= 0
     	and LP 	= 0
     	and PT 	= 0
     	and GT 	= 0
     	and CLE 	= 0
     	and CLO 	= 0
     	and CR 	= 0
     	and VB 	= 0
     	and VC 	= 0
     	and REC 	= 0
     	and BMD 	= 0
     	and DST 	= 0
     	and EDLM 	= 0
     	and TRA 	= 0 ";
     
     }
     
      
     
	$request_ogspy_universe_inactif = "SELECT coordinates, max(datere),energie, M, C, D, lower(player) , metal , cristal , deuterium 
										FROM ".TABLE_PARSEDSPY.", ".TABLE_UNIVERSE."
										where `coordinates`=CONCAT(`galaxy`,':',`system`,':',`row`)
										and (status = 'i') and galaxy <= ".$value["g_max"]." and galaxy >= ".$value["g_min"]."  and system >= ".$value["s_min"]."  and system <= ".$value["s_max"]." 
                                        and dateRE > ".since($value["since"])."
										and M > 1
										".$sub_request." 
										group by coordinates";

				
	$result_ogspy_universe_inactif = $db->sql_query($request_ogspy_universe_inactif);
	$tab_player = array();
	$index_tab = 0;
	
	while(list($coordinates, $datere ,$energie, $M, $C, $D, $player , $metal , $cristal , $deuterium )=$db->sql_fetch_row($result_ogspy_universe_inactif)){

		//température moyenne
		$temperature = 60;
		$metal_heure = 20 + round(floor(30 * $M * pow(1.1, $M)));
		$cristal_heure = 10 + round(floor(20 * $C * pow(1.1, $C)));
		$deut_heure = round(floor(10 * $D * pow(1.1, $D) * (-0.002 * $temperature + 1.28)));
		$total = (int)($metal + $cristal + $deuterium);
				
		// nb pt de produit
		$prod_pt = ($metal_heure+$cristal_heure+$deut_heure)*24/5000;
		if(isset($server_config['xtense_universe'])) 
		{
			$prod_pt = $prod_pt * $server_config['speed_uni'];
		}
		$tab_player[$index_tab] = "$player|$coordinates|$M|$C|$D|$prod_pt|$metal|$cristal|$deuterium|$total";
		$index_tab++;
	}	
	
	$nb_colonne = 10;
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

	echo $value["html"];
	echo "<table style=\"background-color: rgba(0,0,0,0.8);\" cellpudding=0 cellspacing=0 border=1>";
	$link ="index.php?action=".ACTION."&subaction=analyseMI&since=".$value["since"]."&g_max=".$value["g_max"]."&g_min=".$value["g_min"]."&s_max=".$value["s_max"]."&s_min=".$value["s_min"]."";

	echo "<tr><th><a href='".$link."&order_by=0&sens=1'><img src='".$prefixe."images/asc.png'></a>  Nom  <a href='".$link."&order_by=0&sens=2'><img src='".$prefixe."images/desc.png'></a></th>";
		if(UNITROUVE)	echo "<th>War riders</th>";
		echo "<th><a href='".$link."&order_by=1&sens=1'><img src='".$prefixe."images/asc.png'></a>  Coord ".help("analyseMI_ouvrir_re")." <a href='".$link."&order_by=1&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=2&sens=1'><img src='".$prefixe."images/asc.png'></a>  M  <a href='".$link."&order_by=2&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=3&sens=1'><img src='".$prefixe."images/asc.png'></a>  C  <a href='".$link."&order_by=3&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=4&sens=1'><img src='".$prefixe."images/asc.png'></a>  D  <a href='".$link."&order_by=4&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=5&sens=1'><img src='".$prefixe."images/asc.png'></a>  PT/jour ".help("analyseMI_pt")." <a href='".$link."&order_by=5&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=6&sens=1'><img src='".$prefixe."images/asc.png'></a>  Metal  ".help("analyseMI_ress")." <a href='".$link."&order_by=5&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=7&sens=1'><img src='".$prefixe."images/asc.png'></a>  Cristal ".help("analyseMI_ress")." <a href='".$link."&order_by=5&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=8&sens=1'><img src='".$prefixe."images/asc.png'></a>  Deuterium  ".help("analyseMI_ress")." <a href='".$link."&order_by=5&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=9&sens=1'><img src='".$prefixe."images/asc.png'></a>  Total  ".help("analyseMI_ress")." <a href='".$link."&order_by=5&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  
		</tr>";
		
	if(count($tabAlgo) > 0)
	{
		foreach ($a as $k => $v){

				$coords = explode(':',$tabAlgo[1][$k]);
				echo "<tr>";
				echo "<td><a href='".SEARCH.$tabAlgo[0][$k]."&strict=on'>".$tabAlgo[0][$k]."</a></td>";
				if(UNITROUVE)	echo "<td><a href='".WARRIDERS.$tabAlgo[0][$k]."'>".$tabAlgo[0][$k]."</a></td>";
				//echo "<td><a href='index.php?action=show_reportspy&galaxy=".$coords[0]."&system=".$coords[1]."&row=".$coords[2]."'>".$tabAlgo[1][$k]."</a></td>";
				echo "<td><a href=\"#\" onclick=\"window.open('index.php?action=show_reportspy&amp;galaxy=".$coords[0]."&amp;system=".$coords[1]."&amp;row=".$coords[2]."','_blank','width=640, height=480, toolbar=0, location=0, directories=0, status=0, scrollbars=1, resizable=1, copyhistory=0, menuBar=0');return(false)\">".$tabAlgo[1][$k]."</a></td>";
                echo "<td style='text-align:center;'>".$tabAlgo[2][$k]."</td>";
				echo "<td style='text-align:center;'>".$tabAlgo[3][$k]."</td>";
				echo "<td style='text-align:center;'>".$tabAlgo[4][$k]."</td>";
				echo "<td style='text-align:center;'>".$tabAlgo[5][$k]."</td>";
				echo "<td style='text-align:center;'>".number_format($tabAlgo[6][$k],0, ',', '.') ."</td>";
				echo "<td style='text-align:center;'>".number_format($tabAlgo[7][$k],0, ',', '.') ."</td>";
				echo "<td style='text-align:center;'>".number_format($tabAlgo[8][$k],0, ',', '.') ."</td>";
				echo "<td style='text-align:center;'>".number_format($tabAlgo[9][$k],0, ',', '.') ."</td>";
				echo "</tr>";
		}
	}
	echo "</table>";
	var_dump($pub_notdef);
?>



