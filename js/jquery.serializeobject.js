(function($){
$.fn.serializeObject = function serializeObject() {
        var a = this.serializeArray(),b={};
        for (var i in a) 
        	b[a[i].name] = a[i].value;
        
		b = (function arrangeJson(data){
			var initMatch = /^([a-z0-9]+?)\[/i;
			var first = /^\[[a-z0-9=]+?\]/i;
			var isNumber = /^[0-9]$/;
			var bracers = /[\[\]]/g;
			var splitter = /\]\[|\[|\]/g;
			
			
			for(var key in data) {
				if (initMatch.test(key)) {
					data[key.replace(initMatch,'[$1][')] = data[key];
				} else {
					data[key.replace(/^(.+)$/,'[$1]')] = data[key];
				}
				delete data[key];
			}


			for (var key in data) {
				processExpression(data, key, data[key]);
				delete data[key];
			}

			function processExpression(dataNode, key, value){
				var e = key.split(splitter);
				if(e){
					var e2 =[];
					for (var i = 0; i < e.length; i++) {
							if(e[i]!==''){e2.push(e[i]);} 
					}
					e = e2;
					if(e.length > 1){
						var x = e[0];
						var target = dataNode[x];
						if (!target) {
							if (isNumber.test(e[1])) {
								dataNode[x] = [];
							} else {
								dataNode[x] ={}
							}
						}
						processExpression(dataNode[x], key.replace(first,''), value);
					} else if (e.length == 1) {
						dataNode[e[0]] = value;
					} else {
						console.log('This should not happen...');
					}
				}
			}
			return data;
		})(b);
        return b;
    };
})(jQuery);