// create the def for this transcription but only if the transcription_detail_def doen't exist
		// get the record
		$transcription_detail_def = $transcription_detail_def_model ->where('project_index', $session->current_project[0]['project_index'])
																	->where('identity_index', $session->BMD_identity_index)
																	->where('transcription_index', $id)
																	->find();
		// not found? so create it
		if ( ! $transcription_detail_def )
			{		
				// get the standard def
				$session->set('standard_def', $def_fields_model	
						->where('project_index', $session->current_project[0]['project_index'])
						->where('data_entry_format', $session->current_allocation[0]['data_entry_format'])
						->orderby('field_order','ASC')
						->find());
				
				// loop through standard def element by element and write the transcription_detail_def - this is to allow the user to change the display parameters
				foreach ($session->standard_def as $def) 
					{ 
						// write to transcription_detail_def
						$data =	[
								'project_index' => $def['project_index'],
								'transcription_index' => $id,
								'identity_index' => $session->BMD_identity_index,
								'data_entry_format' => $def['data_entry_format'],
								'field_index' => $def['field_index'],
								'field_order' => $def['field_order'],
								'field_name' => $def['field_name'],
								'column_name' => $def['column_name'],
								'column_width' => $def['column_width'],
								'font_family' => $def['font_family'],
								'font_size' => $def['font_size'],
								'font_weight' => $def['font_weight'],
								'field_align' => $def['field_align'],
								'pad_left' => $def['pad_left'],
								'validation_set' => $def['validation_set'],
								];
						$transcription_detail_def_model->insert($data);
					}
			}
