<?php

// no direct access
defined('_JEXEC') or die;
// Include the syndicate functions only once
require_once( dirname(__FILE__) . '/tabt_helper.php' );

//Include CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'plugins/content/tabt/css/tabt.css'); 
 


class plgContentTabt extends JPlugin
{

    
    private $federatie = 'vttl';
    private $clubId = '';
	private $ranking = 1;
    
    private $team = 'A';
    private $week = 0;
    private $division = 1;
    
    
    public function __construct(&$subject, $config = array()) {
       parent::__construct($subject, $config);
       $this->init();
    }
    
    private function init() {
       $this->clubId = $this->getParameterValue('clubId', $this->clubId);
       $this->federatie = $this->getParameterValue('federatie', $this->federatie);
       $this->ranking = $this->getParameterValue('ranking', $this->ranking);
    }

	 function onContentPrepare($context, &$article, &$params, $limitstart) {
         
         try {
 
		      if (strpos($article->text, '{tabt') === false) {
                  return;  // short-circuit plugin activation 
		      }

            // find gallery tags and emit code
             $pattern = '#\{tabt\}(.+,?)+\{/tabt\}#';
             
             $article->text = preg_replace_callback($pattern, 
                              array($this, 'getRanking'), 
                              $article->text, 
                              -1);

		} catch (Exception $e) {
             print 'Error occured <br/>';
             print $e->getMessage();
			$app = JFactory::getApplication();
			$app->enqueueMessage( $e->getMessage(), 'error' );
			$article->text = $e->getMessage();
		}
	}


    private function getRanking($matches) {
        $replacement = "";
        
        //reinitialize
        $this->init();
        
        //get tabt article parameters
        $this->setParameters($matches[1]);
        
        $tabt = new PlgTabTHelper($this->federatie, $this->clubId, $this->team, $this->division, $this->week, $this->ranking);
        $replacement = '<div class="tabtRanking">';
        $replacement .= '<h3>' . $tabt->getDivisionName() . "</h3>";
        $replacement .= '<span class="week">Week: '.$tabt->getWeek().'</span>';
        $replacement .= $tabt->getRanking();
        $replacement .= '</div>';
        
        return $replacement;
    }
    
    private function setParameters($matches) {
        $params = split(',', $matches);
        foreach ($params as $elem) {
                $array = split("=", $elem);
                $key = trim($array[0]);
                $value = trim($array[1]);
                switch($key) {
                    case 'team':
                        $this->team=$value;
                        break;
                    case 'division':
                        $this->division=$value;
                        break;
                    case 'week':
                        $this->week=$value;
                        break;
                    case 'ranking':
                        $this->ranking=$value;
                }
                
        }
    }
    
	private function getParameterValue($name, $default) {
		if ($this->params instanceof stdClass) {
			if (isset($this->params->$name)) {
				return $this->params->$name;
			}
		} else if ($this->params instanceof JRegistry) {  // Joomla 2.5 and earlier
			$paramvalue = $this->params->get($name);
			if (isset($paramvalue)) {
				return $paramvalue;
			}
		}
		return $default;
	}
}

?>