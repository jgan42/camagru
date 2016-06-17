/* FORMS and MENU display */

function load_register(n)
{
	document.getElementById("login_form").style.display = n == 1 ? "none" : "block";
	document.getElementById("register_form").style.display = n == 1 ? "block" : "none";
}

function load_forget(n)
{
	document.getElementById("login_form").style.display = "none";
	document.getElementById("forget_form").style.display = "block";
}

function drop_menu(n)
{
	var h;

	h = "32px";
	if (window.innerWidth < 408)
		h = "96px";
	else if (window.innerWidth < 683)
		h = "64px";
	document.getElementById("menu_list").style.height = n == 1 ? h : "0";
}

/* VIDEO handler */
function streamer(stream)
{
	if (navigator.mozGetUserMedia)
		video.mozSrcObject = stream;
	else
	{
		var vendorURL = window.URL || window.webkitURL;
		video.src = vendorURL.createObjectURL(stream);
	}
	video.play();
}

function videoError(e)
{
	alert("video error : " + e);
}

function myplay()
{
	document.getElementById("cam").play();
	canvas.getContext('2d').clearRect(0, 0, width, height);
}

function usephoto(elemId)
{
	var elem = document.getElementById(elemId);
	var val = elem.value;

	if (elem && document.createEvent)
	{
		var evt = document.createEvent("MouseEvents");
		evt.initEvent("click", true, false);
		elem.dispatchEvent(evt);
	}
	var stop = setInterval(function() {
		if (elem.value != val)
		{
			clearInterval(stop);
			document.getElementById('mypic').submit();
		}
	}, 10);
}

/* Toolbox */
function get_info(i)
{
	var info = document.getElementById("info");
	var ico = '<i class="fa fa-info-circle" aria-hidden="true"></i>';
	var infoText;

	if (!obj[0] && i == 3)
		infoText = " Une photo sans calque n'est pas un Agru ! ";
	else if (i == 1)
		infoText = " Webcam: Lecture ";
	else if (i == 2)
		infoText = " Webcam: Pause ";
	else if (i == 3)
		infoText = " Envoyer l'image ";
	else if (i == 4)
		infoText = " Faites glisser l'image dans le cadre ";
	else if (i == 5)
		infoText = " Remplacer la Webcam par votre photo ";
	else
		infoText = " Zoom+:DoubleClic, Zoom-:ClicDroit, Supprimer:ClicMolette ";
	if (php_err)
	{
		infoText = php_err;
		php_err = false;
	}
	info.innerHTML = ico + infoText + ico;
}

function screenshot()
{
	var pic_form = document.querySelector('#pic_form');
	var data, data1, post, post1;

	if (!obj[0])
		return ;
	if (!myphoto)
		canvas.getContext('2d').drawImage(video, 0, 0, width, height);
	data = canvas.toDataURL('image/png');
	data1 = canvas1.toDataURL('image/png');

	if (data.length > 500000)
	{
		post = '<input type="text" name="cam" value="'+data.substr(0, 500000)
			+'"></input><input type="text" name="cam1" value="'+data.slice(500000)
			+'"></input>';
	}
	else
		post = '<input type="text" name="cam" value="'+data+'"></input>';
	if (data1.length > 500000)
	{
		post1 = '<input type="text" name="layer" value="'+data1.substr(0, 500000)
			+'"></input><input type="text" name="layer1" value="'+data1.slice(500000)
			+'"></input>';
	}
	else
		post1 = '<input type="text" name="layer" value="'+data1+'"></input>';
	pic_form.innerHTML = post + post1;
	pic_form.submit();
	myplay();
}

/* Draggable Canvas */
function init_drag(img_src)
{
	var tmp;

	tmp = {img: new Image(), size: 0, dragok: false, x: 320, y: 240};
	tmp.img.src = img_src;
	tmp.size = tmp.img.width > 200 ? 200 / tmp.img.width : 1;
	obj.push(tmp);
	canvas1.onmousedown = myDown;
	canvas1.onmouseup = myUp;
	canvas1.ondblclick = myZoomIn;
	canvas1.oncontextmenu = myZoomOut;
	canvas1.onmousemove = myMove;
}

function draw()
{
	ctx.clearRect(0, 0, width, height);
	obj.forEach(function(item, i)
	{
		ctx.drawImage(item.img, item.x - item.img.width * item.size / 2, item.y - item.img.height
			* item.size / 2, item.img.width * item.size, item.img.height * item.size);
	});
}

