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
		
		<title><?= esc($session->title); ?></title>
		
		<div class="container-fluid px-5">
			<?php
				switch ($session->environment)
					{
						case 'LIVE':
						?>
							<div class="row d-flex justify-content-between alert alert-info align-items-center" role="alert">
						<?php
							break;
						case 'TEST':
						?>
							<div class="row d-flex justify-content-between alert alert-danger align-items-center" role="alert">
						<?php
							break;
						default:
						?>
							<div class="row d-flex justify-content-between alert alert-warning align-items-center" role="alert">
						<?php
					}

			?>
				<span class="small font-weight-bold">
					<img src="<?php echo base_url().'/Icons/FreeComETT.png' ?>" alt="FreeComETT" style="width:150px;height:50px">
					<img src="<?php echo base_url().'/'.$session->current_project[0]['project_pathtoicon'].'/'.$session->current_project[0]['project_iconname']; ?>" alt="freeukreg" style="width:90px;height:30px">
				</span>
				<span class="small font-weight-bold"><?= esc($session->environment); ?></span>
				<span class="small font-weight-bold"><?= esc($session->realname); ?></span>
				<?php if ( $session->user ): ?>
					<span class="small font-weight-bold"><?= 'So far you have transcribed and uploaded '.esc($session->user[0]['BMD_total_records']). ' to this project'; ?></span>
				<?php endif ?>
				<span class="small font-weight-bold"><?= esc(date("jS F Y")); ?></span>
			</div>
		</div>
	</head>
	
	<body>

		<div class="container-fluid px-5">
			<div class="<?= esc($session->message_class_1); ?> row pl-0 " role="alert">
				<span class="col-12"><?= esc($session->message_1) ?></span>
			</div>
			<div class="<?= esc($session->message_class_2); ?> row pl-0" role="alert">
				<br>
				<span class="col-12"><?= esc($session->message_2); ?></span>
			</div>
