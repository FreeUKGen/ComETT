<?php $session = session(); ?>    
    
    <br>
    <div class="row d-flex justify-content-between alert alert-info" role="alert">
		<a class="" href="/home/signout/">Signout</a>
		<a class="" href="/housekeeping/index/0">Housekeeping and Interesting Facts</a>
		<a class="" href="https://www.freebmd.org.uk/vol_faq.html" target="_blank"><?php echo $session->current_project[0]['project_name']; ?> FAQ</a> 
		<a class="" href="<?php echo esc($session->curl_url); ?>" target="_blank"><?php echo $session->current_project[0]['project_name']; ?></a> 
		<a class="" target="_blank" href="/Manual/webbmd_help.pdf">Help</a>
		<em class="col-2 small">&copy; FreeUKGen 2020, 2021, 2022, 2023</em>
	</div>
</div>

  </body>
</html>
