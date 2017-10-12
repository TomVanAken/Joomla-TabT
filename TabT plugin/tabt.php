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

  private $federatie = '';
  private $clubId = '';
  private $helper;


  public function __construct(&$subject, $config = array()) {
    $this->loadLanguage();
    parent::__construct($subject, $config);
    $this->clubId = $this->getPluginValue('clubId', $this->clubId);
    $this->federatie = $this->getPluginValue('federatie', $this->federatie);
    $this->helper  = new PlgTabTHelper();
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



  private function getResults($request) {
    $replacement = "";

    //reinitialize
    $this->init();

    //get tabt article parameters
    $this->setParameters($request[1]);

  }

  private function getRanking($request) {
    //get tabt article parameters
    $params = $this->getRequestParameters($request[1]);
    $fed=array_key_exists("fed", $params )?$params["fed"]:$this->federatie;
    $club=array_key_exists("clubId", $params)?$params["clubId"]:$this->clubId;
    $team=array_key_exists("team", $params)?$params["team"]:"A";
    $week=array_key_exists("week", $params)?$params["week"]:0;

    return $this->helper->getRanking($fed, $club, $team, $week); 
  }

  private function getRequestParameters($pattern) {
    $params = split(',', $pattern);
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
