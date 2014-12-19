// Copyright 2012 sasya8080@gmail.com
//

if (typeof safutil == "undefined") {
    var safutil = {};
}

defaultIpAPI = "http://sasha8080.com/ip.php";
defaultIpAPI ="http://ip.jsontest.com";

var safutil =(function(){
	return {
fetchUrl:function (url, callback, asJSON, asXML) {
	var results = null;
	var request = new XMLHttpRequest();
	request.open("GET", url, false); // sync mode
	request.onreadystatechange = function() {
		if (this.readyState == XMLHttpRequest.DONE) {

			if (asJSON){
				var ipstr = this.responseText;

				try{
					
					results = JSON.parse(ipstr);
				}catch(err){
					results = JSON.parse('{"ip":"' + ipstr.trim() + '"}');
				}
			}
			else if (asXML)
				results = this.responseXML;
			else
				results = this.responseText;

			if (callback)
				callback(results);
		}
		
	};
	request.send();
	return results;
},

  updatePaths : function(paths) {
	if (paths === undefined)
	  return;

	paths = paths.split("\n");
	path_white = new Array();
	path_black = new Array();

	for (var i = 0 ; i < paths.length; ++i) {
		var p = paths[i];
		if (p.match(/^\s*$/)) {
		} else {
		   var add = path_black;	    
		   if (p[0] == '+') {
			  p = p.substr(1);
			  add = path_white;
		   }
		   p = p.replace('.', '\\.');
		   p = p.replace('*', '.*');
		   add.push(new RegExp(p, 'ig'));
		}
	}
 },
	

 isBlocked : function(url, opts) {
	
	ips = opts.ips
	api = opts.api || defaultIpAPI;
//	alert(api);
	var blocked = false;

	if (url !== undefined && url.match(/https?:/)) {
	   var p;
	   for (p in path_black) {
			 if (url.search(path_black[p]) != -1) {
				 blocked = true;
				 break;
			 }
	   }
	   for (p in path_white) {
		   continue; /// uncomment this for special purpose, example usage of + sign
			 if (url.search(path_white[p]) != -1) {
				 blocked = false;
				 break;
			 }
	   }
	}

	if ( blocked == true){
		if (ips === undefined)
			ips = "";
		ips = ips.split("\n");
		var myipPath = api;
		var myIp = '';
			
		this.fetchUrl(myipPath, 
			function(ip){
				myIp = ip['ip'];
			},
			true);

		for ( i = 0; i < ips.length;i++ ){
			var ip = ips[i];
			if ( ip == '' || ip ==  null)
				continue;
			if ( myIp == ip)
				return false;
		}
	}
	return blocked;
 },

    read_options : function(stg) {
       var opts = {};

       if (stg === undefined) {
         stg = localStorage;
       }

       if ("settings" in stg) {
         opts = JSON.parse(stg['settings']);
       }

       if ("siteblockip_list" in stg) {
         opts['rules'] = stg['siteblockip_list'];
       }

       if (! ("rules" in opts)) 
          opts.rules = "";

       if (! ("allowed" in opts))
          opts.allowed = 0;

       if (! ("period" in opts))
          opts.period = 1440;

       return opts;
    },

    write_options : function(opts, stg) {
       if (stg === undefined) {
          stg = localStorage;
       } 

       stg['settings'] = JSON.stringify(opts);
       if ("siteblockip_list" in stg)
           delete stg["siteblockip_list"];
    }
  }; 
})();



