<?php
	if (strpos($_SERVER['PHP_SELF'], 'login') === false && strpos($_SERVER['PHP_SELF'], 'validation') === false)
	{	
		session_start();
		if (!isset($_SESSION['login']) && strpos($_SERVER['PHP_SELF'], 'index') === false)
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
</head>
<body>

<?php
	require_once ('db_connect.php');
$err = false;
if (isset($_GET['photo_id']))
{
	$req = $db->prepare('SELECT id, photo_path FROM photo WHERE id = :id');
	$req->execute(array('id' =>$_GET['photo_id']));
	$photo = $req->fetch();
}
if ($photo['id'] > 0)
{
	$req = $db->prepare('SELECT user_id FROM photo WHERE id = :id');
	$req->execute(array('id' => $photo['id']));
	$owner = $req->fetch();
	$req = $db->prepare('SELECT id, login, email FROM user WHERE id = :id');
	$req->execute(array('id' => $owner['user_id']));
	$owner = $req->fetch();
	if (isset($_POST['del_comm']))
	{
		$req = $db->prepare('SELECT user_id FROM comment WHERE id = :id');
		$req->execute(array('id' => $_POST['del_comm']));
		$check = $req->fetch();
		if ($check['user_id'] == $_SESSION['user_id'])
		{
			$req = $db->prepare('DELETE FROM comment WHERE id = :id');
			$req->execute(array('id' => $_POST['del_comm']));
		}
		else
			$err = "Merci de ne supprimer que les commentaires dont vous êtes propriétaire";
	}
	if (isset($_POST['new_comm']))
	{
		$new_comm = htmlspecialchars($_POST['new_comm']);
		$req = $db->prepare('INSERT INTO comment(user_id, photo_id, comment)
			VALUES(:user_id, :photo_id, :comment)');
		$req->execute(array('user_id' => $_SESSION['user_id'],
							'photo_id' => $_GET['photo_id'],
							'comment' => $new_comm));

		$message = 'Vous avez du succès ! Vous avez recu un nouveau commentaire !
 
---------------
Ceci est un mail automatique, Merci de ne pas y répondre.';

		if ($owner['id'] != $_SESSION['user_id'])
			mail($owner['email'], 'Nouveau commentaire Cam-Agru', $message);
	}
	$req1 = $db->prepare('SELECT COUNT(*) AS nb FROM `like`
		WHERE photo_id = :photo_id');
	$req1->execute(array('photo_id' => $photo['id']));
	$nb_like = $req1->fetch();
	$req2 = $db->prepare('SELECT id FROM `like`
		WHERE photo_id = :photo_id AND user_id = :user_id');
	$req2->execute(array('photo_id' => $photo['id'],
						'user_id' => $_SESSION['user_id']));
	$mylike = $req2->fetch();
	if ($mylike['id'] < 1)
		$mylike['id'] = '0';
	$req3 = $db->prepare('SELECT COUNT(*) AS nb FROM comment
		WHERE photo_id = :photo_id');
	$req3->execute(array('photo_id' => $photo['id']));
	$nb_comm = $req3->fetch();
?>
<div id="myagru">
	<i class="fa fa-times" aria-hidden="true"
		onclick="parent.postMessage('hello', '*')"></i>
	<img id="single" src='<?php echo $photo['photo_path']; ?>'></img>
	<?php echo 'By '.($owner['id'] != $_SESSION['user_id']
		? $owner['login'] : 'me'); ?>
	<i class="fa fa-heart" aria-hidden="true"
		onclick="like_it(<?php echo $photo['id'].','.$mylike['id']; ?>)"
		<?php if ($mylike['id'] != 0) echo 'style="color:red"'; ?>></i>
	<span>(<?php echo $nb_like['nb']; ?>)</span>
	<i class="fa fa-comments" aria-hidden="true"></i>
	<span>(<?php echo $nb_comm['nb']; ?>)</span>
<form method="post" action="oneagru.php?photo_id=<?php echo $_GET['photo_id']; ?>">
	<input type="text" name="new_comm" required />
	<input type="submit" value="Commenter"></input>
</form>
<form id="del_form" method="post" action="oneagru.php?photo_id=<?php echo $_GET['photo_id']; ?>">
	<input id="del_id" type="hidden" name="del_comm" value="">
</form>
<?php
	if ($err)
		echo $err.'<br />';
	$req = $db->prepare('SELECT id, user_id, comment FROM comment
		WHERE photo_id = :photo_id ORDER BY id DESC');
	$req->execute(array('photo_id' => $photo['id']));
	while ($comm = $req->fetch())
	{
		echo '<div class="comm_div">';
		if ($comm['user_id'] == $_SESSION['user_id'])
		{
			echo '<i class="fa fa-trash-o" aria-hidden="true"
				onclick="js_del_img('.$comm['id'].')"></i>';
			echo ' Vous:';
		}
		else
		{
			$req1 = $db->prepare('SELECT login FROM user WHERE id = :id');
			$req1->execute(array('id' => $comm['user_id']));
			$login = $req1->fetch();
			echo $login['login'].': ';
		}
		echo $comm['comment'];
		echo '</div>';
	}
	echo '</div>';
}
else
	echo '<div id="main">Passez par un chemin normal svp.</div>';
?>
</body>
</html>
