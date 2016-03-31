<?php

//Dump Resisration Details in to Database.
header("Content-Type:application/json");
//$m=new MongoClient();
$m = new MongoClient('mongodb://freddyuser:missionpossible@ds019708.mlab.com:19708/rateus');

$db = $m->rateus;

$collection = $db->outlet;


$outletJsonObject = file_get_contents('php://input');
$outletRequest = json_decode($outletJsonObject);

error_log("request=" . $outletJsonObject);
$outletCode = $outletRequest->outletCode;
$name = $outletRequest->outletName;
error_log("name=" . $name);

$alias_name = $outletRequest->aliasName;

$address_line1 = $outletRequest->addrLine1;

$address_line2 = $outletRequest->addrLine2;

$pincode = $outletRequest->pinCode;

$email = $outletRequest->email;

$work_phone_number = '';

$mobile_number = $outletRequest->cellNumber;

$outlettype = $outletRequest->outletType;

if (empty($outletCode)) {
    //new outlet registration process...
    if (
            (!empty($name)) and ( !empty($alias_name)) and ( !empty($address_line1)) and ( !empty($address_line2)) and ( !empty($pincode)) and ( !empty($email)) and ( !empty($mobile_number)) and ( !empty($outlettype))
    ) {
        $not_to_insert = False;
        // check if the same outlet already exists on server, checking condition is if same pincode for same outlet name
        $same_retailer_query = array("name.outletname" => $name);
        $cursor = $collection->find($same_retailer_query);
        $i = 1;
        foreach ($cursor as $doc) {

            $address_array = $doc['address'];

            if ($address_array['pincode'] == $pincode) {
                $not_to_insert = True;
                //$primary_key=$doc['_id']->{'$id'};
                $outletCd = $doc['outletCd'];
            }

            $i = $i + 1;
        }


        if ($not_to_insert == False) {
            $outletCd = uniqid();
            date_default_timezone_set('Asia/Calcutta');
            $cdate = date('Y-m-d h-i-s');

            $document = array(
                "name" => (object) array("outletname" => $name, "alias" => $alias_name),
                "address" => (object) array("line1" => $address_line1, "line2" => $address_line2, "pincode" => $pincode),
                "email" => $email,
                "phonecontact" => (object) array("work" => $work_phone_number, "mobile" => $mobile_number),
                "outletTypecd" => $outlettype,
                "outletCd" => $outletCd,
                "createDate" => $cdate,
                "updateDate" => ""
            );
            $collection->insert($document);

            deliver_response(True, $outletCd, "Success! We have received your details, we will verify and approve it at earliest. Thank You");
        } else {
            deliver_response(False, $outletCd, "Sorry, We already have your details in our system!");
        }
    } else {
        //echo 'something missing!';
        deliver_response(False, "Some Data is missing", "Sorry, We cannot process your request at the moment!");
    }
} else {
    // outlet information is getting updated here....
    if (
            (!empty($name)) and ( !empty($alias_name)) and ( !empty($address_line1)) and ( !empty($address_line2)) and ( !empty($pincode)) and ( !empty($email)) and ( !empty($mobile_number)) and ( !empty($outlettype)) and ( !empty($outletCode))
    ) {
        $existing_retailer = array("outletCd" => $outletCode);
        $cursor = $collection->find($existing_retailer);

        date_default_timezone_set('Asia/Calcutta');
        $setupdate = array('$set' => array('name.outletname' => $name, 'name.alias' => $alias_name, 'address.line1' => $address_line1, 'address.line2' => $address_line2, 'address.pincode' => $pincode, 'email' => $email, 'phonecontact.work' => $work_phone_number, 'phonecontact.mobile' => $mobile_number, 'outletTypecd' => $outlettype, 'updateDate' => date('Y-m-d h-i-s')));

        $collection->update($existing_retailer, $setupdate);


        deliver_response(True, $outletCode, "Success! Outlet changes updated!");
    } else {
        //echo 'something missing!';
        deliver_response(False, "Some Data is missing", "Sorry, We cannot update your details at the moment!");
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