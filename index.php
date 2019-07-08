<?php
session_start();
//error_reporting(0);
$siteRoot = "/";
$siteURL = "http://training.org"; // Change this to your domain name
$adminEmail = 'admin@training.org'; // Change this to your admin's email address
$adminUsername = 'admin'; // Change this to your admin's username
$adminPassword = 'f33aGae$2){{ab'; // Change this to your admin's password
$pageName = $_REQUEST['page'];
$page = preg_replace('/\//','___',$pageName);
$page = preg_replace('/\.(.*)$/','',$page);
$page = preg_replace('/(.*)___$/','$1', $page);
$editID = $_REQUEST['id'];
$editValue = $_REQUEST['value'];
$deletePage = $_REQUEST['deletePage'];
$resultNum = '';
if($_SESSION['userFullName']){
	$userFullName = $_SESSION['userFullName'];
} elseif ($_REQUEST["nameLogin"]){
	$userFullName = preg_replace('/[^a-z\s]/i','',$_REQUEST["nameLogin"]);
	$_SESSION['userFullName'] = $userFullName;
}
if(isset($_SESSION['contactID'])){
	$contactID = $_SESSION['contactID'];
} else {
	$contactID = get_random_string();
	$_SESSION['contactID'] = $contactID;
}

$loginError = '<p style="color: #900;">Wrong username or password.</p>';
$noPageError = "<h1>404</h1><p>We are sorry, you have somehow navigated to a page that does not exist.</p>";
$contactError = "";
$registerError = "";
$contactSuccess = "";
$registerSuccess = "";
$editPageMessage = "<h1>No Current Page</h1><p>We are sorry, but this page does not exist yet or has been deleted. If you would like to create a page at this URL, just click here and edit this content. Add any content you want, then link to it from another page.</p>";

// Authentication
if(!$_SESSION['admin'] || $_SESSION['admin'] == false){
	$username = $_REQUEST["vip"];
	$password = $_REQUEST["vipp"];
	$userLogin = $_REQUEST["Login"];

	$userRegister = urlencode(preg_replace('/\@/', '-_-' , $_REQUEST['use']));
	$user = preg_replace('/\@/', '-_-', $username);
	$user2 = preg_replace('/\@/', '-_-', $userLogin);

	if($page == 'logout'){ // Logout
		session_destroy();
		$page = 'index';
		header('Location: ' . $siteRoot);
	} else if ($_SESSION['userID']){ // Currently logged in user
		$userLogin = $_SESSION['userID'];
		$_SESSION['user'] = true;
		$user = $userLogin;
	} else if ($username == $adminUsername && $password == $adminPassword){ // Admin user
		$_SESSION['admin'] = true;
		$_SESSION['user'] = true;
		$admin = true;
		$user = $username;
	} else if (filter_var($userLogin, FILTER_VALIDATE_EMAIL)){ // Logging in
		$filename = '../encountersReg/'. urlencode($user2) .'.json';
		if (file_exists($filename)) {
			$_SESSION['user'] = true;
			$_SESSION['userID'] = $user2;
			$_SESSION['userFullName'] = $userFullName;
			$user = $userLogin;
			$currentPage = getInfo("currentPage", $user2);
			$currentSection = getInfo("currentSection", $user2);
			$page = $currentPage."___".$currentSection;
			header('Location: /' . $currentPage.'/'.$currentSection);
		}
		$admin = false;
	} else if (filter_var($_REQUEST['use'], FILTER_VALIDATE_EMAIL)){ // Registering user 1
		$_SESSION['register'] = $userRegister;
		$_SESSION['userID'] = $user2;
		$_SESSION['userFullName'] = $userFullName;
		$admin = false;
		$user = $userRegister;
		$userLogin = $userRegister;

	} else if ($_SESSION['user'] == true){ // If currently a user
		if ($page == 'logout'){ // allow logging out
			session_destroy();
			$page = 'index';
			header('Location: ' . $_SERVER['HTTP_REFERER']);
		} else { // or registration
			$user = $_SESSION['register'];
			$_SESSION['userID'] = $user2;
		}
	} else if ($_SESSION['register'] != false){ // If the user has a session of register
		$user = $_SESSION['register'];
		$_SESSION['userID'] = $userRegister;

	} else {
		$admin = false;
		$user = false;
	};
} else if ($_SESSION['admin'] == true){	// If currently logged in as admin
	if ($page == 'logout'){
		session_destroy();
		$page = 'index';
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	} else{
		$admin = true;
		$noPageError = $editPageMessage;
	};
};

