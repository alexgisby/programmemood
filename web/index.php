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

BBC\TinyORM::$app = &$app;

/**
 * Runs the analysis and generates the mood for a show
 */
$app->get('/analyse', function() use ($app) {
    
    $show_pids = array(
        'b0072lb2', 'b01mrh21', 'b006wkb6', 'b006wkt4', 
        'b0080x5m', 'b006wkth', 'b00c000j', 'b0100rp6'
    );

    $curl = new BBC\cURL(true);
    foreach($show_pids as $pid)
    {
        $url = 'http://www.bbc.co.uk/programmes/' . $pid . '.json';
        $pdata = $curl->request($url);
        
        // Insert the programme data into the db:
        $prog_data = array(
            'pid' => $pdata->programme->pid,
            'title' => $pdata->programme->title,
            'service_name' => $pdata->programme->ownership->service->title,
            'service_key' => $pdata->programme->ownership->service->key,
            'service_id' => $pdata->programme->ownership->service->id,
            'image' => $pdata->programme->image->filename,
        );

        $programme = new BBC\TinyORM('bbc_programmes', $prog_data);
        // $programme->save();

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
            ));
            
            // $ep->save();

            // Go and find tracks for this episode:
            
        }

    }

    return 'All Done!';

});

$app->get('/', function(){
    return 'Hello World';
});

$app->run();
