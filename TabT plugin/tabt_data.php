<?php

defined('_JEXEC') or die;

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
    
  private $weekName;



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

    //Get Club Teams
    $teamResponse = $this->soapTabT->GetClubTeams(
      array('Club'  => $this->clubId));

    //Loop through teams to retrieve division information of current team
    foreach ($teamResponse->TeamEntries as $teamEntry)
      {
        if($teamEntry->Team==$this->teamLetter) {
          $this->divisionId=$teamEntry->DivisionId;
          $this->divisionName=$teamEntry->DivisionName;
          break;
        }
      }
      
    //Get Current week
    $this->weekName = $this->getCurrentWeek();
      
    //Load translations
    

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
    
 public function getWeek() {
     return $this->weekName;
 }

public function getRanking($week) {
    if($week!=0) $this->weekName = $week;
    //Retrieve Division Name through Division Ranking webservice
    $rankingResponse = $this->soapTabT->GetDivisionRanking(
        array('DivisionId'  => $this->divisionId,
              'WeekName' => $this->weekName));
    return $rankingResponse->RankingEntries;
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

 private function getCurrentWeek() {
        $week = 1;
        $currenttime = date('Y-m-d');
        $weekday = date('N', strtotime($currenttime));
        $currenttime = strtotime($currenttime . ' - '. $weekday .' days');
        $date = date('Y-m-d', $currenttime);

    
        $MatchesResponse = $this->soapTabT->GetMatches(
             array('DivisionId'  => $this->divisionId,
				'Club' => $this->clubId,
				'Team' => trim($this->teamLetter)));
    
         foreach ($MatchesResponse->TeamMatchesEntries as $matchEntry) {
                if($matchEntry->Date < $date) {
                  $week = $matchEntry->WeekName;
                } else {
                    break;
                }
        }


        return $week;
    }
}

?>
