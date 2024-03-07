<?php $session = session(); ?>    
    
    <br>
    
	<?php
	if ( $session->masquerade == 1 )
		{ ?>
			<div class="row d-flex justify-content-between alert alert-primary bg-danger" role="alert">
		<?php
		}
	else
		{ ?>
			<div class="row d-flex justify-content-between alert alert-primary" role="alert">
		<?php
		} ?>
		<a class="" href="/home/signout/">Signout</a>
		<a class="" href="<?=(base_url('transcribe/transcribe_step1/0')) ?>">Transcribe Home Page</a>
		<a class="" href="<?=(base_url('identity/change_details_step2/0')) ?>">Manage your Identity</a>
		<a class="" href="/housekeeping/index/0">Housekeeping</a>
		<a class="" href="https://www.freebmd.org.uk/vol_faq.html" target="_blank"><?php echo $session->current_project[0]['project_name']; ?> FAQ</a> 
		<a class="" href="<?php echo esc($session->curl_url); ?>" target="_blank"><?php echo $session->current_project[0]['project_name']; ?> File Management</a> 
		<a class="" href="/help/help_show/0">Help</a>
		<em class="col-2 small">&copy; FreeUKGen 2020 - <?php echo date("Y"); ?></em>
	</div>
</div>

  </body>
</html>
