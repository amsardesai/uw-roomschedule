var calStart = 3;
var calEnd = 23;
var height = 0;

function setUpCalendar() {

	$("#calendar").html("")
		.append($("<div>").attr("id","toplabels")
			.append($("<div>").addClass("week").html("Monday"))
			.append($("<div>").addClass("week").html("Tuesday"))
			.append($("<div>").addClass("week").html("Wednesday"))
			.append($("<div>").addClass("week").html("Thursday"))
			.append($("<div>").addClass("week").html("Friday")))
		.append($("<div>").attr("id","sidetime"))
		.append($("<div>").attr("id","weekcontainer")
			.append($("<div>").addClass("week").attr("id","monday"))
			.append($("<div>").addClass("week").attr("id","tuesday"))
			.append($("<div>").addClass("week").attr("id","wednesday"))
			.append($("<div>").addClass("week").attr("id","thursday"))
			.append($("<div>").addClass("week").attr("id","friday")));

	for (var i=calStart-1;i<calEnd;i++) {
		var sidetext = i+":00";
		var sideobj = $("<div>").addClass("hour").html(sidetext);
		if (i>calStart-1) $("#calendar #sidetime").append(sideobj);
		$("#mainContent #calendar #weekcontainer .week").each(function(index) {
			$(this).append($("<div>").addClass("hour"));
		});
	}

	height = $("#calendar .hour").outerHeight()
	var topheight = $("#calendar #toplabels").outerHeight();
	$("#calendar").innerHeight(height*(calEnd-calStart+1)+topheight);

	var hi;
	addToCalendar(9,0,0,50,1,hi);

}

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

function addToCalendar(starthour, startminute, lengthhour, lengthminute, day, course) {
	var entry = $("<div>").addClass("item");
	var focus;
	switch (day) {
		case 1: focus = $("#calendar #monday .hour");
		break;
		case 2: focus = $("#calendar #tuesday .hour");
		break;
		case 3: focus = $("#calendar #wednesday .hour");
		break;
		case 4: focus = $("#calendar #thursday .hour");
		break;
		case 5: focus = $("#calendar #friday .hour");
		break;
	}

	var height = focus.outerHeight();
	
	focus.eq(starthour-calStart+1).html(entry);





}





$(document).ready(function() {

	// create calendar - move this code to processJSON later
	setUpCalendar();

	$("#submit").click(function(e) {
		e.preventDefault();
		var building = $("#building").val();
		var room = $("#room").val();
		$.ajax({
			url: "query.php",
			type: "POST",
			data: { b: building, r: room },
			dataType: "json",
			beforeSend: function() {
				$("#loading").removeClass("hide");
			},
			success: function() {
				$("#loading").addClass("hide");
			}
		}).done(function(msg){processJSON(msg)});
	});

	$('#building').keyup(function() {
		if(this.value.length == $(this).attr('maxlength')) {
			$('#room').val("").focus();
		}
	});

	$("#building").limitkeypress({ rexp: /^[A-Za-z0-9]*$/ });
	$("#room").limitkeypress({ rexp: /^[0-9]*$/ });


});