function myMove(e)
{
	var curs = false;

	obj.forEach(function(item, i)
	{
		if (e.pageX < item.x + 50 + canvas1.offsetLeft && e.pageX > item.x - 50 +
			canvas1.offsetLeft && e.pageY < item.y + 50 + canvas1.offsetTop &&
			e.pageY > item.y - 50 + canvas1.offsetTop)
			curs = true;
		canvas1.style.cursor = curs ? 'pointer' : 'default';
		if (item.dragok)
		{
			item.x = e.pageX - canvas1.offsetLeft;
			item.y = e.pageY - canvas1.offsetTop;
		}
	});
}

function myZoomIn(e)
{
	e.preventDefault();
	obj.forEach(function(item, i)
	{
		if (e.pageX < item.x + 50 + canvas1.offsetLeft && e.pageX > item.x - 50 +
			canvas1.offsetLeft && e.pageY < item.y + 50 + canvas1.offsetTop &&
			e.pageY > item.y - 50 + canvas1.offsetTop)
			item.size *= 1.2;
	});
}

function myZoomOut(e)
{
	obj.forEach(function(item, i)
	{
		if (e.pageX < item.x + 50 + canvas1.offsetLeft && e.pageX > item.x - 50 +
			canvas1.offsetLeft && e.pageY < item.y + 50 + canvas1.offsetTop &&
			e.pageY > item.y - 50 + canvas1.offsetTop)
			item.size /= 1.2;
	});
	e.preventDefault();
}

function myDown(e)
{
	obj.forEach(function(item, i)
	{
		if (e.pageX < item.x + 50 + canvas1.offsetLeft && e.pageX > item.x - 50 +
			canvas1.offsetLeft && e.pageY < item.y + 50 + canvas1.offsetTop &&
			e.pageY > item.y - 50 + canvas1.offsetTop)
		{
			if (e.button == 0 && !dragonce)
			{
				dragonce = true;
				item.dragok = true;
			}
			if (e.button == 1)
				obj.splice(i, 1);
		}
	});
}

function myUp()
{
	obj.forEach(function(item, i)
	{
		item.dragok = false;
	});
	dragonce = false;
	canvas1.style.cursor = 'default';
}

function select_img(e)
{
	console.log(e.target.src);
	e.dataTransfer.setData("text", e.target.src);
}

function add_img(e)
{
	e.preventDefault();
	init_drag(e.dataTransfer.getData("text"));
}

/* Options on Photos */
function js_del_img(photo_id)
{
	if (confirm('Etes vous certain ? (Suppression irréversible)'))
	{
		document.getElementById("del_id").value = photo_id;
		document.getElementById("del_form").submit();
	}
}

function like_it(photo_id)
{
	if (!sessionOK)
		return ;
	document.getElementById("like_id").value = photo_id;
	document.getElementById("like_form").submit();
}

function comm_it(photo_id)
{
	var oneagru = document.getElementById("oneagru");

	if (!sessionOK)
		return ;
	document.getElementById("comm_id").value = photo_id;
	document.getElementById("comm_form").submit();
	oneagru.style.width = '100%';
}

/* Pagination */

function get_row(nb_photo)
{
	var row;

	row = 10;
	if (window.innerWidth < 1300)
		row = 6;
	else if (window.innerWidth < 1650)
		row = 8;
	return (row);
}

function build_nav_row(nb_page)
{
	var nav_row = document.getElementById("nav_row");
	var disp = "Page: ";
	var i = 0;

	while (++i <= nb_page)
	{
		disp += "<span> </span>"
		if (i == active_page)
			disp += "<span style='text-decoration: underline;'>"+i+"</span>";
		else
			disp += "<span onclick='load_page("+i+")'>"+i+"</span>";
	}
	nav_row.innerHTML = disp;
}

function load_page(i)
{
	var j = 0;
	var limit = (i - 1) * row;

	while (j < nb_photo)
	{
		if (j >= limit && j < limit + row)
			document.querySelector('.imgnum'+j).style.display = 'inline-block';
		else
			document.querySelector('.imgnum'+j).style.display = 'none';
		++j;
	}
	document.getElementById("like_form").action = 'allagru.php?active_page='+i;
	active_page = i;
	build_nav_row(nb_page);
}
