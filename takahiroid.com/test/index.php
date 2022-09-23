<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta property="og:type" content="article" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://rawgit.com/sitepoint-editors/jsqrcode/master/src/qr_packed.js">
</script>

<style type="text/css">
body, input {font-size:14pt}
input, label {vertical-align:middle}


.wrapper{
    width: 50%;
}
.wrapper img{
    width: 100%;
}
.qrcode-text-btn {
    cursor:pointer
}
.qrcode-text-btn > input[type=file] {
    position:absolute; 
    overflow:hidden; 
    width:0px; 
    height:0px; 
    opacity:0;
}

</style>

</head>

<body>
<h3>TEST</h3>

<div class="wrapper">
<label class=qrcode-text-btn>
    <img src="btn_camera.png">
    <input type=file accept="image/*" capture=environment onclick="return showQRIntro();" onchange="openQRCamera(this);" tabindex=-1>
</label> 
</div>

<script type="text/javascript">
    
function openQRCamera(node) {
  var reader = new FileReader();
  reader.onload = function() {
    node.value = "";
    qrcode.callback = function(res) {
      if(res instanceof Error) {
        alert("No QR code found. Please make sure the QR code is within the camera's frame and try again.");
      } else {
        // node.parentNode.previousElementSibling.value = res;
        window.location.href = res;
      }
    };
    qrcode.decode(reader.result);
  };
  reader.readAsDataURL(node.files[0]);
}

function showQRIntro() {
  return confirm("Use your camera to take a picture of a QR code.");
}

</script>
</body>
</html>