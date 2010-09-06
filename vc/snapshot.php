<?php

/**
 *
 *	Snapshot manager
 *
 *
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['action'])) {

	$sAction	= &$_GET['action'];
	$sName	= isset($_GET['name']) ? $_GET['name'] : '';

	if ($sAction == 'create') {

		// Create the snapshot
		if ($oSnapshot->createSnapshot($sName) !== false) {
			header('Location: '.BASE_URL.'?module=snapshot#'.$sName);
			exit;
		}
		else {
			echo 'Snapshot was not created!';
		}

	}
	elseif ($sAction == 'delete') {

		$sID	= isset($_GET['id']) ? $_GET['id'] : '';

		// Delete the snapshot
		if ($oSnapshot->deleteSnapshot($sID)) {
			header('Location: '.BASE_URL.'?module=snapshot#'.$sName);
			exit;
		}
		else {
			echo 'Snapshot was not deleted!';
		}

	}
	elseif ($sAction == 'restore') {

		$sID	= isset($_GET['id']) ? $_GET['id'] : '';


		// Restore the snapshot
		if ($oSnapshot->restoreSnapshot($sID)) {
			header('Location: '.BASE_URL.'?module=snapshot#'.$sName);
			exit;
		}
		else {
			echo 'Snapshot was not restored!';
		}

	}
	elseif ($sAction == 'update') {

		$sID	= isset($_GET['id']) ? $_GET['id'] : '';

		// Delete the snapshot
		if ($oSnapshot->updateSnapshot($sID)) {
			header('Location: '.BASE_URL.'?module=snapshot#'.$sName);
			exit;
		}
		else {
			echo 'Snapshot has been updated!';
		}

	}
	elseif ($sAction == 'rename') {

		$sID = isset($_GET['id']) ? $_GET['id'] : '';

		if (isset($_POST["renameto"]) && strlen(trim($_POST["renameto"]))>0) {

			//echo $sID; exit;
			$oSnapshot->rename( $sID, trim($_POST["renameto"]) );
		}



		header('Location: '.BASE_URL.'?module=snapshot#'.$sName);


	}

}

$aDBOrder = array('torpia_local', 'ge_game', 'ge_admin');
$aDatabases = $oSnapshot->getDatabases();

foreach ($aDatabases as $sDbName) {
	if (!in_array($sDbName, $aDBOrder)) {
		$aDBOrder[] = $sDbName;
	}
}

?>
<html>
	<head>
		<title>Snapshot tool</title>
		<link rel="stylesheet" rev="stylesheet" href="style.css" media="screen" />
		<style>
			*{font-size: 11px; font-family: arial}

			#tabmenu {
				/*border-bottom: 1px solid #000;*/

				height: 23px;
				margin: 0 0 0 0;
				list-style: none;
				padding: 10px 5px 0 20px;
				background: rgb(67,74,93);
				background-image: -webkit-gradient(linear,
					left bottom,
					left top,
					color-stop(0, rgb(34,37,48)),
					color-stop(20%, rgb(67,74,93)),
					color-stop(90%, rgb(67,74,93)),
					color-stop(1, rgb(34,37,48))
					);
			}
			#tabmenu li {
				cursor: pointer;

				float: left;
				height: 23px;
				margin: 0 2px 0 0;
				background-color: rgb(138,157,185);
				-webkit-border-top-left-radius: 5px;
				-webkit-border-top-right-radius: 5px;
				-moz-border-radius: 5px 5px 0 0;
				border-radius: 5px 5px 0 0;
				background-image: -webkit-gradient(linear,
					left top,
					left bottom,
					color-stop(0, rgb(138,157,185)),
					color-stop(0.8, rgb(138,157,185)),
					color-stop(1, rgba(0,0,0,0.5))
					);
			}

			#tabmenu li.active {
				background: #fff;
				border-bottom: 1px solid #fff;
			}

			.tab {
				margin: 0;
				padding: 20px 5px 20px 20px;
				display: none;
				border-right: 1px solid #000;
				border-bottom: 1px solid #000;
				border-left: 1px solid #000;
			}

			.button {
				-moz-border-radius: 10px;
				border-radius: 10px;
				border: 1px solid #000;
				padding: 5px 10px;
				text-decoration: none;
				color: #000;
				cursor: pointer;

				border: 1px solid rgba(54,97,93,0.5);
				-moz-border-radius: 10px;
				-webkit-border-radius: 10px;
				-opera-border-radius: 10px;
				-khtml-border-radius: 10px;
				border-radius: 10px;
				background-image: -webkit-gradient(linear,
					left bottom,
					left top,
					color-stop(1.00, #efefef),
					color-stop(0.00, #b9b9b9)
					);
				text-shadow: rgba(255,255,255,1) 0px 1px 0px;
			}

			#tabmenu li a {
				display: block;
				padding: 6px 5px;
				text-decoration: none;
				color: #fff;
				font-weight: bold;

			}

			#tabmenu li.active a {
				color: #000;
			}

			.snaps {
				border-collapse: collapse;
			}

			.snaps th {
				padding: 5px;
				text-align: left;
				background-image: -webkit-gradient(linear,
					left bottom,
					left top,
					color-stop(1.00, #efefef),
					color-stop(0.00, #b9b9b9)
					);
				border-right: 1px solid #efefef;
			}
			.snaps td { padding: 5px; }
			.snaps tr td:nth-child(1) { width: 200px;}
			.snaps tr td:nth-child(2) { width: 150px;}

		</style>
		<script type="text/javascript" src="http://www.google.com/jsapi"></script>
		<script type="text/javascript">

			google.load("jquery", "1");
			/**
			 * tabbing system
			 */
			google.setOnLoadCallback(function () {

				$('#tabmenu li').click(function (e) {
					e.preventDefault();
					selectTab(this);
				});

				$('#tabmenu li.active').click();

				var urlHash = window.location.hash;
				selectTab($('#tabmenu li.'+urlHash.substr(1)));
			});

			/**
			 * selecting the tab. Closes all other tabs
			 * @param el
			 * @return
			 */
			function selectTab(el) {
				var sClassName = $(el).attr('className');
				// activate the right tablink
				$('#tabmenu li').removeClass('active');
				$(el).addClass('active');

				// activate the right tabpanel
				$('#tabmenu li').each(function () {
					var sClassName = $(this).attr('className');
					$('#'+sClassName).hide();
				});
				$('#'+sClassName).show();
			}
		</script>
	</head>
	<body>
		<h1>Snapshot Manager</h1>

		<ul id="tabmenu">
			<?php foreach ($aDBOrder as $sDatabase) : ?>
			<li class="<?php echo $sDatabase; ?>"><a href="#"><?php echo $sDatabase; ?></a></li>
			<?php endforeach; ?>
		</ul>

		<?php

		foreach ($aDBOrder as $sDatabase) {

			?>
		<div class="tab" id="<?php echo $sDatabase; ?>">
			<div style="margin: 0 0 20px 0;"><a class="button" href="<?php echo BASE_URL; ?>?module=snapshot&amp;action=create&amp;name=<?php echo $sDatabase ?>">Create snapshot</a> of: <?php echo $sDatabase; ?></div>


				<?php
				$aSnapshotsGroupped = array();
				foreach ($oSnapshot->getSnapshots($sDatabase) as $sID => $aSNInfo) {

					if (!isset($aSnapshotsGroupped[$aSNInfo['name']])) {
						$aSnapshotsGroupped[$aSNInfo['name']]	= array();
					}

					$aSNInfo['sID'] = $sID;
					$aSnapshotsGroupped[$aSNInfo['name']][] = $aSNInfo;

				}

				if (count($aSnapshotsGroupped) == 0) {
					?><div>No snapshots yet</div>
				<?php }

				foreach ($aSnapshotsGroupped as $sName => $aSnapshotInfo) { ?>
			<table class="snaps">
				<tr>
					<th>Name</th>
					<th>Time</th>
					<th>actions</th>
					<th>rename</th>
				</tr>
						<?php
						$aSnapshotInfo = array_reverse($aSnapshotInfo);

						foreach ($aSnapshotInfo as $iTime => $aInfo) {
							$sID = $aInfo['sID'];
							?>
				<tr>
					<td>
						<?php if (!empty($aInfo['title'])) : ?>
						<strong><?php echo $aInfo['title']; ?></strong>
						<?php else : ?>
						No name
						<?php endif; ?>
									<?php if ($aInfo['adus']>0) : ?>
							(<?php echo $aInfo['adus']; ?>)
									<?php endif; ?>
					</td>
					<td>
									<?php echo date('d-m-Y H:i:s', $aInfo['time'] ) ?>
					</td>
					<td>
						<div>
							<a href="<?php echo BASE_URL; ?>?module=snapshot&amp;action=restore&amp;id=<?php echo $sID ?>&amp;name=<?php echo $sDatabase ?>" title="Restore" class="button">Restore</a>
							<a href="<?php echo BASE_URL; ?>?module=snapshot&amp;action=delete&amp;id=<?php echo $sID ?>&amp;name=<?php echo $sDatabase ?>" title="Delete" class="button">Delete</a>
						</div>
					</td>
					<td>
						<form style='height:12px;' action='<?php echo BASE_URL; ?>?module=snapshot&amp;action=rename&amp;id=<?php echo $sID ?>&amp;name=<?php echo $sDatabase ?>' method='post'>
							<input type="text" value="" name="renameto" size="16" /><input type="submit" value="rename" class="button" />
						</form>
					</td>
				</tr>
							<?php
						}
						?>
			</table>
					<?php
				}
				?>
		</div>
			<?php } ?>

		<div>

			External snapshot:
			<form action="<?php echo BASE_URL; ?>?module=snapshot&amp;action=createexternal&amp;id=<?php echo $sID; ?>&amp;name=<?php echo $sDatabase; ?>" method="post">
				<label>host</label><input type="text" name="host" value="" />
				<label>databasename</label><input type="text" name="database" value="" />
				<label>username</label><input type="text" name="username" value="" />
				<label>pass</label><input type="text" name="pass" value="" />
			<input type="submit" name="createexternal" value="create" class="button" />
			</form>
		</div>

	</body>
</html>