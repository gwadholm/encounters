<?php // Results page for each module
if($page == "1___results"){	
	$walkingArray = array (
		1 => $_REQUEST['walking1'],
		2 => $_REQUEST['walking2'],
		3 => $_REQUEST['walking3'],
		4 => $_REQUEST['walking4'],
		5 => $_REQUEST['walking5'],
	);
	if (count($walkingArray) == 5) {
		$i = 0;
		if($walkingArray[1] == "4"){ $i++; } else {$corrected = array(1, 4);}
		if($walkingArray[2] == "1"){ $i++; } else {$corrected = array(2, 1);}
		if($walkingArray[3] == "4"){ $i++; } else {$corrected = array(3, 4);}
		if($walkingArray[4] == "4"){ $i++; } else {$corrected = array(4, 4);}
		if($walkingArray[5] == "1"){ $i++; } else {$corrected = array(5, 1);}
		
		$resultNum = ($i * 20);
		$nextSection = '/2/1';
		$prevSection = '/1/1';
	} 
}
if($page == "2___results"){
	$encounteringArray = array (
		1 => $_REQUEST['encountering1'],
		2 => $_REQUEST['encountering2'],
		3 => $_REQUEST['encountering3'],
		4 => $_REQUEST['encountering4'],
		5 => $_REQUEST['encountering5'],
	);
	if (count($encounteringArray) == 5) {
		$i = 0;
		
		if($encounteringArray[1] == "4"){ $i++; } else {$corrected = array(1, 4);}
		if($encounteringArray[2] == "1"){ $i++; } else {$corrected = array(2, 1);}
		if($encounteringArray[3] == "2"){ $i++; } else {$corrected = array(3, 2);}
		if($encounteringArray[4] == "2"){ $i++; } else {$corrected = array(4, 2);}
		if($encounteringArray[5] == "4"){ $i++; } else {$corrected = array(5, 4);}
		
		$resultNum = ($i * 20);
		$nextSection = '/3/1';
		$prevSection = '/2/1';
	} 
}
if($page == "3___results"){
	$visionArray = array (
		1 => $_REQUEST['vision1'],
		2 => $_REQUEST['vision2'],
		3 => $_REQUEST['vision3'],
		4 => $_REQUEST['vision4'],
		5 => $_REQUEST['vision5'],
	);
	if (count($visionArray) == 5) {
		$i = 0;
		
		if($visionArray[1] == "4"){ $i++; } else {$corrected = array(1, 4);}
		if($visionArray[2] == "1"){ $i++; } else {$corrected = array(2, 1);}
		if($visionArray[3] == "4"){ $i++; } else {$corrected = array(3, 4);}
		if($visionArray[4] == "1"){ $i++; } else {$corrected = array(4, 1);}
		if($visionArray[5] == "1"){ $i++; } else {$corrected = array(5, 1);}
		
		$resultNum = ($i * 20);
		$nextSection = '/4/1';
		$prevSection = '/3/1';
	} 
}
if($page == "4___results"){
	$reportingArray = array (
		1 => $_REQUEST['reporting1'],
		2 => $_REQUEST['reporting2'],
		3 => $_REQUEST['reporting3'],
		4 => $_REQUEST['reporting4'],
		5 => $_REQUEST['reporting5'],
	);
	if (count($reportingArray) == 5) {
		$i = 0;
		
		if($reportingArray[1] == "4"){ $i++; } else {$corrected = array(1, 4);}
		if($reportingArray[2] == "1"){ $i++; } else {$corrected = array(2, 1);}
		if($reportingArray[3] == "1"){ $i++; } else {$corrected = array(3, 1);}
		if($reportingArray[4] == "4"){ $i++; } else {$corrected = array(4, 4);}
		if($reportingArray[5] == "1"){ $i++; } else {$corrected = array(5, 1);}
		
		$resultNum = ($i * 20);
		$prevSection = '/4/1';
	} 
}
?>