<?php
/**
 * Apache License, Version 2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * The work represented by this file is partially funded by the ABLE-TO-INCLUDE
 * project through the European Commission's ICT Policy Support Programme as
 * part of the Competitiveness & Innovation Programme (Grant no.: 621055)
 * Copyright © 2016, ABLE-TO-INCLUDE Consortium.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions & limitations
 * under the License.
 */
function quitar_tildes($cadena) {
	$no_permitidas = array (
			"á",
			"é",
			"í",
			"ó",
			"ú",
			"Á",
			"É",
			"Í",
			"Ó",
			"Ú",
			"ñ",
			"À",
			"Ã",
			"Ì",
			"Ò",
			"Ù",
			"Ã™",
			"Ã ",
			"Ã¨",
			"Ã¬",
			"Ã²",
			"Ã¹",
			"ç",
			"Ç",
			"Ã¢",
			"ê",
			"Ã®",
			"Ã´",
			"Ã»",
			"Ã‚",
			"ÃŠ",
			"ÃŽ",
			"Ã”",
			"Ã›",
			"ü",
			"Ã¶",
			"Ã–",
			"Ã¯",
			"Ã¤",
			"«",
			"Ò",
			"Ã",
			"Ã„",
			"Ã‹" 
	);
	$permitidas = array (
			"a",
			"e",
			"i",
			"o",
			"u",
			"A",
			"E",
			"I",
			"O",
			"U",
			"n",
			"N",
			"A",
			"E",
			"I",
			"O",
			"U",
			"a",
			"e",
			"i",
			"o",
			"u",
			"c",
			"C",
			"a",
			"e",
			"i",
			"o",
			"u",
			"A",
			"E",
			"I",
			"O",
			"U",
			"u",
			"o",
			"O",
			"i",
			"a",
			"e",
			"U",
			"I",
			"A",
			"E" 
	);
	$texto = str_replace ( $no_permitidas, $permitidas, $cadena );
	return $texto;
}
function normalizeEmail($address) {
	$address = str_replace ( "<", "&lt;", $address );
	$address = str_replace ( ">", "&gt;", $address );
	return $address;
}
?>
<script type='text/javascript'
	src='<?php echo $helper->urlBase(); ?>/public/js/popup_delete.js'></script>
<script type="text/javascript"
	src="<?php echo $helper->urlBase(); ?>/public/js/audio_popup.js"></script>

