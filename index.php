<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<title>University of Waterloo Room Schedule Finder</title>
	<link rel="stylesheet" href="base.css" type="text/css" />
	<script src="jquery.js" type="text/javascript"></script>
	<script src="main.js" type="text/javascript"></script>
</head>
<body>

<form id="topBar">
	<span id="textbox">
	<input type="text" id="building" class="text" maxlength="3" />
	<input type="text" id="room" class="text" maxlength="4" />
	</span>
<input type="submit" id="submit" class="button" value="Submit" />
<img src="load.gif" id="loading" style="width: 25px; height: 25px;visibility: hidden;" />
</form>

<div id="mainContent">

<table id="output" style="padding: 10px;"></table>

</div>
</body>
</html>