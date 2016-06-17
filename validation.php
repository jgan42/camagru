<?php
	include_once ('header.php');

	require_once ('db_connect.php');
	$new_passw = false;
	if (isset($_POST['new_passw']))
	{
		$new_passw = true;
		$status = $_POST['status'];
		if (strlen($_POST['passw']) < 6 || !preg_match('/[a-z]/i', $_POST['passw']) || !preg_match('/[0-9]/', $_POST['passw']))
			$msg = "Mot de passe 6 caractères dont au moins un chiffre et une lettre !";
		else if ($_POST['passw'] != $_POST['passw2'])
			$msg = "Les mots de passe sont différents !";
		else
		{
			$pass_hache = hash('whirlpool', $_POST['passw']);
			$req = $db->prepare("SELECT id FROM user WHERE status = :status");
			$req->execute(array(':status' => $status));
			$ret = $req->fetch();
			if (!$ret['id'])
				$msg = "Erreur ! Votre mot de passe n'a pas été réinitialisé !";
			else
			{
				$req = $db->prepare("UPDATE user SET status = 'ok',
				 password = :password WHERE status = :status ");
				if ($req->execute(array('password' => $pass_hache, 'status' => $status)))
				{
					$msg = "Vous pouvez à présent vous connecter avec votre nouveau mot de passe.";
					$new_passw = false;
				}
				else
					$msg = "Un problème technique empêche l'activation, merci de réessayer plus tard.";
			}
		}
	}
	else if (isset($_GET['login']) && isset($_GET['status']))
	{
		$login = $_GET['login'];
		$status = $_GET['status'];
		$req = $db->prepare("SELECT status FROM user WHERE login = :login");
		$req->execute(array(':login' => $login));
		if (($ret = $req->fetch()) && $ret['status'] == 'ok')
			$msg = "Votre compte est déjà actif !";
		else if ($ret && $_GET['status'] == $ret['status'])
		{
			$req = $db->prepare("UPDATE user SET status = 'ok' WHERE login = :login ");
			$req->bindParam(':login', $login);
			if ($req->execute())
				$msg = "Votre compte a bien été activé, vous pouvez à présent vous connecter.";
			else
				$msg = "Un problème technique empêche l'activation, merci de réessayer plus tard.";
		}
		else
			$msg = "Erreur ! Votre compte ne peut être activé...";
	}
	else if (isset($_GET['status']) && $_GET['status'] != 'ok')
	{
		$status = $_GET['status'];
		$msg = "Veuillez entrer votre nouveau mot de passe.";
		$new_passw = true;
	}
	else
		$msg = "Merci de suivre les instructions reçues par mail afin d'activer votre compte.";
?>

<div id="main">
<span><?php echo $msg; ?></span>

<?php
	if ($new_passw)
	{
?>

	<form method="post" action="validation.php">
		<label for="passw">Nouveau mot de passe :</label>
		<input type="password" name="passw" required/>
		<label for="passw2">Confirmation :</label>
		<input type="password" name="passw2" required/>
		<input type="hidden" name="status" value=
		<?php if (isset($_GET['status']) || isset($_POST['new_passw'])) echo $status; ?>>
		<input type="submit" name="new_passw" value="Réinitialiser mot de passe"/>
	</form>

<?php
	}
	include_once ('footer.php'); ?>
</div>
	</body>
</html>