<?php

/**
 * 
 *	Development server control panel.
 *	Version 0.1, written by Dennis Futselaar <dennis.futselaar@webgamic.nl>
 *	Version 0.2, written by Dennis Futselaar <dennis.futselaar@webgamic.nl>
 *	Version 0.3, written by Dennis Futselaar <dennis.futselaar@webgamic.nl> & 
 *							Gabor de Mooij <gabor.de.mooij@webgamic.nl>
 *  Version 0.4, written by Dennis Futselaar & Gabor de Mooij & Robert Cabri
 *	
 *	This page is used for controlling the development image, like:
 *
 *	-	Snapshotting.
 * 
 * 	This script assumes, as all the other modules, that sudo is available and properly installed for use.
 * 	
 */
require_once('main.inc.php');
header('location: '.Conf::get('general.url.www').'/snapshot');