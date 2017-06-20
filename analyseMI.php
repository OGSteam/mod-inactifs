<?php

	if (!defined('IN_SPYOGAME')) die("Hacking attempt");

	global $db, $prefixe;
    
    
     $value = array() ;
     $value = tab_recherche() ;
     
	//rajouter vitesse univers			
	//requete V3
     

     	$sub_request = " , 
     	CASE 
			WHEN LM < 0
			THEN 0
			ELSE LM*2000
		END
		+
		CASE
		 	WHEN LLE <0
			THEN 0
			ELSE LLE*2000
		END
		+
		CASE
			WHEN LLO <0
			THEN 0
			ELSE LLO*8000
		END
		+
		CASE
			WHEN CG <0
			THEN 0
			ELSE CG*37000
		END
		+
		CASE
			WHEN AI<0
			THEN 0
			ELSE AI*8000
		END
		+
		CASE
			WHEN LP<0
			THEN 0
			ELSE LP*130000
		END
		+
		CASE
			WHEN GT <0
			THEN 0
			ELSE GT*6000
		END
		+
		CASE
			WHEN PT<0
			THEN 0
			ELSE PT*4000
		END
		+
		CASE
			WHEN CLE<0
			THEN 0
			ELSE CLE*4000
		END
		+
		CASE
			WHEN CLO<0
			THEN 0
			ELSE CLO*10000
		END
		+
		CASE
			WHEN CR<0
			THEN 0
			ELSE CR*29000
		END	
		+
		CASE
			WHEN VB<0
			THEN 0
			ELSE VB*60000
		END		
		+
		CASE
			WHEN VC<0
			THEN 0
			ELSE VC*40000
		END
		+
		CASE
			WHEN REC<0
			THEN 0
			ELSE REC*18000
		END
		+
		CASE
			WHEN BMD<0
			THEN 0
			ELSE BMD*90000
		END
		+
		CASE
			WHEN DST<0
			THEN 0
			ELSE DST*125000
		END
		+
		CASE
			WHEN EDLM<0
			THEN 0
			ELSE EDLM*10000000
		END
		+
		CASE
			WHEN TRA<0
			THEN 0
			ELSE TRA*85000
		END
		+
		CASE
			WHEN PB<= 0
			THEN 0
			ELSE PB*20000
		END
		+
		CASE
			WHEN GB<=0
			THEN 0
			ELSE GB*100000
		END
			AS totaldef      	";
     
	$request_ogspy_universe_inactif = "SELECT coordinates, max(datere),energie, M, C, D, lower(player) , metal , cristal , deuterium 	".$sub_request." 
										FROM ".TABLE_PARSEDSPY.", ".TABLE_UNIVERSE."
										where `coordinates`=CONCAT(`galaxy`,':',`system`,':',`row`)
										and (status = 'i') and (status <>'%v%') and galaxy <= ".$value["g_max"]." and galaxy >= ".$value["g_min"]."  and system >= ".$value["s_min"]."  and system <= ".$value["s_max"]." 
                                        and dateRE > ".since($value["since"])."
										and M > 1
									
										group by coordinates";

				
	$result_ogspy_universe_inactif = $db->sql_query($request_ogspy_universe_inactif);
	$tab_player = array();
	$index_tab = 0;
	
	while(list($coordinates, $datere ,$energie, $M, $C, $D, $player , $metal , $cristal , $deuterium ,$totaldef)=$db->sql_fetch_row($result_ogspy_universe_inactif)){

		//température moyenne
		$temperature = 60;
		$metal_heure = 20 + round(floor(30 * $M * pow(1.1, $M)));
		$cristal_heure = 10 + round(floor(20 * $C * pow(1.1, $C)));
		$deut_heure = round(floor(10 * $D * pow(1.1, $D) * (-0.002 * $temperature + 1.28)));
		$total = (int)($metal + $cristal + $deuterium);
				
		// nb pt de produit
		$prod_pt = ceil(($metal_heure+$cristal_heure+$deut_heure)*24/25000);
		if(isset($server_config['xtense_universe'])) 
		{
			$prod_pt = $prod_pt * $server_config['speed_uni'];
		}
		$tab_player[$index_tab] = "$player|$coordinates|$M|$C|$D|$prod_pt|$metal|$cristal|$deuterium|$total|$totaldef";
		$index_tab++;
	}	
	
	$nb_colonne = 11;
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
			  <th><a href='".$link."&order_by=5&sens=1'><img src='".$prefixe."images/asc.png'></a>  GT/jour ".help("analyseMI_pt")." <a href='".$link."&order_by=5&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=6&sens=1'><img src='".$prefixe."images/asc.png'></a>  Metal  ".help("analyseMI_ress")." <a href='".$link."&order_by=6&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=7&sens=1'><img src='".$prefixe."images/asc.png'></a>  Cristal ".help("analyseMI_ress")." <a href='".$link."&order_by=7&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=8&sens=1'><img src='".$prefixe."images/asc.png'></a>  Deuterium  ".help("analyseMI_ress")." <a href='".$link."&order_by=8&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=9&sens=1'><img src='".$prefixe."images/asc.png'></a>  Total  ".help("analyseMI_ress")." <a href='".$link."&order_by=9&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=10&sens=1'><img src='".$prefixe."images/asc.png'></a>  Total defense  ".help("analyseMI_unitdef")." <a href='".$link."&order_by=10&sens=2'><img src='".$prefixe."images/desc.png'></a></th>
			  
		</tr>";
		
	if(count($tabAlgo) > 0)
	{
		foreach ($a as $k => $v){

				$coords = explode(':',$tabAlgo[1][$k]);
				echo "<tr>";
				echo "<td><a href='".SEARCH.$tabAlgo[0][$k]."&strict=on'>".$tabAlgo[0][$k]."</a></td>";
				if(UNITROUVE)	echo "<td><a target=\"_blank\" href='".WARRIDERS.$tabAlgo[0][$k]."'>".$tabAlgo[0][$k]."</a></td>";
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
				echo "<td style='text-align:center;'>".number_format($tabAlgo[10][$k],0, ',', '.') ."</td>";
				echo "</tr>";
		}
	}
	echo "</table>";

?>