// Register for training
if($_REQUEST['use'] && $_REQUEST['registerID'] == $contactID){
	$registerEmail = $_REQUEST['use'];
	$registerName = urlencode(preg_replace('/\@/', '-_-' , $_REQUEST['use']));
	$registerBot = $_REQUEST['registerPassword'];
	$registerFullName = urlencode ($_REQUEST["fullName"]);

	if($registerBot != ''){
		$registerError .=  '<p>You are a spambot. Go away!</p>';
	} else {
		if(filter_var($registerEmail, FILTER_VALIDATE_EMAIL)){
		    if(preg_match("/^[A-Z][a-zA-Z\s(\')\. -]+$/", $registerFullName) !== 0){
	            $registerError .=  '<p>Please provide your full name (with only letters).</p>';
	        } else {
    			$to = $registerEmail;
    			$subject = "Encounters Registration Confirmation";
    			$body = 'You have registered for training on the Encounters Website.
Go to the URL below to confirm your registration and begin training:

'.$siteURL .'/?reg='.$registerName.'&regID='.$contactID.'&regName='. $registerFullName .'

Login to the site each time with your name and the following email (no password required):

'. $registerEmail;
    		    $headers   = array();
    			$headers[] = 'From: '. $adminEmail;

    			if (mail($to, $subject, $body, implode("\r\n", $headers))) {
    				$registerSuccess .=  ("<p><strong>Check your email for the registration confirmation. Remember to click on the link in the email to complete your registration.</strong></p>");
    			} else {
    				$registerError .= '<p>We were unable to send your registration via this website. Please try contacting the web site administrator directly at '.$adminEmail.'. Sorry for any inconvenience.</p>';
    			}
	        }
		} else {
			$registerError .= '<p>Please provide a valid email address</p>';
		}
	}
}
// After registration email is sent and confirmed/validated, create user account
if($_REQUEST['reg'] == $_SESSION['register'] && $_REQUEST['regID'] == $contactID){
	$usersEmail = preg_replace('/-_-/', '@' ,$_REQUEST['reg']);
	$usersName = urlencode($_REQUEST['reg']);
	$userFullName = $_REQUEST['regName'];
	if(filter_var($usersEmail, FILTER_VALIDATE_EMAIL)){
	    // Make sure name has only safe characters
	    if(!preg_match("/^[a-zA-Z'-]+$/", $userFullName)){
	        setUser($usersName, 'RegisterNow', $usersEmail, $userFullName);
    		$_SESSION['user'] = true;
    		$_SESSION['userID'] = $usersEmail;
			$_SESSION['userFullName'] = $userFullName;
    		$user = $usersEmail;
    		header('Location: /');
	    } else {
	    	$registerError .= '<p>We are sorry, there was an error setting up your account. Your name appears to have invalid characters in it. Try again, or contact us directly at <a href="mailto:'.$adminEmail.'">'.$adminEmail.'</a>.</p>';
	    }
	} else {
	    $registerError .= '<p>We are sorry, there was an error setting up your account. It appears that the email address you gave is not valid. Try again, or contact the administrator directly at <a href="mailto:'.$adminEmail.'">'.$adminEmail.'</a>.</p>';
	}
}else if($_REQUEST['reg']) {
	 $registerError .= '<p>We are sorry, there was an error setting up your account. Try again, or contact us directly at <a href="mailto:'.$adminEmail.'">'.$adminEmail.'</a>.</p>';
}

// Contact Page
if($page == 'contact' && $_REQUEST['contact'] == $contactID){
	$contactName = $_REQUEST['name'];
	$contactEmail = $_REQUEST['email'];
	$contactBot = $_REQUEST['phone'];
	$contactMessage = strip_tags($_REQUEST['message']);

	if($contactBot != ''){
		$contactError .=  '<p>You are a spambot. Go away!</p>';
	} else {
		if(preg_match("/^[A-Z][a-zA-Z\s(\')\. -]+$/", $contactName) !== 0){
			$contactError .=  '<p>Please provide your full name. (Only letters and spaces)</p>';
		} else {
			if(filter_var($contactEmail, FILTER_VALIDATE_EMAIL)){
				$to = $adminEmail;
				$subject = "Message from ". $contactName ." on the Encounters Website";
				$body = "The following message is from ". $contactName ." on the Encounters Website:\n\n". $contactMessage ."\n\n". $contactName ."\n". $contactEmail;
				if (mail($to, $subject, $body)) {
					$contactSuccess .=  ("<p><strong>The following message was sent successfully. We will try to get back to you soon. Thanks!</strong><br /><br />". $contactMessage ."</p>");
				} else {
					$contactError .= 'We were unable to send your message via this website. Please try contacting the website administrator directly via the email below. Sorry for any inconvenience.';
				}
			} else {
				$contactError .= '<p>Please provide a valid email address</p>';
			}
		}
	}
}


// Set index as default page
if(!$page || $page === false || $page == ''){
	$page = 'index';
}

function getContent($elementID, $pageID){
	$filename = $siteRoot .'pages/'. urlencode($pageID) .'.json';
	if (file_exists($filename)) {
		$result = json_decode(file_get_contents($filename));
		$mainContent = $result->$elementID;
		return $mainContent;
	} else {
		return;
	};
};

function setContent($elementID, $elementValue, $pageID){
	$filename = $siteRoot .'pages/'. urlencode($pageID) .'.json';
	$content = json_decode(@file_get_contents($filename), true);
	$elementValue = str_replace('\"','"',$elementValue);
	$elementValue = str_replace("\'","'",$elementValue);

	if($elementID == 'nav_menu'){
		$content[$elementID] = $elementValue;
	}else{
		$elementValue = str_replace(array("\r", "\n", "  "), ' ', $elementValue);
		$content[$elementID] = trim($elementValue);
	}

	@file_put_contents($filename, json_encode($content));

	if($pageID != 'all_pics'){
		echo getContent($elementID, $pageID);
	};
}

