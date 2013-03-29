var calStart = 9;
var calEnd = 24;
var calHourHeight = 60;
var height = 0;

function clearCalendar() {
	$("#calendar").html("").css("background-color","white").stop().css("opacity",1);
}

function setUpCalendar() {
	clearCalendar();
	$("#calendar").css("background-color","#EEE")
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
		var sidetext;
		if (i<12) sidetext = i+" am";
		else if (i==12) sidetext = "noon";
		else sidetext = (i-12)+" pm";
		var sideobj = $("<div>").addClass("hour").html(sidetext);
		if (i>calStart-1) $("#calendar #sidetime").append(sideobj);
		$("#mainContent #calendar #weekcontainer .week").each(function(index) {
			$(this).append($("<div>").addClass("hour"));
		});
	}

	$("#calendar .hour").height(calHourHeight);
	height = $("#calendar .hour").outerHeight();
	var topheight = $("#calendar #toplabels").outerHeight();
	$("#calendar").innerHeight(height*(calEnd-calStart+1)+topheight);
}

function errorCalendar(number,msg) {
	clearCalendar();
	$("#calendar")
		.append($("<div>").attr("id","errorcontainer")
			.append($("<div>").attr("id","number").html("Error " + number + ":"))
			.append($("<div>").attr("id","message").html(msg)));
}

function addToCalendar(day,course) {
	var start = parseInt(course.start);
	var end = parseInt(course.end);
	var starthour = Math.floor(start/100);
	var startminute = start % 100;
	var endhour = Math.floor(end/100);
	var endminute = end % 100;
	var lengthhour = endhour - starthour;
	var lengthminute = endminute - startminute;
	if (lengthminute < 0) {
		lengthhour--;
		lengthminute += 60;
	}

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
	var vOffset = height*(startminute/60);
	var vHeight = height*(lengthhour+(lengthminute/60));
	entry.css("top",vOffset);
	entry.css("height",vHeight);

	entry.append($("<div>").addClass("info")
			.append($("<div>").addClass("coursename").html(course.course))
			.append($("<div>").addClass("coursesection").html(" - " + course.type + " " + course.section)))
		.append($("<div>").addClass("info coursetitle").html(course.title))
		.append($("<div>").addClass("info").html(course.instructor));

	var type = course.type;
	if (type=="LEC") entry.addClass("lec");
	else if (type=="TUT") entry.addClass("tut");
	else entry.addClass("other");


}

function processJSON(json) {

	if (json.error) {
		errorCalendar(json.number, json.message);
		return;
	}
	setUpCalendar();
	// params: course, title, type, section, instructor, start, end, days, term, id

	for (var i=0;i<json.length;i++) {
		var days = json[i].days;
		for (var j=0;j<days.length;j++) {
			var cur = parseInt(days.charAt(j));
			addToCalendar(cur,json[i]);
		}
	}
}



$(document).ready(function() {
	clearCalendar();
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
				$("#calendar").animate({ opacity:0 }, 500, "linear");
			},
			success: function() {
				$("#loading").addClass("hide");
			}
		}).done(function(msg){processJSON(msg)});
	});

	$('#building').keyup(function(e) {
		if(this.value.length == $(this).attr('maxlength') || e.keyCode==32) {
			$('#room').val("").focus();
		}
	});

	$("#building").limitkeypress({ rexp: /^[A-Za-z0-9]*$/ });
	$("#room").limitkeypress({ rexp: /^[0-9]*$/ });


});
