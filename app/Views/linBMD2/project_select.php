<!doctype html>

<html>
	<head>	
		<!-- initialse session -->
		<?php
			$session = session();
		?>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
		
		<!-- this for the autocomplete function -->
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		
		<!-- this for the panzoom function -->
		<script src="https://cdn.jsdelivr.net/npm/@panzoom/panzoom/dist/panzoom.min.js"></script>
		
		<!-- this for the hotkeys function -->
		<script src="https://unpkg.com/hotkeys-js/dist/hotkeys.min.js"></script>
		
		<!-- this for the spinner function -->
		<style>
			.spinner-border, #districts_staleness_spinner, #districts_refresh_spinner {display:none;}
			.spinner-border.active, #districts_staleness_spinner.active, #districts_refresh_spinner.active {display:block;}
			.ui-autocomplete { max-height: 130px; max-width: 190px; overflow-y: auto; overflow-x: hidden; }
		</style>

		<!-- this for the panzoom and sharpen -->
		<style>
			.panzoom-wrapper 
			{
				height: <?php echo($session->image_y); ?>px;
				border: 1px solid blue;
				overflow: hidden;
				user-select: none;
				touch-action: none;
			}

			.panzoom > img 
			{
				width: 100%;
				filter: url(#unsharpy);
				transform: rotate(<?php echo($session->image_r); ?>deg);
			}
			
			#filters {
				display: block;
				position: absolute;
				top: -9999px;
				left: -9999px;
				width: 0;
				height: 0;
			}
		</style>			
		
		

	</head>
	
	<body>
		
	<div class="container-fluid px-5">
	<!-- show logo -->
		<br>
		<div class="text-left">
				<img src="<?php echo base_url().'/Icons/freeukgen-icon.png' ?>" alt="freeukreg">
				<img src="<?php echo base_url().'/Icons/FreeComETT.png' ?>" alt="FreeComETT" style="width:500px;height:150px">
				
		</div>
		
		<br><br><br>
		
		<!-- show welcome text -->
		<div class="text-left">
			<h1>Welcome to FreeComETT, FreeUKGenealogy's transcription application.</h1>
		</div>
			
		<!-- show instruction -->
		<div class="text-left">
			<h3>To get started, please select the project you wish to work with.</h3>
		</div>
	
		<div class="row text-left table-responsive w-auto">
		<table class="table table-hover table-borderless" style="border-collapse: separate; border-spacing: 0;">
			<thead class="sticky-top bg-white">
				<tr>
					<th></th>
				</tr>
			</thead>

			<tbody>
				<?php 	foreach ($session->projects as $project): ?>
					<?php 	if ( $project['project_status'] == 'Open' )
									{ ?>
										<tr>
											<td>
												<a id="select_line" href="<?=(base_url().'/projects/load_project/'.esc($project['project_index'])) ?>">
												<span><img src="<?php echo base_url().'/'.$project['project_pathtoicon'].'/'.$project['project_iconname'] ?>" alt="freeukreg" style="width:300px;height:100px"</span>
											</td>
										</tr>
									<?php
								}
						endforeach; ?>
			</tbody>
		</table>
		</div>
	</div>
	
	

<br>
    <div class="row d-flex justify-content-between alert alert-info" role="alert">
		<a class="" href="/home/close/">Signout</a>
		<a class="" target="_blank" href="/Manual/webbmd_help.pdf">Help</a>
		<em class="col-2 small">&copy; FreeUKGen 2020, 2021, 2022, 2023</em>
	</div>
</div>



  </body>
</html>


