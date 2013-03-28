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
	<label for="building">Enter <strong>building code</strong> and <strong>room number</strong>: </label>
	<span id="textbox">
	<input type="text" name="building" id="building" class="text" maxlength="3" placeholder="EIT" />
	<input type="text" name="room" id="room" class="text" maxlength="4" placeholder="1015" />
	</span>
	<input type="submit" id="submit" value="Submit" />
	<img src="load.gif" id="loading" class="hide" />
</form>

<div id="mainContent">

<table id="output" style="padding: 10px;"></table>

</div>
</body>
</html>