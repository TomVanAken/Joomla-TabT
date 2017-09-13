<?php
/**
* Helper class for TabT module
*
* @package    TabT
* @subpackage Plugin
* @link http://tabt.frenoy.net/index.php?l=NL&display=TabTAPI_NL
* @license        GNU/GPL, see LICENSE.php
* mod_tabt is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/
class TabTDataRetrieval
{
  private $federatie;
  private $clubId;
  private $teamLetter;

  private $soapTabT;
  private $teamName;
  private $clubName;
  private $divisionId;
  private $divisionName;



  public function __construct($fed, $clubId, $teamLetter) {
    $this->federatie = $fed;
    $this->clubId = $clubId;
    $this->teamLetter = $teamLetter;


    //Initializing soap client
    $this->soapTabT = TabTDataRetrieval::getSoapClient($this->federatie);

    //Retrieve club name
    $clubResponse = $this->soapTabT->GetClubs(
      array('Club'  => $this->clubId));
    $this->clubName = $clubResponse->ClubEntries->LongName;

    //Retrieve team information
    $this->teamName = $this->clubName.' '.$this->teamLetter;

    $teamResponse = $this->soapTabT->GetClubTeams(
      array('Club'  => $this->clubId));

    foreach ($teamResponse->TeamEntries as $teamEntry)
      {
        if($teamEntry->Team==$this->teamLetter) {
          $this->divisionId=$teamEntry->DivisionId;
          $this->divisionName=$teamEntry->DivisionName;
          break;
        }
      }

  }

 public function getTeamName() {
   return $this->teamName;
 }

 public function getClubName() {
   return $this->clubName;
 }

 public function getDivisionName() {
   return $this->divisionName;
 }

 public function getDivisionId() {
   return $this->divisionId;
 }

public functin getRanking() {

}


  private static function getSoapClient($clientType) {
    switch($clientType) {
      case "sporta":
      return new SoapClient("http://ttonline.sporta.be/api/?wsdl");
      break;
      case "vttl":
      return new SoapClient("http://api.vttl.be/0.7/?wsdl");
      break;
      default:
      return null;
    }
  }

}


?>
