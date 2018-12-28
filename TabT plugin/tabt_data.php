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

  private $soapTabT;

  private $teamLetter;
  private $teamName;
  private $teamLongName;
  private $clubName;
  private $clubLongName;
  private $divisionId;
  private $divisionName;
    
  private $weekName;



  public function __construct($fed, $clubId) {
    $this->federatie = $fed;
    $this->clubId = $clubId;


    //Initializing soap client
    $this->soapTabT = TabTDataRetrieval::getSoapClient($this->federatie);

    //Retrieve club name
    $clubResponse = $this->soapTabT->GetClubs(
      array('Club'  => $this->clubId));
    $this->clubLongName = $clubResponse->ClubEntries->LongName;
    $this->clubName = $clubResponse->ClubEntries->Name;
  }
    
 public function initTeam($divisionId, $teamId) {
     $this->divisionId = $divisionId;
     $this->teamLetter = $teamId;
     $this->teamName = $this->clubName.' '.$this->teamLetter;
     $this->teamLongName = $this->clubLongName.' '.$this->teamLetter;
     
     /**Get division name**/
     $divisions = $this->soapTabT->GetDivisions(
      array('ShowDivisionName'  => 'yes'));
     
     //Loop through divisions to get names
    foreach ($divisions->DivisionEntries as $division)
      {
        if($division->DivisionId==$divisionId) {
          $this->divisionName=$division->DivisionName;
          break;
        }
      }
     
     
 }
    
 public function isCurrentTeam($team) {
     $returnval = $this->teamName==$team;
     $returnval = $returnval || $this->teamLongName==$team;
     return $returnval;
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
    if($week!=0) {
        $this->weekName = $week;
    } else {
        $currentWeek = $this->getCurrentWeek();
        $this->weekName = $currentWeek==0?'':$currentWeek;
     }

    //Retrieve Division Name through Division Ranking webservice
    $rankingResponse = $this->soapTabT->GetDivisionRanking(
        array('DivisionId'  => $this->divisionId,
              'WeekName' => $this->weekName));
    return $rankingResponse->RankingEntries;
}
    
public function getResults() {
    $MatchesResponse = $this->soapTabT->GetMatches(
             array('DivisionId'  => $this->divisionId,
                 'Club' => $this->clubId,
                 'Team' => $this->teamLetter));
    return $MatchesResponse->TeamMatchesEntries;
    
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
        $week = 0;
        $currenttime = date('Y-m-d');
      
        //To avoid that the 'current' week is influenced by preponed games, 
        //we take the week number of the first game next week and distract 1 number.
        //If the last game is played already, the highest weeknumber will be used.
     
        $daysToAdd = 7 - date('N', strtotime($currenttime));
        $fromDateTime = strtotime($currenttime . ' + ' . $daysToAdd . ' days');
        $fromDate = date('Y-m-d', $fromDateTime);
             
        $MatchesResponse = $this->soapTabT->GetMatches(
             array('DivisionId'  => $this->divisionId,
                 'YearDateFrom' => $fromDate));
        $teamMatches = $MatchesResponse->MatchCount;
        if($teamMatches > 0) {
            $matchEntry = current($MatchesResponse->TeamMatchesEntries);
             $week = $matchEntry->WeekName - 1;
        } else {
            //No future matches, get all matches
            $MatchesResponse = $this->soapTabT->GetMatches(
             array('DivisionId'  => $this->divisionId));
            $matchEntry = end($MatchesResponse->TeamMatchesEntries);
            $week = $matchEntry->WeekName;
        }
        
         return $week;
    }
}

?>
