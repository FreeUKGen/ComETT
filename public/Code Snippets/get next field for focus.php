// get next focus

// get next ID for focus
											// increment index
											fieldsIndex++;
											// check past end of array
											if ( fieldsIndex > defFields.length - 1 )
											{
												fieldsIndex = defFields.length - 1;
											}		
											
											// focus
											$('#' + defFields[fieldsIndex]["html_id"]).focus();
