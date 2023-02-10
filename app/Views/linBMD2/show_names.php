	<?php $session = session(); ?>
	
	<div class="row text-center table-responsive w-auto" style="max-height: 450px;">
		<table class="table table-hover table-striped table-borderless" style="border-collapse: separate; border-spacing: 0;">
			<thead class="sticky-top bg-white">
				<tr>
					<th>Name</th>
					<th>Popularity</th>
				</tr>
			</thead>

			<tbody>
				<?php if( $session->names ): ?>
					<?php foreach ($session->names as $name): ?>
						<tr>
							<td><?= esc($name['name'])?></td>
							<td><?= esc($name['popularity'])?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
		
		<div>	
			<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?= esc(base_url('housekeeping/index/0')); ?>">
				<span>Return</span>
			</a>
		</div
	</div>


		
	



