<?php

$config = array(

	'dir' => array(
		'www' => SYS_DIR,
		'templates' => APP_DIR.'/views',
		'lang' => APP_DIR.'/lang',
	),
	'url' => array(
		'www' => 'http://'.DOMAIN.'/cp',
		'images' => 'http://'.DOMAIN.'/cp/images',
		'css' => 'http://'.DOMAIN.'/cp/css',
		'js' => 'http://'.DOMAIN.'/cp/js',
	),

	'default_lang' => 'NL',
);