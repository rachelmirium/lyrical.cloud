<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require './APIManager.php';
require './CacheManager.php';

# define new api manager
$api = new APIManager();

# define new cache manager
$cache = new CacheManager();

# define new slim app router and pass ip middleware
$router = new \Slim\App;
$router->add(new RKA\Middleware\IpAddress(false));

# get word cloud for given artist
$router->get('/api/wordcloud/new/{artist}', function (Request $request, Response $response) use ($api, $cache) {
	# get and sanitize params
    $artist = $request->getAttribute('artist');
    $artist = str_replace(' ', '%20', trim($artist));
    
    # query api through manager
    $songs = $api->get_songs($artist);

    # compute frequency through helper
    $overall_freq = array();
    $overall_freq_formatted = $api->parse_all_lyrics($songs, $overall_freq, $cache);

    # new response to return json
	$res = $response->withJson($overall_freq_formatted)->withHeader('Access-Control-Allow-Origin', 'http://localhost:8081');
	return $res;
});

# get word cloud for merged set of artists (can be merged into previous route)
$router->get('/api/wordcloud/merge/{artist}', function (Request $request, Response $response) use ($api, $cache) {
	# get and sanitize params
    $artist = $request->getAttribute('artist');
    $artist = str_replace(' ', '%20', $artist);

    # query api through manager
    $songs = $api->get_songs($artist);

    # compute merged frequencies through helper
    $overall_freq = array();
    $api->merge_all_lyrics($songs, $old_freq, $overall_freq);
});

# get lyrics for a song 
$router->get('/api/lyrics/{artist}/{song}', function (Request $request, Response $response) use ($api) {
	# get params
	$artist = $request->getAttribute('artist');
	$song = $request->getAttribute('song');

	# query api through manager
	$lyrics = $api->get_lyrics($artist, $song);

	# new response to return json
	$res = $response->withJson($overall_freq_formatted)->withHeader('Access-Control-Allow-Origin', 'http://localhost:8081');
	return $res;
});

# get  suggestions for the search bar's dropdown
$router->get('/api/dropdown/suggestions/{search}', function (Request $request, Response $response) use ($api) {
	# get params
	$search = $request->getAttribute('search');
	$search = str_replace(' ', '%20', trim($search));

	# query api through manager
	$suggestions = $api->get_search_suggestions($search);

	# new response to return json
	$res = $response->withJson($suggestions)->withHeader('Access-Control-Allow-Origin', 'http://localhost:8081');
	return $res;
});

# run routing server
$router->run();