<script type='text/javascript'>
		<?php
			$texto_email = "";
			foreach ( $vars ['Mail'] ['Body'] as $value ) {
				if (strcmp ( $value ['mime'], 'text/plain' ) == 0) {
					$aux = preg_replace ( '/<\/?[^>]+(>|$)/', '', $value ['content'] );
					$aux = preg_replace ( "/\r\n|\r|\n/", ' ', $aux );
					$aux = preg_replace ( '/"/', '', $aux );
					$texto_email .= $aux . "\n";
				}
			}
		
			require_once 'Text/LanguageDetect.php';
			$l = new Text_LanguageDetect();
			$result = $l->detect($texto_email, 4);
			$availableLangs = $l->getLanguages();
			$lang_detected = "en";
			if (!empty($result) && is_array($result)) {
			   	reset($result);
				$lang_detected = key($result); // Get first key of array
			} else {
				if(isset($_SESSION['lang']) && !empty($_SESSION['lang'])) {
					$lang_detected = $_SESSION['lang'];
				}
			}
			
			if($lang_detected == 'spanish') $lang_detected = "es";
			if($lang_detected == 'english') $lang_detected = "en";
			
			if($lang_detected != 'es' && $lang_detected != 'en') {
				$lang_detected = "en";
			}
			
			
			echo "var current_language = '" . $lang_detected . "';";
			$lang_detected_ext = "-";
			if($lang_detected == "es") {
				echo "var current_language_ext = 'spanish';";
				$lang_detected_ext = "spanish";
			}
			else {
				echo "var current_language_ext = 'english';";
				$lang_detected_ext = "english";
			}
			
		?>

        function getText(){
            var texto = '<?php echo $lang['EMPTY_MESSAGE']; ?>';
                <?php
																foreach ( $vars ['Mail'] ['Body'] as $value ) {
																	if (strcmp ( $value ['mime'], 'text/plain' ) == 0) {
																		$aux = preg_replace ( '/<\/?[^>]+(>|$)/', '', $value ['content'] );
																		$aux = preg_replace ( "/\r\n|\r|\n/", ' ', $aux );
																		$aux = preg_replace ( '/"/', '', $aux );
																		$texto .= $aux;
																		?>
                        texto = <?php print ('"'.$texto.'"'); ?>;
                        <?php
																	}
																}
																?>
                return texto;
        }

        $(document).ready(function(){
        	// Hide pictogram output
	        $('#pictogramOutput').hide();
	        
  	        //text2picto - Beta
	        $('#picto').click(function() {
	        	// Show pictogram output 
		        $('#pictogramOutput').html("<?php echo $lang['LOADING_SPEECH_INTERFACE']; ?>...");
		        $('#pictogramOutput').show();
		        
	            var texto = "";
	            $('span:visible[id^="utterance"]').each(function(){
	         		texto += $(this).html().trim() + "\n";
	         	});
	            $.ajax({
	                url: '<?php echo TEXT2PICTO ?>',
	                type: 'GET',
	                // headers: {
	                //   'Origin': 'http://kolumba.eu'
	                // },
	                async: true,
	                dataType : 'json',
	                data: 'text='+texto+'&type=<?php echo $_SESSION['picto']; ?>&language=' + current_language_ext,
	                beforeSend: function() {
	                    $("#dvLoading").fadeIn("slow");
	                },
	                complete: function() {
	                    $("#dvLoading").fadeOut("slow");
	                },
	                success: function(json) {
	                    var newBody = '<b><?php echo $lang['HEADER_PICTOGRAM_SET'] . ": " . $_SESSION['picto']; ?></b><br/>';

						if(json !== null && json.pictos !== null && Object.prototype.toString.call(json.pictos) === '[object Array]' ) {
							var newText = "";
							json.pictos.forEach(function(item){
								if(item !== null) {
									if(item.indexOf("http") > -1) {
										newText += '<img src="'+item+'" style="width: 32px; height:32px;">'+' ';
						            } else {
						              	newText +=  '<font size="6">'+item+'</font>'+' ';
						            }
								}
				            });
				            if(newText !== null && newText.length > 0) {
				              	document.getElementById("pictogramOutput").innerHTML = newBody + newText;
					        }
				            else {
				              	document.getElementById("pictogramOutput").innerHTML = newBody + '<?php echo $lang['PICTO_ERROR']; ?>';
					        }
						}
						else {
			              	document.getElementById("pictogramOutput").innerHTML = newBody + '<?php echo $lang['PICTO_ERROR']; ?>';
				        }
	                },
	                error: function(xhr, status) {
	                	var newBody = '<b><?php echo $lang['HEADER_PICTOGRAM_SET'] . ": " . $_SESSION['picto']; ?></b><br/>';
	                	document.getElementById("pictogramOutput").innerHTML = newBody + '<?php echo $lang['PICTO_ERROR']; ?>';
	                	console.log(status);
	                }
	            });
	        });
			
	        //simplext
	        $('#simplext').click(function() {
				// Hide pictogram output 
		        $('#pictogramOutput').html("");
		        $('#pictogramOutput').hide();
		        
				if($('.simplifiedTextUtterance').length > 0) {
					// Delete simplified text
					$('.simplifiedTextUtterance').remove();
					
					// Show original text
					$('.originalTextUtterance').each(function( index ) {
						 $(this).show();
					});

					$('#simplext').html($('#simplext').html().replace('<?php echo $lang['BUTTON_ORIGINAL_TEXT']; ?>', '<?php echo $lang['BUTTON_SIMPLIFY']; ?>'));

				}
				else {
					var texto = getText();
					var simplificationURL = '<?php echo SIMPLEXT_EN; ?>';
					if(current_language_ext !== null && current_language_ext == 'spanish') {
						simplificationURL = '<?php echo SIMPLEXT_ES; ?>'; 
					}
					console.log("Invoking simplification URL: " + simplificationURL);
		            $.ajax({
		                url: simplificationURL,
		                type: 'GET',
		                //  headers: {
		                //   'Origin': 'http://kolumba.eu'
		                // },
		                async: true,
		                dataType : 'json',
		                data: 'text='+texto+'&language=' + current_language_ext,
		                beforeSend: function() {
		                    $("#dvLoading").fadeIn("slow");
		                },
		                 complete: function() {
		                    $("#dvLoading").fadeOut("slow");
		                },
		                success: function(json) {
							// Hide original text
							$('.originalTextUtterance').each(function( index ) {
								 $(this).hide();
							});

		                	// OLD IMPLEMENTATION
							var simplifiedText;
		                                        var simplifiedTextJSONtoParse = json.textSimplified;

							if(simplifiedTextJSONtoParse.search("simplifiedText") > -1) {
								simplifiedTextJSON = JSON.parse(simplifiedTextJSONtoParse);
			                    simplifiedText = simplifiedTextJSON.simplifiedText;
							}
							else {
								simplifiedText = simplifiedTextJSONtoParse;
							}
							

						        console.log(json);	
							if(json !== null && json.textSimplified !== null) {
								//var simplifiedText = json.simplifiedText;
								
								//simplifiedText = simplifiedText.trim();
								if(simplifiedText.substring(simplifiedText.length-1) !== "." && simplifiedText.substring(simplifiedText.length-1) !== "!" && simplifiedText.substring(simplifiedText.length-1) !== "?") {
									simplifiedText += ".";
								}
								
								var simplifiedTextSentences = simplifiedText.match( /[^\.!\?]+[\.!\?]+/g );

								$('#mailBody').append('<div class="simplifiedTextUtterance">Simplified text:</div>');
								for(var sentNum = 0; sentNum < simplifiedTextSentences.length; sentNum++) {
									var sentenceText = simplifiedTextSentences[sentNum];
									if(sentenceText !== null && sentenceText.trim().length > 0) {
										$('#mailBody').append('<div class="simplifiedTextUtterance">' + '<div class="marginButton btn btn-success btnReduced">' + '<span class="glyphicon glyphicon-volume-up audioplay icono" id="Simpl' + sentNum + '"></span>' + '</div>' +
					 					'<span id="utteranceSimpl' + sentNum + '" class="afterBtnReduced">' +
					 					 sentenceText.trim() +
					                     '</span>' + '</div>');
									}
								}
								 
							}
							else {
								$('#mailBody').append('<div>' + '<div class="marginButton btn btn-success btnReduced">' + '<span class="glyphicon glyphicon-volume-up audioplay icono" id="0"></span>' + '</div>' +
					 					'<span id="utterance0" class="afterBtnReduced">' +
					 					 '<?php echo $lang['SIMPLIFY_ERROR']; ?>' +
					                     '</span>' + '</div>');
							}

							$('#simplext').html($('#simplext').html().replace('<?php echo $lang['BUTTON_SIMPLIFY']; ?>', '<?php echo $lang['BUTTON_ORIGINAL_TEXT']; ?>'));
							
							audioPlayEventAssociation();
		                },
		                error: function(xhr, status) {
		                	// Hide original text
							$('.originalTextUtterance').each(function( index ) {
								 $(this).hide();
							});

							$('#mailBody').append('<div>' + '<div class="marginButton btn btn-success btnReduced">' + '<span class="glyphicon glyphicon-volume-up audioplay icono" id="0"></span>' + '</div>' +
				 					'<span id="utterance0" class="afterBtnReduced">' +
				 					 '<?php echo $lang['SIMPLIFY_ERROR']; ?>' +
				                     '</span>' + '</div>');
							console.log(status);
		                }
		            });
				}

	            
	        });
        });
