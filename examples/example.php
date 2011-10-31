<?php

// Copy the configuration file from "/config/eventbrite.php" to "fuel/app/config/eventbrite.php"
// Fill in your API key and user_key, the config file contains url's where you can request them.

// Initiate the wrapper
$eb = \Eventbrite::forge();

// Load events list
$result = $eb->user_list_events();
foreach( $result->events as $e )
    echo $e->event->id . "\t" . $e->event->title . "\n";

// Load event list by organizer (id is a required value, otherwise the wrapper will give an error)
$result = $eb->organizer_list_events(array('id' => '1620186468'));
foreach( $result->events as $e )
    echo $e->event->id . "\t" . $e->event->title . "\n";

?>