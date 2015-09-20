<?php

	if (!defined('IN_SPYOGAME')) die("Hacking attempt");
	
	global $db , $table_prefix ;

     // global $pub_ , $pub_g_min , $pub_s_min , $pub_s_max ,$server_config , $pub_subaction , $pub_action;
     
	if(isset($pub_order_by)) $order_by = $pub_order_by;
	else $order_by = 1;
	
	if(isset($pub_sens)) $sens = $pub_sens;
	else $sens = 1;
    
    
    
     $value = array() ;
     $value = tab_recherche() ;
     
	
	$request_ogspy_universe_inactif = "SELECT galaxy,system,row,lower(player)
										FROM ". TABLE_UNIVERSE ."
										where (status = 'i') and galaxy <= ".$value["g_max"]." and galaxy >= ".$value["g_min"]."  and system >= ".$value["s_min"]."  and system <= ".$value["s_max"]."  
										and CONCAT(`galaxy`,':',`system`,':',`row`) not in
										(select coordinates from ". TABLE_PARSEDSPY ." where dateRE > ".since($value["since"])." )";
	if( $order_by == 1 && $sens == 1)	$request_ogspy_universe_inactif .= "order by galaxy desc, system desc, row desc;";
	if( $order_by == 1 && $sens == 2)	$request_ogspy_universe_inactif .= "order by galaxy asc, system asc, row asc;";
	//	var_dump($request_ogspy_universe_inactif);		
	$result_ogspy_universe_inactif = $db->sql_query($request_ogspy_universe_inactif);
	
	$tab_player = array();
	$index_tab = 0;
	$ttttttttt = time();
	while(list($galaxy,$system,$row,$player)=$db->sql_fetch_row($result_ogspy_universe_inactif)){
	
		$request_ogspy_inactif = "select inactivite_date from ".$table_prefix."inactivite where inactivite_nom like '$player' limit 1";
		$result_ogspy_inactif = $db->sql_query($request_ogspy_inactif);
		list($inactivite_date) = $db->sql_fetch_row( $result_ogspy_inactif );
		if($inactivite_date > 0)
		{
			$dif = $ttttttttt - $inactivite_date;
			$tab_player[$index_tab] = "$player|$galaxy:$system:$row|".(round($dif/(3600*24),1));
		}
		else
		{
			$tab_player[$index_tab] = "$player|$galaxy:$system:$row|mettre_a_jour_ogspy";
		}
		
		$index_tab++;
	}
	$nb_colonne = 3;
	
	//--------------------------------------
	//algorithme de tri sur la colonne n° order_by
	$tabAlgo = array();
	$sort_by = SORT_REGULAR;
	$tabAlgo = preparationTri($tab_player, $nb_colonne);
	if(count($tabAlgo) > 0)
	{
		$a = $tabAlgo[$order_by];
		if($sens == 1 && $order_by <> 1)	arsort($a, $sort_by);
		if($sens == 2 && $order_by <> 1)	 asort($a, $sort_by);
	}
	//---------------------------------------

     echo $value["html"];

     
	$link ="index.php?action=".ACTION."&subaction=non_sonde&since=".$value["since"]."&g_max=".$value["g_max"]."&g_min=".$value["g_min"]."&s_max=".$value["s_max"]."&s_min=".$value["s_min"]."";
	
    echo "<table style=\"background-color: rgba(0,0,0,0.8);\" cellpudding=0 cellspacing=0 border=1>";
	echo "<tr><th><a href='".$link."&order_by=0&sens=1'><img src='images/asc.png'></a>  Nom  <a href='".$link."&order_by=0&sens=2'><img src='images/desc.png'></a></th>
		       <th><a href='".$link."&order_by=1&sens=1'><img src='images/asc.png'></a>  Coord  <a href='".$link."&order_by=1&sens=2'><img src='images/desc.png'></a></th>
			  <th><a href='".$link."&order_by=2&sens=1'><img src='images/asc.png'></a>  nb jours  ".help("inactif_nbjours")."<a href='".$link."&order_by=2&sens=2'><img src='images/desc.png'></a></th>
		</tr>";
	if(count($tabAlgo) > 0)
	{
		foreach ($a as $k => $v){

				if(strlen($tabAlgo[0][$k]) > 2 )
				{
					echo "<tr>";
					echo "<td><a href='".SEARCH.$tabAlgo[0][$k]."&strict=on'>".$tabAlgo[0][$k]."</a></td>";
					echo "<td style='text-align:right;'>".$tabAlgo[1][$k]."</td>";
					echo "<td style='text-align:right;'>".$tabAlgo[2][$k]."</td>";
					echo "</tr>";
				}
		}
	}
	echo "</table>";
    	
?>