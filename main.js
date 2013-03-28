

function processJSON(json) {




}



$(document).ready(function() {


$("#submit").click(function() {

	var building = $("#building").val();
	var room = $("#room").val();

	$.ajax({
		url: "query.php",
		type: "POST",
		data: { b: building, r: room },
		dataType: "json"
	}).done(function(msg){processJSON(msg)});

	$(this).attr("value","done!");


});


});
