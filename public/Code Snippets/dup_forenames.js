switch (dup.target.id)
								{
									case 'forenames':
										var dupValue = "<?php echo $session->dup_forenames; ?>";
										alert(dupValue);
										$('#forenames').val("<?php echo $session->dup_forenames ?>");
										break;
									default:
										break;
								}
								
// iterate through field array
			for (var fieldsIndex in fields) 
				{
					// load this iteration 
					var currentLine = fields[fieldsIndex];
					
					// have I found the iteration with the current input ID?
					// if so assign ID to nextID and break
					if ( currentIDFound == 1 )
						{
							var nextID = currentLine["html_id"];
							break;
						}
					
					// have I found the iteration with the current ID?
					if ( currentLine["html_id"] == element )
						{
							currentIDFound = 1;
						}
				}
			return nextID;
