<?php

require_once 'vendor/autoload.php';
require_once 'HttpCall.php';


/**
 * Class sampleList
 */
class sampleList
{
  /**
   * @var array of people ID's from our list call
   */
  private $aIDs = array();

  /**
   * sampleList constructor.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function __construct()
  {
    try
    {
      $this->aIDs = $this->makeCall();
    } catch (Exception $E)
    {
      echo "makeCall failed: ".$E;
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
   *
   * @return array an array of userid's
   * @throws \GuzzleHttp\Exception\GuzzleException
   *
   * @todo Guard rails to prevent a circular/endless loop.
   */
  public function makeCall ($szToken = '') : array
  {
    $szParameters = "";

    // If there's a token passed in, add it to the request
    if (strlen($szToken) > 0 )
    {
      $szParameters = "token=".(string)$szToken;
    }

    $oCall = new HttpCall (HttpCall::BASE_URL);

    // Make the actual call
    if (!$oCall->sendRequest('list', $szParameters))
    {
      throw new Exception("Call failed.");
    }

    if ($oCall->getStatus() != HttpCall::HTTP_SUCCESS)
    {
      throw new Exception("Call failed with error code " . $oCall->getStatus() . ".");
    }

    $oJson = json_decode($oCall->getBody());

    // Add the decoded JSON object to the results array
    $aResult = (is_array($oJson->{'result'})) ? array_values($oJson->{'result'}) : array();

    // If there is a token in the return call, repeat call with token
    if (isset($oJson->{'token'}))
    {
      echo "\nToken=".$oJson->{'token'};
      // Merge previous and recursive results
      $aResult = array_merge($aResult,$this->makeCall($oJson->{'token'}));
    }

    return ($aResult);
  }

}


/**
 * Load the details from a single user from a call like:
 * // https://appsheettest1.azurewebsites.net/sample/detail/21
 */
class sampleDetails
{
  /**
   * these properties are required for loading a user
   *
   * @var array
   */
  private $aPROPERTIES = array('id', 'name', 'age', 'number', 'photo', 'bio');

  /**
   * sampleDetails constructor.
   *
   * @param $iId
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function __construct($iId)
  {

    $this->loadDetails($iId);

  }

  /**
   * Loads the details for the user.
   *
   * @param $iId int userid to load
   *
   * @return bool
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function loadDetails ($iId)
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
    // variable for each property we're looking for.  If a property isn't present,
    // throw an exception and skip it.
    foreach ($this->aPROPERTIES as $szProp) {

      if (isset($oJson->{$szProp}))
      {
        $this->{$szProp} = $oJson->{$szProp};
      } else {
        //$this->{$szProp} = '';
        throw new Exception("$szProp wasn't returned");
      }
    }

    return true;

  }

  /**
   * Returns true if string passed in is a valid US telephone number, otherwise false.
   *
   * @param string $szNumber
   *
   * @return bool
   *
   * @Note: This would be a good method for unit testing.
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


