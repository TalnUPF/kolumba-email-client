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

<script type="text/javascript"
	src="<?php echo $helper->urlBase(); ?>/public/js/espeakng.js/espeakng.min.js"></script>	
<script type='text/javascript'>
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

        var speakGenMethod = "";
		var speakVoice = "---";
		
		function remoteABLEreadTexto(texto) {
			speakGenMethod = "ABLE TO INCLUDE SERVICE";
			speakVoice = "";
			
			console.log('WEB SERVICE Speech Synthesis - Generating voice of text: "' + texto + '"...');
        	
        	$.ajax({
                url: '<?php echo TEXT2SPEECH ?>',
                type: 'GET',
                // headers: {
                //   'Origin': 'http://kolumba.eu'
                // },
                async: true,
                dataType : 'json',
                data: 'text='+texto+'&language=<?php echo $lang['TEXT2SPEECH_LANGUAGE']; ?>',

                beforeSend: function() {
                    $("#dvLoading").fadeIn("slow");
                },
                 complete: function() {
                    $("#dvLoading").fadeOut("slow");
                },

                success: function(json) {
					if(json !== null && json.audioSpeech !== null) {
						alert("Check if the service works again by downloading the generated MP4: " + json.audioSpeech);
						window.open(json.audioSpeech, '_blank');
					}
					else {
						alert("No url of audio file with generated speech to download.");
					}

                    // Activate real generated audio URL
                    $('#audio').attr('src', '/public/beep.mp3'); // json.audioSpeech);

                    if (running_iOS()) {
                      document.getElementById('audioDiv').style.display = "block";
                      document.getElementById('playPause').innerHTML = '<span class="glyphicon glyphicon-play"></span> <?php echo $lang['PLAY'];?>';
                    } else {
                      document.getElementById('audio').play();
                      document.getElementById('playPause').innerHTML = '<span class="glyphicon glyphicon-pause"></span> <?php echo $lang['PAUSE'];?>';
                    }
                },
                error: function(xhr, status) {
                    console.log(status);
                }
            });
		}
		
        function browserReadText(texto) {
        	speakGenMethod = "BROWSER HTML5";
			
        	pusher = null;
			
        	$('#playButton').attr('disabled', true);
   			$('#pauseButton').attr('disabled', false);
            
        	console.log('BROWSER SpeechSynthesisUtterance - Generating voice of text: "' + texto + '"...');

        	var msg = new SpeechSynthesisUtterance();
			/*
        	speechSynthesis.getVoices().forEach(function(voice) {
        		  console.log(voice.name, voice.default ? voice.default :'');
        		});
			*/
			
           // Set up voice generation
           msg.voiceURI = 'native';
           msg.volume = 1; // 0 to 1
           msg.rate = 1; // 0.1 to 10
           msg.pitch = 1; //0 to 2
           msg.text = texto;

           var selectedVoice = null;
           var speechVoices = window.speechSynthesis.getVoices();
           
           <?php
           switch ($_SESSION['lang']) {
	           case 'en':
	           ?> 
	           msg.lang = 'en-EN'; 
	           for(var voxIndex = 0; voxIndex < speechVoices.length; voxIndex++) {
					if(speechVoices[voxIndex].name.valueOf() == 'Google UK English Female') {
						speakVoice = 'Google UK English Female (' + speechVoices[voxIndex].name + ')';
	          			msg.voice = speechVoices[voxIndex];
					}
				}
	           <?php
	           break;
	
	           case 'es':
	           ?> 
	           msg.lang = 'es-ES';
	           for(var voxIndex = 0; voxIndex < speechVoices.length; voxIndex++) {
					if(speechVoices[voxIndex].name.valueOf() == 'Google español') {
						speakVoice = 'Google español (' + speechVoices[voxIndex].name + ')';
	          			msg.voice = speechVoices[voxIndex];
					}
				}
	           <?php
	           break;
	
	           default:
	           ?>
	           msg.lang = 'en-EN'; 
	           for(var voxIndex = 0; voxIndex < speechVoices.length; voxIndex++) {
					if(speechVoices[voxIndex].name.valueOf() == 'Google UK English Female') {
						speakVoice = 'Google UK English Female (' + speechVoices[voxIndex].name + ')';
	          			msg.voice = speechVoices[voxIndex];
					}
				}
	           <?php
	       }
	       ?>
           

	       $("#modalPlayLocalFooterText").html("<b><?php echo $lang['BROWSER_SPEECH_TEXT']; ?></b>" + ': "---"' );
           
           // EVENTS: onboundary onend onerror onmark onpause onresume onstart
           msg.onstart = function(e) {
               console.log("ON START EVENT");
               if(!$('#modalPlayLocal').hasClass('in')) {
           		$('#modalPlayLocal').modal('show');
               }
               $("#modalPlayLocalFooterText").html("<b><?php echo $lang['BROWSER_SPEECH_TEXT']; ?></b>" + ': "' + texto + '"' );
           };

           /*
           msg.onpause = function(e) {
               console.log("ON PAUSE EVENT");
           };

           msg.onresume = function(e) {
               console.log("ON RESUME EVENT");
           };

           msg.onerror = function(e) {
               console.log("ON ERROR EVENT");
           };
           */
			
           msg.onend = function(e) {
           	console.log("ON END EVENT");         	
           	console.log('Voice generated in ' + event.elapsedTime + ' seconds.');
           	$('#modalPlayLocal').modal('hide');
           };

           if( typeof texto === 'string' ) {
        	   speechSynthesis.speak(msg);
           }
           else if( Object.prototype.toString.call( texto ) === '[object Array]' ) {
        	   browserUserClosed = false;
        	   browserReadSent(texto, 0, msg);
		   }
           
           console.log(msg);
   		};

		var browserUserClosed = false;
   		function browserReadSent(texto, index, msg) {
			var nextIndex = index + 1;
			msg.onstart = function(e) {
	            console.log("ON START EVENT");
	            if(!$('#modalPlayLocal').hasClass('in')) {
	           		$('#modalPlayLocal').modal('show');
	            }
	            $("#modalPlayLocalFooterText").html("<b><?php echo $lang['BROWSER_SPEECH_TEXT']; ?></b>" + ': "' + texto[index] + '"' );
	        };
	        msg.onend = function(e) {
   	           	console.log("ON END EVENT");         	
   	           	console.log('Voice generated in ' + event.elapsedTime + ' seconds.');
   	           	if(nextIndex < texto.length && !browserUserClosed) {
   	           	browserReadSent(texto, nextIndex, msg);
   	   	        }
   	        };
			console.log("READING: " + texto[index]);
			msg.text = texto[index];
   			speechSynthesis.speak(msg);
   		}

   		var espeakNgInitialized = false;
   		function initializeEspeakNG() {
	   		console.log('Creating eSpeakNG instance...');
	   		$("#dvLoading").fadeIn("slow");
	   		  tts = new eSpeakNG(
	   		    '<?php echo $helper->urlBase(); ?>/public/js/espeakng.js/espeakng.worker.js',
	   		    function cb1() {
	   		      /*
	   		      tts.list_voices(
	   		        function cb2(result) {
	   		          for (voice of result) {
		   		            var languages = voice.languages.map(function(lang) {
		   		              return lang.name;
		   		            }).join(", ");
		   		         	console.log('Voice name: ' + voice.name);
		   		         	console.log('Voice identifier: ' + voice.identifier);
		   		         	console.log('Languages: ' + languages);
	   		          }
	   		          console.log('Leaving cb2');
	   		        } // end of function cb2
	   		      );
	   		      */
	   		   	  espeakNgInitialized = true;
	   		   	  $("#dvLoading").fadeOut("slow");
	   		    }
	   		  );
	   		  console.log('Creating eSpeakNG instance... done');
   		}

		/* ESPEAKNG CODE - START */	
		function PushAudioNode(context, start_callback, end_callback, buffer_size) {
		  this.context = context;
		  this.start_callback = start_callback;
		  this.end_callback = end_callback;
		  this.buffer_size = buffer_size || 4096;
		  this.samples_queue = [];
		  this.scriptNode = context.createScriptProcessor(this.buffer_size, 1, 1);
		  this.connected = false;
		  this.sinks = [];
		  this.startTime = 0;
		  this.closed = false;
		  this.track_callbacks = new Map();
		}
		
		PushAudioNode.prototype.push = function(chunk) {
		  if (this.closed) {
		    throw 'Cannot push more chunks after node was closed';
		  }
		  this.samples_queue.push(chunk);
		  if (!this.connected) {
		    if (!this.sinks.length) {
		      throw 'No destination set for PushAudioNode';
		    }
		    this._do_connect();
		  }
		}
		
		PushAudioNode.prototype.close = function() {
		  this.closed = true;
		}
		
		PushAudioNode.prototype.connect = function(dest) {
		  this.sinks.push(dest);
		  if (this.samples_queue.length) {
		    this._do_connect();
		  }
		}
		
		PushAudioNode.prototype._do_connect = function() {
		  if (this.connected) return;
		  this.connected = true;
		  for (var dest of this.sinks) {
		    this.scriptNode.connect(dest);
		  }
		  this.scriptNode.onaudioprocess = this.handleEvent.bind(this);
		}
		
		PushAudioNode.prototype.disconnect = function() {
		  this.scriptNode.onaudioprocess = null;
		  this.scriptNode.disconnect();
		  this.connected = false;
		}
		
		PushAudioNode.prototype.addTrackCallback = function(aTimestamp, aCallback) {
		  var callbacks = this.track_callbacks.get(aTimestamp) || [];
		  callbacks.push(aCallback);
		  this.track_callbacks.set(aTimestamp, callbacks);
		}
		
		PushAudioNode.prototype.handleEvent = function(evt) {
		  if (!this.startTime) {
		    this.startTime = evt.playbackTime;
		    if (this.start_callback) {
		      this.start_callback();
		    }
		  }
		
		  var currentTime = evt.playbackTime - this.startTime;
		  var playbackDuration = this.scriptNode.bufferSize / this.context.sampleRate;
		  for (var entry of this.track_callbacks) {
		    var timestamp = entry[0];
		    var callbacks = entry[1];
		    if (timestamp < currentTime) {
		      this.track_callbacks.delete(timestamp);
		    } else if (timestamp < currentTime + playbackDuration) {
		      for (var cb of callbacks) {
		        cb();
		      }
		      this.track_callbacks.delete(timestamp);
		    }
		  }
		
		  var offset = 0;
		  while (this.samples_queue.length && offset < evt.target.bufferSize) {
		    var chunk = this.samples_queue[0];
		    var to_copy = chunk.subarray(0, evt.target.bufferSize - offset);
		    if (evt.outputBuffer.copyToChannel) {
		      evt.outputBuffer.copyToChannel(to_copy, 0, offset);
		    } else {
		      evt.outputBuffer.getChannelData(0).set(to_copy, offset);
		    }
		    offset += to_copy.length;
		    chunk = chunk.subarray(to_copy.length);
		    if (chunk.length)
		      this.samples_queue[0] = chunk;
		    else
		      this.samples_queue.shift();
		  }
		
		  if (!this.samples_queue.length && this.closed) {
		    if (this.end_callback) {
		      this.end_callback(evt.playbackTime - this.startTime);
		    }
		    this.disconnect();
		  }
		}
		
		var ctx = new (window.AudioContext || window.webkitAudioContext)();
		var tts;
		var pusher = null;
		var pusher_buffer_size = 4096;
		var chunkID = 0;
		/* ESPEAKNG CODE - END */
				
   		function espeakNgReadText(texto) {
   			speakGenMethod = "BRWOSER eSpeakNG";

   			$('#playButton').attr('disabled', true);
   			$('#pauseButton').attr('disabled', true);
   			
   			var now = Date.now();
   			
        	console.log('ESPEAK NG SpeechSynthesisUtterance - Generating voice of text: "' + texto + '"...');

        	console.log('Inside speak()');

        	console.log('  Stopping...');
        	stop();
        	console.log('  Stopping... done');

        	console.log('  Setting rate to: 1.0');
        	tts.set_rate(190); // RATE RANGE: 80 - 450
        	console.log('  Setting pitch to: 1.0');
        	tts.set_pitch(70); // PITCH RANGE: 0 - 100
        	
        	// VOICES roa/ca --- roa/es --- gmw/en
        	<?php
           	switch ($_SESSION['lang']) {
	           case 'en':
	           ?> 
	           tts.set_voice('gmw/en');
	           console.log('  Setting voice gmw/en');
	           speakVoice = "gmw/en (English)";
	           <?php
	           break;
	
	           case 'es':
	           ?> 
	           tts.set_voice('roa/es');
	           console.log('  Setting voice roa/es');
	           speakVoice = "roa/es (Spanish)";
	           <?php
	           break;
	
	           default:
	           ?>
	           tts.set_voice('gmw/en');
	           console.log('  Setting voice gmw/en');
	           speakVoice = "gmw/en (English)";
	           <?php
	       	}
	       	
	       	if($_GET['TEST']) {
	       		?>
	       		tts.set_voice('gmw/en');
	       		console.log('  Setting voice roa/ca');
	       		speakVoice = "roa/es (Catalan)";
	       		<?php
	       	}
	       	?>

        	var now = Date.now();
        	chunkID = 0;

        	console.log('  Creating pusher...');
        	pusher = new PushAudioNode(
        	    ctx,
        	    function() {
        	    	console.log("ON START EVENT - ESPEAK NG");
                    if(!$('#modalPlayLocal').hasClass('in')) {
                		$('#modalPlayLocal').modal('show');
                    }
                    $("#modalPlayLocalFooterText").html("<b><?php echo $lang['BROWSER_SPEECH_TEXT']; ?></b>" + ': "' + texto + '"' );
        	    },
        	    function() {
        	    	console.log("ON END EVENT - ESPEAK NG");    
                   	$('#modalPlayLocal').modal('hide');
        	    },
        	    pusher_buffer_size
        	);
        	pusher.connect(ctx.destination);
        	console.log('  Creating pusher... done');

        	// actual synthesis
        	console.log('  Calling synthesize...');

        	if( typeof texto === 'string' ) {
        		tts.synthesize(
                	    texto,
                	    function cb(samples, events) {
                	      if (!samples) {
                	        if (pusher) {
                	          pusher.close();
                	        }
                	        return;
                	      }
                	      if (pusher) {
                	        pusher.push(new Float32Array(samples));
                	        ++chunkID;
                	      }
                	    }
                	);
            }
            else if( Object.prototype.toString.call( texto ) === '[object Array]' ) {
         	   espeakNgUserClosed = false;
         	   espeakNgReadSent(texto, 0);
 		    }
        	console.log('  Calling synthesize... done');
   		};

		var espeakNgUserClosed = false;
   		function espeakNgReadSent(texto, index) {
			var nextIndex = index + 1;
			pusher = new PushAudioNode(
	        	    ctx,
	        	    function() {
	        	    	console.log("ON START EVENT - ESPEAK NG");
	                    if(!$('#modalPlayLocal').hasClass('in')) {
	                		$('#modalPlayLocal').modal('show');
	                    }
	                    
	                    $("#modalPlayLocalFooterText").html("<b><?php echo $lang['BROWSER_SPEECH_TEXT']; ?></b>" + ': "' + texto[index] + '"' );
	        	    },
	        	    function() {
	        	    	console.log("ON END EVENT");         	
	       	           	console.log('Voice generated in ' + event.elapsedTime + ' seconds.');
	       	           	if(nextIndex < texto.length && !espeakNgUserClosed) {
	       	           		espeakNgReadSent(texto, nextIndex);
	       	   	        }
	        	    },
	        	    pusher_buffer_size
	        	);
			pusher.connect(ctx.destination);
        	console.log('  Creating pusher... done');
        	
			console.log("READING: " + texto[index]);
			tts.synthesize(
					texto[index],
            	    function cb(samples, events) {
            	      if (!samples) {
            	        if (pusher) {
            	          pusher.close();
            	        }
            	        return;
            	      }
            	      if (pusher) {
            	        pusher.push(new Float32Array(samples));
            	        ++chunkID;
            	      }
            	    }
            	);
   		}

        $(document).ready(function(){
        	initializeEspeakNG();
        	
            //text2speech
            $('.audioplayAll').click(function() {
            	$('.audioplayAll').attr('disabled', true);

            	var linearText = "";
            	var compositeText = [];
            	var counter = 0;
            	
				// Adding from / to / subject / date
				var fromEm = $('#fromEmail').text().replace(/([^.@\s]+)(\.[^.@\s]+)*@([^.@\s]+\.)+([^.@\s]+)/,"");
				var toEm = $('#toEmail').text().replace(/([^.@\s]+)(\.[^.@\s]+)*@([^.@\s]+\.)+([^.@\s]+)/,"");
				var subjectEm = $('#subjectEmail').text();
				var dateEm = $('#dateEmail').text();
				linearText += fromEm + "\n" + subjectEm + "\n" + dateEm;
				compositeText[counter++] = fromEm;
				compositeText[counter++] = subjectEm;
				compositeText[counter++] = dateEm;
				
             	$('span[id^="utterance"]').each(function(){
             		compositeText[counter] = $(this).html().trim();
             		counter++;
             		linearText += $(this).html().trim() + "\n";
             	});
				
            	if ('speechSynthesis' in window && navigator.userAgent.toLowerCase().indexOf('firefox') <= -1) {
                	// Synthesis support. Make your web apps talk!
                 	browserReadText(compositeText);
                 	$('.audioplayAll').attr('disabled', false);
                }
                else if(espeakNgInitialized) {
					// EspeakNG
                	espeakNgReadText(compositeText);
                	$('.audioplayAll').attr('disabled', false);
                }
                else {
                	remoteABLEreadTexto(linearText);
                	$('.audioplayAll').attr('disabled', false);
                }
            });
            
            $('.audioplay').click(function() {
                var texto = document.getElementById("utterance"+this.id).innerHTML;
				texto = texto.trim();
				
                if ('speechSynthesis' in window && navigator.userAgent.toLowerCase().indexOf('firefox') <= -1) {
                	// Synthesis support. Make your web apps talk!
                	browserReadText(texto);
                }
                else if(espeakNgInitialized) {
                	// EspeakNG
                	espeakNgReadText(texto);
                }
                else {
                	remoteABLEreadTexto(texto);
                }
            });

            //text2picto - Beta
            $('#picto').click(function() {
                var texto = getText();
                $.ajax({
                    url: '<?php echo TEXT2PICTO ?>',
                    type: 'GET',
                    // headers: {
                    //   'Origin': 'http://kolumba.eu'
                    // },
                    async: true,
                    dataType : 'json',
                    data: 'text='+texto+'&type=beta&language=<?php echo $lang['TEXT2SPEECH_LANGUAGE']; ?>',
                    beforeSend: function() {
                        $("#dvLoading").fadeIn("slow");
                    },
                    complete: function() {
                        $("#dvLoading").fadeOut("slow");
                    },
                    success: function(json) {
                        var newBody ='';
                        json.pictos.forEach(function(item){
                            if(item.indexOf("http") > -1) {
                                newBody += '<img src="'+item+'">'+' ';
                            } else {
                                 newBody +=  '<font size="6">'+item+'</font>'+' ';
                            }
                        });
                        document.getElementById("mailBody").innerHTML = texto + "<br/><hr/><br/>" + newBody;
                    },
                    error: function(xhr, status) {
                        console.log(status);
                    }
                });
            });

            //text2picto - Sclera
            $('#picto2').click(function() {
                var texto = getText();

                $.ajax({
                    url: '<?php echo TEXT2PICTO ?>',
                    type: 'GET',
                 	// headers: {
                  	//     'Origin': 'http://kolumba.eu'
                   	// },
                    async: true,
                    dataType : 'json',
                    data: 'text='+texto+'&type=sclera&language=<?php echo $lang['TEXT2SPEECH_LANGUAGE']; ?>',
                    beforeSend: function() {
                        $("#dvLoading").fadeIn("slow");
                    },
                    complete: function() {
                        $("#dvLoading").fadeOut("slow");
                    },
                    success: function(json) {
                        var newBody ='';
                        json.pictos.forEach(function(item){
                            if(item.indexOf("http") > -1) {
                                newBody += '<img width="150px" src="'+item+'" />'+' ';
                            } else {
                                 newBody += '<font size="6">'+item+'</font>'+' ';
                            }
                        });
                        document.getElementById("mailBody").innerHTML = texto + "<br/><hr/><br/>" + newBody;
                    },
                    error: function(xhr, status) {
                        console.log(status);
                    }
                });
            });
            //text2picto - Arasaac
            $('#picto3').click(function() {
                var texto = getText();

                $.ajax({
                    url: '<?php echo TEXT2PICTO ?>',
                    type: 'GET',
                    headers: {
                      'Origin': 'http://kolumba.eu'
                    },
                    async: true,
                    dataType : 'json',
                    data: 'text='+texto+'&type=arasaac&language=<?php echo $lang['TEXT2SPEECH_LANGUAGE']; ?>',
                    beforeSend: function() {
                        $("#dvLoading").fadeIn("slow");
                    },
                    complete: function() {
                        $("#dvLoading").fadeOut("slow");
                    },
                    success: function(json) {
                        var newBody ='';
                        json.pictos.forEach(function(item){
                            if(item.indexOf("http") > -1) {
                                newBody += '<img width="150px" src="'+item+'" />'+' ';
                            } else {
                                 newBody += '<font size="6">'+item+'</font>'+' ';
                            }
                        });
                        document.getElementById("mailBody").innerHTML = texto + "<br/><hr/><br/>" + newBody;
                    },
                    error: function(xhr, status) {
                        console.log(status);
                    }
                });
            });

            //simplext
            $('#simplext').click(function() {
                var texto = getText();
                $.ajax({
                    url: '<?php echo SIMPLEXT ?>',
                    type: 'GET',
                    //  headers: {
                    //   'Origin': 'http://kolumba.eu'
                    // },
                    async: true,
                    dataType : 'json',
                    data: 'text='+texto+'&language=<?php echo $lang['TEXT2SPEECH_LANGUAGE']; ?>',
                    beforeSend: function() {
                        $("#dvLoading").fadeIn("slow");
                    },
                     complete: function() {
                        $("#dvLoading").fadeOut("slow");
                    },
                    success: function(json) {
                        document.getElementById("mailBody").innerHTML = json.textSimplified;
                    },
                    error: function(xhr, status) {
                        console.log(status);
                    }
                });
            });

			
            var textoInit = "<?php echo $lang['LOADING_SPEECH_INTERFACE'];?>";
        	
            if ('speechSynthesis' in window && navigator.userAgent.toLowerCase().indexOf('firefox') <= -1) {
            	// Synthesis support. Make your web apps talk!
            	browserReadText(textoInit);
            }
            else {
            	setTimeout(function(){ 
            		if(espeakNgInitialized) {
                    	// EspeakNG
                    	espeakNgReadText(textoInit);
                    }
                    else {
                    	remoteABLEreadTexto(textoInit);
                    }
    			}, 1000);
            }
            
        });
        
    </script>

