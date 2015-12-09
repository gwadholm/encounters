<?php
    $URL = "https://docs.google.com/file/d/0B40n-RNZ68NhRXYyc3RoU1JTYlk/preview";

    $domain = file_get_contents($URL);
	
	$find[] = '/<link(.*)href="\/(.*)"/';
	$find[] = '/<script(.*)src="\/(.*)"/';
	$find[] = '/:\["(\\\)(.*)static(\\\)/';
	$find[] = '/:\["(\\\)(.*)comments(\\\)/';
	
	$replace[] = '<link$1href="https://docs.google.com/$2"';
	$replace[] = '<script$1src="https://docs.google.com/$2"';
	$replace[] = ':["\/\/docs.google.com$1$2static$3';
	$replace[] = ':["\/\/docs.google.com$1$2comments$3';
	
	$newContents = preg_replace($find,$replace,$domain);
    echo $newContents;
	
	/*<iframe src="https://docs.google.com/file/d/0B40n-RNZ68NhSWpCaDdwcDR1MGc/preview"
width="932" height="485" class="videoFrame"></iframe>*/
?>