var script = document.createElement('script'); 
 
script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js'; 
script.type = 'text/javascript';
document.getElementsByTagName('head')[0].appendChild(script);

var searchdata;
/*
send request to our webserver then send to api.
*/


//on jquery script done loading
script.onload = function() {
	var $ = window.jQuery; //set $ to use jquery.
$( document ).ready(function() {
	
	
$.searchrequest = function(id) {
	var searchval = $('#searchtext').val();
	searchval = encodeURIComponent(searchval, "UTF-8");
	$.ajax({ 
		type: "GET",
		url: "php/request.php?search=" + searchval,
		dataType: 'json',
		success: function(data) {
			searchdata = data;
			$("#showresults").html("");
			$.each(data.results, function(i, item) {
				$("#showresults").append(data.results[i].title + "(Minutes:" + data.results[i].readyInMinutes + ")<br><a href='javascript:get_step(" + i + ");'><img src='" + "https://spoonacular.com/recipeImages/" + data.results[i].id + "-90x90.jpg" + "'></a>" + "<br>");
			});
			
		}
	});
}

$.clearshowresults = function() {
	$("#showresults").html("");
}

$.clearshowsteps = function () {
	$("#showsteps").html("");
}
$.clearshowurl = function() {
	$("#showurl").html("");
}
$.clearshowingredients = function () {
	$("#showingredients").html("");
}

$.stepbystep = function(id) {
	var url = searchdata.results[id].sourceUrl;
	$.ajax({ 
		type: "GET",
		url: "php/request.php?stepurl=" + url,
		dataType: 'json',
		success: function(data) {
			$.clearshowresults();
			$.clearshowurl();
			$.clearshowingredients();
			$("#showurl").html(url);
			$.each(data.extendedIngredients, function(i, item) {
				$("#showingredients").append(data.extendedIngredients[i].originalString + "<br>");
			});
			$.each(data.analyzedInstructions[0].steps, function(i, item) {
				$("#showsteps").append(data.analyzedInstructions[0].steps[i].number + ". " + data.analyzedInstructions[0].steps[i].step + "<br>");
			});
			
		}
	});
}

});
};

function get_step(id) {
	$.stepbystep(id);
}
function get_search(inputid) {
	var search = document.getElementById(inputid).value;
	$.searchrequest(search);
}	