<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Delfshaven Dans!</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="keywords" content="mwah" />
	<meta name="description" content="mwah" />
	<style type="text/css">
		#menu {
			list-style: none;
			padding: 0;
		}
		#menu li {
			display: inline;
			border: 1px solid black;
			padding: 5px 10px;
		}

		.paginationmenu li {
			display: inline;
		}

		th { text-align: left; }
		form { clear: left; }
		.option { border: 1px solid black; width: 400px; }
		.option th { width: 50px; }
		table { margin: 0 0 20px 0; width: 100%; }
		table td { vertical-align: top;}
		#optiontable td { border: 1px solid #ccc; text-align: center; padding: 5px; }
		#optiontable th { border: 1px solid #ccc; padding: 5px; }

		#productmenu {
			list-style: none;
			margin: 0;
			padding: 0;
			border-bottom: 1px solid #000;
			height: 41px;
		}

		#productmenu li {
			float: left;
			height: 40px;
			border: 1px solid black;
			background: #ccc;
		}
		#productmenu li.active { background: white; border-bottom: 1px solid #fff; }

		#productmenu li a {
			display: block;
			padding: 10px 10px 0 10px;
			height: 30px;
		}

		#productmenu li.active a { text-decoration: none; }

		#productimages, #options {
			display: none;
		}
	</style>
	<script type="text/javascript" src="<?php echo Conf::get('general.url.js'); ?>/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="<?php echo Conf::get('general.url.js'); ?>/addoptions.js"></script>
</head>
<body>
	<ul id="menu">
		<li><a href="<?php echo WWW_URL; ?>/news">Nieuws</a></li>
		<li><a href="<?php echo WWW_URL; ?>/logout">Uitloggen</a></li>
	</ul>
	<div>
		<?php echo $module; ?>
	</div>
</body>
</html>