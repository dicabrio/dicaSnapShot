<?php

/**
 * 
 *	Development server control panel.
 *	Version 0.1, written by Dennis Futselaar <dennis.futselaar@webgamic.nl>
 *
 *	This file contains a list with all the modules.
 *	
 */

$aModules	= array('general' => array(	'vc' => 'general.php',
										'title' => 'General',
										'image' => '',
										'models' => NULL),
					'branch' =>	array(	'vc' => 'branch.php',
										'title' => 'Branch switching',
										'image' => 'logo.png',
										'models' => array('branch' => 'Branch.class.php') ),
					'snapshot' => array('vc' => 'snapshot.php',
										'title' => 'Database snapshots manager',
										'image' => 'database_icon.jpg',
										'models' => array('snapshot' => 'Snapshot.class.php'))
					);

?>