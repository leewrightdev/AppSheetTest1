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
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function __construct()
  {
    try {
      $this->aIDs = $this->makeCall();
    } catch (\Exception $exception)
    {
      echo "makeCall failed: ".$exception;
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
   * @param string $szToken
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   *
   * @todo Guard rails to prevent a circular/endless loop.
   */
  public function makeCall ($szToken = '')
  {
    $szParameters = "";

    if (strlen($szToken) > 0 )
    {
      $szParameters = "token=".(string)$szToken;
    }

    $oCall = new HttpCall (HttpCall::BASE_URL);

    if (!$oCall->sendRequest('list', $szParameters))
    {
      throw new \Exception("Call failed.");
    }

    if ($oCall->getStatus() != 200)
    {
      throw new \Exception("Call failed with error code " . $oCall->getStatus() . ".");
    }

    $oJson = json_decode($oCall->getBody());

    $aResult = (is_array($oJson->{'result'})) ? array_values($oJson->{'result'}) : array();

    // If there is a token in the return call, repeat call with token
    if (isset($oJson->{'token'}))
    {
      echo "\n".$oJson->{'token'};
      $aResult = array_merge($aResult,$this->makeCall($oJson->{'token'}));
    }

    return ($aResult);

  }
}
// https://appsheettest1.azurewebsites.net/sample/detail/21

/**
 * Class sampleDetails
 */
class sampleDetails
{
  //private $iID, $szName, $iAge, $szNumber, $szPhoto, $szBio;


  public function __construct($iId)
  {
    try
    {
      $this->loadDetails($iId);
    } catch (\Exception $E)
    {
      die ("Error: $E");
    }
  }

  private function loadDetails ($iId)
  {

    $iId = (int) $iId;

    $oCall = new HttpCall (HttpCall::BASE_URL);

    if (!$oCall->sendRequest("detail/$iId"))
    {
      throw new \Exception("Detail call failed for id: $iId.");
    }

    if ($oCall->getStatus() != 200)
    {
      throw new \Exception("Call failed with error code " . $oCall->getStatus() . ".");
    }

    $oJson = json_decode($oCall->getBody());

    // There are a few ways to do this, but let's just create a class
    // variable for each property we're looking for.
    foreach (array('id', 'name', 'age', 'number', 'photo', 'bio') as $szProp) {

      if (isset($oJson->{$szProp}))
      {
        $this->{$szProp} = $oJson->{$szProp};
      } else {
        throw new \Exception("$szProp wasn't returned");
      }
    }

    return true;

  }

  /**
   * Returns true if string passed in is a valid US telephone number.
   *
   * @param string $szNumber
   * @return bool
   *
   * Note: This would be a good method for unit testing.
   */
  static function isValidPhoneNumber ($szNumber)
  {
    // remove spaces, dashes, etc.
    $szNumberScrubbed = preg_replace("/[^0-9]/", '', $szNumber);

    // does it have
    if (strlen($szNumberScrubbed) != 10) return false;

    echo "\nScrubbed number is: ".(int)$szNumberScrubbed;

    return true;
  }
}


print_r (sampleDetails::isValidPhoneNumber('425-591-5588'));
print_r (sampleDetails::isValidPhoneNumber('4xx-591-5588'));
print_r (sampleDetails::isValidPhoneNumber('0000-591-5588'));


$oDetails = new sampleDetails(15);

$oDetails = new sampleDetails(105);
exit;
$oList = new sampleList();
print_r($oList->getIDs());

$oDetails = new sampleDetails(15);
print_r($oDetails);
