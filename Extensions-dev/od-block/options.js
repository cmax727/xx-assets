function save_options() {
  var opts = safutil.read_options();
    
 opts.rules=     document.getElementById("rules").value 
 opts.ips=   document.getElementById("ips").value 
 opts.api = document.getElementById("api").value; 

  safutil.write_options(opts);

  chrome.extension.getBackgroundPage().onOptionsChanged(opts);

  // Update status to let user know options were saved.
  var status = document.getElementById("status");
  status.innerHTML = "Options Saved.";
  setTimeout(function() {
    status.innerHTML = "";
  }, 750);

}

function restore_options() {
  var opts =safutil.read_options();

  document.getElementById("rules").value = opts.rules;
  document.getElementById("ips").value = opts.ips;
  document.getElementById("api").value = opts.api;

}

function on_load() {
    restore_options();
    document.querySelector('#submit').addEventListener('click', save_options);
}

document.addEventListener('DOMContentLoaded', on_load);
