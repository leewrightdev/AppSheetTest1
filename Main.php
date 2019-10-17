<?php

require_once 'HttpCall.php';
require_once 'SampleClasses.php';


function getYoungest()
{

  // load the list of users
  $oList = new sampleList();

  /**
   * array of Detail objects with the id property as the key
   */
  $aUsers = array();

  foreach ($oList->getIDs() as $iId){
    // echo "\nid=$iId";

    try{
      // load the details
      $oDetails = new sampleDetails($iId);

      // check for valid phone number
      if (sampleDetails::isValidPhoneNumber($oDetails->{"number"}))
      {
        // add to User array with id as the index
        $aUsers[$iId] = $oDetails;
        // echo "\nAdding id=".$iId." as valid number.";
      }
      else {
        // echo "\nSkipping id=".$iId.".";
      }

    } catch (Exception $E)
    {
      echo "\nList reported a valid id, but call for details failed.";
      echo "\n".$E->getMessage();
    }
  }

/*
  foreach ($aUsers as $iId=>$oUser)
  {
    echo "\nid=".$iId." age=".$oUser->{"age"};
  }
*/
  uasort ($aUsers, function ($a, $b){
    return ( ($a->{"age"} < $b->{"age"}) ? 1 : 0); });

  echo "\nYoungest";
  for($i=0; $i < 5; $i++){
    $oUser = array_pop($aUsers); // as $iId=>$oUser)
    echo "\nid=".$oUser->{"id"}." age=".$oUser->{"age"};

  }


 // print_r ($aUsers);
}

getYoungest();
exit;

print_r (sampleDetails::isValidPhoneNumber('(425) 591-5588'));
print_r (sampleDetails::isValidPhoneNumber('425-591-5588'));
print_r (sampleDetails::isValidPhoneNumber('4xx-591-5588'));
print_r (sampleDetails::isValidPhoneNumber('0000-591-5588'));

$oDetails = new sampleDetails(15);
print_r($oDetails);
$oDetails = new sampleDetails('bad data');
$oDetails = new sampleDetails(105);

$oDetails = new sampleDetails(15);
print_r($oDetails);
$oDetails = new sampleDetails(15);

$oDetails = new sampleDetails(105);
exit;
