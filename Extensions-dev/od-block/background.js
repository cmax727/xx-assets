// Copyright 2010-2012 Constantine Sapuntzakis


var e_opts = {};
function onOptionsChanged(opts) {
    e_opts  = opts;
	safutil.updatePaths(e_opts.rules);
}

onOptionsChanged(safutil.read_options());


//odesk block
chrome.webRequest.onBeforeRequest.addListener(
  function(details) {
	if ( safutil.isBlocked(details.url, e_opts))
		return {cancel: true};
	else
		return {cancel: false};
  },
  {urls: ["<all_urls>"]},
  ["blocking"]
);