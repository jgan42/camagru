<?php
	session_start();
	if (isset($_SESSION['login']))
		header('Location: index.php');
	$err = null;
	$registered = null;
	require_once ('db_connect.php');
	if (isset($_POST['forget']))
	{
		if (!preg_match('/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $_POST['email']))
			$err = "L'E-mail fournit est invalide !";
		$req = $db->prepare('SELECT id FROM user WHERE email = :email');
		$req->execute(array('email' => $_POST['email']));
		$ret = $req->fetch();
		if ($ret['id'])
		{
			$status = substr(md5(microtime(TRUE)*4242), -16);

			$message = 'Bienvenue sur Cam-Agru,
 
Pour réinitialiser votre mot de passe, veuillez cliquer sur le lien ci dessous
ou copier/coller dans votre navigateur internet. (remplacez "camagru"
dans l\'adresse par le nom du depot)
 
http://localhost:8080/camagru/validation.php?status='.urlencode($status).'

Veuillez ignorer ce message si vous avez retrouvé votre mot de passe ou que vous
n\'êtes pas le destinataire de ce message.
 
---------------
Ceci est un mail automatique, Merci de ne pas y répondre.';

			if (mail($_POST['email'], 'Reset mot de passe Cam-Agru', $message))
			{
				$req = $db->prepare("UPDATE user SET status = :status WHERE email = :email ");
				$req->execute(array('status' => $status, 'email' => $_POST['email']));
				$registered = "Un E-mail vous a été envoyé afin de réinitialiser votre mot de passe.";
			}
		}
		else
			$err = "L'E-mail fournit ne correspond à aucun compte !";
	}
	if (isset($_POST['connect']) || isset($_POST['register']))
	{
		$login = $_POST['login'];
		$pass_hache = hash('whirlpool', $_POST['passw']);
		if (isset($_POST['register']))
		{
			if (!preg_match('/^[a-z0-9]{4,15}$/i', $login))
				$err = "Identifiant 4 à 15 caractères alpha-numériques !";
			else if (!preg_match('/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $_POST['email']))
				$err = "L'E-mail fournit est invalide !";
			else if (strlen($_POST['passw']) < 6 || !preg_match('/[a-z]/i', $_POST['passw']) || !preg_match('/[0-9]/', $_POST['passw']))
				$err = "Mot de passe 6 caractères dont au moins un chiffre et une lettre !";
			else if ($_POST['passw'] != $_POST['passw2'])
				$err = "Les mots de passe sont différents !";
			else
			{
				$req = $db->prepare('SELECT id FROM user WHERE email = :email');
				$req->execute(array('email' => $_POST['email']));
				$ret = $req->fetch();
				if ($ret['id'])
					$err = "E-mail déjà utilisé !";
				$req = $db->prepare('SELECT id FROM user WHERE login = :login');
				$req->execute(array('login' => $login));
				$ret = $req->fetch();
				if ($ret['id'])
					$err = "Identifiant déjà utilisé !";
			}
		}
	}
	if (isset($_POST['connect']))
	{
		$req = $db->prepare('SELECT id, email, status FROM user WHERE login = :login AND password = :password');
		$req->execute(array('login' => $login, 'password' => $pass_hache));
		$ret = $req->fetch();
		if ($ret['status'])
		{
			if (strlen($ret['status']) == 32)
				$err = "Vérifiez votre boîte mail afin activer votre compte.";
			else
			{
				if (strlen($ret['status']) == 16)
				{
					$req = $db->prepare("UPDATE user SET status = 'ok' WHERE status = :status ");
					$req->bindParam(':status', $ret['status']);
					$req->execute();
				}
				$_SESSION['login'] = $login;
				$_SESSION['user_id'] = $ret['id'];
				$_SESSION['email'] = $ret['email'];
				header('Location: index.php');
			}
		}
		if (!$err)
			$err = 'Mauvais identifiant ou mot de passe !';
	}
	if (isset($_POST['register']) && !$err)
	{
		$status = md5(microtime(TRUE)*4242);

		$message = 'Bienvenue sur Cam-Agru,
 
Pour activer votre compte, veuillez cliquer sur le lien ci dessous
ou copier/coller dans votre navigateur internet. (remplacez "camagru"
dans l\'adresse par le nom du depot)
 
http://localhost:8080/camagru/validation.php?login='.urlencode($login).'&status='.urlencode($status).'
 
---------------
Ceci est un mail automatique, Merci de ne pas y répondre.';

		if (mail($_POST['email'], 'Activation Cam-Agru', $message))
		{
			$req = $db->prepare('INSERT INTO user(login, password, email, status)
			VALUES(:login, :password, :email, :status)');
			$req->execute(array('login' => $login,
							'password' => $pass_hache,
							'email' => $_POST['email'],
							'status' => $status));
			$registered = "Un E-mail de confirmation vous a été envoyé afin d'activer votre compte.";
		}
	}
	include_once ('header.php');
	$display = isset($_POST['register']) && !$registered ? '#login_form' : '#register_form';
	echo '<style>'.$display.' {display: none;}</style>';
?>

<div id="main">
	<form id="login_form" method="post" action="login.php">
		<?php if ($err) echo '<span class="err_str">'.$err.'</span>';?>
		<?php if ($registered) echo '<span>'.$registered.'</span>';?>
		<label for="login">Identifiant :</label>
		<input type="text" name="login" value="<?php if (isset($_POST['connect'])) echo $_POST['login']; ?>" required/>
		<label for="passw">Mot de passe :</label>
		<input type="password" name="passw" required/>
		<input type="submit" name="connect" value="Se connecter"/>
		<span><a href="#" onclick="load_register(1)">Pas encore inscrit ?</a></span>
		<span><a href="#" onclick="load_forget()">J'ai oublié mon mot de passe ?</a></span>
	</form>
	<form id="forget_form" method="post" action="login.php">
		<?php if ($err) echo '<span class="err_str">'.$err.'</span>';?>
		<label for="email">E-mail :</label>
		<input type="text" name="email" value="<?php if (isset($_POST['forget'])) echo $_POST['email']; ?>" required/>
		<input type="submit" name="forget" value="Réinitialiser mot de passe"/>
	</form>
	<form id="register_form" method="post" action="login.php">
		<?php if ($err) echo '<span class="err_str">'.$err.'</span>';?>
		<label for="login">Identifiant :</label>
		<input type="text" name="login" value="<?php if (isset($_POST['register'])) echo $_POST['login']; ?>" required/>
		<label for="email">E-mail* :</label>
		<input type="text" name="email" value="<?php if (isset($_POST['register'])) echo $_POST['email']; ?>" required/>
		<label for="passw">Mot de passe :</label>
		<input type="password" name="passw" required/>
		<label for="passw2">Comfirmation :</label>
		<input type="password" name="passw2" required/>
		<input type="submit" name="register" value="S'inscrire"/>
		<span><a href="#" onclick="load_register(0)">Déjà inscrit ?</a></span>
		<span>*Un lien d'activation vous sera envoyé, veillez à fournir un E-mail valide.</span>
	</form>
</div>
<?php include_once ('footer.php'); ?>