</script>

<?php
	include(dirname(__FILE__). '/../core/speachJavascritpHelpers.php');
?>

<div id="audioDiv" class="audio-player">
	<audio id="audio" src="<?php echo $helper->urlBase(); ?>/public/beep.mp3"></audio>
	<a id="playPause"
		href="javascript:playPause('<?php echo $lang['PLAY'];?>', '<?php echo $lang['PAUSE'];?>');"
		class="btn btn-primary" style="float: left;"><span
		class="glyphicon glyphicon-play"></span> <?php echo $lang['PLAY'];?></a>
	<a href="javascript:closePopUp();" class="btn btn-danger"
		style="float: right;">&times; <?php echo $lang['CLOSE'];?></a>
</div>
<?php
$stringFrom = "";
$stringTo = "";
$stringCc = "";
$stringReplyTo = "";

$stringFrom = quitar_tildes ( str_replace ( '\'', '', str_replace ( '"', '', str_replace ( '>', '', str_replace ( '<', '', $vars ['Mail'] ['From'] ) ) ) ) );
$stringTo = quitar_tildes ( str_replace ( '\'', '', str_replace ( '"', '', str_replace ( '>', '', str_replace ( '<', '', $vars ['Mail'] ['To'] ) ) ) ) );

if (isset ( $vars ['Mail'] ['Cc'] )) {
	$stringCc = quitar_tildes ( str_replace ( '\'', '', str_replace ( '"', '', str_replace ( '>', '', str_replace ( '<', '', $vars ['Mail'] ['Cc'] ) ) ) ) );
}
if (isset ( $vars ['Mail'] ['Reply-To'] )) {
	$stringReplyTo = quitar_tildes ( str_replace ( '\'', '', str_replace ( '"', '', str_replace ( '>', '', str_replace ( '<', '', $vars ['Mail'] ['Reply-To'] ) ) ) ) );
}

