<?php
//Dump Resisration Details in to Database.
header("Content-Type:application/json");
//$m=new MongoClient();
$m = new MongoClient('mongodb://freddyuser:missionpossible@ds019708.mlab.com:19708/rateus');
//echo "connection to db set successfully!";
$db=$m->rateus;
//echo "Database rateus selected!";
$collection=$db->outlet;
//echo "Collection outlet selected!";


$feedbackJsonObject = file_get_contents('php://input');

$feedback = json_decode($feedbackJsonObject);

$outlet_cd=$feedback->outletCode;
$billNo=$feedback->billNumber;
$billAmt=$feedback->billAmount;
$userPhone=$feedback->userPhoneNumber;
$userName=$feedback->userName;
$qsnRatingMap=$feedback->ratingsMap;
$rewardId=$feedback->rewardId;
$rewardCategory=$feedback->rewardCategory;

$qsnRatingArrayInput=array();
$qsnRatingArrayInput=$qsnRatingMap;
$qsnRatingArrayOutput=array();
$i=1;
foreach($qsnRatingArrayInput as $qsn=>$rating)
{
    
    ${'qsnRating'.$i}=array();
    $qsnId=new MongoId($qsn);
    ${'qsnRating'.$i}['qsn'.$i]=$qsnId;
    ${'qsnRating'.$i}['rating'.$i]=$rating;
    $i=$i+1;
}        
        $listarray=array();
 
        for($m=1; $m<$i;$m++)
        {
          array_push($listarray,${'qsnRating'.$m});
        }
 
if(
(!empty($outlet_cd)) and (!empty($userPhone)) and (!empty($billNo)) and (!empty($billAmt))
)
{
            
        $filter = array('outletCd'=>$outlet_cd);
       
        $cursor=$collection->find($filter);
        $atleast_one_feedback=FALSE;
        foreach($cursor as $doc)
            {
                if (isset($doc['feedback']))
                {   
                    
                    $atleast_one_feedback=TRUE;
                }
            }
        
        
        if($atleast_one_feedback){
        $autoId=new MongoId();
		if(!empty($rewardId))
	            {$rewardMongoId=new MongoId($rewardId);}
        	else {$rewardMongoId=$rewardId;}                   
        $data=array("_id"=>$autoId,
            "userName"=>$userName,
            "mobile"=>$userPhone,
            "billNo"=>$billNo,
            "billAmount"=>$billAmt,
            "rewardCategory"=>$rewardCategory,
            "rewardId"=>$rewardMongoId,
            "ratings"=>$listarray
                   );
                   
        $pushupdate =array('$push'=>array('feedback'=>$data));
        $collection->update($filter,$pushupdate);
        deliver_response(True,"", "Feedback received. Thank You");
        }
        else{
            $autoId=new MongoId();
        	if(!empty($rewardId))
	            {$rewardMongoId=new MongoId($rewardId);}
	        else {$rewardMongoId=$rewardId;}
		
            $data=array("_id"=>$autoId,
            "userName"=>$userName,
            "mobile"=>$userPhone,
            "billNo"=>$billNo,
            "billAmount"=>$billAmt,
            "rewardCategory"=>$rewardCategory,
            "rewardId"=>$rewardMongoId,
            "ratings"=>$listarray
                   );
                   
            $setquery =array('$set'=>array('feedback'=>array($data))); 
            $collection->update($filter,$setquery);
            deliver_response(True,"","Feedback received. Thank You");
            
            }
        
}
function deliver_response($success,$data, $status_message)
{
$response['success']=$success;
$response['data']=$data;
$response['msg']=$status_message;
$json_response=json_encode($response);
echo $json_response;
} 
?>