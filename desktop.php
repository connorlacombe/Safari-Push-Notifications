<?php
$id = "";
if(!isset($_COOKIE["pushid"])) {
	$validId = false;
	while($validId == false) {
		$id = substr(number_format(time() * rand(),0,'',''),0,4);
		$r = mysqli_do("SELECT * FROM push WHERE id='$id'");
		if(mysqli_num_rows($r) == 0) {
			$validId = true;
			break;
		}
	}
	setcookie("pushid", $id, time()+60*60*24*30);
}
else {
	$id = $_COOKIE["pushid"];
}
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Safari Push Notification Demo</title>

<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.min.css" rel="stylesheet">
<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>

<style type="text/css">
body {
	margin: 0;
	padding: 0;
	background: #EDEDED;
	font-family: "Avenir Next", "Helvetica Neue", Helvetica, sans-serif;
}

.box {
	border-radius: 8px;
	background: #fff;
	width: 600px;
	height: 300px;
	position: absolute;
	top: 50%;
	left: 50%;
	margin-left: -300px;
	margin-top: -150px;
	border: 1px solid #CECECE;
	box-shadow: rgba(255,255,255,0.7) 0 1px 0, inset rgba(0,0,0,0.1) 0 1px 2px;
}
</style>


<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
<script type="text/javascript">
var token = "";
var id = "<?php echo $id; ?>";
window.onload = function() {
	if(window.navigator.userAgent.indexOf('7.0 Safari') > -1) {
		checkPerms();
	}
	else {
		document.getElementById("old").style.display = "";
		if(window.navigator.userAgent.indexOf("Firefox") > -1) {
			document.getElementById("firefoxlol").style.display = "";
		}
	}
};

function checkPerms() {
	document.getElementById("reqperm").style.display = "none";
	document.getElementById("granted").style.display = "none";
	document.getElementById("denied").style.display = "none";
	
	var pResult = window.safari.pushNotification.permission('web.net.kandutech');
	if(pResult.permission === 'default') {
		//request permission
		document.getElementById("reqperm").style.display = "";
		requestPermissions();
	}
	else if(pResult.permission === 'granted') {
		document.getElementById("granted").style.display = "";
		token = pResult.deviceToken;
	}
	else if(pResult.permission === 'denied') {
		document.getElementById("denied").style.display = "";
	}
}

function requestPermissions() {
	window.safari.pushNotification.requestPermission('https://kandutech.net', 'web.net.kandutech', {"id": id}, function(c) {
		if(c.permission === 'granted') {
			document.getElementById("reqperm").style.display = "none";
			document.getElementById("granted").style.display = "";
			token = pResult.deviceToken;
		}
		else if(c.permission === 'denied') {
			document.getElementById("reqperm").style.display = "none";
			document.getElementById("denied").style.display = "";
		}
	});
}

function do_push() {
	var checksOut = true;
	$("#form input").each(function(index, element) {
		$(this).parents(".control-group").removeClass("error");
        if(element.value == "") {
			$(this).parents(".control-group").addClass("error");
			checksOut = false;
		}
    });
	if(checksOut == true) {
		$.post("https://kandutech.net/v1/push/"+id, {"title": document.getElementById("not_title").value, "body": document.getElementById("not_body").value, "button": document.getElementById("not_button").value});
		$("#form input").val("");
		$("#modal_scrim").fadeOut(300);
	}
}
</script>

</head>

