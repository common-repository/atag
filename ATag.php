<?php
/*
Plugin Name: ATag
Plugin URI: http://scott-herbert.com
Description: Automatically generates tag's for new and updated posts
Version: 1.3
Author: Scott herbert
Author URI: http://scott-herbert.com
*/

if ( is_admin() ){
	// This plugin only runs in the admin area

// Add the admin action
add_action('admin_init','autoTag_admin_init');
add_action('admin_menu', 'autoTag_admin_menu');
}

function autoTag_admin_menu() {
add_options_page('AutoTag', 'Automatic tag generator', 'administrator',
'autoTag-slug', 'autoTag_html_page');
}

function autoTag_html_page() {
?>
<H2>Thank you for installing ATag</h2>
This plugin is free but man cannot live my code alone, so if you've found it useful and you want to say thanks. Please either donates via PayPal or by me something from my Amazon wish list.<table><tr><td><a href="http://www.amazon.co.uk/registry/wishlist/2ER3EQGGIMXBU"><img src="http://t0.gstatic.com/images?q=tbn:ANd9GcTogLJLwj644Y70W6t-qvGP6y7tKhXBCyk6bq1O8z6PxNUAvYgL" alt="amazon" style="border: 0;"></a>
</td><td><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="8103900">
<input type="image" src="http://www.paypal.com/en_GB/i/btn/x-click-butcc-donate.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
</form></td></tr></table>
<hr/>
<?PHP
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	} else {
?>
<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
<table width="510">
<tr valign="top">
<th width="192" scope="row">Maximum number of tags per post</th>
<td width="406">
<input name="autoTag_number" type="text" id="autoTag_number"
value="<?php echo get_option('autoTag_number'); ?>" onkeyup="this.value = this.value.replace (/\D+/, '')" />
(ex. 5)</td>
</tr>
<tr valign="top">
<th width="192" scope="row">Override existing tags</th>
<td width="406">

<input type="radio" name="autoTag_option" value="false" <?PHP if(get_option('autoTag_option')=="false") echo"checked"; ?>/> Yes
<input type="radio" name="autoTag_option" value="true" <?PHP if(get_option('autoTag_option')=="true") echo"checked"; ?>/> No
</td>
</tr>
</table>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="autoTag_number" />
<input type="hidden" name="page_options" value="autoTag_option" />
<p>
<input type="submit" value="<?php _e('Save Changes') ?>" />
</p>

</form>

<?Php
	}
}

function autoTag_admin_init() {
     // hook into save_post action - save our data at the same time the post is saved
     add_action('publish_post','autoTag_save_post');
	 register_setting( 'autoTag-settings-group', 'autoTag_number' );
	 register_setting( 'autoTag-settings-group', 'autoTag_option' );
}

