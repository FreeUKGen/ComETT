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
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/latest/css/bootstrap.min.css">

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS, then Simple Keyboard -->
		<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/latest/js/bootstrap.min.js"></script>
		
		<!-- this for the autocomplete function -->
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		<style>
			.ui-autocomplete 
				{
					position: absolute;
					top: 0;
					left: 0;
					z-index: 2000;
					float: left;
					display: none;
					min-width: 20vw;   
					padding: 0px 0;
					margin: 0 0 0px 0px;
					list-style: none;
					background-color: #F0FFF0;
					border-color: #ccc;
					border-color: rgba(0, 0, 0, 0.2);
					border-style: solid;
					border-width: 10px;
					-webkit-border-radius: 5px;
					-moz-border-radius: 5px;
					border-radius: 5px;
					-webkit-box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
					-moz-box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
					box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
					-webkit-background-clip: padding-box;
					-moz-background-clip: padding;
					background-clip: padding-box;
					*border-right-width: 2px;
					*border-bottom-width: 2px;
				}
		
			.ui-menu-item 
				{
					display: block;
					padding: 0px 0px;
					clear: both;
					font-weight: bold;
					line-height: 0.5hw;
					color: #555555;
					white-space: nowrap;
					text-decoration: none;
				}
				
			.ui-state-hover, .ui-state-active 
				{
					color: #ffffff;
					text-decoration: none;
					background-color: #0088cc;
					border-radius: 0px;
					-webkit-border-radius: 0px;
					-moz-border-radius: 0px;
					background-image: none;
				}
		
		</style>
		
		<!-- this for the virtual keyboard function -->
		<link rel="stylesheet" href="<?php echo base_url().'/Keyboard-master/css/keyboard.css'; ?>">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
		<script src="<?php echo base_url().'/Keyboard-master/js/jquery.keyboard.js'; ?>"></script>
		<script src="<?php echo base_url().'/Keyboard-master/js/jquery.keyboard.extension-autocomplete.js'; ?>"></script>
		<style>
			.keyboardicon {
				position: relative;
				z-index: 1;
				left: -25px;
				top: 1px;
				color: #5f9ea0;
				cursor: pointer;
				width: 0;
			}
		</style>
		
		<!-- this for the panzoom function https://github.com/timmywil/panzoom/ -->
		<script src="https://cdn.jsdelivr.net/npm/@panzoom/panzoom/dist/panzoom.min.js"></script>
		
		<style>
			.panzoom-wrapper 
			{
				height: <?php echo $session->image_y * $session->actual_y / 1080; ?>px;
				border: 3px solid blue;
				overflow: hidden;
				user-select: none;
				touch-action: none;
			}

			.panzoom > img 
			{
				width: 100%;
				filter: url(#unsharpy);
				transform: rotate(<?php echo($session->rotation); ?>deg);
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
		
		<!-- this for the hotkeys function -->
		<!-- <script src="https://unpkg.com/hotkeys-js/dist/hotkeys.min.js"></script> -->
		
		<!-- this for the spinner function -->
		<style>
			.spinner-border, #districts_staleness_spinner, #districts_refresh_spinner {display:none;}
			.spinner-border.active, #districts_staleness_spinner.active, #districts_refresh_spinner.active {display:block;}
			.ui-autocomplete { max-height: 130px; max-width: 190px; overflow-y: auto; overflow-x: hidden; }
		</style>

		<!-- this for the drop down menu and search boxes -->
		<style>
			.box 
			{
				width: 11vw;
				height: 4vh;
				border: 1px solid #999;
				color: green;
				background-color: #eee;
				border-radius: 5px;
			}
			.box_sm 
			{
				width: 8vw;
				height: 4vh;
				border: 1px solid #999;
				color: green;
				background-color: #eee;
				border-radius: 5px;
			}
			.go_button 
			{
				width: 3vw;
				height: 4vh;
				border: 1px solid #999;
				font-weight: bold;
				color: white;
				background-color: green;
				border-radius: 5px;
			}
		</style>
		
		<style>
			body 
				{
					a, button
					{
						font-size: 1vw !important;
					}
					font-size: 0.8vw;
				}
			header
				{
					font-size: 0.8vw;
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
				<img src="<?php echo base_url().'/'.$session->current_project[0]['project_pathtoicon'].'/'.$session->current_project[0]['project_iconname']; ?>" alt="freeukreg" style="width:10vw;height:auto"></img>
				
				<?php if ( $session->signon_success == 1 ): ?>
					<span class="small font-weight-bold"><?= esc('Environment = '.$session->environment); ?></span>
					<span class="small font-weight-bold"><?= esc($session->realname.' in '.$session->syndicate_name); ?></span>
					<span class="small font-weight-bold"><?= esc($session->total_records.' records transcribed and uploaded to this project'); ?></span>
				<?php endif ?>
				<span class="small font-weight-bold"><?= esc(date("jS F Y")); ?></span>
				<span class="small font-weight-bold">
					<img src="<?php echo base_url().'/Icons/FreeComETT.png' ?>" alt="FreeComETT" style="width:10vw;height:auto">
					<?= esc('Version '.$session->version); ?>
				</span>
			</div>
		</div>
	</head>
	
	<body>

		<div class="container-fluid px-5">
			<div class="<?= esc($session->message_class_1); ?> row pl-0 " role="alert">
				<span class="col-12"><?= esc($session->message_1) ?></span>
			</div>
			
			<?php
				// reserve space on screen for the error/information messages
				// this to stop the sreen jumping around when such messages are shown especially now that the data entry rows are draggable.
				if ( $session->message_2 == '' )
					{ 
						$session->message_2 = '.';
						$session->message_class_2 = 'alert alert-light';
					}
			?>
			
			<div class="<?= esc($session->message_class_2); ?> row pl-0 " role="alert">
				<span class="col-12"><?= esc($session->message_2); ?></span>
			</div>
