<?php
/**
 *	update..php Page analyseI
 *	@package	analyseI
 *	@author	benneb 
 *	created	: 10/02/2010   
 *	modified	: 11/02/2010  
 */

if (!defined('IN_SPYOGAME')) {
    die("Hacking attempt");
}

global $db, $table_prefix;
$mod_folder = "inactifs";
$mod_name = "Analyse Inactifs";
update_mod($mod_folder, $mod_name);

?>