function setUser($elementID, $elementValue, $pageID, $userFullName){	//$usersName,'RegisterNow',$user4, $userFullName
	$filename = '../encountersReg/'. urlencode(preg_replace('/\@/', '-_-' , $pageID)) .'.json';
	$content = json_decode(@file_get_contents($filename), true);
	if($_SESSION['userID']){
		if($content[$userFullName]){
			$content[$userFullName][$elementID] = $elementValue;
		} else {
			$content[$userFullName]['currentSection'] = 1;
			$content[$userFullName]['currentPage'] = 1;
			$content[$userFullName]['currentAssessment'] = false;
			$content[$userFullName]['assessment1'] = false;
			$content[$userFullName]['assessment2'] = false;
			$content[$userFullName]['assessment3'] = false;
			$content[$userFullName]['assessment4'] = false;
			$content[$userFullName]['mail'] = $pageID;
			$content[$userFullName]['fullname'] = $userFullName;
			$_SESSION['userID'] = $pageID;
			$content[$userFullName][$elementID] = $elementValue;
		}
	} else if ($elementValue == 'RegisterNow'){
			$content[$userFullName]['currentSection'] = 1;
			$content[$userFullName]['currentPage'] = 1;
			$content[$userFullName]['currentAssessment'] = false;
			$content[$userFullName]['assessment1'] = false;
			$content[$userFullName]['assessment2'] = false;
			$content[$userFullName]['assessment3'] = false;
			$content[$userFullName]['assessment4'] = false;
			$content[$userFullName]['mail'] = $pageID;
			$content[$userFullName]['fullname'] = $userFullName;
			$_SESSION['userID'] = $pageID;
	};
	@file_put_contents($filename, json_encode($content));
}

function getInfo($elementID, $pageID){
	$filename = '../encountersReg/'. urlencode($pageID) .'.json';
	if (file_exists($filename)) {
		$result = json_decode(@file_get_contents($filename), true);
		$userFullName = $_SESSION['userFullName'];
		if($result[$userFullName]){
			return $result[$userFullName][$elementID];
		} else {
			return;
		}
	} else {
		return;
	}
}

function getUser($userID){
	$filename = '../encountersReg/'. urlencode($userID) .'.json';
	if (file_exists($filename)) {
		$result = json_decode(file_get_contents($filename));
		if($result[$userFullName]){
			return $result[$userFullName];
		} else {
			return;
		}
	} else {
		return;
	};
};

function trackPage($page, $passedQuiz){
	// Track current page
	$pageArray = explode("___", $page);
	if($_SESSION['userID']){
		$userLogin = $_SESSION['userID'];
		$userFullName = $_SESSION['userFullName'];
		if($pageArray[0]){
			if($pageArray[1] == "results"){
				setUser('assessment'. $pageArray[0], $passedQuiz, $userLogin, $userFullName);
				setUser('currentAssessment', false, $userLogin, $userFullName);
			} else if ($pageArray[1]){
				setUser('currentSection', $pageArray[1], $userLogin, $userFullName);
				setUser('currentPage', $pageArray[0], $userLogin, $userFullName);
			} else {
				setUser('currentSection', $pageArray[1], $userLogin, $userFullName);
				setUser('currentPage', '', $userLogin, $userFullName);
			}
		}
	}
}

function survey($survey){
	if($_SESSION['userID']){
		$i = 1;
		while($i <8){
			preg_replace('/\D/', '', $survey[$i]);
			$i++;
		};
		$survey[8] = strip_tags($survey[8]);
		$survey[9] = strip_tags($survey[9]);
		$survey[10] = strip_tags($survey[10]);
		$survey[11] = date("F j, Y, g:i a");

		$filename = fopen("../encountersReg/survey.csv", "a");
		fputcsv($filename, $survey);
		fclose($filename);
	}
}

function archiveContent($pageID){
	$filename = $siteRoot .'pages/'. urlencode($pageID) .'.json';
	$archived = $siteRoot .'archive/'. urlencode($pageID) .'.json';

	$content = json_decode(@file_get_contents($filename), true);
	@file_put_contents($archived, json_encode($content));
	@unlink($filename);
}

function exit_status($str){
	echo json_encode(array('status'=>$str));
	exit;
}

function get_extension($file_name){
	$ext = explode('.', $file_name);
	$ext = array_pop($ext);
	return strtolower($ext);
}

