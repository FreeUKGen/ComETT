<?php $session = session(); ?>	

<!-- Draggable div - thanks to Rob Crowther - http://jsfiddle.net/robertc/kKuqH/ -->
<style>
:root {
    --leftStyle: 50;
    --topStyle: 0;
}
#dragme_yes { 
    position:  	absolute;
    background:	rgba(255,255,255,0.66);
    left:		var(--leftStyle);
    top:		var(--topStyle); 
    z-index: 	99999;
}
</style>

<script>
// get the current position of the dragme_yes
function drag_start(event) 
	{
		var style = window.getComputedStyle(event.target, null);
		event.dataTransfer.setData("text/plain",
		(parseInt(style.getPropertyValue("left"),10) - event.clientX) + ',' + (parseInt(style.getPropertyValue("top"),10) - event.clientY));
	} 
	
// allow drag over to drop event
function drag_over(event) 
	{ 
		event.preventDefault(); 
		return false; 
	} 
	
// calculate new position and position the dragme_yes to it
function drop(event) 
	{ 
		var offset = event.dataTransfer.getData("text/plain").split(',');
		var dm = document.getElementById('dragme_yes');
		
		// set position of entry fields
		var offsetX = parseInt(offset[0],10);
		var offsetY = parseInt(offset[1],10);
		// set session fields
		sessionStorage.styleLeft = (event.clientX + offsetX) + 'px';
		sessionStorage.styleTop = (event.clientY + offsetY) + 'px';
		event.preventDefault();
		// set css
		dm.style.setProperty('--leftStyle', sessionStorage.styleLeft);
		dm.style.setProperty('--topStyle', sessionStorage.styleTop);
		
		return false;
	} 

// initialise
var dm = document.getElementById('dragme_yes');
// get error flag
var errorFlag = <?php echo json_encode($session->error_field); ?>;
if ( errorFlag != '' )
	{
		// if error is shown, move the fields down so the image doesn't get covered.
		sessionStorage.styleTop = sessionStorage.styleTop + 100;
	}
// set css
dm.style.setProperty('--leftStyle', sessionStorage.styleLeft);
dm.style.setProperty('--topStyle', sessionStorage.styleTop);
// add event listener
dm.addEventListener('dragstart',drag_start,false);
// call functions
document.documentElement.style.setProperty('--my-variable-name', 'pink');
document.body.addEventListener('dragover',drag_over,false); 
document.body.addEventListener('drop',drop,false);
 
</script>


