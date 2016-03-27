<?php
//Fetch questions for a particular outlet type!
header("Content-Type:application/json");
$m = new MongoClient('mongodb://freddyuser:missionpossible@ds019708.mlab.com:19708/rateus');
$db=$m->rateus;
$collection=$db->questions;
$outlet_cat=$_GET['outletType'];

if(!empty($outlet_cat))
{
    
    //Check if questions collection has this outlettype    
    
    $outlet_type_query=array("shop_category"=>$outlet_cat);
    $cursor=$collection->find($outlet_type_query);
    $res=array();//this array will be passed as final response to JSON object.
	
    $count_of_such_questions=$cursor->count();
    if($count_of_such_questions<>0)
        {
            $qsn_not_found=False;    
            $list=array();
            foreach($cursor as $doc)
                {
				
                    $p=new StdClass();
            
                    $p->questionId=$doc['_id']->{'$id'};
                    $p->questionName=$doc['qsn_name'];
                    $p->questionType=$doc['questionType'];
                    	$r= $doc['rating_value_associated'];
                    	$qsnratings=array();
                    		foreach($r as $rating)
                        	{
                            		array_push($qsnratings, $rating);            
                        	}

                    $p->optionValues=$qsnratings;
                        $emoIds=array();
                        $e=$doc['emoticonId'];
                    		foreach($e as $emo)
                        	{
		                         array_push($emoIds,$emo);
                        	}
                   $p->emoticonIds=$emoIds;
                   array_push($list,$p);             
		}
        }
    else
        {
            $qsn_not_found=true;    
        }
		
    if(!$qsn_not_found)
        {
            deliver_response(True, $list, "Success! Questions found for outlet type");
        }
    else
        {
            deliver_response(False,$outlet_cat,"No questions configured for outlet type");
        }
}
else
{   
   deliver_response(False, NULL,"Something went wrong, could not perform the server option, outlet category not specified");
}

function deliver_response($status,$data, $status_message)
{
    $json_response=json_encode($data);
    echo $json_response;
    error_log("JSON list".$json_response);
}
?>
