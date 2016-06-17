<?php
	if (strpos($_SERVER['PHP_SELF'], 'login') === false && strpos($_SERVER['PHP_SELF'], 'validation') === false)
	{	
		session_start();
		if (!isset($_SESSION['login']) && strpos($_SERVER['PHP_SELF'], 'index') === false
			&& strpos($_SERVER['PHP_SELF'], 'allagru') === false
			&& strpos($_SERVER['PHP_SELF'], 'contact') === false)
			header('Location: login.php');
	}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8"/>
	<script type="text/javascript" src="js/script.js"></script>
	<link rel="stylesheet" href="style/style.css" />
<!-- included for the fonts -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">
	<title>Camagru</title>
</head>
<body>
	<h1>

<?php
	if (isset($_SESSION['login']))
		echo '<a href="logout.php" class="round red">LogOut<span class="round">Se déconnecter</span></a>';
	else
		echo '<a href="login.php" class="round green">LogIn<span class="round">Page de connexion</span></a>';
?>

	</h1>
	<h2>
		Cam-Agru.me<br />
		<span id="menu_img" 
			onclick="drop_menu(1)"
			onmouseenter="drop_menu(1)" onmouseleave="drop_menu(0)">
			<i class="fa fa-chevron-down" aria-hidden="true"></i>
			<i class="fa fa-chevron-down" aria-hidden="true"></i>
			<i class="fa fa-chevron-down" aria-hidden="true"></i>
		</span>
	</h2>
	<h3><img src="img/global/logo.png" /></h3>
	<ul id="menu_list" onmouseenter="drop_menu(1)" onmouseleave="drop_menu(0)">
		<li><a href="index.php">Accueil</a></li>
		<li><a href="profile.php">Mon Profil</a></li>
		<li><a href="cam.php">New Agru</a></li>
		<li><a href="allagru.php">Tous les Agrus</a></li>
		<li><a href="contact.php">Contact</a></li>
	</ul>