function get_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function uploadContent(){
	$upload_dir = $siteRoot .'uploads/';
	$allowed_ext = array('jpg','jpeg','JPG','JPEG','png','PNG','gif','GIF');
	$all_thumbs = json_decode(@file_get_contents($siteRoot .'pages/all_pics.json'), true);

	if(strtolower($_SERVER['REQUEST_METHOD']) != 'post'){
		exit_status('Error! Wrong HTTP method!');
	}

	if(array_key_exists('pic',$_FILES) && $_FILES['pic']['error'] == 0 ){

		$pic = $_FILES['pic'];
		$picNameFix = array("+", "(", ")", "|", "'", "?", " ");
		$picName = str_replace($picNameFix, '',$pic['name']);

		if(!in_array(get_extension($picName),$allowed_ext)){
			exit_status('Only '.implode(',',$allowed_ext).' files are allowed!');
		}
		// Ensure that file name is unique
		if($all_thumbs[$picName]){
			for ($i = 1; $i <= 20; $i++){
				if ($all_thumbs[$i .'_'. $picName]){
				} else {
					$picName = $i .'_'. $picName;
					break;
				}
			}
		}
		// Move the uploaded file from the temporary directory to the uploads folder,
		// create a thumbnail, and log all pics in json file:
		if(move_uploaded_file($pic['tmp_name'], $upload_dir.$picName)){
			setContent($picName, $upload_dir.$picName, 'all_pics');

			$type=false;
			function open_image ($file) {
				//detect type and process accordinally
				global $type;
				$size=getimagesize($file);
				switch($size["mime"]){
					case "image/jpeg":
						$im = imagecreatefromjpeg($file); //jpeg file
					break;
					case "image/gif":
						$im = imagecreatefromgif($file); //gif file
				  break;
				  case "image/png":
					  $im = imagecreatefrompng($file); //png file
				  break;
				default:
					$im=false;
				break;
				}
				return $im;
			}
			$thumbnailImage = open_image($upload_dir.$picName);

			$w = imagesx($thumbnailImage);
			$h = imagesy($thumbnailImage);

			//calculate new image dimensions (preserve aspect)
			if(isset($_GET['w']) && !isset($_GET['h'])){
				$new_w=$_GET['w'];
				$new_h=$new_w * ($h/$w);
			} elseif (isset($_GET['h']) && !isset($_GET['w'])) {
				$new_h=$_GET['h'];
				$new_w=$new_h * ($w/$h);
			} else {
				$new_w=isset($_GET['w'])?$_GET['w']:60;
				$new_h=isset($_GET['h'])?$_GET['h']:60;
				if(($w/$h) > ($new_w/$new_h)){
					$new_h=$new_w*($h/$w);
				} else {
					$new_w=$new_h*($w/$h);
				}
			}

			$im2 = ImageCreateTrueColor($new_w, $new_h);

			$imageFormat = strtolower(substr(strrchr($picName,"."),1));
			if($imageFormat == "gif" || $imageFormat == "png"){
				imagecolortransparent($im2, imagecolorallocatealpha($im2, 0, 0, 0, 127));
				imagealphablending($im2, false);
				imagesavealpha($im2, true);
			}

			imagecopyResampled ($im2, $thumbnailImage, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
			//effects
			if(isset($_GET['blur'])){
				$lv=$_GET['blur'];
				for($i=0; $i<$lv;$i++){
					$matrix=array(array(1,1,1),array(1,1,1),array(1,1,1));
					$divisor = 9;
					$offset = 0;
					imageconvolution($im2, $matrix, $divisor, $offset);
				}
			}
			if(isset($_GET['sharpen'])){
				$lv=$_GET['sharpen'];
				for($i=0; $i<$lv;$i++){
					$matrix = array(array(-1,-1,-1),array(-1,16,-1),array(-1,-1,-1));
					$divisor = 8;
					$offset = 0;
					imageconvolution($im2, $matrix, $divisor, $offset);
				}
			}
			imagejpeg($im2, $upload_dir.'thumbs/'.$picName);
			imagegif($im2, $upload_dir.'thumbs/'.$picName);
			imagepng($im2, $upload_dir.'thumbs/'.$picName);
			//move_uploaded_file($thumbnailImage, $upload_dir.'thumbs/'.$picName);

			//copy($upload_dir.$picName, $upload_dir.'thumbs/'.$picName);
			exit_status('File was uploaded successfuly!');
		}
	}
	exit_status('Something went wrong with your upload!');
}

function getThumbnails(){
	$dirpath = $siteRoot ."uploads/thumbs/";
	$dh = opendir($dirpath);
	echo '<div id="all_pics_holder">';
	while (false !== ($file = readdir($dh))) {
		if (!is_dir("$dirpath/$file")) {

		   echo '<div class="all_pics"><a class="insertPic" rel="'. $file .'" title="Insert image into page"><img src="'. $dirpath . $file .'" /></a>'. $file .'<br /><a class="insertPicBtn insertPic" rel="'. $file .'" title="Insert image into page">Insert</a></div>';
		}
	}
	echo '</div>';
	closedir($dh);
}


if($deletePage == 'yes' && $page != 'index'){
	if($admin == true){
		archiveContent($page);
	};
}

if($editID){
	if($admin == true && $page != "all_pics"){
		setContent($editID, $editValue, $page);
	};
} elseif ($page == "upload"){
	if($admin == true){
		uploadContent();
	};
} elseif ($page == "thumbnails"){
	if($admin == true){
		getThumbnails();
	};
} else {
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<title><?php
	$pageTitle =  getContent('page_title', $page, $noPageError);
	if($pageTitle != ""){
		echo $pageTitle .' - ';
	}
 	echo getContent('site_title', 'index');
?></title>
<meta name="description" content="<?php echo getContent('site_title', 'index'); ?>" />
<meta name="viewport" content="width=device-width" />
<link rel="icon" href="/favicon.ico?<?php echo get_random_string(); ?>" />
<link rel="shortcut icon" href="/favicon.ico?5" />
<link href="<?php echo $siteRoot ?>css/global.css?<?php echo get_random_string()?>" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="<?php echo $siteRoot ?>videos/player/mediaelementplayer.css" /></code>
<!--[if gte IE 9]>
  <style type="text/css">
    .gradient {
       filter: none;
    }
  </style>
<![endif]-->
<script src="<?php echo $siteRoot ?>js/libs/modernizr-2.5.3.min.js"></script>
<!--[if lt IE 9]>
<script src="<?php echo $siteRoot ?>js/html5shiv.js"></script>
<![endif]-->
</head>
<body id="<?php echo urlencode($page); ?>" rel="<?php echo $contactID; ?>">

    <div id="skipnav">
        <a href="#skip">Skip to main content</a>
        <hr />
    </div>

    <?php
	if($admin == true){
		// Admin bar at top
		echo '<div id="adminBar"><p><a href="#editTitle" id="editTitleBtn">Edit Page Title</a> ';
		if($page != 'index') {
			echo '<a href="#deleter" id="deleterBtn">Delete Page</a> ';
		}
		echo '<a href="#editMenu" id="editMenuBtn">Edit Menu</a> <a href="http://nm.bob.wadholm.com/help" class="menuItem">Help</a> <a style="float: right;" href="'. $siteRoot .'logout">Logout</a></p></div>';
		// Edit Page Title
		echo '<div id="title" class="menuItem"><h2>Page Title: (shows in browser tab)</h2><div class="edit_title" id="page_title">'. getContent('page_title', $page) .'</div></div>';
		// Delete Page
		echo '<div id="deleter" class="menuItem"><h2>Are you sure you want to delete this page?</h2><form method="POST"><p><input type="checkbox" value="yes" name="deletePage" id="deletePage" /> <label for="deletePage"><strong>Yes, delete it!</strong></label><br /><button type="submit">Delete</button><button type="reset" id="cancelDelete">Cancel</button></p></form></div>';
		// Edit Nav Menu
		echo '<div id="editMenu" class="menuItem"><h2>Top Navigation Menu:</h2><div class="edit_menu" id="nav_menu">'. getContent('nav_menu', 'nav_menu') .'</div></div>';
		// Upload image or file
?>
    <div class="menuItem">
        <div id="upload">
            <h2>Upload Image or File</h2>
            <div id="dropbox">
                <span class="message">Drop images here to upload. <br /><i>(Use Chrome or Firefox Browser)</i></span>
            </div>
            <h2>All uploaded files</h2>
            <p><input type="text" id="searcher" placeholder="Search" /></p>

            <div id="all_pics">
            </div><!-- /all_pics -->
        </div><!-- /upload -->
    </div><!-- /menuitem -->
<?php }
// End of Admin bar
?>
	<div id="outsideWrapper">
        <header>
            <div id="logo">
                <a href="<?php echo $siteRoot ?>"><?php echo getContent('site_title', 'index'); ?></a>
            </div>

            <nav id="topNav">
                <?php

				$navValue = getContent('nav_menu', 'nav_menu');
				$navValue = preg_replace('/(href=\")(.*\")/i', '$1'. $siteRoot .'$2 rel="$2', $navValue);
				$navValue = preg_replace('/(href=\")(.*http:)(.*\")/i', '$1http:$3', $navValue);
				echo $navValue;
				?>
            </nav>
        </header>

        <div id="wrapper">
          <div id="insideWrapper">

             <?php if ($page == 'index' || $page == '' || $page == 'index.php'){	?>

                 <div role="homePage" id="home_page_wrapper">
                 	<nav id="innerNav" class="gradient blueGradient">

                        <?php if($_SESSION['user'] != true){?>
                        	<form method="post"><button type="submit" href="#" id="userLogin">Login</button><input type="text" title="Full Name" placeholder="Full Name" required name="nameLogin"><input type="email" title="Email Address" placeholder="email@example.com" required name="Login" /></form><a href="#registrationForm" class="inline cboxElement" id="register">Register for Training</a>
						<?php } else { ?>
                        	<p><a href="<?php echo $siteRoot; ?>logout" class="userLogout">Logout</a></p>
						<?php } ?>
                 	</nav>

                    <div id="contactErrors">
						<?php echo $registerError . $registerSuccess ?>
                    </div>

                 	<div role="home_page" class="edit_area" id="home_page">
                 	 	<?php echo getContent('home_page', 'index'); ?>
                    </div>
                 </div><!-- /home_page_wrapper -->



				<?php }else if($page == 'contact'){ ?>

                <div role="main" id="main_content">
                	<nav id="innerNav" class="gradient blueGradient">

                        <?php if($_SESSION['user'] != true){?>
                        	<p><form method="post"><button type="submit" href="#" id="userLogin">Login</button><input type="text" placeholder="Full Name" title="Full Name" required name="nameLogin"><input type="email" placeholder="email@example.com" title="Email Address" required name="Login" /></form><a href="#registrationForm" class="inline cboxElement" id="register">Register for Training</a></p>
						<?php } ?>

                        <?php if($_SESSION['user'] == true){?>
                        	<p><a href="<?php echo $siteRoot; ?>logout">Logout</a></p>
						<?php } ?>
                 	</nav>

                	<form method="POST" action="contact" id="contactForm">
                    	<div id="contactErrors">
                        	<?php echo $contactError; ?>
                        </div>
                        <?php
						if ($contactSuccess != ''){
							echo '<div id="contactSuccess">'. $contactSuccess .'</div>';
						} else {
							?>

							<div id="contact_content" class="edit_area">
							<?php echo getContent('contact_content', $page); ?>
							</div>

							<div id="phoneContact">
								<p><label for="phone">Please leave this field blank.</label><br />
								<input type="phone" value="" name="phone" />
								<input type="hidden" value="<?php echo $contactID; ?>" name="contact" /></p></div>
							<?php if($admin == false){
								echo '<p><button type="submit" value="Submit">Submit</button></p>';
							}
						}?>
                    </form>
                </div>

                <?php } else if($page == "1___results" || $page == "2___results" || $page == "3___results" || $page == "4___results") {

					include 'results.php'; ?>

                        <div role="home_page" class="edit_area" id="home_page">
                            <div id="results">
                            <?php if($_SESSION['user'] == true){
									trackpage($page, false);

								} else {
									echo "<div id=\"contactErrors\"><p><strong>You are not logged in!</strong> Make sure to log in so that your score can be recorded.</p></div>";
								}

							    echo "<h2>Your score was: ". $resultNum ."%</h2>";

                                if($resultNum == 80){ ?>
									<div class="error" style="border-left: 10px solid #900; color: #900; padding: 10px; margin: 10px 0px; box-shadow: 0px 0px 3px #ccc;" id="corrected" rel-q="<?php echo ($corrected[0]);?>" rel-a="<?php echo ($corrected[1]);?>">Please view your incorrect answer above (Question #<?php echo ($corrected[0]);?>)</div>
								<?php }

								if($resultNum <= 60){
                                    echo '<p>Please review the previous material in this section and take the quiz again.</p><p><a href="'. $prevSection .'" class="prev">Review Section</a></p>';

									if($_SESSION['user'] == true){
										trackpage($page, false);
									}
                                } else if($page != "4___results") {
                                    echo '<progress max="3" value="3"></progress>
<p class="progressor">
    <strong>Progress:&hellip;Completed Module!</strong>
</p>';

									$userLogin = preg_replace('/\@/','-_-', $_SESSION['userID']);
                                    if (getInfo("assessment1", $userLogin) == true && getInfo("assessment2", $userLogin) == true && getInfo("assessment3", $userLogin) == true && getInfo("assessment4", $userLogin) == true){
										echo '<p>Great job! You have completed the training! You may now print a certificate of completion.</p><p><a href="print" class="next">Print</a></p>';

									} else {
										echo '<p>Great job! You are ready to move on to the next section.</p><p><a href="'. $nextSection .'" class="next">Next</a></p>';
									}

									if($_SESSION['user'] == true){
										trackpage($page, true);
									}
                                } else {
									if($_SESSION['user'] == true){
										trackpage($page, true);
									}

									$userLogin = preg_replace('/\@/','-_-', $_SESSION['userID']);
									if (getInfo("assessment1", $userLogin) == true && getInfo("assessment2", $userLogin) == true && getInfo("assessment3", $userLogin) == true && getInfo("assessment4", $userLogin) == true){
										echo '<br /><p id="allDoneNow"><b>Great job! You have completed the training!</b> Before you print your certificate of completion, we would like to ask you several brief questions below about your experience.</p>

										<!--<p><a href="print" class="next">Print Results</a></p>-->

										<br />
<form id="surveyForm" method="POST" action="print" onselectstart="return false;">
    <h1>How was your experience?</h1>
    <p>Please answer these questions about the material you have viewed. We value
        your opinion and will use it to make the website better! Your answers will
        solely be used to improve the site and will not be shared.</p>
    <p class="survey">
        <label for="survey1">The material in this website was relevant to my job:</label>
        <span>
            <input type="radio" name="survey1" value="1">Strongly disagree</span>
        <span>
            <input type="radio" name="survey1" value="2">Disagree</span>
        <span>
            <input type="radio" name="survey1" value="3">Neither agree nor disagree</span>
        <span>
            <input type="radio" name="survey1" value="4">Agree</span>
        <span>
            <input type="radio" name="survey1" value="5">Strongly agree</span>
    </p>
    <p class="survey">
        <label for="survey2">The material in this website was helpful:</label>
        <span>
            <input type="radio" name="survey2" value="1">Strongly disagree</span>
        <span>
            <input type="radio" name="survey2" value="2">Disagree</span>
        <span>
            <input type="radio" name="survey2" value="3">Neither agree nor disagree</span>
        <span>
            <input type="radio" name="survey2" value="4">Agree</span>
        <span>
            <input type="radio" name="survey2" value="5">Strongly agree</span>
    </p>
    <p class="survey">
        <label for="survey3">The material in this website was easy to understand:</label>
        <span>
            <input type="radio" name="survey3" value="1">Strongly disagree</span>
        <span>
            <input type="radio" name="survey3" value="2">Disagree</span>
        <span>
            <input type="radio" name="survey3" value="3">Neither agree nor disagree</span>
        <span>
            <input type="radio" name="survey3" value="4">Agree</span>
        <span>
            <input type="radio" name="survey3" value="5">Strongly agree</span>
    </p>
    <p class="survey">
        <label for="survey4">Overall, I would rate the Walking to the Counter module as:</label>
        <span>
            <input type="radio" name="survey4" value="1">Poor</span>
        <span>
            <input type="radio" name="survey4" value="2">Fair</span>
        <span>
            <input type="radio" name="survey4" value="3">Good</span>
        <span>
            <input type="radio" name="survey4" value="4">Very Good</span>
        <span>
            <input type="radio" name="survey4" value="5">Excellent</span>
    </p>
    <p class="survey">
        <label for="survey5">Overall, I would rate the Exchanging Information module as:</label>
        <span>
            <input type="radio" name="survey5" value="1">Poor</span>
        <span>
            <input type="radio" name="survey5" value="2">Fair</span>
        <span>
            <input type="radio" name="survey5" value="3">Good</span>
        <span>
            <input type="radio" name="survey5" value="4">Very Good</span>
        <span>
            <input type="radio" name="survey5" value="5">Excellent</span>
    </p>
    <p class="survey">
        <label for="survey6">Overall, I would rate the Test module as:</label>
        <span>
            <input type="radio" name="survey6" value="1">Poor</span>
        <span>
            <input type="radio" name="survey6" value="2">Fair</span>
        <span>
            <input type="radio" name="survey6" value="3">Good</span>
        <span>
            <input type="radio" name="survey6" value="4">Very Good</span>
        <span>
            <input type="radio" name="survey6" value="5">Excellent</span>
    </p>
    <p class="survey">
        <label for="survey7">Overall, I would rate the State Reporting module as:</label>
        <span>
            <input type="radio" name="survey7" value="1">Poor</span>
        <span>
            <input type="radio" name="survey7" value="2">Fair</span>
        <span>
            <input type="radio" name="survey7" value="3">Good</span>
        <span>
            <input type="radio" name="survey7" value="4">Very Good</span>
        <span>
            <input type="radio" name="survey7" value="5">Excellent</span>
    </p>

    <p class="survey">
    	<label for="survey8">What should be changed or improved on this website?</label>
    	<textarea name="survey8" rows="4" cols="50"></textarea>
    </p>
    <p class="survey">
    	<label for="survey9">The state of my workplace</label>
    	<select name="survey9">
    		<option value="AL">Alabama</option><option value="AK">Alaska</option><option value="AZ">Arizona</option><option value="AR">Arkansas</option><option value="CA">California</option><option value="CO">Colorado</option><option value="CT">Connecticut</option><option value="DE">Delaware</option><option value="DC">District Of Columbia</option><option value="FL">Florida</option><option value="GA">Georgia</option><option value="HI">Hawaii</option><option value="ID">Idaho</option><option value="IL">Illinois</option><option value="IN">Indiana</option><option value="IA">Iowa</option><option value="KS">Kansas</option><option value="KY">Kentucky</option><option value="LA">Louisiana</option><option value="ME">Maine</option><option value="MD">Maryland</option><option value="MA">Massachusetts</option><option value="MI">Michigan</option><option value="MN">Minnesota</option><option value="MS">Mississippi</option><option value="MO" selected="selected">Missouri</option><option value="MT">Montana</option><option value="NE">Nebraska</option><option value="NV">Nevada</option><option value="NH">New Hampshire</option><option value="NJ">New Jersey</option><option value="NM">New Mexico</option><option value="NY">New York</option><option value="NC">North Carolina</option><option value="ND">North Dakota</option><option value="OH">Ohio</option><option value="OK">Oklahoma</option><option value="OR">Oregon</option><option value="PA">Pennsylvania</option><option value="RI">Rhode Island</option><option value="SC">South Carolina</option><option value="SD">South Dakota</option><option value="TN">Tennessee</option><option value="TX">Texas</option><option value="UT">Utah</option><option value="VT">Vermont</option><option value="VA">Virginia</option><option value="WA">Washington</option><option value="WV">West Virginia</option><option value="WI">Wisconsin</option><option value="WY">Wyoming</option>
		</select>
	</p>
	<p class="survey">
		<label for="survey10">The city of my workplace</label>
		<input type="text" name="survey10">
	</p>
	<p><button type="submit">Submit Survey &amp; Print Certificate of Completion</button></p>
</form>

										<!--<p><a href="print" class="next">Print</a></p><p><a href="print" class="next">Print</a></p>-->';
									}
								}
                            ?>
                            </div>
                    	</div>
				<?php }else { ?>



                 <div role="main" class="edit_area" id="main_content">

                 <?php if(!$admin || $admin != true){ ?>

                 <nav id="innerNav" class="gradient blueGradient">
                    <p>
                    <?php if($_SESSION['user'] != true){?>
                        <form method="post"><button type="submit" href="#" id="userLogin">Login</button><input type="text" placeholder="Full Name" title="Full Name" required name="nameLogin"><<input type="email" placeholder="email@example.com" title="Email Address" required name="Login" />/form><a href="#registrationForm" class="inline cboxElement" id="register">Register for Training</a>
                    <?php } ?>

                    <?php if($_SESSION['user'] == true){?>

                        <?php $pageArray = explode("___", $page);
							if($pageArray[0]== "1"){ ?>
								<a href="/1/1" id="currentHead"><img src="/img/walkingSmall.png" alt="Walking to the counter" title="Walking to the counter" width="38" height="38" /> Walking to the Counter</a>
							<?php } elseif ($pageArray[0]== "2"){ ?>
								<a href="/2/1"  id="currentHead"><img src="/img/informationSmall.png" alt="Information Exchange" title="Information Exchange" width="38" height="38" /> Information Exchange</a>
							<?php } elseif ($pageArray[0]== "3"){ ?>
								<a href="/3/1"  id="currentHead"><img src="/img/visionSmall.png" alt="Vistion Testing" title="Testing" width="38" height="38" />Testing</a>
							<?php } elseif ($pageArray[0]== "4"){ ?>
								<a href="/4/1"  id="currentHead"><img src="/img/reportingSmall.png" alt="State Reporting" title="State Reporting" width="38" height="38" /> State Reporting</a>
							<?php }?>

                        <span id="yourProgress">Your Progress
                        <?php
							$userLogin = preg_replace('/\@/','-_-', $_SESSION['userID']);
							$completed = 0;
						?>
                        	<span><a href="/1/1"><img src="/img/<?php
								if (getInfo("assessment1", $userLogin) === true){
									echo 'check';
									$completed++;
								} else { echo 'noCheck'; }; ?>.png?4" alt="Check box" /> Walking to the Counter</a>
                            <a href="/2/1"><img src="/img/<?php
								if (getInfo("assessment2", $userLogin) === true){
									echo 'check';
									$completed++;
								} else { echo 'noCheck'; }; ?>.png?4" alt="Check box" /> Information Exchange</a>
                            <a href="/3/1"><img src="/img/<?php
								if (getInfo("assessment3", $userLogin) === true){
									echo 'check';
									$completed++;
								} else { echo 'noCheck'; }; ?>.png?4" alt="Check box" /> Testing</a>
                            <a href="/4/1"><img id="assessment4Img" src="/img/<?php
								if (getInfo("assessment4", $userLogin) === true){
									echo 'check';
									$completed++;
								} else { echo 'noCheck'; }; ?>.png?4" alt="Check box" /> State Reporting</a>
								<?php if ($completed === 4){?>
                                	<br />
                                    <a href="/4/print" id="printButton">Print Results</a>
                                <?php } ?>
                            </span>
                        </span>

                        <a href="<?php echo $siteRoot; ?>logout" class="pageLogout">Logout</a>
						<?php } ?>
                        <a href="/4/1/" class="sectionLink"><img src="/img/reportingSmall.png?1" alt="State Reporting" title="Module 4: State Reporting" width="38" height="38" /></a>
                        <a href="/3/1" class="sectionLink"><img src="/img/visionSmall.png" alt="Testing" title="Module 3: Testing" width="38" height="38" /></a>
                         <a href="/2/1" class="sectionLink"><img src="/img/informationSmall.png?2" alt="Information Exchange" title="Module 2: Information Exchange" width="38" height="38" /></a>
                        <a href="/1/1" class="sectionLink"><img src="/img/walkingSmall.png?1" alt="Walking to the counter" title="Module 1: Walking to the counter" width="38" height="38" /></a>
                    </p>
                </nav>
                <?php } ?>

                <?php
						$main_content = getContent('main_content', $page);
						$userLogin = $_SESSION['userID'];
						if($page == '4___print'){
							$userLogin = preg_replace('/\@/','-_-', $_SESSION['userID']);
							$survey = array (
								1 => $_REQUEST['survey1'],
								2 => $_REQUEST['survey2'],
								3 => $_REQUEST['survey3'],
								4 => $_REQUEST['survey4'],
								5 => $_REQUEST['survey5'],
								6 => $_REQUEST['survey6'],
								7 => $_REQUEST['survey7'],
								8 => $_REQUEST['survey8'],
								9 => $_REQUEST['survey9'],
								10 => $_REQUEST['survey10'],
							);

							if($survey[1] != ''){
								survey($survey);
							}

							if ($completed === 4){
							?>
                            <h1>Certificate of Completion</h1>
                            <p><strong><?php echo getInfo("fullname", $userLogin); ?></strong> has successfully completed training on identifying possible medical impairments during the license renewal process. The training is meant to help with the identification of drivers who may have vision, cognitive, or psychomotor impairments and refer them for additional evaluation.</p>

                            <p><img src="/img/check.png?4" alt="Check box"> Walking to the Counter<br />
                            <img src="/img/check.png?4" alt="Check box"> Information Exchange<br />
                            <img src="/img/check.png?4" alt="Check box"> Testing<br />
                            <img src="/img/check.png?4" alt="Check box"> State Reporting</p>
                            <br />
                            <button id="printResults">Print</button>
                        <?php
							} else {
								echo $noPageError;
							}
						} else if ($main_content != ''){
							echo $main_content;
							trackPage($page, false);

						} else {
							echo $noPageError;
						}
                ?>
                </div>
                <?php }	?>
             	<br class="clear" />
         	</div><!-- /insideWrapper -->
  		</div><!-- /wrapper -->
    </div><!-- /outsideWrapper -->



    <footer>
        <div id="copyright">

            <div id="copyrightContainer">
                <div role="footerContent" class="edit_footer" id="footer_content">
                <?php
                       echo getContent('footer_content', 'footer');
                ?>
                </div>
                <br />
                <p>&copy; <?php echo date("Y"); ?>  Missouri Coalition for Roadway Safety<br />
                P.O. Box 270, Jefferson City, MO 65102<br />
                (800) 800-2358, (573) 751-4161<br /><br />
                <?php
                if ($admin == true){
                    echo '<a href="'. $siteRoot .'logout">Admin Logout</a>';
                } else {
                    echo '<a href="#loginForm" class="inline cboxElement" id="login">Admin</a>';
                }
                ?>
                </p>
            </div><!-- /copyrightContainer -->
         </div><!-- /copyright -->
         <div id="loginFormWrapper">
             <div id="loginForm">
                <h2>Admin</h2>
                <form action="" method="post" name="login_form" id="login_form">
                    <?php if ($username && $username != $adminUsername || $password && $password != $adminPassword){ echo $loginError; }; ?>
                    <p><label>Username: </label><input type="text" name="vip" id="vip" /></p>
                    <p><label>Password: </label><input type="password" name="vipp" id="vipp" /></p>
                    <p><input type="submit" value="Submit"/></p>
                </form>
             </div>


             <div id="registrationForm">
                <h2>Register for Training</h2>
                <form action="<?php echo $siteRoot ?>" method="post" name="registration_form" id="registration_form">
                    <p>You will log in with your name and email address, no password required.</p>
                    <p><label for="fullName"><strong>Full Name:</strong> </label><input type="text" name="fullName" id="fullName" placeholder="First &amp; Last Name" required /></p>
                    <p><label for="use"><strong>Email:</strong> </label><input type="email" name="use" id="use" placeholder="email@example.com" required /></p>
                    <div id="phoneContact">
                    	<input type="hidden" value="<?php echo $contactID; ?>" name="registerID" />
                        <label for="registerPassword">Leave this field blank</label>
                        <input type="password" placeholder="Leave this blank" value="" name="registerPassword" /></div>

                    <p><input type="submit" value="Register" class="blueGradient gradient" /> </p>
                    <br />
                    <p><em>You should receive a confirmation email after you click &quot;Register&quot;. Click on the link in the email to complete registration.</em></p>

                    <br /><p>No confirmation email? Resend <a href="">confirmation email</a>.</p>
                </form>
             </div>
         </div>
         <br class="clear" />
    </footer>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script>window.jQuery || document.write('<script src="<?php echo $siteRoot ?>js/libs/jquery-1.7.1.min.js" type="text/javascript"><\/script>')</script>
<script src="<?php echo $siteRoot ?>videos/player/mediaelement-and-player.min.js"></script>
<script src="<?php echo $siteRoot ?>js/plugins.js"></script>
<script src="<?php echo $siteRoot ?>js/script.js?<?php echo get_random_string()?>"></script>
<?php if($admin == true && $page != "all_pics"){ echo '<script src="'. $siteRoot .'js/editable.js?'. get_random_string() .'" type="text/javascript"></script>';}; ?>

<?php
//My Version created July 2019
?>

<script type="text/javascript">
/* Update _setAccount value to user's Google Analytic account and uncomment this script
 var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '//////']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();*/
</script>
</body>
</html>
<?php } ?>
