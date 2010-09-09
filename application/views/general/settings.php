<html>
	<head>
		<title>Settings tool</title>
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
		<h1>Settings Manager</h1>

		<p>
			<a href="<?php echo Conf::get('general.url.www'); ?>/snapshot/">Snapshot manager</a>
		</p>

		<form method="post" action="<?php echo Conf::get('general.url.www'); ?>/settings/">
			<table class="snaps">
			<tr>
				<th style="width: 180px; text-align: left;">Label</th>
				<th style="width: 300px; text-align: left;">Value</th>
			</tr>
		<?php foreach ($settings as $setting) : ?>
			<tr>
				<td><strong><?php echo $setting->getName(); ?></strong></td>
				<td>
					<input style="font-family: courier; width: 250px;" name="<?php echo $setting->getName(); ?>" type="text" value="<?php echo $setting->getValue(); ?>" />
				</td>
			</tr>
		<?php endforeach; ?>
			<tr>
				<td>&nbsp;</td>
				<td>
					<input name="update" type="submit" value="Update" />
				</td>
			</tr>
		</table>
		</form>

	</body>
</html>