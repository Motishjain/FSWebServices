<?php

//Dump selected rewards in to Database.
header("Content-Type:application/json");
//$m=new MongoClient();
$m = new MongoClient('mongodb://freddyuser:missionpossible@ds019708.mlab.com:19708/rateus');
$db = $m->rateus;
$collection = $db->rewards_to_outlet;

$rewardsJsonObject = file_get_contents('php://input');

$rewardRequest = json_decode($rewardsJsonObject);

$outlet_cd = $rewardRequest->outletCode;
$r_map = $rewardRequest->rewardsMap;

$input_category = key($r_map);
$selected_rwrds = array();
foreach ($r_map as $k => $v) {
    foreach ($v as $inputreward) {

        array_push($selected_rwrds, $inputreward);
    }
}
$i = 1;
$r_array = array(); //storing as key-value pairs, where keys are r1,r2,r3 and so on and values as received in input
foreach ($selected_rwrds as $val) {
    $reward_primary_key = $val;
    $m_id = new MongoId($reward_primary_key);
    $r_array['r' . $i] = $m_id;
    $i = $i + 1;
}

$filter = array('outletCd' => $outlet_cd);
$cursor = $collection->find($filter);
$b = iterator_to_array($cursor);

$count_of_outlet = $cursor->count();

if ($count_of_outlet == 0) {

    $document = array(
        "outletCd" => $outlet_cd,
        "rewards" => array(array("category" => $input_category, "values" => ($r_array))),
    );
    $collection->insert($document);
    deliver_response(True, "Category inserted - " . $input_category, "Update Successful!");
}

if ($count_of_outlet == 1) {
    //update the categories and rewards in already present document

    $innerfilter = array('outletCd' => $outlet_cd, 'rewards.category' => $input_category);
    $cursor = $collection->find($innerfilter);
    $sll = iterator_to_array($cursor);
    $count = $cursor->count();

    if ($count <> 0) {
        $setupdate = array('$set' => array('rewards.$.values' => $r_array));
        $collection->update($innerfilter, $setupdate);
    }
    if ($count == 0) {
        $data = array('category' => $input_category, 'values' => $r_array);
        $pushupdate = array('$push' => array('rewards' => $data));
        $collection->update($filter, $pushupdate);
    }
    deliver_response(True, "Category updated - " . $input_category, "Update Successful!");
}

function deliver_response($success, $data, $status_message) {
    $response['success'] = $success;
    $response['data'] = $data;
    $response['msg'] = $status_message;
    $json_response = json_encode($response);
    echo $json_response;
}
?>



