var theTimeout = false;
function foto() {
	$('.content').empty();
	$('.content').append("<img style='margin: 5px 473px;' id='progressbar' src='http://www.spoofee.com/img/main/loading.gif'>");

	$.ajax({
		type: "POST",
		url: "pagin.php",
		//data: "",
		success: function (data) {
			var login = $(data).find("input[name='auth']").val()
			if (login == "auth") {
				$("body").empty().append(data);
			}
			else {
				$(".content").empty().append(data);
			}
		}
	})
}
function bigFoto(doResort) {
	$('.content').empty();
	$('.content').append("<img style='margin: 5px 473px;' id='progressbar' src='http://www.spoofee.com/img/main/loading.gif'>");

	$.ajax({
		type: "POST",
		url: "pagin_big_foto.php"+(doResort ? '?resort' : ''),
		//data: "",
		success: function (data) {
			var login = $(data).find("input[name='auth']").val()
			if (login == "auth") {
				$("body").empty().append(data);
			}
			else {
				$(".content").empty().append(data);
			}
		}
	})
}
function showDeleteResults(doResort) {
	$('.content').empty();
	$('.content').append("<img style='margin: 5px 473px;' id='progressbar' src='http://www.spoofee.com/img/main/loading.gif'>");

	$.ajax({
		type: "POST",
		url: "prew.php"+(doResort ? '?resort' : ''),
		//data: "",
		success: function (data) {
			var login = $(data).find("input[name='auth']").val()
			if (login == "auth") {
				$("body").empty().append(data);
			}
			else {
				$(".content").empty().append(data);
			}	
		}	
	})
}
function statistic() {
	$('.content').empty();
	$('.content').append("<img style='margin: 5px 473px;' id='progressbar' src='http://www.spoofee.com/img/main/loading.gif'>");

	$.ajax({
		type: "POST",
		url: "index.php",
		data: "loadStats=123",
		success: function (data) {
			var login = $(data).find("input[name='auth']").val()
			if (login == "auth") {
				$("body").empty().append(data);
			}
			else {
				$(".content").empty().append(data);
			}
		}
	});
}
function showPage(page, event) {
	event.preventDefault(event);
	var page = page;

	$.ajax({
		type: "POST",
		url: "pagin.php",
		data: "page=" + page,
		success: function (data) {
			$("#pagin").empty().append($(data).find("#data").html());
			$("#title_left").empty().append($(data).find("#title_left").html());
		}
	});
}
function showPageBig(page, event) {
	event.preventDefault(event);
	var page = page;

	$.ajax({
		type: "POST",
		url: "pagin_big_foto.php",
		data: "page=" + page,
		success: function (data) {
			$("#pagin").empty().append($(data).find("#data").html());
			$("#title_left").empty().append($(data).find("#title_left").html());
		}
	});
}
function showPagePrew(page, event) {
	event.preventDefault(event);
	var page = page;

	$.ajax({
		type: "POST",
		url: "prew.php",
		data: "page=" + page,
		success: function (data) {
			$("#pagin").empty().append($(data).find("#data").html());
			$("#title_left").empty().append($(data).find("#title_left").html());
		}
	});
}
function runall() {
	closePopup();
	$.ajax({
		type: "POST",
		url: "proces.php",
		data: "send_form=3",
	});
	
	$(".popupbg").show();
	$(".popup").css('margin-left', ($(window).width() / 2 - $(".popup").width() / 2) + 'px');
	$(".popup").empty().append("<p style=\"width:100%;\">Задача поставлена в очередь</p><button class='cancel' onclick='closePopup()'>Ок</button>").show();
	$('#a2').click();
}
function closePopup() {
	$(".popup").hide();
	$(".popupbg").hide();
}
function saveForm1() {
	var value = $(".saveForm1Val").val();
	$(".content").empty().append("<img style='margin: 20px 453px;' id='progressbar' src='http://www.spoofee.com/img/main/loading.gif'>");
	closePopup();
	$.ajax({
		type: "POST",
		url:  "prew.php",
		data: "puts="+value+"&send_form=3",
		success: function(html){
			$(".content").empty().append(html);
		}
	});
}
function saveForm9() {
	closePopup();
	$(".content").empty().append("<img style='margin: 20px 453px;' id='progressbar' src='http://www.spoofee.com/img/main/loading.gif'>");
	$.ajax({
		type: "POST",
		url:  "prew.php",
		data: "send_form=9",
		success: function(html){
			$(".content").empty().append(html);
			}
	});	
}