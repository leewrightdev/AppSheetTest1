<?php

require_once 'vendor/autoload.php';
require_once 'HttpCall.php';


/**
 * Provides a list of users from a call like:
 * https://appsheettest1.azurewebsites.net/sample/list?token=b32b3
 * If there is a token returned in the result set, the call is repeated
 * until there isn't a token.
 */
class UserList
{
  /**
   * @var array of people ID's from our list call
   */
  private $aIDs = array();

  /**
   * UserList constructor.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function __construct()
  {
    try {
      $this->aIDs = $this->loadList();
    } catch (Exception $E) {
      echo "loadList failed: " . $E;
      exit;
    }

  }

  /**
   * @return array
   */
  public function getIDs()
  {
    return $this->aIDs;
  }

  /**
   * @param string $szToken If there's a token being passed, it's a continuation of a previous request
   * @return array an array of userid's
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @todo Guard rails to prevent a circular/endless loop.
   */
  private function loadList($szToken = ''): array
  {
    $szParameters = "";

    // If there's a token passed in, add it to the request
    if (strlen($szToken) > 0) {
      $szParameters = "token=" . (string)$szToken;
    }

    $oCall = new HttpCall (HttpCall::BASE_URL);

    // Make the actual call
    if (!$oCall->sendRequest('list', $szParameters)) {
      throw new Exception("Call failed.");
    }

    if ($oCall->getStatus() != HttpCall::HTTP_SUCCESS) {
      throw new Exception("Call failed with error code " . $oCall->getStatus() . ".");
    }

    $oJson = json_decode($oCall->getBody());

    // Add the decoded JSON object to the results array
    $aResult = (is_array($oJson->{'result'})) ? array_values($oJson->{'result'}) : array();

    // If there is a token in the return call, repeat call with token
    if (isset($oJson->{'token'})) {
      echo "\nToken=" . $oJson->{'token'};
      // Merge previous and recursive results
      $aResult = array_merge($aResult, $this->loadList($oJson->{'token'}));
    }

    return ($aResult);
  }

  /**
   * Get the youngest $iNumber of users from the list (default is 5)
   * that have a valid US phone number. Our work is done here so just print results.
   * The sort function used is:
   * https://www.php.net/manual/en/function.uasort.php
   *
   * @param $iNumber
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getYoungest($iNumber=5)
  {
    foreach ($this->getIDs() as $iId) {

      try {
        // load the details
        $oDetails = new UserDetails($iId);

        // check for valid phone number
        if (isset($oDetails->{"number"}) && (UserDetails::isValidPhoneNumber($oDetails->{"number"})))
        {
          // add to User array with id as the index
          $aUsers[$iId] = $oDetails;
          // echo "\nAdding id=".$iId." as valid number.";
        } else {
          // echo "\nSkipping id=".$iId.".";
        }

      } catch (Exception $E) {
        echo "\nList reported a valid id, but call for details failed.";
        echo "\n" . $E->getMessage();
      }
    }

    // sort the $aUsers array via the "age" property
    uasort($aUsers, function ($a, $b) {
      return (($a->{"age"} < $b->{"age"}) ? 1 : 0);
    });

    echo "\nYoungest $iNumber:";

    // pop the top $iNumber of $oUser objects off the array
    for ($i = 0; $i < $iNumber; $i++) {

      $oUser = array_pop($aUsers);
      echo "\nid=" . $oUser->{"id"} . " age=" . $oUser->{"age"};

    }
  }
}

/**
 * Load the details from a single user from a call like:
 * https://appsheettest1.azurewebsites.net/sample/detail/21
 */
class UserDetails
{
  /**
   * These properties are required for loading the details of a user.
   * @var array
   */
  private $aPROPERTIES = array('id', 'name', 'age', 'number', 'photo', 'bio');

  /**
   * Loads whatever user is passed in when instantiated by "id"
   *
   * @param int $iId
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function __construct($iId)
  {
    try{

      $this->loadDetails($iId);

    } catch (Exception $E){
      echo "\nCall failed for id=$iId";
      echo "\n".$E->getMessage();
    }

  }

  /**
   * Loads the details for the user.
   *
   * @param $iId int userid to load
   * @return bool
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function loadDetails ($iId) : bool
  {
    $iId = (int) $iId;

    $oCall = new HttpCall (HttpCall::BASE_URL);

    if (!$oCall->sendRequest("detail/$iId"))
    {
      throw new Exception("Detail call failed for id: $iId.");
    }

    if ($oCall->getStatus() != HttpCall::HTTP_SUCCESS)
    {
      throw new Exception("Call failed with error code " . $oCall->getStatus() . ".");
    }

    $oJson = json_decode($oCall->getBody());

    // There are a few ways to do this, but let's just create a "magic" class
    // variable for each property we're looking for this case.  If a property isn't present,
    // throw an exception and skip it.
    foreach ($this->aPROPERTIES as $szProp) {

      if (isset($oJson->{$szProp}))
      {
        $this->{$szProp} = $oJson->{$szProp};
      } else {
        throw new Exception("$szProp wasn't returned");
      }
    }
    return true;
  }

  /**
   * Returns true if string passed in is a valid US telephone number, otherwise false.
   *
   * @param string $szNumber
   * @return bool
   * @Note: This would be a good method for unit testing.
   * @Note: Doesn't take into account +1
   */
  static function isValidPhoneNumber ($szNumber) : bool
  {
    // remove spaces, dashes, etc.
    $szNumberScrubbed = preg_replace("/[^0-9]/", '', $szNumber);

    // does it have 10 digits?
    if (strlen($szNumberScrubbed) != 10) return false;

    // echo "\nScrubbed number is: ".(int)$szNumberScrubbed;

    return true;
  }
}


