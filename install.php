<?php
/**
 *	install..php Page d'accès au module analyseI
 *	@package	analyse
 *	@author	benneb 
 *	created	: 10/02/2010   
 *	modified	: 10/02/2010  
 */
if (!defined('IN_SPYOGAME')) {
    die("Hacking attempt");
}
global $db, $table_prefix;

$is_ok = false;
$mod_folder = "inactifs";
$is_ok = install_mod ($mod_folder);

if ($is_ok == true){
    $query = "CREATE TABLE IF NOT EXISTS `".$table_prefix.'inactivite'."` ("
        . " inactivite_id INT NOT NULL AUTO_INCREMENT, "
        . " inactivite_nom VARCHAR(50) NOT NULL, "
        . " inactivite_date INT NOT NULL, "
        . " primary key ( inactivite_id )"
        . " )";
        
    $db->sql_query($query);
}else
{
		echo  "<script>alert('Désolé, un problème a eu lieu pendant l'installation, corrigez les problèmes survenue et réessayez.');</script>";
}

