<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

require_once 'vendor/autoload.php';

/**
 * Essentially a bare bones wrapper for our HTTP calls.
 */
class HttpCall
{

  const HTTP_SUCCESS = 200;

  // Base URL without methods or parameters
  const BASE_URL = 'https://appsheettest1.azurewebsites.net/sample/';

  // Body of return call - presumably json
  private $szBody = '';

  // return status of our http call
  private $iHttpReturn = 0;

  /**
   * @var Client object for Guzzle client.
   *
   * This is Guzzle specific, but could be broken out to a different
   * client and injected to the call at a later point.
   */
  private $oClient;

  /**
   * HttpCall constructor.
   *
   * @param string $szBaseUrl Base URL without the functional call or parameters.
   */
  public function __construct($szBaseUrl)
  {
    $this->oClient = new GuzzleHttp\Client(['base_uri' => $szBaseUrl]);
  }

  /**
   * Really simple GET request.
   *
   * @param string $szFunction
   * @param string $szParameters
   *
   * @return bool success or failure
   * @throws GuzzleException
   */
  public function sendRequest($szFunction, $szParameters = ''): bool
  {
    if (strlen($szParameters) > 0) {
      $szParameters = '?' . $szParameters;
    }

    try
    {
      $oResponse = $this->oClient->request('GET', $szFunction . $szParameters);
    }
    catch (Exception $oException)
    {
      //Log message
      return false;
    }

    // There are other headers of course, but we'll just look at the body and http response
    $this->iHttpReturn = $oResponse->getStatusCode();
    $this->szBody = $oResponse->getBody();

    return true;

  }

  /**
   * Return the body of the http request
   *
   * @return string
   */
  public function getBody(): string
  {
    return $this->szBody;
  }

  /**
   * Return the status code of the http request
   *
   * @return integer
   */
  public function getStatus(): int
  {
    return (integer)$this->iHttpReturn;
  }

}

