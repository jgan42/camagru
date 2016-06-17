<?php include_once ('header.php'); ?>

<form id="like_form" method="post" action="allagru.php">
	<input id="like_id" type="hidden" name="like_id" value="">
</form>
<form id="comm_form" method="get" action="oneagru.php" target="oneagru">
	<input id="comm_id" type="hidden" name="photo_id" value="">
</form>
<iframe id="oneagru" name="oneagru" src="oneagru.php"></iframe>
<div id="main">
	<p id="nav_row"></p>

<?php
	require_once ('db_connect.php');
	if (isset($_POST['like_id']) && isset($_SESSION['user_id']))
	{
		$req2 = $db->prepare('SELECT id FROM `like`
			WHERE photo_id = :photo_id AND user_id = :user_id');
		$req2->execute(array('photo_id' => $_POST['like_id'],
							'user_id' => $_SESSION['user_id']));
		$mylike = $req2->fetch();
		if (!$mylike['id'])
			$req = $db->prepare('INSERT INTO `like`(photo_id, user_id)
				VALUES(:photo_id, :user_id)');
		else
			$req = $db->prepare('DELETE FROM `like`
				WHERE photo_id = :photo_id AND user_id = :user_id');
		$req->execute(array('photo_id' => $_POST['like_id'],
							'user_id' => $_SESSION['user_id']));
	}
	$req = $db->prepare('SELECT id, photo_path FROM photo ORDER BY id DESC');
	$req->execute();
	$i = 0;
	while ($photo = $req->fetch())
	{
		$req1 = $db->prepare('SELECT COUNT(*) AS nb FROM `like`
			WHERE photo_id = :photo_id');
		$req1->execute(array('photo_id' => $photo['id']));
		$nb_like = $req1->fetch();
		if (isset($_SESSION['user_id']))
		{
			$req2 = $db->prepare('SELECT id FROM `like`
				WHERE photo_id = :photo_id AND user_id = :user_id');
			$req2->execute(array('photo_id' => $photo['id'],
								'user_id' => $_SESSION['user_id']));
			$mylike = $req2->fetch();
		}
		$req3 = $db->prepare('SELECT COUNT(*) AS nb FROM comment
			WHERE photo_id = :photo_id');
		$req3->execute(array('photo_id' => $photo['id']));
		$nb_comm = $req3->fetch();
		$req4 = $db->prepare('SELECT user_id FROM photo WHERE id = :id');
		$req4->execute(array('id' => $photo['id']));
		$owner = $req4->fetch();
		$req4 = $db->prepare('SELECT id, login, email FROM user WHERE id = :id');
		$req4->execute(array('id' => $owner['user_id']));
		$owner = $req4->fetch();
?>

	<div class='img_list <?php echo "imgnum".$i;?>'>
		<img src='<?php echo $photo['photo_path']; ?>'
			onclick="comm_it(<?php echo $photo['id']; ?>)"></img>
		<?php echo 'By '.(isset($_SESSION['user_id'])
			&& $owner['id'] == $_SESSION['user_id'] ? 'me' : $owner['login']); ?>
		<i class="fa fa-heart" aria-hidden="true"
			onclick="like_it(<?php echo $photo['id']; ?>)"
			<?php if (isset($_SESSION['user_id']) && $mylike['id']) echo 'style="color:red"'; ?>></i>
		<span>(<?php echo $nb_like['nb']; ?>)</span>
		<i class="fa fa-comments" aria-hidden="true"
			onclick="comm_it(<?php echo $photo['id']; ?>)"></i>
		<span>(<?php echo $nb_comm['nb']; ?>)</span>
	</div>

<?php
		++$i;
	}
?>
</div>
		
<?php include_once ('footer.php'); ?>
<script type="text/javascript">
	var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
	var eventer = window[eventMethod];
	var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";
	
	// Listen to message from child window
	eventer(messageEvent,function(e) {
	  document.getElementById("oneagru").style.width = 0;
	},false);
	var sessionOK = false;
<?php
	$page = isset($_GET['active_page']) ? $_GET['active_page'] : 1;
	echo 'var nb_photo = '.$i.';';
	echo 'var active_page = '.$page.';';
	if (isset($_SESSION['user_id']))
		echo 'sessionOK = true;';
?>
	var row = get_row(nb_photo);
	var nb_page = nb_photo / row;

	nb_page = nb_page == Math.floor(nb_page) ? nb_page : Math.floor(nb_page) + 1;
	build_nav_row(nb_page);
	load_page(active_page);
</script>
