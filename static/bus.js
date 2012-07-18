function expand(ideee) {

	if($("#ins" + ideee).css('display') == 'none') {
		$("#ins" + ideee).slideDown('fast');
		$("#caller" + ideee).html("collapse"); 
	}
	else {
		$("#ins" + ideee).slideUp('fast');
		$("#caller" + ideee).html("expand"); 
	}
}

function render(data) {

	var html = ''; var first = ''; var even = '';
	
	if(data['links'] == undefined) {
	
		html += "<div class=\"title\"><strong>We're sorry, but no buses were found between "
		+ data['from'] + " and " + data['to'] + ". You might have to use another way to get there.</strong></div>";
	}
	else {

		html += "<div class=\"title\"><strong>Buses from " + data['from'] + " to " + data['to'] +
			"</strong><br>\n<small>(<a href=\"#\" onclick=\"flip()\">flip locations</a> &middot; " +
			"<a href=\"./?client&id=" + String(data['permalink']) + "\">permalink</a>)</small></div>";
	
		for(var i = 0; i < data['links'].length; i++) {
	
			first = (i == 0) ? ' first' : '';
		
			html += "<div class=\"sug\"><strong>" + data['links'][i]['nobuses'];
			html += (data['links'][i]['nobuses'] == 1) ? "</strong> bus, <strong>" : "</strong> buses, <strong>";
			html += (data['links'][i]['totaldist'] / 1000) + "</strong> km " +
			"(<a name=\"l" + i + "\" id=\"caller" + i + "\" href=\"#l" + i + "\" onclick=\"expand('" + i + "');\">";
		
			if(first) {
				html += "collapse</a>)";
			}
			else {
				html += "expand</a>)";
			}
		
			html += "<div class=\"instructions" + first + "\" id=\"ins" + i + "\">" +
			"<div class=\"firststep\"><strong>Instructions:</strong></div>";
		
			for(var j = 0; j < data['links'][i]['inst'].length; j++) {		
		
				html += "<div class=\"step\">Take the <strong>" + String(data['links'][i]['inst'][j]['route'])
				+ "</strong> (" + data['links'][i]['inst'][j]['busfrom'] + ' &ndash; ' + String(data['links'][i]['inst'][j]['busto']) + ") bus";
			
				if(j == 0) { html += " at <strong>" + String(data['links'][i]['inst'][j]['geton']) + "</strong>"; }
			
				html += "</div>\n<div class=\"step\">Get down at <strong>"
				+ String(data['links'][i]['inst'][j]['getoff']) + "</strong> <div class=\"dist\">" 
				+ String(data['links'][i]['inst'][j]['distance']) + " km</div></div>\n\n";
			}
		
			html += "</div></div>";
		}
	}
	
	$('#list').append(html);	
	
}

function populate(from, to) {
	$.get('http://yamu.lk/bus/', { from: from, to: to }, function(data) { render(data); });
}

function populatebyid(id) {
	$.get('http://yamu.lk/bus/', { id: id }, function(data) { render(data); });
}

function findbus() {
	$('#loading').show();
	$('#list').empty();
	populate($('#f').val(),$('#t').val());
}

function flip() {
	$('#list').empty();
	populate($('#t').val(),$('#f').val());
}

$(function () {
        $('#f, #t').tagSuggest({
            tags: ["Pettah","Fort Railway Station","Lotus Road","Galle Face Green","Kollupitiya Junc","McDonalds Kollupitiya","Bambalapitiya Junc","Holy Family Convent Bambalapitiya","Savoy Cinema Wellawatte","Wellawatte Junc","Lake House","Gamini Hall Junc","Darley Road/Excel World","Gangarama","Bishop's College","Mahanama College","British Council","Regal Cinema","Slave Island","Town Hall","Public Library","St Bridget's Convent","Race Course Grounds","Campus (Arts Faculty)","Campus (near Wycherley)","Thurstan College","Glass House","Thunmulla","Police Park","Thimbirigasyaya","BRC Grounds","Redimola Junc","Maya Ave","Kirulapone Junc","Kirulapone South","Balapokuna Road","Anula Vidyalaya","Nugegoda","Seventh Mile Post","Gansabha Junc","Delkanda","Wijerama","Navinna","Arpico M'gama","Wattegedara Junc","Maharagama","Sugathadasa Stadium","Armour Street","Ananda College","Maradana","Castle Street Hospital","Devi Balika Vidyalaya","Alwis Place Kollupitiya","Arts Fac Horton Place","Green Path","Stratford Avenue","Kirulapone Ave","Suwisuddharamaya","Sapphire Halt","Pamankada","Vijaya Kumaratunge Mw","Apollo Hospital","Narahenpita Junc","Borella","Horton Place - Baseline Junc","Borella Cemetery Junc","Sarana Road","Maitland Place","Delmon Hospital","Ramakrishna Road","William Grinding Mills","Dehiwala Municipal Council","St Mary's Church Dehiwala","Holy Family Convent Dehiwala","Dehiwala Junc","Dehiwala Cemetery","Hotel Road Mt Lavinia","S Thomas' College Mt Lavinia","Mount Lavinia Junc","Maliban Junc","Belekkade Junc Rathmalana","Rathmalana Airport","Soysapura","Katubedda Junc","Rawatawatta","Moratuwa","Panadura","Angulana","Dutugemunu Street","Kohuwala","Woodlands","Pepiliyana","Raththanapitiya","Boralesgamuwa","Werahera","Bokundara","Piliyandala","Kesbewa","Polgasovita","Kahathuduwa","Horana","Panagoda","Godagama","Migoda","Padukka","Handapangoda","Ingiriya","Teachers' Training College","Pannipitiya","Kottawa","Makumbura","Homagama","Kottawa Railway Station","Rukmalgama","Walgama Junc Athurugiriya","Athurugiriya Junc","Pinhena Junc","Mattegoda","Open University Nawala","Nawala Junc","Koswatta Nawala","Bellanthota Junc","Nandimala","Karagampitiya","Kalubowila Hospital","Nugegoda Supermarket","Pita Kotte","Impala Cinema Rajagiriya","Rajagiriya Junc","Ethul Kotte Junc","Sethsiripaya","Battaramulla","Thalangama","Thalahena","Malabe","Pittugala","SLIIT Malabe","Kaduwela","Thalawathugoda","Pelawatta/Isurupaya","Peliyagoda Junc","Nawaloka Junc","Wattala","Handala","Welisara","Kandana","Kapuwatta","Ja-Ela","Katunayake Airport"] }); });



