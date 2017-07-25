$(document).ready(function () {
	$(".menup").off();
	$(".menup").on('click', function (data) {
		if(typeof theTimeout != 'undefined' && theTimeout) window.clearTimeout(theTimeout);
		var id = $(this).attr('id');
		$(".menu").find(".active").removeClass();
		$('#' + id).addClass('active');
		if (id == "a1") {
			statistic();
		}
		if (id == "a2") {
			foto();
		}
		if (id == "a3") {
			showDeleteResults();
		} 
		if (id == "a4") {
			bigFoto();
		}
	});
	$(".resort").off();
	$(".resort").on('click', function() {
		showDeleteResults(true);
		theTimeout = setTimeout(function(){
		   $('#a3').click();
		}, 3000);
	});
	$(".rebig").off();
	$(".rebig").on('click', function() {
		bigFoto(true);
		theTimeout = setTimeout(function(){
		   $('#a4').click();
		}, 3000);
	});
	$(".showlist").off();
	$(".showlist").on('click', function (event) {
		if(typeof theTimeout != 'undefined' && theTimeout) window.clearTimeout(theTimeout);
		event.preventDefault(event);
		$("#pagin").empty().append("<img style='margin: 20px 453px;' id='progressbar' src='http://www.spoofee.com/img/main/loading.gif'>");
		$(".runall").attr('disabled', 'disabled');
		$("input[name='fg']").remove();
		var zapros = $("input[name='zapros']").val();
		var shir = $("input[name='shir']").val();
		var vis = $("input[name='vis']").val();
		var size = $("input[name='size']").val();

		$.ajax({
			type: "POST",
			url: "pagin.php",
			data: "sub=sub&zapros=" + zapros + "&shir=" + shir + "&vis=" + vis + "&size=" + size,
			success: function (data) {
				/* $("#pagin").empty().append($(data).find("#data").html());
				$("#title_left").empty().append($(data).find("#title_left").html());
				var fg = $(data).find("#lines").text();
				$(".content").remove(".fg").append("<input class='fg' type='hidden' name='fg' value='" + fg + "'>");
				$(".runall").removeAttr('disabled'); */
				$("#_vr_msg").html(data);
			}
		})
	});
	$(".runall").off();
	$(".runall").on('click', function() {
		var fg = $("input[name=fg]").val();
		$(".popupbg").show();
		$(".popup").css('margin-left', ($(window).width() / 2 - $(".popup").width() / 2) + 'px');
		$(".popup").empty().append("<p>Будет обработано " + fg + " файлов. Продолжить?</p><button class='submit' onclick='runall()'>Да</button><button class='cancel' onclick='closePopup()'>Нет</button>").show();
	});
	$(".preview").off();
	$(".preview").on('click', function() {
		var check = $(this).attr('id');
		
		if (check == "") {
			//var value = $(this).text();
			var value = $(this).val();
			var idshir = $("#shir").val();
			var idvis = $("#vis").val();

			//alert(value)
			$.ajax({
				type: "POST",
				url: "proces.php",
				data: "shir="+idshir+"&vis="+idvis+"&put="+value+"&send_form=2",
				success: function(html){
					$("#mod").html(html);
				}
			});
			
			$('#exampleModal').arcticmodal({
				afterClose: function(data, el) {
					$.ajax({
						type: "POST",
						url: "proces.php",
						data: "send_form=4",
					});
				}
			});
		}
		else {
			var url = $(this).val();
			$("#mod").empty().append("<img style='width: 900px; height: 600px;' src='" + url + "'>");
			$('#exampleModal').arcticmodal();
		}
	});
	$(".saveForm").off();
	$(".saveForm").on('click', function() {
		var value = $(this).val();
		$.ajax({
			type: "POST",
			url:  "prew.php",
			data: "put="+value+"&send_form=2",
			success: function(html){
				$("#mod").html(html);
			}
		});
		
		$('#exampleModal').arcticmodal({
			/*afterClose: function(data, el) {
				$('.content').empty();
				$('.content').append("<img style='margin: 20px 473px;' id='progressbar' src='http://www.spoofee.com/img/main/loading.gif'>");

				$.ajax({
					type: "POST",
					url: "prew.php",
					//data: "",
					success: function (data) {
						$('#progressbar').remove();
						$(".content").append(data);		
					}	
				})
			}*/
		});
	});
	$(".saveForm1").off();
	$(".saveForm1").on('click', function() {
		var value = $(this).val();
		$(".popupbg").show();
		$(".popup").css('margin-left', ($(window).width() / 2 - $(".popup").width() / 2) + 'px');
		$(".popup").empty().append("<input type='hidden' class='saveForm1Val' value='" + value + "'><p>Вы собираетесь удалить выбранную фотограффию. Продолжить?</p><button class='submit' onclick='saveForm1()'>Да</button><button class='cancel' onclick='closePopup()'>Нет</button>").show();
	});
	$(".saveForm2").off();
	$(".saveForm2").on('click', function() {
		var value = $(this).val();
		//if (window.confirm('Вы собираетесь удалить оригинал выбранной фотограффии. Продолжить?')){
			$(".content").empty().append("<img style='margin: 20px 473px;' id='progressbar' src='http://www.spoofee.com/img/main/loading.gif'>");

			$.ajax({
				type: "POST",
				url: "prew.php",
				data: "puts="+value+"&send_form=4",
				success: function(html){
					$("#mod").html(html);
				}	
			});
			$('#exampleModal').arcticmodal({

			});
		//}
	});
	$(".saveForm9").off();
	$(".saveForm9").on('click', function() {
		$(".popupbg").show();
		$(".popup").css('margin-left', ($(window).width() / 2 - $(".popup").width() / 2) + 'px');
		$(".popup").empty().append("<p>Вы собираетесь удалить все фото данного списка. Продолжить?</p><button class='submit' onclick='saveForm9()'>Да</button><button class='cancel' onclick='closePopup()'>Нет</button>").show();
	});
	$(".saveForm_big").off();
	$(".saveForm_big").on('click', function () {
		var value = $(this).val();

		$.ajax({
			type: "POST",
			url: "proces.php",
			data: "put="+value+"&send_form=5",
			success: function(html){
				$("#mod").html(html);
			}
		});
		$('#exampleModal').arcticmodal();
	});
	$(".saveForm_small").off();
	$(".saveForm_small").on('click', function () {
		var value = $(this).val();

		$.ajax({
			type: "POST",
			url: "proces.php",
			data: "put="+value+"&send_form=6",
			success: function(html){
				$("#mod").html(html);
			}
		});
		$('#exampleModal').arcticmodal();
	});
});