function autoTag_save_post($post_id) {

	$content_post = get_post($post_id);
	$content = $content_post->post_content;
	$content = apply_filters('the_content', $content);
	$rawData = str_replace(']]>', ']]>', $content);
	$rawData = strip_tags($rawData);
	
	$stopwords = array(' ',',','(',')','!','?',"`","\n","”","“");
	
	$rawData = str_replace($stopwords, " ", $rawData);
	$rawData = str_replace("’", "'", $rawData);
	$rawData = preg_replace("/&[A-Za-z0-9]*;/","",$rawData);
	
	// need to strip common words array from the post information at or before this point
	
	//
	// Start of common word list
	//
	
	$wordlist = array("the","a","b","c","d","e","f","g",
"of","and","a","to","in","is","you","that","you’re","h",
"it","he","was","for","on","are","as","with","don't","j",
"his","they","i","at","be","this","have","from","it's",
"or","one","had","by","word","but","not","i'd","k","l","m",
"what","all","were","we","when","your","can","you’d","n",
"said","there","use","an","each","which","she","o","p",
"do","how","their","if","will","up","other","about","q",
"out","many","then","them","these","so","some","her","s",
"would","make","like","him","into","time","has","look","r",
"two","more","write","go","see","number","no","way","t","u",
"could","people","my","than","first","water","been","v","w",
"call","who","oil","its","now","find","long","down","x","y","z",
"day","did","get","come","made","may","part","over","1","2","3",
"new","sound","take","only","little","work","know","4","5","6","7",
"place","year","live","me","back","give","most","very","8","9","0",
"after","thing","our","just","name","good","sentence","you're",
"man","think","say","great","where","help","through","that's",
"much","before","line","right","too","mean","old","you'd",
"any","same","tell","boy","follow","came","want",
"show","also","around","form","three","small","set","put","end",
"does","another","well","large","must","big","even","such","because","turn",
"here","why","ask","went","men","read","need",
"land","different","home","us","move","try","kind","hand","picture",
"again","change","off","play","spell","air","away",
"animal","house","point","page","letter",
"mother","answer","found","study","still","learn","should","america",
"world","high","every","near","add","food","between",
"own","below","country","plant","last","school","father",
"keep","tree","never","start","city","earth","eye",
"light","thought","head","under","story","saw","left",
"don't","few","while","along","might","close","something",
"seem","next","hard","open","example","begin","life",
"always","those","both","paper","together","got",
"group","often","run","important","until","children",
"side","feet","car","mile","night","walk","white",
"sea","began","grow","took","river","four","carry",
"state","once","book","hear","stop","without","second",
"later","miss","idea","enough","eat","face","watch","far",
"indian","really","almost","let","above","girl","sometimes",
"mountain","cut","young","talk","soon","list","song",
"being","leave","family","it's","body","music","color",
"stand","sun","questions","fish","area","mark","dog",
"horse","birds","problem","complete","room","knew",
"since","ever","piece","told","usually","didn't",
"friends","easy","heard","order","red","door","sure",
"become","top","ship","across","today","during","short",
"better","best","however","low","hours","black",
"products","happened","whole","measure","remember",
"early","waves","reached","listen","wind","rock",
"space","covered","fast","several","hold","himself",
"toward","five","step","morning","passed","vowel",
"true","hundred","against","pattern","numeral",
"table","north","slowly","money","map","farm",
"pulled","draw","voice","seen","cold","cried",
"plan","notice","south","sing","war","ground","fall",
"king","town","i'll","unit","figure","certain","field",
"travel","wood","fire","upon");

	//
	// End of common word list
	//

	$rawData = strtolower($rawData);
	
	foreach($wordlist As $current_word){
		$rawData = preg_replace("/\s". $current_word ."\s/"," ",$rawData);
	}
	// $rawData = str_replace($wordlist, " ", $rawData);
	

	// and make tags rawdata lower case
	
	
	$wordsArray = explode(' ',$rawData);

	//$orderedArray = array_count_values($wordsArray);
	
	$orderedArray = array();
	for($counter=0;$counter<count($wordsArray);$counter++){
		//Loop through the array and add to an assocated array
		$thisWord = trim($wordsArray[$counter]);
		if ($thisWord != null){
			if (array_key_exists ( $thisWord , $orderedArray  )){
			
				//Already found at least one of these words. just add one to the value
				$orderedArray[$thisWord] = $orderedArray[$thisWord] +1;
				} else {
				//Doesn't yet exist so set the value to one
				$orderedArray[$thisWord] = 1;
			}
		}
	}		
	// $orderedArray now contains a list of Key=>value where key is the word and value is the number of occurances
	// pull out the keys relating to the n highest values from the array and put them into a commer seperated string
	// where n is the number of tag per post (defult to 5, but should be user defined)

	arsort($orderedArray);
	reset($orderedArray);
	
	
	$newTags = "";
	
	if(get_option('autoTag_number') != ""){
		$numberOfTags = get_option('autoTag_number');
	} else {
		$numberOfTags = 5;
	}
	
	for($counter=0;$counter<$numberOfTags;$counter++){
		if (key($orderedArray) != "") {
			$newTags = $newTags . key($orderedArray) . ", ";
		}
		next($orderedArray);
	}
	trim($newTags,' ');
	trim($newTags,',');
	
	if(get_option('autoTag_option') != ""){
		$replace = get_option('autoTag_option');
	} else {
		$replace = "true";
	}
	
	wp_set_post_tags( $post_id, $newTags, $replace );
	
}