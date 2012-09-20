<?php
/*
 * TripleFlap, a flying twitter bird
 *
 * @version:    1.2 (12 Aug 2010)
 * @autor:      Florian Buenzli <http://floern.com/>
 * @copyright:  Florian Buenzli, 2010
 * @licence:    GNU/GPL <http://www.gnu.org/licenses/>
 * @download:   http://floern.com/software/tripleflap
 * @language:   de-ch
 * @charset:    UTF-8
 */

header('Content-Type: text/html');

define('TRIPLEFLAP', 'TripleFlap/1.2 (@http://'.$_SERVER['SERVER_NAME'].'/; +http://floern.com/software/tripleflap)');


// Feed-ID auslesen
$twitterAccount = !empty($_POST['tuac']) ? $_POST['tuac'] : die('invalid Twitter-account');

// ungültige Zeichen im Namen löschen
$twitterAccount = preg_replace('#[^a-zA-Z0-9_]#', '', $twitterAccount);

// Cache-Datei
$tweetCache = 'tweetcache.'.$twitterAccount.'.tmp';
// Dauer des Cachings in Sekunden
$cacheMaxAge = 15*60;
// prüfen, ob Cache noch aktuell
if(file_exists($tweetCache) && filemtime($tweetCache)>=(time()-$cacheMaxAge)){
  $newestTweet = file_get_contents($tweetCache);
  if($newestTweet===false){
    // Fehler beim Lesen der Cache-Datei
	die('Error: could not read Tweet from cache file');
  }
  else{
    // Inhalt ausgeben & fertig
    die($newestTweet);
  }
}


// Feed-URL
$feedUrl = 'http://twitter.com/status/user_timeline/'.$twitterAccount.'.rss';