<body>
	<div style="background: rgba(0,0,0,0.8); position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: none; z-index: 899;" id="modal_scrim">
    	<div class="modal">
        	<div class="modal-header">
            	<button type="button" class="close" onClick="$('#modal_scrim').fadeOut(300);" aria-hidden="true">&times;</button>
            	<h3>Create Push Notification</h3>
          	</div>
        	<div class="modal-body form-horizontal" id="form">
                <div class="control-group">
                  <label class="control-label" for="not_title">Title</label>
                  <div class="controls">
                    <input type="text" id="not_title">
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label" for="not_title">Body</label>
                  <div class="controls">
                    <input type="text" id="not_body">
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label" for="not_title">Button Label</label>
                  <div class="controls">
                    <input type="text" id="not_button">
                  </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="btn btn-primary btn-small" onClick="do_push();">Push!</div>
            </div>
        </div>
    </div>
	<div class="box">
    	<div style="font-weight: 500; font-size: 20px; margin: 10px;">Safari Push Notification Demo</div>
        <!-- old safari lolz -->
        <div style="margin-top: 100px; text-align: center; display: none;" id="old">
        	You need Safari 7.0 on OS X Mavericks to run this demo!
            <div id="firefoxlol" style="display: none; font-size: 11px; margin-top: 20px;">u serious with firefox right now tho? maybe don't.</div>
        </div>
        <!-- checking permissions -->
        <div style="margin-top: 100px; text-align: center; display: none;" id="reqperm">
        	<img src="loader.gif">
            <div>Requesting permission...</div>
        </div>
        <!-- denied permissions -->
        <div style="margin-top: 100px; text-align: center; display: none;" id="denied">
            <div>You have denied this website permission to send push notifications.</div>
            <div class="btn btn-primary btn-small" onClick="checkPerms();">I've changed my mind...</div>
        </div>
        <!-- granted permissions -->
        <div style="margin-top: 20px; text-align: center; display: none;" id="granted">
        	<div class="btn btn-primary" onClick="$('#modal_scrim').fadeIn(300); document.getElementById('not_title').focus();">Create New Push Notification</div>
            <hr>
            <div style="position: absolute; margin-top: -30px; text-align: center; width: 100%; color: #CCC;">OR</div>
            <div style="overflow: auto;"> 
            <div style="border-radius: 8px; box-shadow: inset rgba(0,0,0,0.3) 0 1px 2px; border: 1px solid #A0A0A0; height: 50px; width: 50px; font-size: 50px; float: left; margin: 20px; margin-top: 15px; margin-left: 135px; text-align: center; padding-top: 30px;"><?php echo substr($id, 0, 1); ?></div>
            <div style="border-radius: 8px; box-shadow: inset rgba(0,0,0,0.3) 0 1px 2px; border: 1px solid #A0A0A0; height: 50px; width: 50px; font-size: 50px; float: left; margin: 20px; margin-top: 15px; text-align: center; padding-top: 30px;"><?php echo substr($id, 1, 1); ?></div>
            <div style="border-radius: 8px; box-shadow: inset rgba(0,0,0,0.3) 0 1px 2px; border: 1px solid #A0A0A0; height: 50px; width: 50px; font-size: 50px; float: left; margin: 20px; margin-top: 15px; text-align: center; padding-top: 30px;"><?php echo substr($id, 2, 1); ?></div>
            <div style="border-radius: 8px; box-shadow: inset rgba(0,0,0,0.3) 0 1px 2px; border: 1px solid #A0A0A0; height: 50px; width: 50px; font-size: 50px; float: left; margin: 20px; margin-top: 15px; text-align: center; padding-top: 30px;"><?php echo substr($id, 3, 1); ?></div>
            </div>
            <div>
            Type in this code at http://kandutech.net on a mobile device.
            <div style="font-size: 11px;">(You can close safari now if you want.)</div>
            </div>
        </div>
    </div>
    <div style="position: absolute; top: 50%; margin-top: 170px; left: 50%; width: 600px; text-align: center; margin-left: -300px; text-shadow: #fff 0 1px 0;">
    <a href="https://twitter.com/mynamesconnor" class="twitter-follow-button" data-show-count="false">Follow @mynamesconnor</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
    	<a href="https://twitter.com/share" class="twitter-share-button" data-url="https://kandutech.net" data-via="mynamesconnor" data-related="mynamesconnor">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
<!-- Place this tag where you want the +1 button to render. -->
<div class="g-plusone" data-size="medium" data-href="https://kandutech.net"></div>

<!-- Place this tag after the last +1 button tag. -->
<script type="text/javascript">
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script><br><div style="font-size: 12px;">Idea thanks to: <a href="http://9to5mac.com/2013/06/26/demo-of-the-first-public-website-using-safaris-new-native-push-notifications-feature-video/" target="_blank">9to5mac.com</a></div>
    </div>
    <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-42055607-1', 'kandutech.net');
  ga('send', 'pageview');

</script>
</body>
</html>
