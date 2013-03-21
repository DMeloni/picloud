

window.onload = function() {			
	lnk = document.getElementById('contenuDocumentHighLight');
	init();
}		

var observe;
if (window.attachEvent) {
	observe = function (element, event, handler) {
		if(element !=null)
		element.attachEvent('on'+event, handler);
	};
}
else {
	observe = function (element, event, handler) {
		if(element !=null)
		element.addEventListener(event, handler, false);
	};
}
function init () {

	
  
	var text = document.getElementById('contenuDocument');
	if(text==null)
		return false;
	function resize () {
		text.style.height = 'auto';
		text.style.height = text.scrollHeight+'px';
	}
	/* 0-timeout to get the already changed text */
	function delayedResize () {
		window.setTimeout(resize, 0);
	}
	observe(text, 'change',  resize);
	observe(text, 'cut',     delayedResize);
	observe(text, 'paste',   delayedResize);
	observe(text, 'drop',    delayedResize);
	observe(text, 'keydown', delayedResize);

	text.focus();
	//text.select();
	resize();
}

var isCtrl = false;
document.onkeyup=function(e){
	if(e.which == 17)
		isCtrl=false;
}


		
document.onkeydown=function(e){
	if(e.which == 17)
		isCtrl=true;
		if(e.which == 83 && isCtrl == true) {
			document.getElementById('formulaire').action = '?action=Enregistrer';
			document.getElementById('formulaire').submit();
		return false;
		}

	if(e.which == 78 && isCtrl == true) {
		document.getElementById('formulaire').action = '?action=Nouveau';
		document.getElementById('formulaire').submit();
	return false;
	}
}