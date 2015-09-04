<?php

namespace ExtendedSphinxClient;

use \StandartSphinxClient\SphinxClient as ApiSphinx;

class SphinxClient {

  private $_sphinxApi;
  private $_max_matches = 100000;

  protected $__port = 9312;
  protected $__host = 'localhost';

  private $_rawSearchResult = null;

  public function __construct($port=null, $host=null)
  {
    $this->_sphinxApi = new ApiSphinx();

    if(is_numeric($port))
      $this->__port = $port;

    if(!is_null($host))
      $this->__host = $host;

    $this->_sphinxApi->SetServer( $this->__host, $this->__port );
    $this->setAnyWordSearchMode();

    $this->_sphinxApi->setRankingMode(SPH_RANK_SPH04);
  }

  public function setAnyWordSearchMode()
  {
    $this->_sphinxApi->SetMatchMode( SPH_MATCH_EXTENDED2 );
  }

  public function setAllWordsSearchMode()
  {
    $this->_sphinxApi->SetMatchMode( SPH_MATCH_ALL );
  }

  public function setPhraseSearchMode()
  {
    $this->_sphinxApi->SetMatchMode( SPH_MATCH_PHRASE );
  }

  public function setSortByRelevancy()
  {
    $this->_sphinxApi->setSortMode(SPH_SORT_EXTENDED, '@relevance desc, cdate_int desc');
  }

  public function setSortByAttribute($attr=null, $desc=false)
  {
    if(is_null($attr))
      throw new Exception("Invalid attribute");

    $sortDirection = ($desc) ? SPH_SORT_ATTR_DESC : SPH_SORT_ATTR_ASC;

    $this->_sphinxApi->setSortMode($sortDirection, $attr);
  }

  public function setFilter($attr=null, $values=[])
  {
    if(is_null($attr))
      throw new Exception("Invalid attribute");

    if(count($values) == 0)
      throw new Exception("Values list is empty");

    $this->_sphinxApi->setFilter($attr, $values);
  }

  public function setFilterRange($attr=null, $min=0, $max=1)
  {
    if(is_null($attr))
      throw new Exception("Invalid attribute");

    if($max < $min)
      throw new Exception("Invalid range points");

    $this->_sphinxApi->SetFilterRange($attr, $min, $max);
  }

  public function setLimits($start, $limit)
  {
    $this->_sphinxApi->setLimits($start, $limit, $this->_max_matches);
  }

  public function search($query, $index='*')
  {
    $this->_rawSearchResult = $this->_sphinxApi->Query($query, $index);

    if(!$this->_rawSearchResult)
      echo $this->getError();

    return (is_array($this->_rawSearchResult) && isset($this->_rawSearchResult['matches'])) ? array_keys($this->_rawSearchResult['matches']) : [];
  }

  public function getSearchResultCount()
  {
    return $this->_rawSearchResult['total_found'];
  }

  protected function getError()
  {
    return '<p style="color: #f00; font-weight: 700">'.$this->_sphinxApi->GetLastError().'</p>';
  }
}
