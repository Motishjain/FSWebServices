<?php
    echo "hey";
    $connection = new Mongo('mongodb://freddyuser:missionpossible@ds019708.mlab.com:19708/rateus');
    $database   = $connection->selectDB('rateus');
    $collection = $database->selectCollection('userinfo');
    $cursor = $collection->find();
    echo 'Hello';
?>