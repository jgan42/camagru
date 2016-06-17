<?php include_once ('header.php'); ?>
		<div id="main">
			<form id="pic_form" method="post" action="myagru.php" target="myagru">
			</form>
			<form id="mypic" method="post" action="cam.php"
				enctype="multipart/form-data">
				<input type="file" id="my" name="my" accept="image/*" value=""></input>
			</form>
			<video id="cam"></video>
			<canvas id="canvas" width="640" height="480"></canvas>
			<canvas id="canvas1" width="640" height="480"
				ondrop="add_img(event)" ondragover="event.preventDefault()"></canvas>
		</div>
		<div id="toolbox">
			<div id="tooltop">
				<i class="fa fa-file-image-o" aria-hidden="true" onmouseout='get_info(0)'
					onmouseover='get_info(5)' onclick='usephoto("my");'></i>
				<i class="fa fa-play" aria-hidden="true" onmouseout='get_info(0)'
					onmouseover='get_info(1)' onclick='myplay();'></i>
				<i class="fa fa-pause" aria-hidden="true" onmouseout='get_info(0)'
					onmouseover='get_info(2)' onclick='document.getElementById("cam").pause();'></i>
				<i class="fa fa-cloud-upload" aria-hidden="true" onmouseout='get_info(0)'
					onmouseover='get_info(3)' onclick='screenshot();'></i>
				<p id='info'></p>
			</div>
			<div id="toolbot">
<?php
	$n = 1;
	while (++$n < 32)
		echo 	"<img src='img/layer/".$n.".png' onmouseout='get_info(0)'
				onmouseover='get_info(4)' ondragstart='select_img(event)'></img>";
?>
			</div>
		</div>
		<iframe name="myagru" src="myagru.php"></iframe>
		<style>
			@media screen and (min-width: 1280px)
			{
				#main {
					width: 75%;
					-webkit-transition: all .5s ease-in-out;
					-o-transition: all .5s ease-in-out;
					-moz-transition: all .5s ease-in-out;
					transition: all .5s ease-in-out;
				}
			}
		</style>
<?php include_once ('footer.php'); ?>
<script>
	var video = document.querySelector("#cam"),
		canvas = document.querySelector('#canvas'),
		myphoto = false,
		php_err = false,
		width = 640,
		height = 480,
		canvas1 = document.getElementById("canvas1"),
		ctx = canvas1.getContext("2d"),
		obj = [],
		dragonce = false;

	navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia
						|| navigator.mozGetUserMedia || navigator.msGetUserMedia
						|| navigator.oGetUserMedia;
	navigator.getUserMedia({video: true}, streamer, videoError);
<?php
	if (isset($_FILES['my']))
	{
		list(, $my_path) = explode('/', $_FILES['my']['type']);

		if ($my_path != 'jpeg' && $my_path != 'gif' &&
			$my_path != 'jpg' && $my_path != 'png')
		{
			$err = ' Type de fichier incorrect ! ';
			echo 'php_err = "'.$err.'";';
		}
		else if ($_FILES['my']['size'] > 1000000)
		{
	
			$err = " Fichier de 1Mo maximum ! ";
			echo 'php_err = "'.$err.'";';
		}
		else
		{
			$my_path = 'img/agru/'.$_SESSION['login'].'.'.$my_path;
			move_uploaded_file($_FILES['my']['tmp_name'], $my_path);
			echo "myphoto = true;
				var my = new Image();
				my.src = '".$my_path."';
				setTimeout(function() {
					canvas.getContext('2d').drawImage(my, 0, 0, 640, 480);
				}, 500);";
		}
	}
?>
	get_info(0);
	document.onselectstart = new Function ("return false");
	setInterval(draw, 10);
</script>