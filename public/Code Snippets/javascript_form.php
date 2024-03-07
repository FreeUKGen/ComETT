<script>


	
	
function change_identity()
	{	
				// create the form
				form = document.createElement("form");
				document.body.appendChild(form);
				form.method		=	"POST";
				form.action		=	"https://www.freebmd.org.uk/cgi/bmd-user-admin.pl";
				form.target		=	"_blank";
				form.name		=	"loginform";
				form.enctype	=	"application/x-www-form-urlencoded";   
				
				// create userid element
				var element1	=	document.createElement("INPUT");         
				element1.name	=	"__bmd1"
				element1.value	= 	<?php echo json_encode($session->identity_userid); ?>;
				element1.type	=	'hidden'
				form.appendChild(element1);
			
				// create password element
				var element2 = document.createElement("INPUT");
				element2.name	=	"__bmd2"
				element2.value	= 	<?php echo json_encode($session->identity_password); ?>;
				element2.type	=	'hidden'
				form.appendChild(element2);       
	
				// create action element
				var element3 = document.createElement("INPUT");
				element3.name	=	"__bmd0"
				element3.value	= 	<?php echo json_encode('Log In'); ?>;
				element3.type	=	'hidden'
				form.appendChild(element3);
				
				// create type element
				var element4 = document.createElement("INPUT");
				element4.name	=	"__bmd8"
				element4.value	= 	<?php echo json_encode('FreeBMDauth'); ?>;
				element4.type	=	'hidden'
				form.appendChild(element4);
	
				// submit
				form.submit();
	};

</script>
