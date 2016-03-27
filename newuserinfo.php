<?php
//insert new user information in database. It inserts only if it doesnt not finds the same mobile number already present in the database!.It user is inserted, gives appropriate message and if user is existing, gives appropriate message.
header("Content-Type:application/json");
//$m=new MongoClient();
$m = new MongoClient('mongodb://freddyuser:missionpossible@ds019708.mlab.com:19708/rateus');
echo "connection to db set successfully!";
$db=$m->rateus;
echo "Database test selected!";
$collection=$db->userinfo;
echo "Collection Rating selected!";
if(!empty($_POST['mobile'])){
    //check record in mongo
    $mb=$_POST['mobile'];
    $name=$_POST['name'];
    $not_to_insert=False;
// check if it already exists on server
    $cursor=$collection->find();
    foreach($cursor as $doc)
        {
        if ($doc['mobile']==$mb)
        $not_to_insert=True;
        }
    if(!$not_to_insert)
    {
        $document=array(
        "name"=>$name,
        "mobile"=>$mb);
    $collection->insert($document);
    echo "document inserted!";
    deliver_response(True, "User Inserted Successfully", $mb);
    }
    else
    {
        deliver_response(True, "Not Inserted, User already Exists in system", $mb);//i can send u back the user id as we need at in feedback step
    }
}
else
{
    //throw invalid mobile number
   
   deliver_response(False,"Something went wrong, could not insert!", NULL);
}

function deliver_response($status, $status_message, $data)
{
header("HTTP/1.1 $status $status_message");
$response['status']=$status;
$response['status_message']=$status_message;
$response['data']=$data;
$json_response=json_encode($response);
echo $json_response;

}
//Read document from mongodb
//Delete document from mongodb
?>