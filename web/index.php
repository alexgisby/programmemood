<?php

require_once __DIR__.'/../vendor/autoload.php';
ini_set('display_errors', 'ON');

define('BASEDIR', __DIR__ . '/..');

$app = new Silex\Application();
$app['debug'] = true;

/**
 * Register services
 */
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => 'mood_data',
        'user'      => 'root',
        'password'  => 'root',
        'charset'   => 'utf8',
    ),
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => BASEDIR.'/app/views',
));

BBC\TinyORM::$app = &$app;

/**
 * Runs the analysis and generates the mood for a show
 */
$app->get('/analyse', function() use ($app) {
    
    $show_pids = array(
        'b0072lb2', 'b01mrh21', 'b006wr3p', 'b00p2dfq',
        'b0080x5m', 'b006wkth', 'b00c000j', 'b0100rp6'
    );

    $curl = new BBC\cURL(true);
    foreach($show_pids as $pid)
    {
        $url = 'http://www.bbc.co.uk/programmes/' . $pid . '.json';
        $pdata = $curl->request($url);
        
        // Insert the programme data into the db:
        $programme = new BBC\TinyORM('bbc_programmes', array(
            'pid' => $pdata->programme->pid,
            'title' => $pdata->programme->title,
            'service_name' => $pdata->programme->ownership->service->title,
            'service_key' => $pdata->programme->ownership->service->key,
            'service_id' => $pdata->programme->ownership->service->id,
            'image' => $pdata->programme->image->filename,
            'angry' => 0,
            'excited' => 0,
            'happy' => 0,
            'relaxing' => 0,
            'sad' => 0,
        ));

        // Fetch the episodes for this show in January this year:
        $episodes_url = 'http://www.bbc.co.uk/programmes/' . $programme->pid . '/episodes/2013/01.json';
        $edata = $curl->request($episodes_url);
        foreach($edata->broadcasts as $i => $broadcast) 
        {
            $ep = new BBC\TinyORM('bbc_episodes', array(
                'pid' => $broadcast->programme->pid,
                'parent_pid' => $programme->pid,
                'date' => date('Y-m-d H:i:s', strtotime($broadcast->start)),
                'synopsis' => $broadcast->programme->short_synopsis,
                'angry' => 0,
                'excited' => 0,
                'happy' => 0,
                'relaxing' => 0,
                'sad' => 0,
            ));

            // Go and find tracks for this episode:
            $segments_url = 'http://www.bbc.co.uk/programmes/' . $ep->pid . '/segments.json';
            $sdata = $curl->request($segments_url);
            if($sdata)
            {
                foreach($sdata->segment_events as $segment)
                {
                    if($segment->segment->type == 'music')
                    {
                        $track = new BBC\TinyORM('bbc_segments', array(
                            'pid' => $segment->pid,
                            'episode_pid' => $ep->pid,
                            'artist' => $segment->segment->artist,
                            'track' => $segment->segment->track_title,
                        ));

                        // Go and find the artist echonest ID:
                        $artist = $app['db']->fetchAssoc(
                            'SELECT * FROM artists WHERE artist_name = ? LIMIT 1'
                        , array($track->artist));

                        if($artist)
                        {
                            // We only bother saving if we have an artist. No point otherwise.
                            $track->artist_echonest_id = $artist['echonest_id'];

                            // Now loop through the emotions and see if we have a rating on them:
                            $emosh_tables = array('angry', 'excited', 'happy', 'relaxing', 'sad');
                            foreach($emosh_tables as $table)
                            {
                                $emosh = $app['db']->fetchAssoc(
                                    'SELECT * FROM ' . $table . ' WHERE echonest_artist_id = ?'
                                , array($track->artist_echonest_id));

                                if($emosh) {
                                    $track->$table = $emosh['lastmood_index'];
                                }
                            }

                            $track->save();

                            // Update the running totals on the episode:
                            $ep->angry += $track->angry;
                            $ep->excited += $track->excited;
                            $ep->happy += $track->happy;
                            $ep->relaxing += $track->relaxing;
                            $ep->sad += $track->sad;
                        }
                    }
                }
            }

            // Save the episode now all the running totals are there:
            $ep->save();

            // Update the running totals on the programme:
            $programme->angry += $ep->angry;
            $programme->excited += $ep->excited;
            $programme->happy += $ep->happy;
            $programme->relaxing += $ep->relaxing;
            $programme->sad += $ep->sad;
        }

        // Save the programme with the new running totals:
        $programme->save();

    }

    return 'All Done!';

});

/**
 * Homepage of the app
 */
$app->get('/', function() use ($app) {

    // Fetch all of the programmes;
    $programmes = $app['db']->fetchAll(
        'SELECT * FROM bbc_programmes ORDER BY service_key ASC, title ASC'
    );

    // Calculate the responses:
    $emosh_cols = array('angry', 'excited', 'happy', 'relaxing', 'sad');
    foreach($programmes as &$programme)
    {
        $programme['max'] = 0;
        $programme['sum'] = 0;
        foreach($emosh_cols as $emosh)
        {
            if($programme[$emosh] > $programme['max'])
            { $programme['max'] = $programme[$emosh]; }

            $programme['sum'] += $programme[$emosh];
        }
    }

    return $app['twig']->render('home.twig', array(
        'programmes' => $programmes
    ));
});

/**
 * Examining a single programme
 */
$app->get('/{programme_pid}', function($programme_pid) use($app) {
    return $programme_pid;
});

$app->run();
