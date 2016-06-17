<?php
	include_once ('header.php');
	$err = false;
	if (isset($_POST['reset_pw']))
	{
		require_once ('db_connect.php');
		$pass_hache = hash('whirlpool', $_POST['old_pw']);
		$req = $db->prepare('SELECT password FROM user WHERE id = :id');
		$req->execute(array('id' => $_SESSION['user_id']));
		$ret = $req->fetch();
		if ($ret['password'] != $pass_hache)
			$err = 'L\'ancien mot de passe est erroné !';
		else if (strlen($_POST['new_pw']) < 6 || !preg_match('/[a-z]/i', $_POST['new_pw']) || !preg_match('/[0-9]/', $_POST['new_pw']))
			$err = "Mot de passe 6 caractères dont au moins un chiffre et une lettre !";
		else if ($_POST['new_pw'] != $_POST['new_pw2'])
			$err = "Les mots de passe sont différents !";
		else
		{
			$pass_hache = hash('whirlpool', $_POST['new_pw']);
			$req = $db->prepare("UPDATE user SET password = :password WHERE id = :id ");
			$req->execute(array('password' => $pass_hache, 'id' => $_SESSION['user_id']));
			$err = 'Votre mot de passe a bien été changé.';
		}
	}
?>
	<div id="main">
		Bonjour <?php echo $_SESSION['login']; ?><br /><br />
		Changer mon mot de passe :<br />
		<form method="post" action="profile.php">
			<?php if ($err) echo '<span class="err_str">'.$err.'</span>';?>
			<label for="old_pw">Ancien :</label>
			<input type="password" name="old_pw" required/>
			<label for="new_pw">Nouveau :</label>
			<input type="password" name="new_pw" required/>
			<label for="new_pw2">Confirmation :</label>
			<input type="password" name="new_pw2" required/>
			<input type="submit" name="reset_pw" value="Changer mon mot de passe"/>
		</form><br />
		<a href="#" onclick="document.getElementById('oneagru').style.width = '100%';">Voir Mes Agrus</a><br />
	</div>
	<iframe id="oneagru" name="oneagru" src="myagru.php"></iframe>
<?php include_once ('footer.php'); ?>

<script type="text/javascript">
	var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
	var eventer = window[eventMethod];
	var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";
	
	// Listen to message from child window
	eventer(messageEvent,function(e) {
	  document.getElementById("oneagru").style.width = 0;
	},false);
</script>
