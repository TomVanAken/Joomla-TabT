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
 
    //Loading front end language (since it's a plugin, language files are installed in admin folder)
    $lang = JFactory::getLanguage();
    $lang->load('plg_content_tabt', JPATH_ADMINISTRATOR);

    class PlgTabTHelper
    {

        public function __construct() {

        }

        public function getRanking($fed, $clubid, $team, $week) {
        $replacement = "";
        $styles = array("cPos", "cTeam", "cPlayed", "cPoints");

        $tabt = new TabTDataRetrieval($fed, $clubid, $team);
        $ranking = $tabt->getRanking($week);    

        $replacement = '<div class="tabtRanking">';
        $replacement .= '<h3>' . $tabt->getDivisionName() . "</h3>";
        $replacement .= '<span class="week">'. JText::_("TABT_LABEL_WEEK") . $tabt->getWeek().'</span>';

        $table =  $this->getTableRow(array(JText::_("TABT_HEADER_POSITION"), JText::_("TABT_HEADER_TEAM"), JText::_("TABT_HEADER_PLAYED"), JText::_("TABT_HEADER_POINTS")), $styles, true, "" );


        $row = 0;
        $myRow = 0;
        $myTeam = $tabt->getTeamName();
        foreach ($ranking as $rankingEntry)
        {
            $row = $row+1;
            $currentTeam = $rankingEntry->Team;
            $rowStyle = $currentTeam==$myTeam?"myTeam":"";
            $table .= $this->getTableRow(array($rankingEntry->Position,
                   $rankingEntry->Team,
                   $rankingEntry->GamesPlayed,
                   $rankingEntry->Points), $styles, false, $rowStyle );
        }

        $table = "<table>" . $table . "</table>";
            
            //  $replacement .= $tabt->getRanking();
        $replacement .= $table . '</div>';

        return $replacement;

        }

        public function geResults($fed, $division, $team, $week) {

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
