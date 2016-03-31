<?php

//Dump Resisration Details in to Database.
header("Content-Type:application/json");
//$m=new MongoClient();
$m = new MongoClient('mongodb://freddyuser:missionpossible@ds019708.mlab.com:19708/rateus');
//echo "connection to db set successfully!";
$db = $m->rateus;
//echo "Database rateus selected!";
$collection = $db->feedback;
//echo "Collection outlet selected!";


$feedbackJsonObject = file_get_contents('php://input');

$feedback = json_decode($feedbackJsonObject);

$outlet_cd = $feedback->outletCode;
$billNo = $feedback->billNumber;
$billAmt = $feedback->billAmount;
$userPhone = $feedback->userPhoneNumber;
$userName = $feedback->userName;
$qsnRatingMap = $feedback->ratingsMap;
$rewardId = $feedback->rewardId;
$rewardCategory = $feedback->rewardCategory;

$qsnRatingArrayInput = array();
$qsnRatingArrayInput = $qsnRatingMap;
$qsnRatingArrayOutput = array();
$i = 1;
foreach ($qsnRatingArrayInput as $qsn => $rating) {

    ${'qsnRating' . $i} = array();
    $qsnId = new MongoId($qsn);
    ${'qsnRating' . $i}['qsn' . $i] = $qsnId;
    ${'qsnRating' . $i}['rating' . $i] = $rating;
    $i = $i + 1;
}
$listarray = array();

for ($m = 1; $m < $i; $m++) {
    array_push($listarray, ${'qsnRating' . $m});
}

if (
        (!empty($outlet_cd)) and ( !empty($userPhone)) and ( !empty($billNo)) and ( !empty($billAmt))
) {

    $filter = array('outletCd' => $outlet_cd);

    $cursor = $collection->find($filter);
    $atleast_one_feedback = FALSE;
    foreach ($cursor as $doc) {
        if (isset($doc['feedback'])) {

            $atleast_one_feedback = TRUE;
        }
    }



    $autoId = new MongoId();
    if (!empty($rewardId)) {
        $rewardMongoId = new MongoId($rewardId);
    } else {
        $rewardMongoId = $rewardId;
    }

    date_default_timezone_set('Asia/Calcutta');
    $cdate = date('Y-m-d h-i-s');

    $data = array("_id" => $autoId,
        "userName" => $userName,
        "mobile" => $userPhone,
        "billNo" => $billNo,
        "billAmount" => $billAmt,
        "rewardCategory" => $rewardCategory,
        "rewardId" => $rewardMongoId,
        "ratings" => $listarray,
        "createDate" => $cdate
    );


    if ($atleast_one_feedback) {


        $pushAndSet = array('$push' => array('feedback' => array($data)), '$set' => array("updateDate" => $cdate));

        $collection->update($filter, $pushAndSet);


        deliver_response(True, " ", "Feedback received. Thank You");
    } else {

        $document = array("outletCd" => $outlet_cd, "CreateDate" => $cdate, "updateDate" => "", "feedback" => array($data));

        $collection->insert($document);


        deliver_response(True, " ", "Feedback received. Thank You");
    }
}

function deliver_response($success, $data, $status_message) {
    $response['success'] = $success;
    $response['data'] = $data;
    $response['msg'] = $status_message;
    $json_response = json_encode($response);
    echo $json_response;
}

?>