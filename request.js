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
	
	var pubinputid;
$( document ).ready(function() {
	
            $(function() {
                if (window.history && window.history.pushState) {
                    $(window).on('popstate', function() {
                        var stateObj = window.history.state;
						if(stateObj != null) {
							$('#searchtext').val(stateObj.searchval);
							$.searchrequest(pubinputid, true);
						}
						else {
							$("#showresults").html("");
							$('#searchtext').val("");
						}
                    });
                }
            });
	
$.searchrequest = function(id, popstate) {
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
	if(popstate == false) history.pushState({ searchval }, 'Title: ' + searchval, '?search=' + searchval);
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
	//if(popstate == false) history.pushState({ data }, 'Title: ' + id, '?step=' + id);
}

$.upcquery = function(upc) {
	$.ajax({ 
		type: "GET",
		url: "php/request.php?upc=" + upc,
		dataType: 'json',
		success: function(data) {
			$.clearshowresults();
			$.clearshowurl();
			$.clearshowingredients();
			$("#showsteps").html("<h3>" + data.items[0].title + "</h3><p>UPC:" + data.items[0].upc + "<br>" + data.items[0].brand + "</p>");
			
		}
	});
}
});

//note this is a body onload function
$.checkconnection = function() {
	$.ajax({ 
		type: "GET",
		url: "php/login.php",
		dataType: 'json',
		success: function(data) {
			if(data.result == true) {
				$("#menu").html("<li class='selected'><a href='index.html'>Home</a></li><li><a href='accountcreate.html'>My Recipes</a></li><li><a href='accountcreate.html'>My Inventory</a></li><li><a href='accountcreate.html'>Account Info</a></li><li id='loginlink'><a href='javascript:$.logoff();'>Sign out</a></li>");
			}
		}
	});	
};

$.logoff = function() {
	$.ajax({ 
		type: "GET",
		url: "php/login.php?logoff=1",
		dataType: 'json',
		success: function(data) {
			if(data.result == true) {
				$("#menu").html("<li class='selected'><a href='index.html'>Home</a></li><li><a href='accountcreate.html'>Sign Up</a></li><li id='loginlink'><a href='login.html'>Login</a></li>");
				$(".body").prepend("<em><font color=green>Sign off success</font></em>");
			}
			else $(".body").prepend("failed to signed off");
		}
	});	
};

$("#signonfrm").submit(function(e) {
	e.preventDefault();
	var form = $(this);
	var url = form.attr('action');
	$.ajax({ 
		type: "POST",
		url: url,
		data: form.serialize(),
		success: function(data) {
			if(data.result == true) {
				$("#featured").html(data.msg);
				$("#loginlink").html("<a href='javascript:$.logoff();'>Sign Out</a>");
			}
			else {
				//$("#featured").prepend(data.msg + "<br>");
				//have login fail message show and delete after so long.
				$("#loginlink").html("<a href='login.html'>Login</a>");
			}
		},
		error: function (xhr, ajaxOptions, thrownError){
			alert(xhr.statusText);
			alert(thrownError);
		}   
	});	
});

	$.displayinventory = function () {
			$.ajax({ 
				type: "GET",
				url: "php/request.php?inventory=1",
				dataType: 'json',
				success: function(data) {
					if(data.result == true) {
						$("#itemlist").html("<table>");
						//loop json array return.
						$("#itemlist").append(
							"<tr><td><a href='php/request.php?inventory=1&item=" + data.itemid + "'>" + data.name + "</a></td><td>" + data.upc + "</td><td><input id=quantitychange type=number min=0 max=110 value = '" + data.quantity + 
							"'><button onclick='increment()'>+</button><button onclick='decrement()'>-</button></td></tr>"
						);
					}
					else $(".body").prepend("failed to signed off");
				}
			});	
	};

};

function get_step(id) {
	$.stepbystep(id);
}
function get_search(inputid) {
	var search = document.getElementById(inputid).value;
	pubinputid = inputid;
	$.searchrequest(search, false);
}	
function upcquery(upc) {
	$.upcquery(upc);
}