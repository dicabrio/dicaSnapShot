<?php

/**
 *
 *	Development server control panel.
 *	Version 0.1, written by Dennis Futselaar <dennis.futselaar@webgamic.nl>
 *
 *	This page lets you switch between branches
 *
 */

$sTitle	= 'Branch switching.';

// oBranch contains the branch object that is initialized by index.

if (isset($_POST, $_POST['submit'], $_POST['project_id'], $_POST['branch_id'])) {
	$bResult = $oBranch->switchToBranch($_POST['project_id'], $_POST['branch_id']);
}

if (isset($bResult)) {
	echo 'Branch switching '.($bResult ? 'is succesfully done' : 'failed.').'<br>';
}

if (isset($_GET['action'])) {
	if ($_GET['action'] == 'refresh') {

		if (isset($_GET['fast'])) {
			$bFast	= true;
		}
		else {
			$bFast	= false;
		}

		$oBranch->renewBranchCache(NULL, $bFast);

		// Redirect the user, to prevent him from refreshing again.
		header('Location: /?module=branch');
		exit;
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>Branch Switching</title>
	<link rel="stylesheet" rev="stylesheet" href="style.css" media="screen" />
</head>
<body>
<h1>Branch Switching</h1>

<?php include('menu.inc.php'); ?>

<br /><br />
<a href="?module=branch&amp;action=refresh&amp;fast=true">Fast refresh branch cache (may miss somethings..)</a><br />
<!--<a href="?module=branch&amp;action=refresh">Refresh branch cache</a>--><br />
<?php

$aBranches = $oBranch->getAllBranches();
foreach ($aBranches as $iProjectID => &$aProjectInfo) {
	echo '	<form method="post" action="?module=branch" style="float:left;">
				<div style="margin: 0 20px 0 0; height: 400px; overflow: auto; border: 1px solid black; padding: 5px;">
				<fieldset style="border: none; margin: 0; padding: 0;">
					<legend style="padding: 5px; border: 1px solid black; background: red; color: white; font-weight: bold;">'.$aProjectInfo['name'].'</legend>
					<input type="hidden" name="project_id" value="'.$iProjectID.'">';
	foreach ($aProjectInfo['branches'] as $iBranchID => $aBranchInfo) {
		echo (empty($aBranchInfo['name']) ? '<b>(root)</b>' : $aBranchInfo['name']);
		echo ' <input type="radio" name="branch_id" value="'.$iBranchID.'"';
		if ($aBranchInfo['is_current']) {
			echo ' checked="checked"';
		} 
		echo ' /><br />';
	} 
	echo '
					</fieldset>
				</div>
				<input type="submit" name="submit" value="Change branch">
			</form>';

}

?>
</body>
</html>