// Feed mit cURL auslesen
if(function_exists('curl_init')){
  $req = curl_init();
  curl_setopt($req, CURLOPT_URL, $feedUrl);
  curl_setopt($req, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($req, CURLOPT_USERAGENT, TRIPLEFLAP);
  curl_setopt($req, CURLOPT_FOLLOWLOCATION, false);
  curl_setopt($req, CURLOPT_MAXREDIRS, 2);
  curl_setopt($req, CURLOPT_HEADER, false);
  curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
  $feedContent = curl_exec($req);
  if(curl_errno($req)!=0) die('Error: cURL#'.curl_errno($req).': '.curl_error($req));
  if(curl_getinfo($req, CURLINFO_HTTP_CODE)!=200) die('Error: could not read Twitter-feed, HTTP-Code '.curl_getinfo($req, CURLINFO_HTTP_CODE));
  curl_close($req);
}
// sonst Feed mit file_get_contents auslesen
elseif(function_exists('file_get_contents') && (int)ini_get('allow_url_fopen')==1){
  $feedContent = file_get_contents($feedUrl);
  if(!$feedContent) die('Error: could not read Twitter-feed [2]');
}
// sonst Fehler
else{
  die('Error: PHP is not able to access external content');
}


// RSS-Datei auslesen
if(function_exists('simplexml_load_string')){
  $rss = simplexml_load_string($feedContent);
  if(!$rss) die('Error: invalid RSS-Feed');
  
  $newestTweet = 'there is no tweet';
  $newestTweetDate = -1;
  
  // neuster Tweet ermitteln
  foreach($rss->channel->item as $tweet){
	if(($pubdate=strtotime($tweet->pubDate)) > $newestTweetDate){
      $newestTweet = trim($tweet->title);
	  $newestTweetDate = $pubdate;
    }
  }
  // Entity-Fehler von Twitter ausbügeln // htmlspecialchars() ohne < und >
  $newestTweet = preg_replace('#&(?!(lt;|gt;))#', '&amp;', $newestTweet);
  // Multi-Byte-Zeichen als Entites codieren
  $newestTweet = htmlallentities($newestTweet);
}
else{
  $newestTweet = preg_match('#<item.+?<title>([^<]+)</title>#s', $feedContent, $newestTweetTmp) ? trim($newestTweetTmp[1]) : 'there is no tweet [2]';
  // Bug-Fix bzgl. bestimmter Sonderzeichen (u.a. ist '<' als '&amp;lt;' statt '&lt;' codiert)
  $newestTweet = preg_replace('~&amp;(lt|gt|#\d{3,5});~', '&$1;', $newestTweet);
}


// Username wegschneiden
$newestTweet = preg_replace('#^[^:]+:\s#s', '', $newestTweet, 1);

// Links verlinken
$newestTweet = preg_replace_callback('#https?://[^/\s]{4,}(/[^\s]*)?#', 'findUrl', $newestTweet);

// @username verlinken
$newestTweet = preg_replace('#(^|[^a-zA-Z0-9\_])@([a-zA-Z0-9\_]+)#s', '$1@<a href="http://twitter.com/$2" title="@$2" target="_blank">$2</a>', $newestTweet);

// lange Wörter mit &#173; (soft hyphen) trennen
$newestTweet = preg_replace('#(^|>)(([^<]*[[:punct:][:space:]]|)[^[:punct:][:space:]]{15})(?=[^[:punct:][:space:]]{5})#us', '$1$2&#173;', $newestTweet);

// Zeilenumbrüche einfügen
$newestTweet = preg_replace('#(^|[^\s])\s*\n\s*($|[^\s])#s', '$1<br />$2', trim($newestTweet));


// Status-Text ausgeben
echo $newestTweet;


// Cache-Datei aktualisieren
if(!file_put_contents($tweetCache, $newestTweet)) die("\n".'Error: Could not write cache-file');




// Callback-Funktion zur URL-Verlinkung
function findUrl($u){
  $url = htmlspecialchars_decode($u[0]);
  $afterUrl = ''; // Zeichenkette am Ende der URL, die nicht zur URL gehört
  while(preg_match('#[[:punct:]]$#', $url, $found)){
    $chr = $found[0]; // letztes Zeichen
    if($chr==='.' || $chr===',' || $chr==='!' || $chr==='?' || $chr===':' || $chr===';' || $chr==='"' || $chr==="'" || $chr==='>'){
      // Ein Satzzeichen, das nicht zur URL gehört
      $afterUrl = $chr.$afterUrl;
      $url = substr($url, 0, -1);
    }
    elseif($chr===')' && strpos($url, '(')!==false || $chr===']' && strpos($url, '[')!==false || $chr==='}' && strpos($url, '{')!==false)
      break; // Klammer gehört nur zur URL, wenn auch öffnende Klammer vorkommt.
    elseif($chr===')' || $chr===']' || $chr==='}'){
      // .. Klammer gehört nicht zur URL
      $afterUrl = $chr.$afterUrl;
      $url = substr($url, 0, -1);
    }
    elseif($chr==='(' || $chr==='[' || $chr==='{'){
      // öffnende Klammer am Ende gehört nicht zur URL
      $afterUrl = $chr.$afterUrl;
      $url = substr($url, 0, -1);
    }
    else
      break; // Zeichen gehört zur URL
  }
  // Ziel-URL auslesen (z.B. bei Kurz-URLs)
  $targeturl = htmlspecialchars(getTarget($url));
  // URL mit HTML-Code zurückgeben
  return '<a href="'.$targeturl.'" title="'.str_replace('http://', '', $targeturl).'" target="_blank">'.preg_replace('#([^ \-]{22})(?=[^ \-]{8})#', '$1&#173;', $url).'</a>'.$afterUrl;
}

// Abfrage des Location-Headers
function getTarget($url){
  if(!function_exists('curl_init')) return $url;
  $req = curl_init();
  curl_setopt($req, CURLOPT_URL, $url);
  curl_setopt($req, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($req, CURLOPT_USERAGENT, TRIPLEFLAP);
  curl_setopt($req, CURLOPT_FOLLOWLOCATION, false);
  curl_setopt($req, CURLOPT_HEADER, true);
  curl_setopt($req, CURLOPT_NOBODY, true);
  curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
  $resp = curl_exec($req);
  curl_close($req);
  return preg_match('#\sLocation: (https?://[^\s]{4,})#is', $resp, $location) ? $location[1] : $url;
}

// Alle Multibyte-Zeichen als Entity darstellen
function htmlallentities($str){
  $res = '';
  $strlen = strlen($str);
  for($i=0; $i<$strlen; $i++){
    $byte = ord($str[$i]);
    if($byte < 128) // 1-byte char
      $res .= $str[$i];
	elseif($byte < 192); // invalid utf8
	elseif($byte < 224) // 2-byte char
      $res .= '&#'.((63&$byte)*64 + (63&ord($str[++$i]))).';';
	elseif($byte < 240) // 3-byte char
      $res .= '&#'.((15&$byte)*4096 + (63&ord($str[++$i]))*64 + (63&ord($str[++$i]))).';';
	elseif($byte < 248) // 4-byte char
      $res .= '&#'.((15&$byte)*262144 + (63&ord($str[++$i]))*4096 + (63&ord($str[++$i]))*64 + (63&ord($str[++$i]))).';';
  }
  return $res;
}


?>