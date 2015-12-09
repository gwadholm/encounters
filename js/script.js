$(document).ready(function(){
	$('#login').colorbox({inline: true, width: '360px'});
	$('#register').colorbox({inline: true, width: '480px'});
	
	
	currentBodyID = $('body').attr('id');
	$('#topNav > ul > li > a[rel="'+ currentBodyID +'"]').addClass('currentNav');
	
	$('#outsideWrapper #topNav li.dropDown ul').each(function(){
		$(this).attr('style','display: none; left: 0px;');
	});
	
	$('video,audio').mediaelementplayer();
	    
	var hoverIntentConfig = {    
		over: function(){
			$(this).find('ul').fadeIn(100);
		},
		timeout: 300, // number = milliseconds delay before onMouseOut    
		out: function(){	
			$(this).find('ul').fadeOut(100);
		}
	};
	$('#outsideWrapper #topNav li.dropDown').hoverIntent(hoverIntentConfig);
	
	$('nav#innerNav a.sectionLink').hover(function(){
		sectionName = $(this).find('img').attr('title');
		$(this).append('<span>'+ sectionName +'</span>');
		$(this).find('img').attr({'title':''});
	}, function(){
		sectionName = $(this).find('span').text();
		$(this).find('img').attr({'title':sectionName});
		$(this).find('span').fadeOut().remove();
	});
	
		
	/* Assessment */	
	$('.hiddenDiv').css({'display':'none'});
    $('#modelDiv').css({'display':'block'});	
	
	$('form#assessmentPage p span, form#surveyForm p span').live('click', function(e){
		$(this).parent().find('.radioSelect').toggleClass('radioSelect');
		$(this).find('input').attr('checked', true);
		$(this).toggleClass('radioSelect');
	});	
	
	$('#nav a').live('click', function(e){
		e.preventDefault();
		$('a.currentTab').toggleClass('currentTab');
		$(this).toggleClass('currentTab');
		
		
		$('.hiddenDiv').css({'display':'none'});

        var alphaSection = $(this).attr('rel'); 

        $('div#'+ alphaSection).fadeIn();
    });
	
	$('button#printResults').live('click', function(e){
		e.preventDefault();
		window.print();
	});
	
	$('form#assessmentPage').submit(function(e){
		
		e.preventDefault();
		
		var totalChecked = 0,
		formArray = null;
		formArray = {};
		
		$('#assessmentPage p').each(function(){
			var areChecked = $(this).find('input:checked').length;
			var isInput = $(this).find('input').length;
			if(areChecked < 1 && isInput >= 1){
				$(this).addClass('unchecked');
			} else if (areChecked == 1){
				var inputName = $(this).find('input:checked').attr('name');
				var inputVal = $(this).find('input:checked').val();
				
				formArray[inputName] = inputVal;
				totalChecked ++;
				$(this).removeClass('unchecked');
			}
		});
		
		if(totalChecked < 5){
			$('.error').remove();
			$(this).after('<div class="error" style="border-left: 10px solid #900; color: #900; padding: 10px; margin: 10px 0px; box-shadow: 0px 0px 3px #ccc;">Please answer all questions. (All unanswered questions have been highlighted in red.) Only '+ (5 - totalChecked) +' questions above still need to be answered.</div>');
			return false;
		} else if (totalChecked == 5){
			$('.error').remove();
					
			$('form#assessmentPage button').remove();
			$("form#assessmentPage").after('<div class="calculating"><p>Calculating Quiz Results...</p></div>');
			
			
            $(".calculating").load('results.html #results', formArray, function(){
				wrongQuestion = $('#corrected').attr('rel-q');
				rightAnswer = $('#corrected').attr('rel-a');
				
				$('#assessmentPage p:eq('+ (wrongQuestion - 1) +')').addClass('unchecked');
				$('#assessmentPage p:eq('+ (wrongQuestion - 1) +') span').attr('style','text-decoration: line-through;');
				$('#assessmentPage p:eq('+ (wrongQuestion - 1) +') span:eq('+ (rightAnswer - 1) +')').attr('style','text-decoration: none;');
			});
			var calculate = window.setInterval(function(){
                if ($('.calculating').text() === "Calculating Quiz Results..." || $('.calculating').text() === ""){
                    $(".calculating").load('results.html #results', formArray, function(){
						wrongQuestion = $('#corrected').attr('rel-q');
						rightAnswer = $('#corrected').attr('rel-a');
						
						
						
						$('#assessmentPage p:eq('+(wrongQuestion - 1) +')').addClass('unchecked');
						$('#assessmentPage p:eq('+ (wrongQuestion - 1) +') span').attr('style','text-decoration: line-through;');
						$('#assessmentPage p:eq('+ (wrongQuestion - 1) +') span:eq('+ (rightAnswer - 1) +')').attr('style','text-decoration: none;');
					});
                } else {
                    window.clearInterval(calculate);
					if($('#allDoneNow').length){
						$('#assessment4Img').attr('src','/img/check.png?4');
					};
                }
            }, 3000);
		}
	});	
});