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
 // no direct access
 defined('_JEXEC') or die;

//Get data object file
require_once( dirname(__FILE__) . '/tabt_data.php' );

class PlgTabTHelper
{

	public function __construct() {

	}

	public function getRanking($fed, $division, $team, $week) {

	}

	public function geResults($fed, $division, $team, $week) {

	}



	/**
     * Initializes the data
     *
     * @access private
     */
    public function init()  {

		// Create TabT API client
		$this->soapTabT = PlgTabTHelper::getSoapClient($this->federatie);

		//Retrieve club/team name
		try {
			$clubResponse = $this->soapTabT->GetClubs(
                     array('Club'  => $this->clubId));
			$this->fullClubName = $clubResponse->ClubEntries->LongName;
			$this->clubName = $clubResponse->ClubEntries->Name;


			$this->fullTeamName = $this->fullClubName.' '.$this->teamId;
			$this->teamName = $this->clubName.' '.$this->teamId;

            $this->MatchesResponse = $this->soapTabT->GetMatches(
             array('DivisionId'  => $this->divisionId,
				'Club' => $this->clubId,
				'Team' => trim($this->teamId)));

            //Get week number if 0 (current week)
            if($this->week==0) $this->week = $this->getWeekNumber();

            //Retrieve Division Name through Division Ranking webservice
            $this->RankingResponse = $this->soapTabT->GetDivisionRanking(
             array('DivisionId'  => $this->divisionId,
						 			'RankingSystem' => $this->rankingSystem,
                  'WeekName' => $this->week));

            $this->divisionName = $this->RankingResponse->DivisionName;


		} catch(Exception $e) {
            print $e->getMessage();
			$this->fullClubName = "";
			$this->teamId = "";
			$this->clubName="";
		}


	}

	public function getRanking() {
	    $myTeam = trim($this->fullTeamName);
			$table = "";

			$styles = array("cPos", "cTeam", "cPlayed", "cPoints");

			// Loop through received entries of the ranking and prepare
			// the table to be displayed

			$table .=  $this->getTableRow(array('#', 'Ploeg', 'G', 'Ptn'), $styles, true, "" );


			$row = 0;
			$myRow = 0;
			foreach ($this->RankingResponse->RankingEntries as $rankingEntry)
			{
				$row = $row+1;
				$currentTeam = $rankingEntry->Team;
				$rowStyle = $currentTeam==$myTeam?"myTeam":"";
				$table .= $this->getTableRow(array($rankingEntry->Position,
					   $rankingEntry->Team,
					   $rankingEntry->GamesPlayed,
					   $rankingEntry->Points), $styles, false, $rowStyle );

			}

			return '<table>'.$table.'</table>';
	}


    public function getCalendar() {return $this->calendar;}


    public function getClubName() {return $this->fullClubName;}
	public function getTeamName() {return $this->fullTeamName;}
    public function getWeek() {return $this->week;}
	public function getDivisionName() {return $this->divisionName;}


    private function getWeekNumber() {
        $week = 1;
        $currenttime = date('Y-m-d');
        $weekday = date('N', strtotime($currenttime));
        $currenttime = strtotime($currenttime . ' - '. $weekday .' days');
        $date = date('Y-m-d', $currenttime);

         foreach ($this->MatchesResponse->TeamMatchesEntries as $matchEntry) {
                if($matchEntry->Date < $date) {
                  $week = $matchEntry->WeekName;
                } else {
                    break;
                }
        }


        return $week;
    }

	private function getTableRow($arrayValues, $arrayStyles, $isHeader, $rowStyle) {
		$headerTag = $isHeader?'th':'td';
		$row="";
		$valueLength = count($arrayValues);
		$styleLength = $arrayStyles==null?-1:count($arrayStyles);
		for($x = 0; $x < $valueLength; $x++) {
			$content = (htmlspecialchars($arrayValues[$x]));
			$style = $arrayStyles==null || $styleLength <= $x?"":$arrayStyles[$x];
			$class = $style==""?"":("class='".$style."'");
			$row .= '<'.$headerTag.' '.$class.'>'.$content.'</'.$headerTag.'>';

		}
		return "<tr ".($rowStyle==""?"":("class='".$rowStyle."'")).">".$row."</tr>";
	}
}


?>