if (strlen ( $stringReplyTo ) == 0) {
	preg_match_all ( '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i', $stringFrom, $from );
} else {
	$from = array ();
	$from [0] [0] = $stringReplyTo;
}
preg_match_all ( '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i', $stringTo, $to );
preg_match_all ( '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i', $stringCc, $cc );

?>
<div class='row'>
	<div>
		<div class='form-group kolumba-col-lg-4'>
			<div style='text-align: center;'>
				<form action='<?php echo $helper->url('mails','newMail', 'RE'); ?>'
					method='post'>
					<button type='submit' class='btn btn-success btn-lg'
						data-type='zoomin' title="<?php echo $lang['REPLY']; ?>">
						<img class='icono'
							src='<?php echo $helper->urlBase(); ?>/public/img/icons/reply.png'
							alt="<?php echo $lang['REPLY']; ?>" /> <?php echo $lang['REPLY']; ?>
                        </button>

					<input type="hidden" name="to"
						value="<?php
						if (isset ( $vars ['INBOX'] )) {
							foreach ( $from as $value ) {
								echo $value [0] . ', ';
							}
						} else {
							echo $to [0] [0];
						}
						?>"> <input type="hidden" name="subject"
						value="<?php echo $vars['Mail']['Subject']; ?>"> <input
						type="hidden" name="body"
						value="<?php
						
						foreach ( $vars ['Mail'] ['Body'] as $value ) {
							if (strcmp ( $value ['mime'], 'text/plain' ) == 0) {
								print (str_replace ( '"', '', $value ['content'] )) ;
							}
						}
						?>">
				</form>
			</div>
		</div>

		<div class='form-group kolumba-col-lg-4'>
			<div style='text-align: center;'>
				<form action='<?php echo $helper->url('mails','newMail', 'RE'); ?>'
					method='post'>
					<button type='submit' class='btn btn-success btn-lg'
						style='width: 90%' data-type='zoomin'
						title="<?php echo $lang['REPLY_ALL']; ?>">
						<img class='icono'
							src='<?php echo $helper->urlBase(); ?>/public/img/icons/reply-all.png'
							alt="<?php echo $lang['REPLY_ALL']; ?>" /> <?php echo $lang['REPLY_ALL']; ?>
                        </button>

					<input type="hidden" name="to"
						value="<?php
						if (isset ( $vars ['INBOX'] )) {
							foreach ( $from as $value ) {
								echo $value [0] . ', ';
							}
						}
						if (isset ( $cc )) {
							foreach ( $cc [0] as $value ) {
								echo $value . ', ';
							}
						}
						foreach ( $to [0] as $value ) {
							echo $value . ', ';
						}
						?>"> <input type="hidden" name="subject"
						value="<?php echo $vars['Mail']['Subject']; ?>"> <input
						type="hidden" name="body"
						value="<?php
						
						foreach ( $vars ['Mail'] ['Body'] as $value ) {
							if (strcmp ( $value ['mime'], 'text/plain' ) == 0) {
								print (str_replace ( '"', '', $value ['content'] )) ;
							}
						}
						?>">

				</form>
			</div>
		</div>



		<div class='form-group kolumba-col-lg-4'>
			<div style='text-align: center;'>
				<button id='delete' type='submit' class='btn btn-danger btn-lg'
					style='width: 90%' data-type='zoomin'
					title="<?php echo $lang['DELETE']; ?>">
					<img class='icono'
						src='<?php echo $helper->urlBase(); ?>/public/img/icons/trash.png'
						alt="<?php echo $lang['DELETE']; ?>" /> <?php echo $lang['DELETE']; ?>
                    </button>
			</div>
		</div>
	</div>

	<div class='overlay-container'>
		<div class='window-container zoomin'>
			<span
				style="font-size: 130%; text-transform: uppercase; font-weight: bold;"><?=$lang['DELETE']?></span>
			<br>
			<p><?php echo $lang['ARE_YOU_SURE_YOU_WANT_TO_DELETE_MESSAGE']; ?></p>
			<br>
			<hr>
			<a
				href='<?php echo $helper->url('mails', 'delete', $vars['Mail']['Id'], isset($vars['SENT']) ? 'SENT' : 'INBOX'); ?>'>
				<div class='page-scroll btn btn-danger btn-lg' style='width: 40%'>
					<span class="glyphicon glyphicon-trash"></span> <?php echo $lang['DELETE']; ?>
                    </div>
			</a>
			<div id='cancel' class='page-scroll  btn btn-success btn-lg'
				style='width: 40%'>
				<span class="glyphicon glyphicon-remove"></span> <?php echo $lang['CANCEL']; ?>
                </div>
		</div>
	</div>


	<hr class='barra'>

	<div>

		<p id="fromEmail"><?php echo '<strong>'.$lang['FROM'].':</strong> '.normalizeEmail($vars['Mail']['From']); ?> </p>
		<hr class='barra' />

		<p id="toEmail"><?php echo '<strong>'.$lang['TO'].'</strong>: '.normalizeEmail($vars['Mail']['To']);  ?> </p>
		<hr class='barra' />


		<p id="subjectEmail">
			<strong><?php echo $lang['SUBJECT']; ?>:</strong> <?php echo $vars['Mail']['Subject']; ?><?php echo isset($vars['Mail']['attachments']) ? '<img class="icono" src="'.$helper->urlBase().'/public/img/icons/clip.png" alt="Clip" />': '';?></p>
		<hr class='barra'>
		<p id="dateEmail">
			<strong><?php echo $lang['DATE']; ?>:</strong> <?php echo $vars['Mail']['Date']; ?></p>
		<hr class='barra'>
		<div class='col-lg-12' id='sidebar-left'>
			<div style='text-align: center;' class='kolumba-col-lg-3 item'>
				<button type='submit' id='readAll' class='btn btn-success btn-lg audioplayAll'
					style='width: 90%;' title='<?php echo $lang['BROWSER_SPEECH_WHOLE_EMAIL_READ']; ?>'>
					<span class="glyphicon glyphicon-book"></span> <?php echo $lang['BROWSER_SPEECH_WHOLE_EMAIL_READ']; ?>
                    </button>
			</div>
			
			<div style='text-align: center;' class='kolumba-col-lg-3 item'>
				<button type='submit' id='simplext' class='btn btn-success btn-lg'
					style='width: 90%;' title='<?php echo $lang['BUTTON_SIMPLIFY']; ?>'>
					<span class="glyphicon glyphicon-book"></span> <?php echo $lang['BUTTON_SIMPLIFY']; ?>
                    </button>
			</div>

                <?php
																if (USE_TEXT_TO_PICTO) {
																	echo '
                <div style="text-align: center;" class="kolumba-col-lg-3 item">
                    <button type="submit" id="picto" class="btn btn-success btn-lg" style="width:90%"  title="' . $lang ['PICTO'] . '">
                        <span class="glyphicon glyphicon-picture"></span> ' . $lang ['PICTO'] . ' (' . $_SESSION['picto'] . ')' . '
                    </button>
                </div>';
																}
																?>
            </div>
		
		<div class='col-lg-12'>
			<div id='languageDetected' class='afterBtnReduced'>
				<?php echo $lang['LANGUAGE_DETECTED_INTRO'] . ': ' . $lang_detected_ext; ?>
			</div>
			<div id='pictogramOutput'>
				ccc
			</div>
			<div id='mailBody'>
				
                    <?php
																				foreach ( $vars ['Mail'] ['Body'] as $value ) {
																					if (strcmp ( $value ['mime'], 'text/plain' ) == 0) {
																						$mailBodyArray = explode ( '<br/>', $value ['content'] );
																						$i = 0;
																						foreach ( $mailBodyArray as $line ) {
																							if (strlen ( $line ) > 2) {
																								echo "
                    <div class='originalTextUtterance'>
                      <div class='marginButton btn btn-success btnReduced'>
                        <span class='glyphicon glyphicon-volume-up audioplay icono' id='" . $i . "'></span>
                      </div>
 					  <span id='utterance" . $i . "' class='afterBtnReduced'>
                        " . str_replace ( '#', '', $line ) . "
                      </span>
 		           </div>";
																							}
																							$i ++;
																						}
																					}
																				}
																				?>
            </div>
        	
		</div>
	</div>
	<br /> <br /> <br /> <br /> <br /> <br />
</div>
<div class='row div-files'>
	<div class='col-lg-3'></div>
        <?php
								$iconPerRow = 9;
								foreach ( $vars ['Mail'] ['attachmentsArray'] as $key => $value ) {
									if ($iconPerRow == 0) {
										$iconPerRow = 9;
										?>
                 <div class='col-lg-3'></div>
            <?php
									}
									?>
        <div class='col-lg-1 div-file'>
		<a
			href="data:application/octet-stream;charset=utf-8;base64,<?php echo $value['content']?>"
			download='<?php echo $key?>'> <img class='eye'
			src='<?php echo $helper->urlBase(); ?>/public/img/icons/files/<?php echo $value['ext']?>.png'
			alt="File"><br>
                <?php echo $key?>
            </a>
	</div>
        <?php
									$iconPerRow --;
								}
								?>
    </div>
<br />
</div>

