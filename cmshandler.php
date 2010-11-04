<?php
// include main scripts for firing up the app
include_once('main.inc.php');

$serviceFacade = new ServiceFacade(new RequestControllerProtocol());
echo $serviceFacade->execute($_REQUEST);