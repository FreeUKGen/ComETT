// found ?
		if ( $last_indexes )
			{
				// detail def for last transcription, the data entry format found so use it to create detail def this transcription
				$last_transcription = $transcription_model 
					->where('project_index', $session->current_project[0]['project_index'])
					->where('BMD_identity_index', $session->BMD_identity_index)
					->where('BMD_header_index', $last_indexes[0]['transcription_index'])
					->find();
					
				// found?
				if ( $last_transcription )
					{
						// found, so use initial image set
						$image_x = $last_transcription[0]['BMD_image_x'];
						$image_y = $last_transcription[0]['BMD_image_y'];
						$image_rotate = $last_transcription[0]['BMD_image_rotate'];
						$image_scroll_step = $last_transcription[0]['BMD_image_scroll_step'];
						$panzoom_z = $last_transcription[0]['BMD_panzoom_z'];
						$sharpen = $last_transcription[0]['BMD_sharpen'];
						$zoom_lock = $last_transcription[0]['zoom_lock'];
					}
			}
