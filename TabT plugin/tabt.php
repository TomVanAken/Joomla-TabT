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


  public function __construct(&$subject, $config = array()) {
    parent::__construct($subject, $config);
    $this->clubId = $this->getPluginValue('clubId', $this->clubId);
    $this->federatie = $this->getPluginValue('federatie', $this->federatie);
  }

  function onContentPrepare($context, &$article, &$params, $limitstart) {

    try {

      if (strpos($article->text, '{tabt') === false) {
        return;  // short-circuit plugin activation
      }

      //show tabt ranking (tabt for backend compatibility)
      $pattern = '#\{tabt\}(.+,?)+\{/tabt\}#';
      $article->text = preg_replace_callback($pattern,
      array($this, 'getRanking'),
      $article->text,
      -1);

      //show tabt ranking
      $pattern = '#\{tabt_ranking\}(.+,?)+\{/tabt\}#';
      $article->text = preg_replace_callback($pattern,
      array($this, 'getRanking'),
      $article->text,
      -1);

      //show tabt team results
      $pattern = '#\{tabt_results\}(.+,?)+\{/tabt\}#';
      $article->text = preg_replace_callback($pattern,
      array($this, 'getResults'),
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



  private function getResults($matches) {
    $replacement = "";

    //reinitialize
    $this->init();

    //get tabt article parameters
    $this->setParameters($matches[1]);

  }

  private function getRanking($matches) {
    $replacement = "";


    //get tabt article parameters
    $params = $this->getRequestParameters($matches[1]);

    $tabt = new TabTDataRetrieval($this->federatie, $this->clubId, $params["team"]);
    $replacement = '<div class="tabtRanking">';
    $replacement .= '<h3>' . $tabt->getDivisionName() . "</h3>";
    $replacement .= '<span>' . $tabt->getTeamName() . "</span>";
  //  $replacement .= '<span class="week">Week: '.$tabt->getWeek().'</span>';
  //  $replacement .= $tabt->getRanking();
    $replacement .= '</div>';

    return $replacement;
  }

  private function getRequestParameters($matches) {
    $params = split(',', $matches);
    foreach ($params as $elem) {
      $array = split("=", $elem);
      $key = trim(strtolower($array[0]));
      $value = trim($array[1]);
      $returnValue[$key]=$value;

    }
    return $returnValue;
  }

  private function getPluginValue($name, $default) {
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
