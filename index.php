<?php

class userInfo {

	public function getUserInfo($token) {
		
		// makes a call for the authorized user's information.
		$response = file_get_contents("https://api.dailymile.com/people/me.json?oauth_token=".$token);
		
		// decodes json formatted user information into object and returns it.
		$info = json_decode($response);
		return $info;
	}
}

class workoutList {

	public function showWorkouts($un,$woType,$pages) {
		echo '<h1 style="text-decoration: underline;">'.$woType.' Workouts</h1>';
		for($i=1;$i<=$pages;$i++) {
			
			// takes username and #pages passed to function, and uses them to request posts from Dailymile. each page returns 20 posts with details.
			$response = file_get_contents('http://api.dailymile.com/people/'.$un.'/entries.json?page='.$i);
			
			// decodes json respones into a PHP object.
			$entries = json_decode($response);
			
			// for every entry in the object, get the details and print them with some cool html to make some things link back to DM 
			foreach($entries->entries as $wo) {
				if($wo->workout->activity_type === $woType) {
					$id = $wo->id;
					$time = $wo->at;
					$url = $wo->url;
					$title = $wo->workout->title;
					$type = $wo->workout->activity_type;
					$dist = $wo->workout->distance->value;
					$dunits = $wo->workout->distance->units;
					$dur = floor($wo->workout->duration/60)."m ".((($wo->workout->duration/60)-floor($wo->workout->duration	/60))*60)."s";
					echo '<h2 style="margin: 0px;">'.$time.' "<a href="'.$url.'">'.$title.'</a>" '.$dist.' '.$dunits.' in '.$dur.'</h2>';
					
					// if there are comments, get their details from the objects and print them out nicely.
					if(isset($wo->comments)) {
						echo '<div style="padding: 10px 0 20px 10px;">';
						
						// for each comment, print out the author and comment, and any motivation they sent. make the name clickable link back to authors profile on DM.
						foreach($wo->comments as $c) {
							echo '<strong><a href="'.$c->user->url.'">'.$c->user->display_name.'</a></strong> (<em>'.$c->user->username.'</em>): '.$c->body.' ';
							if(isset($c->motivation)) { echo '<div style="padding-left: 20px;"><strong>Motivation -> '.$c->motivation->title.'</strong></div>'; } else { echo '<br />'; }
						}
						echo '</div>';
					}	
				}	
			}
		}
		echo '<br />';
	}
}

// check for authentication cookie and if exists, call for user informaiton and list workouts
if($_COOKIE['dmauth']) {
	
	// instantiates userData object as a member of userInfo class.
	$userData = new userInfo;
	
	// gets oauth token from cookie stored by OAuth2 Javascript in dmOauth2.html and calls for the function that gets the user authorized user's information from DM.
	$user = $userData->getUserInfo($_COOKIE['dmauth']);
	
	// stores authrozied user's key details in easy-to-use variables, then prints the image, and a few other details nicely at the top of the page.
	$userUrl = $user->url;
	$userPhoto = $user->photo_url;
	$displayname = $user->display_name;
	$username = $user->username;
	$location = $user->location;
	echo '<a href="'.$userUrl.'"><img src="'.$userPhoto.'" style="margin: 0 5px 0 5px;float: left;" />';
	echo '<span style="font-size: 16pt;"><strong>'.$displayname.'</strong></div></a> ('.$username.')<br />'.$location.'<br />';
	
	echo '<div style="padding: 10px; clear: both;">';
	
	// instantiates object in workoutList class.
	$running = new workoutList;
	
	// calls function to perform API call, decode JSON response, and display list of running workouts.
	$running->showWorkouts($user->username,"Running","1");
	
	// same as above for cycling workouts this time.
	$cycling = new workoutList;
	$cycling->showWorkouts($user->username,"Cycling","1");
	
	//same as above for fitness workouts this time.
	$fitness = new workoutList;
	$fitness->showWorkouts($user->username,"Fitness","1");
	
	echo '</div>';
	
	}
else {
	// if no cookie, prompts user to click link to proceed with authentication.
	echo 'Ouch! Not Authorized. Click <a href="dmOauth2.html">here</a> to authorize access to your Dailymile Account.';
	}
?>