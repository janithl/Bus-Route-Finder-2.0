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
	
	if(data['links'].length == 0) {
	
		html += "<div class=\"title\"><strong>We're sorry, but no buses were found between "
		+ data['from'] + " and " + data['to'] + ". You might have to use another way to get there</strong></div>";
	}
	else {

		html += "<div class=\"title\"><strong>Buses from " + data['from'] + " to " + data['to'] +
			"</strong><br>\n<small>(<a href=\"#\" onclick=\"flip()\">flip locations</a> &middot; " +
			"<a href=\"./?client&id=" + String(data['permalink']) + "\">permalink</a>)</small></div>";
	
		for(var i = 0; i < data['links'].length; i++) {
	
			first = (i == 0) ? ' first' : '';
			even = (i % 2 == 0) ? ' even' : '';
		
			html += "<div class=\"suggestion" + even + "\"><strong>" + data['links'][i]['nobuses'] + 
			"</strong> buses, <strong>" + (data['links'][i]['totaldist'] / 1000) + "</strong> km " +
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
	$('#list').empty();
	populate($('#f').val(),$('#t').val());
}

function flip() {
	$('#list').empty();
	populate($('#t').val(),$('#f').val());
}

