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
	var IngredientList =  [];
	var recipeSteps = [];

	var recipeeditsub = false;
	

	
//$( document ).ready(function() {
	$(function() {
                if (window.history && window.history.pushState) {
                    $(window).on('popstate', function() {
                        var stateObj = window.history.state;
						if(stateObj != null) {
							$('#searchtext').val(stateObj.searchval);
							$.searchrequest(pubinputid, stateObj.curpage, true);
						}
						else {
							$("#showresults").html("");
							$('#searchtext').val("");
						}
                    });
                }
    });
	

	
	//---------------send search request-----------//
	$.searchrequest = function(id, curpage, popstate) { //need page number
		var searchval = $('#searchtext').val();
		searchval = encodeURIComponent(searchval, "UTF-8");
		$.ajax({ 
			type: "GET",
			url: "php/request.php?search=" + searchval + "&page=" + curpage, //add page number here as well
			dataType: 'json',
			success: function(data) {
				searchdata = data;
				$("#showresults").html("");
				$.each(data.results, function(i, item) {
					$("#showresults").append(data.results[i].title + "(Minutes:" + data.results[i].readyInMinutes + ")<br><a href='javascript:get_step(" + data.results[i].id + ");'><img src='" + 
					"https://spoonacular.com/recipeImages/" + data.results[i].id + "-90x90.jpg" + "'></a>" + "<br>");
				});
				var pages = Math.ceil(data.totalResults / 10);
				$("#showresults").append("<br>Pages: ");
				if(curpage > 0) {
					$("#showresults").append("<a href=''>Prev</a> ");
				}
				for(var i = (curpage * 10) + 1; i <= pages && i <= 10; i++) {
					$("#showresults").append( i + " ");
				}
				if(pages - curpage > 10) {
					$("#showresults").append("<a href=''>Next</a>");
				}
				//note if current page then the i needs to start from current page and the if(pages will be if(pages - currentpage > 10)
				//another if(currentpage > 0) to show previus button
			}
		});
		if(popstate == false) history.pushState({ searchval, curpage }, 'Title: ' + searchval, '?search=' + searchval + "&page=" + curpage); //need page number
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
//-----------------------Load step by step of search result-------------------------//
	$.stepbystep = function(id, spoonid) {
		//var url = searchdata.results[id].sourceUrl;
		var urlvar
		if(spoonid == true) urlvar = "php/request.php?stepid=" + id;
		else urlvar = "php/request.php?steplocalid=" + id;
		$.ajax({ 
			type: "GET",
			url: urlvar,
			dataType: 'json',
			success: function(data) {
				$("#showresults").html("");
				$("#showresults").append("<h2>" + data.title + "</h2>");
				$("#showresults").append("<img src='" + data.image + "'><br>");
				$("#showresults").append("<h2>Information</h2><br>");
				$("#showresults").append("<b>Diets: </b><ul>");
				$.each(data.diets, function(i, item) {
					$("#showresuls").append( "<li>" + data.diets[i] + "</li>");
				});
				$("#showresults").append("</ul>");
				$("#showresults").append("<h2>Ingredients</h2>");
				$.each(data.extendedIngredients, function(i, item) {
					$("#showresults").append(data.extendedIngredients[i].originalString + "<br>");
				});
				$("#showresults").append("<br>");
				$("#showresults").append("<h2>Steps</h2>");
				$.each(data.analyzedInstructions[0].steps, function(i, item) {
					$("#showresults").append(data.analyzedInstructions[0].steps[i].number + ". " + data.analyzedInstructions[0].steps[i].step + "<br><br>");
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

//------------body onload to check connection if the page has restricted content or to force redirect to signon-------------//
	$.checkconnection = function() {
		$.ajax({ 
			type: "GET",
			url: "php/login.php",
			dataType: 'json',
			success: function(data) {
				if(data.result == true) {
					$("#menu").html("<li class='selected'><a href='home.html'>Home</a></li><li><a href='recipes.html'>My Recipes</a></li><li><a href='inventory.html'>My Inventory</a></li><li><a href='accountinfo.html'>Account Info</a></li><li id='loginlink'><a href='javascript:$.logoff();'>Sign out</a></li>");
				}
			}
		});	
	};
//------------click signoff----------//
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
//-------------POST signon form----------------//
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
					$("#menu").html("<li class='selected'><a href='index.html'>Home</a></li><li><a href='accountcreate.html'>My Recipes</a></li><li><a href='inventory.html'>My Inventory</a></li><li><a href='accountcreate.html'>Account Info</a></li><li id='loginlink'><a href='javascript:$.logoff();'>Sign out</a></li>");
				}
				else {
					//$("#featured").prepend(data.msg + "<br>");
					//have login fail message show and delete after so long.
					$("#featured").prepend(data.msg + "<br>");
					$("#loginlink").html("<a href='login.html'>Login</a>");
				}
			},
			error: function (xhr, ajaxOptions, thrownError){
				alert(xhr.statusText);
				alert(thrownError);
			}   
		});	
	});
	//----------convert to json----------//
	function objectifyForm(formArray) {
		//serialize data function
		var returnArray = {};
		for (var i = 0; i < formArray.length; i++){
			returnArray[formArray[i]['name']] = formArray[i]['value'];
		}
		return returnArray;
	}

//-----------POST additem form-----------------//
	$("#additemfrm").submit(function(e) {
		e.preventDefault();
		var form = $(this);
		var url = form.attr('action');
		$.ajax({ 
			type: "POST",
			url: url,
			data: form.serialize(),
			success: function(data) {
				if(data.result == true) {
					$("#displaymsg").html("Item Added");
					$.displayinventory();
				}
				else {
					$("#displaymsg").html("Failed to add item. May already exist");
				}
			},
			error: function (xhr, ajaxOptions, thrownError){
				alert(xhr.statusText);
				alert(thrownError);
			}   
		});	
	});

//---------------------POST addrecipe------------------//
	$("#addrecipefrm").submit(function(e) {
		e.preventDefault();
		var form = $(this);
		var url = form.attr('action');
		var vardata = objectifyForm(form.serializeArray());
		delete vardata['messure'];
		delete vardata['ingredient'];
		delete vardata['unit'];
		delete vardata['step'];
		vardata['ingredient'] = IngredientList;
		vardata['step'] = recipeSteps;
		vardata['posttype'] = 'addrecipe'; //define the json post request.
		$.ajax({ 
			type: "POST",
			url: url,
			dataType: 'json',
			data: JSON.stringify(vardata),
			contentType: "application/json; charset=utf-8",
			success: function(data) {
				if(data.result == true) {
					$("#displaymsg").html("Item Added");
					$.displayinventory();
				}
				else {
					$("#displaymsg").html("Failed to add item. May already exist");
				}
			},
			error: function (xhr, ajaxOptions, thrownError){
				alert(xhr.statusText);
				alert(thrownError);
			}   
		});	
	});
//-----------------list all inventory items------------------//
	$.displayinventory = function () {
			$.ajax({ 
				type: "GET",
				url: "php/request.php?inventory=1",
				dataType: 'json',
				success: function(data) {
					if(data.result == true) {
						$("#itemlist").html("<table id='itemlisttb' width='100%'>");
						//loop json array return.
						$.each(data.item, function(i, item) {
							$("#itemlist").append("<tr id='tr-" + data.item[i].id + "'><td width='150'><a href='php/request.php?inventory=1&item=" + data.item[i].id + "'>" + data.item[i].name + "</a></td><td>" + data.item[i].upc + "</td><td><input style='width:50px;' id='" + data.item[i].id + "' onchange='$.oninvchange(" + data.item[i].id + ", " + data.item[i].quantity + " )' type=number min=0 max=110 value = '" + data.item[i].quantity + 
								"'><button type='button' style='display: none;' id='update-" + data.item[i].id + "' onclick='$.Updateinventory(" + data.item[i].id + ", " + data.item[i].itemid + ")'>Update</button></td></tr>"
							);
						});
						$("#itemlist").append("</table>");
						
					}
					else $(".body").prepend("Failed to recieve list.");
				}
			});	
	};
	//---------------when inventory is changed show/hide update button--------------//
	$.oninvchange = function (id, value) {
		var curval = $('#' + id).val();
		if(value != curval) {
			$('#update-' + id).show();
		}
		else {
			$('#update-' + id).hide();
		}
	};
	
	//-----------------update inventory button------------------//
	$.Updateinventory = function (id, itemid) {
		//alert("Test: " + id);
		var newval = $('#' + id).val();
		$.ajax({
		  type: "POST",
		  url: "php/request.php",
		  data: JSON.stringify( {"posttype": "updateinv", "id": id, "value": newval, "itemid":  itemid } ),
		  datatype: 'json',
		  contentType: "application/json; charset=utf-8",
		  success: function(data) {
			  if(data.result == true) {
				  //update success
				  $("#displaymsg").html("Update Success");
				  if(newval > 0) {
					$('#' + id).attr("onchange", "$.oninvchange(" + id + ", " + newval + ")");
				  }
				  else if(newval == 0) {
					  $('#tr-' + id).remove();
				  }
				  $('#update-' + id).hide();
			  }
			  else {
				  $("#displaymsg").html("Update Failed").show().delay(3000).fadeOut();
				  //show unable to update.
			  }
		  }
		});
		//run ajax if success return then update the quantity input attribute onclick to reflect the updated value.
	}
	//-------------------List all recipes----------//
	$.displayrecipe = function(type) {
		$("#addrecipefrm").hide();
		$("#recipelist").show();
		$("#recipelistadd").html("Add recipe");
		$("#recipelistadd").attr("href", "javascript:$.displayrecipefrm()");
		$("#recipelisttb tbody > tr").remove();
		$("#recipefavtab tbody > tr").remove();
		$("#recipedistab tbody > tr").remove();
		var urlvar = "php/request.php?recipe=" + type;
		$.ajax({ 
		type: "GET",
		url: urlvar,
		dataType: 'json',
		success: function(data) {
			if(data.result == true) {
				//loop json array return.
				$.each(data.recipe, function(i, recipe) {
					$("#recipelisttb tbody").append("<tr><td>" + data.recipe[i].name + "</td><td>" + data.recipe[i].preptime + "</td><td>" + data.recipe[i].nationality + 
					"</td><td>" + data.recipe[i].dietaryrestrictions + " </td><td>" +
					data.recipe[i].foodtype + "</td><td>" + 
					data.recipe[i].servingsize + "</td><td><button type=button onclick='$.openeditrecipe(" + data.recipe[i].id + ")'>Edit</button><button type=button onclick='$.removerecipelist(" + data.recipe[i].id + ", false)'>Remove</button></td></tr>");
				});
				
				$.each(data.favdis, function(i, favdis) {
					var personalstring;
					if(data.favdis[i].spoonid == 0) personalstring = "TRUE";
					else personalstring = "FALSE";
					if(data.favdis[i].like == true) {
						$("#recipefavtab tbody").append("<tr><td>" + data.favdis[i].name + "</td><td>" + data.favdis[i].preptime + "</td><td>" + data.favdis[i].nationality + 
						"</td><td>" + data.favdis[i].dietaryrestrictions + " </td><td>" +
						data.favdis[i].foodtype + "</td><td>" + 
						data.favdis[i].servingsize + "</td><td>" +
						personalstring + "</td><td>" +
						"<td><button type=button onclick='$.removerecipelist(" + data.favdis[i].id + ", true)>Remove</button></td></tr>");
					}
					else {
						$("#recipedistab tbody").append("<tr><td>" + data.favdis[i].name + "</td><td>" + data.favdis[i].preptime + "</td><td>" + data.favdis[i].nationality + 
						"</td><td>" + data.favdis[i].dietaryrestrictions + " </td><td>" +
						data.favdis[i].foodtype + "</td><td>" + 
						data.favdis[i].servingsize + "</td><td>" +
						personalstring + "</td><td>" +
						"<button type=button onclick='$.removerecipelist(" + data.favdis[i].id + ", true)'>Remove</button></td></tr>");
					}
				});
							
				}
				else $(".body").prepend("Failed to recieve list.");
		},
		error: function (xhr, ajaxOptions, thrownError){
			alert(xhr.statusText);
			alert(thrownError);
		}
		});	
	}
	//--------------------------editbutton on recipe edit--------------------//
	$.openeditrecipe = function(id) {
		$("#addrecipefrm").show();
		$("#recipelist").hide();
		$.ajax({ 
		type: "GET",
		url: "php/request.php?recipe=" + id,
		dataType: 'json',
		success: function(data) {
			if(data.result == true) {
				//empty ingredients list
				//empty recipestep list
					recipeeditsub = true;
					IngredientList.splice(0, IngredientList.length);
					recipeSteps.splice(0, recipeSteps.length);
					$.each(data.ingredients, function(i, ingredients) {
						IngredientList.push({name: data.ingredients[i].name, measure: data.ingredients[i].quantity , unit: data.ingredients[i].measurement });
					});
					$.each(data.recipestep, function(i, recipestep) {
						
						recipeSteps[i] = {id: data.recipestep[i].id, content: data.recipestep[i].step };
					});
					$("#recname").val(data.recipe.name);
					//$("#image").val(recipeList[i].img);
					$("#serving").val(data.recipe.servingsize);
					$("#prep-time").val(data.recipe.preptime);
					//$("#serving").val(data.recipe.);
					//$("#serving").val(recipeList[i].img);
					//$("#serving").val(recipeList[i].img);
					$("#nationality").val(data.recipe.nationality);
					$("#dietrestriction").val(data.recipe.dietaryrestrictions);
					$("#foodtype").val(data.recipe.foodtype);
					
					//loop steps post to table here
					$("#steptbl tbody > tr").remove();
					for(var i = 0; i < recipeSteps.length; i++) {
						$("#steptbl tbody").append("<tr><td id='stepindx-" + i + "'>#" + (i + 1) + "</td><td id='stepcont-" + i + "'>" + recipeSteps[i].content.substr(0, 30) + "</td><td><button id='updstep-" + i + "' type='button' onclick='$.editrecipestep(" + i + ")'>Edit</button><button type='button' onclick='$.removerecipestep(" + i + ")'>Remove</button></td></tr>");
					}
					//loop ingredeints and post here to table.
					$("#inglist tbody > tr").remove();
					for(var i = 0; i < IngredientList.length; i++) {
						var selection = '<select id="unitid-' + i + '" onchange="$.changedingredient(' + i + ')"><option value="teaspoon">tsp</option>' +
						'<option value="tablespoon">tbs</option><option value="ounce">Fluid ounce</option>' +
						'<option value="cup">cup</option><option value="pint">pint</option><option value="quart">quart</option><option value="gallon">gallon</option>' +
						'<option value="whole">whole</option><option value="piece">piece</option><option value="loaf">loaf</option></select>';
						$("#inglist tbody").append("<tr id='" + i + "'><td><input onchange='$.changedingredient(" + i + ")' id='name-" + i + "' type='text' value='" + IngredientList[i].name + "'></td><td><input onchange='$.changedingredient(" + i + ")' style='width: 60px;' min='0' id='mes-" + i + "'  type='number' value='" + IngredientList[i].measure + "'></td><td>" + selection + "</td>" +
						"<td><button id='update-" + i + "' type='button' disabled onclick='$.updateingredient(" + i + ")'>Update</button><button type='button' onclick='$.removeingredient(" + i + ")'>Remove</button></td></tr>");
						$("#unitid-" + i).val(IngredientList[i].unit);
					}
				}
				else $(".body").prepend("Failed to recieve.");
			}
		});	
	}
	//------------remove from my rcipelist-------------//
	$.removerecipelist = function(id, fav) {
		//post to remove  (recieve new list)
		var vurl;
		if(fav == false) vurl = "php/request.php?recipe=" + id + "&remove=true";
		else vurl = "php/request.php?favrmv=" + id;
		$.ajax({ 
		type: "GET",
		url: vurl,
		dataType: 'json',
		success: function(data) {
			if(data.result == true) {
				$.displayrecipe(0);
			}
		}
			
		});
	}
	
	//----------------displayrecipeadd form--------------//
	$.displayrecipefrm = function() {
		$("#addrecipefrm").show();
		$("#recipelist").hide();
		recipeeditsub = false;
		//------clean up if they were eaditing
		IngredientList.splice(0, IngredientList.length);
		recipeSteps.splice(0, recipeSteps.length);
		$("#recname").val("");
		$("#serving").val("");
		$("#prep-time").val("");
		$("#nationality").val("");
		$("#dietrestriction").val("");
		$("#foodtype").val("");
		$("#steptbl tbody > tr").remove();
		$("#inglist tbody > tr").remove();
		$("#recipelistadd").html("My Recipe List");
		$("#recipelistadd").attr("href", "javascript:$.displayrecipe(0)");
	}
		//-------------button add recipe step--------------//
	$.addrecipestep = function() {
		var valstepcontent = $("#step").val();
		var indexid = recipeSteps.length;
		recipeSteps.push({id: indexid, content: valstepcontent });
		$("#step").val("");
		$("#stepheader").html("Step #" + (recipeSteps.length + 1));
		$("#steptbl tbody").append("<tr><td id='stepindx-" + indexid + "'>#" + (indexid + 1) + "</td><td id='stepcont-" + indexid + "'>" + valstepcontent.substr(0, 30) + "</td><td><button id='updstep-" + indexid + "' type='button' onclick='$.editrecipestep(" + indexid + ")'>Edit</button><button type='button' onclick='$.removerecipestep(" + indexid + ")'>Remove</button></td></tr>");
	}
	
	//-------------button remove recipe step-------------//
	$.removerecipestep = function(indexid) {
		if(typeof recipeSteps[indexid] != 'undefined') {
			recipeSteps.splice(indexid, 1); //delete 1 element from indexid
			$("#steptbl tbody > tr").remove();
			$("#stepheader").html("Step #" + (recipeSteps.length + 1));
			for(var i = 0; i < recipeSteps.length; i++) {
					$("#steptbl tbody").append("<tr><td id='stepindx-" + i + "'>#" + (i + 1) + "</td><td id='stepcont-" + i + "'>" + recipeSteps[i].content.substr(0, 30) + "</td><td><button id='updstep-" + i + "' type='button' onclick='$.editrecipestep(" + i + ")'>Edit</button><button type='button' onclick='$.removerecipestep(" + i + ")'>Remove</button></td></tr>");
			}
		}
	}
	//-------------edit button for recipe step-----------//
	$.editrecipestep = function(indexid) {
		if(typeof recipeSteps[indexid] != 'undefined') {
			$("#step").val(recipeSteps[indexid].content);
			$("#recipestepbtn").attr("onclick", "$.updaterecipestep(" + indexid + ")");
			$("#recipestepbtn").html("update");
			$("#stepheader").html("Step #" + (indexid + 1));
		}
	}
	//--------recipestepbtn if edit update clicked-----------//
	$.updaterecipestep = function(indexid) {
		if(typeof recipeSteps[indexid] != 'undefined') {
			var valstepcontent = $("#step").val();
			recipeSteps[indexid] = {id: indexid, content: valstepcontent };
			$("#step").val("");
			$("#stepcont-" + indexid).html(valstepcontent.substr(0,30));
			$("#recipestepbtn").attr("onclick", "$.addrecipestep()");
			$("#recipestepbtn").html("Add");
			$("#stepheader").html("Step #" + (recipeSteps.length +1));
		}
	}
	
	//-------------button add ingredient--------------//
	$.addingredientbutton = function() {
		var valunit = $("#unitid").val();
		var valmeasure = $("#messureid").val();
		var valname = $("#ingredientid").val();
		IngredientList.push({name: valname, measure: valmeasure , unit: valunit });
		var indexid = IngredientList.length - 1;
		var selection = '<select id="unitid-' + indexid + '" onchange="$.changedingredient(' + indexid + ')"><option value="teaspoon">tsp</option>' +
		'<option value="tablespoon">tbs</option><option value="ounce">Fluid ounce</option>' +
		'<option value="cup">cup</option><option value="pint">pint</option><option value="quart">quart</option><option value="gallon">gallon</option>' + 
		'<option value="whole">whole</option><option value="piece">piece</option><option value="loaf">loaf</option></select>';
		$("#inglist tbody").append("<tr id='" + indexid + "'><td><input onchange='$.changedingredient(" + indexid + ")' id='name-" + indexid + "' type='text' value='" + valname + "'></td><td><input onchange='$.changedingredient(" + indexid + ")' style='width: 60px;' min='0' id='mes-" + indexid + "'  type='number' value='" + valmeasure + "'></td><td>" + selection + "</td>" +
		"<td><button id='update-" + indexid + "' type='button' disabled onclick='$.updateingredient(" + indexid + ")'>Update</button><button type='button' onclick='$.removeingredient(" + indexid + ")'>Remove</button></td></tr>");
		$("#unitid-" + indexid).val(valunit);
	}
	//-------------------button update ingredient----------//
	$.updateingredient = function(indexid) {
		if(typeof IngredientList[indexid] != 'undefined') {
			var valunit = $("#unitid-" + indexid).val();
			var valmeasure = $("#mes-" + indexid).val();
			var valname = $("#name-" + indexid).val();
			IngredientList[indexid] = {name:  valname, measure: valmeasure, unit: valunit };
			$("#ingredientmsg").html("<font color=green>Update Success</font>").show().delay(4000).fadeOut();
			$("#update-" + indexid).prop("disabled", true);
		}
	}
	//-------------button remove ingredient-------------//
	$.removeingredient = function(indexid) {
		if(typeof IngredientList[indexid] != 'undefined') {
			//$("table#inglist tr#" + indexid).remove();
			
			console.log(IngredientList[0].name);
			$("#ingredientmsg").html("<font color=red>Item #" + (indexid + 1) + " " + (IngredientList[indexid].name) + " removed</font>").show().delay(4000).fadeOut();
			IngredientList.splice(indexid, 1); //delete 1 element from indexid
			$("#inglist tbody > tr").remove();
			for(var i = 0; i < IngredientList.length; i++) {
				var selection = '<select id="unitid-' + i + '" onchange="$.changedingredient(' + i + ')"><option value="teaspoon">tsp</option>' +
				'<option value="tablespoon">tbs</option><option value="ounce">Fluid ounce</option>' +
				'<option value="cup">cup</option><option value="pint">pint</option><option value="quart">quart</option><option value="gallon">gallon</option>' +
				'<option value="whole">whole</option><option value="piece">piece</option><option value="loaf">loaf</option></select>';
				$("#inglist tbody").append("<tr id='" + i + "'><td><input onchange='$.changedingredient(" + i + ")' id='name-" + i + "' type='text' value='" + IngredientList[i].name + "'></td><td><input onchange='$.changedingredient(" + i + ")' style='width: 60px;' min='0' id='mes-" + i + "'  type='number' value='" + IngredientList[i].measure + "'></td><td>" + selection + "</td>" +
				"<td><button id='update-" + i + "' type='button' disabled onclick='$.updateingredient(" + i + ")'>Update</button><button type='button' onclick='$.removeingredient(" + i + ")'>Remove</button></td></tr>");
				$("#unitid-" + i).val(IngredientList[i].unit);
			}
		}
	}
	
	//---------------if changes on ingredient enable/disable update button------------//
	$.changedingredient = function (indexid) {
		if(typeof IngredientList[indexid] != 'undefined') {
			var valunit = $("#unitid-" + indexid).val();
			var valmeasure = $("#mes-" + indexid).val();
			var valname = $("#name-" + indexid).val();
			if(valunit != IngredientList[indexid].unit || valmeasure != IngredientList[indexid].measure || valname != IngredientList[indexid].name) {
				$("#update-" + indexid).prop("disabled", false);
			}
			else {
				$("#update-" + indexid).prop("disabled", true);
			}
		}
	}
	//--------------toggle link content Add Item/Close (used in inventory)------------//
	$.displayitemadd = function() {
		$('#additemfrm').toggle();
		if($('#additemfrm:visible')[0]) {
			$('#addlink').html('Close');
		} else {
			$('#addlink').html('Add Item');
		}
	}
	
	$.AddItemInventory = function(id) {
		
		$('#additemfrm').hide();
	}

};

function get_step(id) {
	$.stepbystep(id, true);
}
function get_search(inputid) {
	var search = document.getElementById(inputid).value;
	pubinputid = inputid;
	$.searchrequest(search, false);
}	
function upcquery(upc) {
	$.upcquery(upc);
}