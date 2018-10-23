<?php
/**
 *	uninstall..php Page analyseI
 *	@package	analyseI
 *	@author	benneb 
 *	created	: 10/02/2010   
 *	modified	: 10/02/2010  
 */

if (!defined('IN_SPYOGAME')) {
    die("Hacking attempt");
}

global $db, $table_prefix;
$mod_uninstall_name = "Analyse Inactifs";
$mod_uninstall_table = $table_prefix."inactivite";
uninstall_mod ($mod_uninstall_name, $mod_uninstall_table);

