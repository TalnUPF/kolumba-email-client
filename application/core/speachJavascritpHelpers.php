<script type="text/javascript"
	src="<?php echo $helper->urlBase(); ?>/public/js/espeakng.js/espeakng.min.js"></script>	


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


	<script type='text/javascript'>
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
                    $('#audio').attr('src', '<?php echo $helper->urlBase(); ?>/public/beep.mp3'); // json.audioSpeech);

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
		
        function browserReadText(texto, volume, showPopup) {
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

			// Volume: 0 to 1
			if(volume !== null && !isNaN(parseFloat(volume)) && isFinite(volume) && volume >= 0 && volume <= 1) {
				msg.volume = volume;
			}
			else {
				msg.volume = 1;
			}
			
           // Set up voice generation
           msg.voiceURI = 'native';
           msg.rate = 1; // 0.1 to 10
           msg.pitch = 1; //0 to 2
           msg.text = texto;

           var selectedVoice = null;
           var speechVoices = window.speechSynthesis.getVoices();

           if(current_language !== null) {
       			switch(current_language) {
	        	    case 'en':
	        	    	msg.lang = 'en-EN'; 
	     	           	for(var voxIndex = 0; voxIndex < speechVoices.length; voxIndex++) {
	     					if(speechVoices[voxIndex].name.valueOf() == 'Google UK English Female') {
	     						speakVoice = 'Google UK English Female (' + speechVoices[voxIndex].name + ')';
	     	          			msg.voice = speechVoices[voxIndex];
	     					}
	     				}	        	        
	     	           	break;
	        	    case 'es':
	        	    	msg.lang = 'es-ES';
	     	           	for(var voxIndex = 0; voxIndex < speechVoices.length; voxIndex++) {
	     					if(speechVoices[voxIndex].name.valueOf() == 'Google español') {
	     						speakVoice = 'Google español (' + speechVoices[voxIndex].name + ')';
	     	          			msg.voice = speechVoices[voxIndex];
	     					}
	     				}
	        	        break;
	        	    default:
	        	    	msg.lang = 'en-EN'; 
	 	           		for(var voxIndex = 0; voxIndex < speechVoices.length; voxIndex++) {
	 					if(speechVoices[voxIndex].name.valueOf() == 'Google UK English Female') {
	 							speakVoice = 'Google UK English Female (' + speechVoices[voxIndex].name + ')';
	 	          				msg.voice = speechVoices[voxIndex];
	 						}
	 					}
       			}
       		}
       		else {
       			msg.lang = 'en-EN'; 
 	           	for(var voxIndex = 0; voxIndex < speechVoices.length; voxIndex++) {
 					if(speechVoices[voxIndex].name.valueOf() == 'Google UK English Female') {
 						speakVoice = 'Google UK English Female (' + speechVoices[voxIndex].name + ')';
 	          			msg.voice = speechVoices[voxIndex];
 					}
 				}
          	}

	       $("#modalPlayLocalFooterText").html("<b><?php echo $lang['BROWSER_SPEECH_TEXT']; ?></b>" + ': "---"' );
           
           // EVENTS: onboundary onend onerror onmark onpause onresume onstart
           if(showPopup == undefined || showPopup) {
        	   msg.onstart = function(e) {
                   console.log("ON START EVENT");
                   if(!$('#modalPlayLocal').hasClass('in')) {
               			$('#modalPlayLocal').modal('show');
                   }
                   $("#modalPlayLocalFooterText").html("<b><?php echo $lang['BROWSER_SPEECH_TEXT']; ?></b>" + ': "' + texto + '"' );
               };

               msg.onend = function(e) {
                  	console.log("ON END EVENT");         	
                  	console.log('Voice generated in ' + event.elapsedTime + ' seconds.');
                  	$('#modalPlayLocal').modal('hide');
               };
           }

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
        	tts.set_rate(160); // RATE RANGE: 80 - 450
        	console.log('  Setting pitch to: 1.0');
        	tts.set_pitch(70); // PITCH RANGE: 0 - 100
        	
        	// VOICES roa/ca --- roa/es --- gmw/en
        	if(current_language !== null) {
        		switch(current_language) {
	        	    case 'en':
	        	    	tts.set_voice('gmw/en');
	     	           	console.log('  Setting voice gmw/en');
	     	           	speakVoice = "gmw/en (English)";	        	        
	     	           	break;
	        	    case 'es':
	        	    	tts.set_voice('roa/es');
	     	           	console.log('  Setting voice roa/es');
	     	          	speakVoice = "roa/es (Spanish)";
	        	        break;
	        	    default:
	        	    	tts.set_voice('gmw/en');
	 	           		console.log('  Setting voice gmw/en');
	 	           		speakVoice = "gmw/en (English)";
        		}
        	}
        	else {
        		tts.set_voice('gmw/en');
	           	console.log('  Setting voice gmw/en');
	           	speakVoice = "gmw/en (English)";
           	}
           	
        	<?php
	       	if($_GET['TEST']) {
	       		?>
	       		tts.set_voice('roa/ca');
	       		console.log('  Setting voice roa/ca');
	       		speakVoice = "roa/ca (Catalan)";
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

        $(document).ready(function() {
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

				linearText += "<?php echo $lang['BROWSER_SPEECH_CONTENT']; ?>" + "\n";
				compositeText[counter++] = "<?php echo $lang['BROWSER_SPEECH_CONTENT']; ?>";
             	$('span:visible[id^="utterance"]').each(function(){
             		compositeText[counter] = $(this).html().trim();
             		counter++;
             		linearText += $(this).html().trim() + "\n";
             	});
             	
            	if ('speechSynthesis' in window && navigator.userAgent.toLowerCase().indexOf('firefox') <= -1) {
                	// Synthesis support. Make your web apps talk!
                	console.log("BROWSER text-to-speech (client-side) ---> Going to read: " + compositeText.toString());
                 	browserReadText(compositeText);
                 	$('.audioplayAll').attr('disabled', false);
                }
                else if(espeakNgInitialized) {
					// EspeakNG
					console.log("ESPEAKNG text-to-speech (client-side) ---> Going to read: " + compositeText.toString());
                	espeakNgReadText(compositeText);
                	$('.audioplayAll').attr('disabled', false);
                }
                else {
                	console.log("ABLE text-to-speech (server-side) ---> Going to read: " + compositeText.toString());
                	remoteABLEreadTexto(linearText);
                	$('.audioplayAll').attr('disabled', false);
                }
            });
            
            audioPlayEventAssociation();
			
            var textoInit = "<?php echo $lang['LOADING_SPEECH_INTERFACE'];?>";

        	
            if ('speechSynthesis' in window && navigator.userAgent.toLowerCase().indexOf('firefox') <= -1) {
            	// Synthesis support. Make your web apps talk!
            	browserReadText(textoInit, 0, false);
            }
        });

        function audioPlayEventAssociation() {
        	$('.audioplay').click(function() {
                var texto = "<?php echo $lang['BROWSER_SPEECH_NO_TEXT']; ?>";

				var elemWithText = null;
				if(document.getElementById("utterance"+this.id) !== null) {
					elemWithText = document.getElementById("utterance"+this.id).innerHTML;
				}
				
				// If there is NOT some text in an HTML element with id = "utterance"+this.id
				if(elemWithText !== null && elemWithText.trim().length > 0) {
					texto = elemWithText;
				}
				else {
					elemWithText = $(this).attr("title");
					if(elemWithText !== null && elemWithText.trim().length > 0) {
						texto = elemWithText;
					}
				}
				
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
        }
        
    </script>