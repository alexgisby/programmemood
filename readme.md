# Moodometer

A BBC 10% time project around calculating the mood of a show based on the music that they play.

![Demo](http://www.solution10.com/alex/moodometer.png)

## Tech used

* [Silex](http://silex.sensiolabs.org)
* MySQL
* Echonest (sort of, the mood database is pulled in from Echonest)
* Frontend is written with [Bootstrap](http://getbootstrap.com) and [Font-Awesome](http://fortawesome.github.com/Font-Awesome/)

## Installation

You'll need a locally running webserver (I'm using MAMP). Create a database for this project to sit in.

Clone in the project in the normal way. The project has composer files, but composer and the BBC proxies don't
play nice, so I've included all the deps in the repo.

Edit the database config if needed in web/index.php.

I haven't included the full mood lookup database as it's about 24MB and nuked phpMyAdmin when I tried to export
it for inclusion in the repo. Therefore:

* Download and run [the SQL file at the bottom of this page](http://blog.portwd.com/music-research/calculating-artists-moods-using-echonest-api/)
* Run the extra_schema.sql file in this repo.

In your browser, go to http://localhost:8888/analyse and wait for it to complete. This populates the database
will all the stuff from the BBC.

    !! If you're not on Reith (BBC Network), change the BBC\cURL constructor parameter to FALSE.

Go to / in the app and you should have some beautiful stats to look at.
