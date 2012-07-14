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

function populate(from, to) {
	$.get('http://yamu.lk/bus/', { from: from, to: to }, function(data) {

		var html = ''; var first = ''; var even = '';

		html += "<div class=\"title\">" + data['title'] + "</div>";
		
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
			
				html += "<div class=\"step\">" + data['links'][i]['inst'][j] + "</div>";
			}
			
			html += "</div></div>";
		}
		$('#list').append(html);	
		
	})
}

function findbus() {
	$('#list').empty();
	populate($('#f').val(),$('#t').val());
}

$("#t").keyup(function(event) {
    if(event.keyCode == 13) {
        findbus();
    }
});

