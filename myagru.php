<?php
	if (strpos($_SERVER['PHP_SELF'], 'login') === false && strpos($_SERVER['PHP_SELF'], 'validation') === false)
	{	
		session_start();
		if (!isset($_SESSION['login']) && strpos($_SERVER['PHP_SELF'], 'index') === false)
			header('Location: login.php');
	}
	$err = false;
	require_once ('db_connect.php');
	if (isset($_POST['del_img']))
	{
		$req = $db->prepare('SELECT user_id, photo_path FROM photo WHERE id = :id');
		$req->execute(array('id' => $_POST['del_img']));
		$check = $req->fetch();
		if ($check['user_id'] == $_SESSION['user_id'])
		{
			unlink($check['photo_path']);
			$req = $db->prepare('DELETE FROM photo WHERE id = :id');
			$req->execute(array('id' => $_POST['del_img']));
			$req = $db->prepare('DELETE FROM comment WHERE photo_id = :photo_id');
			$req->execute(array('photo_id' => $_POST['del_img']));
			$req = $db->prepare('DELETE FROM `like` WHERE photo_id = :photo_id');
			$req->execute(array('photo_id' => $_POST['del_img']));
		}
		else
			$err = "Merci de ne supprimer que les photos dont vous êtes propriétaire";
	}
	if (isset($_POST['cam']))
	{
		$cam = $_POST['cam'];
		$layer = $_POST['layer'];
		if (isset($_POST['cam1']))
			$cam = implode('', array($cam, $_POST['cam1']));
		if (isset($_POST['layer1']))
			$layer = implode('', array($layer, $_POST['layer1']));
		list($type, $cam) = explode(';', $cam);
		list($type, $layer) = explode(';', $layer);
		list(, $cam) = explode(',', $cam);
		list(, $layer) = explode(',', $layer);
		$cam = imagecreatefromstring(base64_decode($cam));
		$layer = imagecreatefromstring(base64_decode($layer));
		imagecopy($cam, $layer, 0, 0, 0, 0, imagesx($cam), imagesy($cam));
		$req = $db->prepare('INSERT INTO photo(user_id) VALUES(:user_id)');
		$req->execute(array('user_id' => $_SESSION['user_id']));
		$photo_id = $db->lastInsertId();
		$photo_path = 'img/agru/'.$photo_id.'.png';
		imagepng($cam, $photo_path);
		$req = $db->prepare('UPDATE photo SET photo_path = :photo_path WHERE id = :photo_id');
		$req->execute(array('photo_path' => $photo_path, 'photo_id' => $photo_id));
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
	<i class="fa fa-times" aria-hidden="true"
		onclick="parent.postMessage('hello', '*')"></i>
	<form id="del_form" method="post" action="myagru.php">
		<input id="del_id" type="hidden" name="del_img" value="">
	</form>
<div id="myagru">
<?php
	if ($err)
		echo $err.'<br />';
	echo "Mes agrus<br />";
	$req = $db->prepare('SELECT id, photo_path FROM photo WHERE user_id = :user_id ORDER BY id DESC');
	$req->execute(array('user_id' => $_SESSION['user_id']));
	while ($photo = $req->fetch())
	{
?>
		<div class='img_list'>
			<img src='<?php echo $photo['photo_path']; ?>'></img>
			<i class="fa fa-trash-o" aria-hidden="true" onclick="js_del_img('<?php echo $photo['id']; ?>')"></i>
		</div>
<?php
	}
?>
</div>
</body>
</html>
