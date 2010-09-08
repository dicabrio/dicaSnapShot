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
				background-image: -moz-linear-gradient(top, rgb(34,37,48) 0, rgb(67,74,93) 20%, rgb(67,74,93) 90%, rgb(34,37,48) 100%);
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
				background-image: -moz-linear-gradient(top, rgb(138,157,185) 0, rgb(138,157,185) 80%, rgba(0,0,0,0.5) 100%);
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
				background-image: -moz-linear-gradient(top, #efefef, #b9b9b9);
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

		<?php if (!empty($error)) : ?>
		<div style="background-color: red; color: #fff; font-weight: bold;padding: 10px 5px;"><?php echo $error; ?></div>
		<?php endif; ?>

		<ul id="tabmenu">
			<?php foreach ($databases as $database) : ?>
			<li class="<?php echo $database->getName(); ?>"><a href="#"><?php echo $database->getName(); ?></a></li>
			<?php endforeach; ?>
		</ul>

		<?php foreach ($databases as $database) : ?>
		<div class="tab" id="<?php echo $database->getName(); ?>">
			<div style="margin: 0 0 20px 0;">
				<a class="button" href="<?php echo Conf::get('general.url.www'); ?>/snapshot/create/<?php echo $database->getName() ?>">Create snapshot</a> of: <?php echo $database->getName(); ?></div>
				<div>No snapshots yet</div>
				<table class="snaps">
					<tr>
						<th>Name</th>
						<th>Time</th>
						<th>actions</th>
						<th>rename</th>
					</tr>
					<?php foreach ($database->getSnapshots() as $snapshot) : ?>
					<tr>
						<td>
							<?php $label = $snapshot->getLabel(); ?>
							<?php if (!empty($label)) : ?>
							<strong><?php echo $label; ?></strong>
							<?php else : ?>
							No name
							<?php endif; ?>
						</td>
						<td>
							<?php echo date('d-m-Y H:i:s', $snapshot->getTimeOfCreation()); ?>
						</td>
						<td>
							<div>
								<a href="<?php echo Conf::get('general.url.www'); ?>/snapshot/restore/<?php echo $database->getName(); ?>/<?php echo $snapshot->getSnapshotFile(); ?>" title="Restore" class="button">Restore</a>
								<a href="<?php echo Conf::get('general.url.www'); ?>/snapshot/delete/<?php echo $database->getName(); ?>/<?php echo $snapshot->getSnapshotFile(); ?>" title="Delete" class="button">Delete</a>
							</div>
						</td>
						<td>
							<form style='height:12px;' action="<?php echo Conf::get('general.url.www'); ?>/snapshot/rename/<?php echo $database->getName(); ?>/<?php echo $snapshot->getSnapshotFile(); ?>" method='post'>
								<input type="text" value="" name="renameto" size="16" /><input type="submit" value="rename" class="button" />
							</form>
						</td>
					</tr>
					<?php endforeach; ?>
				</table>
		</div>
		<?php endforeach; ?>
	</body>
</html>