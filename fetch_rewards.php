<?php

//Fetch rewards for a particular outlet type!
header("Content-Type:application/json");
$m = new MongoClient('mongodb://freddyuser:missionpossible@ds019708.mlab.com:19708/rateus');
$db = $m->rateus;
$collection = $db->rewards;
$outlet_cat = $_GET['outletType'];

if (!empty($outlet_cat)) {
    //check record in mongo    
    // check if there are rewards configured for this outlet_type
    $outlet_type_query = array("outletType" => $outlet_cat);
    $cursor = $collection->find($outlet_type_query);
    $list = array(); //this list will be passed as final response to JSON object.
    $outlet_not_found = True;
    foreach ($cursor as $doc) {
        if ($doc['outletType'] == $outlet_cat) {
            
            $reward = array();

            $p = new StdClass();
            $p->id = $doc['_id']->{'$id'};
            $p->name = $doc['name'];
            $p->image = $doc['image'];
            $p->cost = $doc['cost'];
            $p->level = $doc['level'];
            $outlet_not_found = False;
            array_push($list, $p);
        }
    }
    if (!$outlet_not_found) {
        deliver_response(True, $list, "Success! rewards found for Outlet type");
    } else {
        deliver_response(False, $outlet_cat, "no rewards found for outlet type");
    }
} else {
    //throw invalid input error
    deliver_response(False, NULL, "Something went wrong, could not perform the server option");
}

function deliver_response($status, $data, $status_message) {
    $json_response = json_encode($data);
    echo $json_response;
}

?>