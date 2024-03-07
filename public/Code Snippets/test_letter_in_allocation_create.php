// test letter
				// do checks if no # at end
				if ( substr($session->letter, -1) != '#' ) 
					{
						// convert to upper
						$session->letter = strtoupper($session->letter);
						// length of input
						$letter_length = mb_strlen($session->letter);
						// do tests
						switch ($letter_length) 
							{
								case 0:
									$session->set('message_2', 'Letter cannot be blank.');
									$session->set('message_class_2', 'alert alert-danger');
									$session->set('field_name', 'letter');
									return redirect()->to( base_url('allocation/create_allocation_step1/1') );
									break;
								case 1:
									// test letter is in alphabet
									if ( ! in_array($session->letter, $session->alphabet) )
										{
											$session->set('message_2', 'The letter you entered is not in the alphabet. Enter # at end of input to bypass validation, this time only.');
											$session->set('message_class_2', 'alert alert-danger');
											$session->set('field_name', 'letter');
											return redirect()->to( base_url('allocation/create_allocation_step1/1') );
										}
									break;
								case 2:
									// test letters are in alphabet
									if ( (! in_array(substr($session->letter, 0, 1), $session->alphabet)) OR (! in_array(substr($session->letter, 1, 1),  $session->alphabet)) )
										{
											$session->set('message_2', 'The letters you entered are not in the alphabet. Enter # at end of input to bypass validation, this time only.');
											$session->set('message_class_2', 'alert alert-danger');
											$session->set('field_name', 'start_page');
											return redirect()->to( base_url('allocation/create_allocation_step1/1') );
										}
									// are they in alpha order
									if ( substr($session->letter, 0, 1) > substr($session->letter, 1, 1) )
										{
											$session->set('message_2', 'The letters you entered are not in alpha order. Enter # at end of input to bypass validation, this time only.');
											$session->set('message_class_2', 'alert alert-danger');
											$session->set('field_name', 'start_page');
											return redirect()->to( base_url('allocation/create_allocation_step1/1') );
										}
									break;
								case 3:
									// is it a range
									if ( substr($session->letter, 1, 1) == '-' )
										{
											// first and third must be in alphabet
											if ( ! in_array(substr($session->letter, 0, 1), $session->alphabet) OR ! in_array(substr($session->letter, 2, 1),  $session->alphabet) )
												{
													$session->set('message_2', 'If this is a letter range, at least one of the letters you entered is not in the alphabet. Enter # at end of input to bypass validation, this time only.');
													$session->set('message_class_2', 'alert alert-danger');
													$session->set('field_name', 'start_page');
													return redirect()->to( base_url('allocation/create_allocation_step1/1') );
												}
											// out of order?
											if ( substr($session->letter, 0, 1) > substr($session->letter, 2, 1) )
												{
													$session->set('message_2', 'If this is a letter range, the letters you entered are not in alpha order. Enter # at end of input to bypass validation, this time only.');
													$session->set('message_class_2', 'alert alert-danger');
													$session->set('field_name', 'start_page');
													return redirect()->to( base_url('allocation/create_allocation_step1/1') );
												}
										}
									break;
								default:	
									break;
							}
					}