<div id="audioDiv" class="audio-player">
	<audio id="audio" src="/public/beep.mp3"></audio>
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
				<button type='submit' id='simplext' class='btn btn-success btn-lg'
					style='width: 90%;' title='<?php echo $lang['SIMPLE_TEXT']; ?>'>
					<span class="glyphicon glyphicon-book"></span> <?php echo $lang['SIMPLE_TEXT']; ?>
                    </button>
			</div>

                <?php
																if (USE_TEXT_TO_PICTO) {
																	echo '
                <div style="text-align: center;" class="kolumba-col-lg-3 item">
                    <button type="submit" id="picto" class="btn btn-success btn-lg" style="width:90%"  title="' . $lang ['PICTO'] . '">
                        <span class="glyphicon glyphicon-picture"></span> ' . $lang ['PICTO'] . '
                    </button>
                </div>
                <div style="text-align: center;" class="kolumba-col-lg-3 item">
                    <button type="submit" id="picto2" class="btn btn-success btn-lg" style="width:90%"  title="' . $lang ['PICTO_2'] . '">
                        <span class="glyphicon glyphicon-picture"></span> ' . $lang ['PICTO_2'] . '
                    </button>
                </div>
                <div style="text-align: center;" class="kolumba-col-lg-3 item">
                    <button type="submit" id="picto3" class="btn btn-success btn-lg" style="width:90%" title="' . $lang ['PICTO_3'] . '">
                        <span class="glyphicon glyphicon-picture"></span> ' . $lang ['PICTO_3'] . '
                    </button>
                </div>';
																}
																?>
            </div>

		<div class='col-lg-12'>
			<div id='mailBody'>

				<!-- Modal text play -->
				<div class="modal fade" id="modalPlayLocal" role="dialog">
					<div class="modal-dialog modal-sm">
						<div class="modal-content">
							<div class="modal-header">
								<h4 id="modalPlayLocalHeader" class="modal-title"><?php echo $lang['BROWSER_SPEECH_READING']; ?>...</h4>
							</div>
							<div class="modal-body">

								<div id="playButton" class="btn btn-success"><?php echo $lang['BROWSER_SPEECH_COMMAND_PLAY'];?></div>

								<div id="pauseButton" class="btn btn-success"><?php echo $lang['BROWSER_SPEECH_COMMAND_PAUSE'];?></div>
								<div id="stopButton" class="btn btn-success"><?php echo $lang['BROWSER_SPEECH_COMMAND_CLOSE'];?></div>
							</div>
							<div id="modalPlayLocalFooter" class="modal-footer"
								style="text-align: left;">
								<div id="modalPlayLocalFooterText"> </div>
								<div style="margin-top: 15px;font-size: 80%;"> 
									<span id="modalPlayLocalFooterEngine"> </span>	
									<span id="modalPlayLocalFooterVoice"> </span>	
								</div>
								
								
							</div>

							<script type='text/javascript'>
									$('#playButton').on('click', function(event) {
										event.preventDefault(); // To prevent following the link (optional)
										$("#modalPlayLocalHeader").text("<?php echo $lang['BROWSER_SPEECH_READING']; ?>...");
										
										$(this).attr('disabled', true);
										$('#pauseButton').attr('disabled', false);
									  	
									  	window.speechSynthesis.resume();
									});
									$('#playButton').attr('disabled', true);
									
									$('#pauseButton').on('click', function(event) {
										event.preventDefault(); // To prevent following the link (optional)
										$("#modalPlayLocalHeader").text("<?php echo $lang['BROWSER_SPEECH_ON_PAUSE']; ?>");
										
										$(this).attr('disabled', true);
										$('#playButton').attr('disabled', false);
									  	
									  	window.speechSynthesis.pause();
									});

									$('#stopButton').on('click', function(event) {
									  	event.preventDefault(); // To prevent following the link (optional)
									  	window.speechSynthesis.cancel();

									  	if(pusher !== null) {
									  		pusher.disconnect();
										}

									  	browserUserClosed = true;
									  	espeakNgUserClosed = true;
									  	
									  	$('#playButton').attr('disabled', true);
									  	$('#pauseButton').attr('disabled', false);

									  	$('#modalPlayLocal').modal('hide');

									  	$("#modalPlayLocalHeader").text("<?php echo $lang['BROWSER_SPEECH_READING']; ?>...");
									});

									$('#modalPlayLocal').on('hidden.bs.modal', function () {
										$('#stopButton').click();
									});

									$('#modalPlayLocal').on('shown.bs.modal', function() {
										$("#modalPlayLocalFooterEngine").html("<b><?php echo $lang['BROWSER_SPEECH_ENGINE']; ?></b>" + ': ---&nbsp-&nbsp;' );
										$("#modalPlayLocalFooterVoice").html("<b><?php echo $lang['BROWSER_SPEECH_VOICE']; ?></b>" + ': ---' );
										setTimeout(function(){ 
											$("#modalPlayLocalFooterEngine").html("<b><?php echo $lang['BROWSER_SPEECH_ENGINE']; ?></b>" + ': ' + speakGenMethod + '&nbsp-&nbsp;' );
											$("#modalPlayLocalFooterVoice").html("<b><?php echo $lang['BROWSER_SPEECH_VOICE']; ?></b>" + ': ' + speakVoice + '' );
										}, 300);
								    })
							</script>
						</div>
					</div>
				</div>

				<div>
					<div class='marginButton btn btn-success btnReduced audioplayAll'>
						<span class='glyphicon glyphicon-volume-up icono'
							id='" . $i . "'></span> <?php echo $lang['BROWSER_SPEECH_WHOLE_EMAIL_READ']; ?>
					</div>
				</div>
                    <?php
																				foreach ( $vars ['Mail'] ['Body'] as $value ) {
																					if (strcmp ( $value ['mime'], 'text/plain' ) == 0) {
																						$mailBodyArray = explode ( '<br/>', $value ['content'] );
																						$i = 0;
																						foreach ( $mailBodyArray as $line ) {
																							if (strlen ( $line ) > 2) {
																								echo "
                    <div>
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

