<?php
header("Content-Type:application/json");
//$m=new MongoClient();
$m = new MongoClient('mongodb://freddyuser:missionpossible@ds019708.mlab.com:19708/rateus');
echo "connection to db set successfully!";
$db=$m->rateus;
echo "Database rateus selected!";
$collection=$db->outlet;
echo "Collection outlet selected!";

//fetch primary key and code from webservice and update the code inside outlet collection.
$pkey=$_POST['primaryKey'];
$outletCd=$_POST['outletCd'];
if(!empty($pkey))
{
        $primary_obj_key=new MongoId($pkey);
        $filter = array('_id'=>$primary_obj_key);
        $cursor=$collection->find($filter);
        $doc=$cursor;
        foreach ($cursor as $doc)
        {
            if (isset($doc['outletCd']))
            {
                $existing_cd=$doc['outletCd'];
                $already_some_outlet_cd=TRUE;
            }
        }
        if(!$already_some_outlet_cd)
        {
        $setupdate =array('$set'=>array('outletCd'=>$outletCd));
          
        $collection->update($filter,$setupdate);
        deliver_response(True,"cd = ".$outletCd, "Approved!");       
        }
        else
        {
            deliver_response(False,$existing_cd, "Already approved and some code is assigned!.Sorry,Cannot Approve again.");
        }
}
else
{
    deliver_response(False,"dont have data to display", "Cant Approve at the momemt, some issue!");       
}



function deliver_response($status,$data, $status_message)
{
header("HTTP/1.1 $status $status_message");
$response['status']=$status;
$response['data']=$data;
$response['status_message']=$status_message;
$json_response=json_encode($response);
echo $json_response;
}

?>