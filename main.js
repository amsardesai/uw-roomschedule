

function processJSON(json) {

$("#output").html(" ");
if (json.error) {
	alert("Error " + json.number + ": " + json.message);
	return;
}

for (i=0;i<json.length;i++) {
	newRow = $("<tr>")
		.append($("<td>").attr("name","course").html(json[i].course))
		.append($("<td>").attr("name","title").html(json[i].title))
		.append($("<td>").attr("name","type").html(json[i].type))
		.append($("<td>").attr("name","section").html(json[i].section))
		.append($("<td>").attr("name","instructor").html(json[i].instructor))
		.append($("<td>").attr("name","start").html(json[i].start))
		.append($("<td>").attr("name","end").html(json[i].end))
		.append($("<td>").attr("name","days").html(json[i].days))
		.append($("<td>").attr("name","term").html(json[i].term))
		.append($("<td>").attr("name","id").html(json[i].id));
	$("#output").append(newRow);
}


}



$(document).ready(function() {


$("#submit").click(function(e) {
	e.preventDefault();
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
