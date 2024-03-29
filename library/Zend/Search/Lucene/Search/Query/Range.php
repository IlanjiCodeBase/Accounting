<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/** Zend_Search_Lucene_Search_Query */
require_once 'Zend/Search/Lucene/Search/Query.php';

/** Zend_Search_Lucene_Search_Query_MultiTerm */
require_once 'Zend/Search/Lucene/Search/Query/MultiTerm.php';


/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Search_Query_Range extends Zend_Search_Lucene_Search_Query
{
    /**
     * Lower term.
     *
     * @var Zend_Search_Lucene_Index_Term
     */
    private $_lowerTerm;

    /**
     * Upper term.
     *
     * @var Zend_Search_Lucene_Index_Term
     */
    private $_upperTerm;


    /**
     * Search field
     *
     * @var string
     */
    private $_field;

    /**
     * Inclusive
     *
     * @var boolean
     */
    private $_inclusive;

    /**
     * Matched terms.
     *
     * Matched terms list.
     * It's filled during the search (rewrite operation) and may be used for search result
     * post-processing
     *
     * Array of Zend_Search_Lucene_Index_Term objects
     *
     * @var array
     */
    private $_matches;


    /**
     * Zend_Search_Lucene_Search_Query_Range constructor.
     *
     * @param Zend_Search_Lucene_Index_Term|null $lowerTerm
     * @param Zend_Search_Lucene_Index_Term|null $upperTerm
     * @param boolean $inclusive
     * @throws Zend_Search_Lucene_Exception
     */
    public function __construct($lowerTerm, $upperTerm, $inclusive)
    {
        if ($lowerTerm === null  &&  $upperTerm === null) {
            throw new Zend_Search_Lucene_Exception('At least one term must be non-null');
        }
        if ($lowerTerm !== null  &&  $upperTerm !== null  &&  $lowerTerm->field != $upperTerm->field) {
            throw new Zend_Search_Lucene_Exception('Both terms must be for the same field');
        }
        $this->_field     = ($lowerTerm !== null)? $lowerTerm->field : $upperTerm->field;
        $this->_lowerTerm = $lowerTerm;
        $this->_upperTerm = $upperTerm;
        $this->_inclusive = $inclusive;
    }

    /**
     * Get query field name
     *
     * @return string|null
     */
    public function getField()
    {
        return $this->_field;
    }

    /**
     * Get lower term
     *
     * @return Zend_Search_Lucene_Index_Term|null
     */
    public function getLowerTerm()
    {
        return $this->_lowerTerm;
    }

    /**
     * Get upper term
     *
     * @return Zend_Search_Lucene_Index_Term|null
     */
    public function getUpperTerm()
    {
        return $this->_upperTerm;
    }

    /**
     * Get upper term
     *
     * @return boolean
     */
    public function isInclusive()
    {
        return $this->_inclusive;
    }

    /**
     * Re-write query into primitive queries in the context of specified index
     *
     * @param Zend_Search_Lucene_Interface $index
     * @return Zend_Search_Lucene_Search_Query
     */
	 
    public function rewrite(Zend_Search_Lucene_Interface $index)
    {
      /*  echo '<pre>';
		print_r($index);
		echo '</pre>';*/
		$this->_matches = array();

        if ($this->_field === null) {
            // Search through all fields
            $fields = $index->getFieldNames(true /* indexed fields list */);
        } else {
            $fields = array($this->_field);
        }

        foreach ($fields as $field) {
            $index->resetTermsStream();
			
            if ($this->_lowerTerm !== null) {

                $lowerTerm = new Zend_Search_Lucene_Index_Term($this->_lowerTerm->text, $field);
				
                $index->skipTo($lowerTerm);
				
					
                if (!$this->_inclusive  &&
                    $index->currentTerm() == $lowerTerm) {
                    // Skip lower term
                    $index->nextTerm();
                }
            } else {
                $index->skipTo(new Zend_Search_Lucene_Index_Term('', $field));
				error_reporting(~E_STRICT);
				$lowerTerm->text ='';
            }

			
            if ($this->_upperTerm !== null) {
                // Walk up to the upper term
                $upperTerm = new Zend_Search_Lucene_Index_Term($this->_upperTerm->text, $field);
			
               /* while ($index->currentTerm() !== null          &&
                       $index->currentTerm()->field == $field  &&
                       (($index->currentTerm()->text  <  $upperTerm->text )) {
                    $this->_matches[] = $index->currentTerm();
                    $index->nextTerm();
                }*/
				
				 $index->skipTo(new Zend_Search_Lucene_Index_Term('', $field));
				 while ($index->currentTerm() !== null) {
					/*echo '<pre>';
					print_r($index->currentTerm());
					echo '</pre>';*/
				//   if($index->currentTerm()->field=='MontantLoyer')	
				// echo "<br>==>".$index->currentTerm()->text."<>".$index->currentTerm()->field ."<>".$lowerTerm->text."<>".$field.'<>'.$upperTerm->text;
				  if(($index->currentTerm()->text  <=  $upperTerm->text) && ($lowerTerm->text <= $index->currentTerm()->text) && ($index->currentTerm()->field == $field))
				  {
						$this->_matches[] = $index->currentTerm();
					//	 $index->nextTerm();
				  }	
				  $index->nextTerm();
					
                }
				
				if($index != null)
				{
	              error_reporting(~E_NOTICE);
					if ($this->_inclusive  &&  $index->currentTerm() == $upperTerm->text) {
	                    // Include upper term into result
	                    $this->_matches[] = $upperTerm;
						 $index->nextTerm();
	                }
				}
            } else {
			
                // Walk up to the end of field data
		//		 
				$index->skipTo(new Zend_Search_Lucene_Index_Term('', $field));
				error_reporting(~E_STRICT);
				//$upperTerm->text ='';
				while ($index->currentTerm() !== null ) {
              //    echo "<br>==>".$index->currentTerm()->text."<>".$index->currentTerm()->field ."<>".$lowerTerm->text."<>".$field;
				  if(($index->currentTerm()->text  >=  $lowerTerm->text)&&($index->currentTerm()->field == $field  ) )
						$this->_matches[] = $index->currentTerm();
				  $index->nextTerm();
					
                }
               /* while ($index->currentTerm() !== null  &&  $index->currentTerm()->field == $field) {
                    $this->_matches[] = $index->currentTerm();
                    $index->nextTerm();
                }*/
            }

            $index->closeTermsStream();
        }
		
        if (count($this->_matches) == 0) {
            return new Zend_Search_Lucene_Search_Query_Empty();
        } else if (count($this->_matches) == 1) {
            return new Zend_Search_Lucene_Search_Query_Term(reset($this->_matches));
        } else {
            $rewrittenQuery = new Zend_Search_Lucene_Search_Query_MultiTerm();

            foreach ($this->_matches as $matchedTerm) {
                $rewrittenQuery->addTerm($matchedTerm);
            }

            return $rewrittenQuery;
        }
    }

    /**
     * Optimize query in the context of specified index
     *
     * @param Zend_Search_Lucene_Interface $index
     * @return Zend_Search_Lucene_Search_Query
     */
    public function optimize(Zend_Search_Lucene_Interface $index)
    {
        throw new Zend_Search_Lucene_Exception('Range query should not be directly used for search. Use $query->rewrite($index)');
    }

    /**
     * Return query terms
     *
     * @return array
     * @throws Zend_Search_Lucene_Exception
     */
    public function getQueryTerms()
    {
        if ($this->_matches === null) {
            throw new Zend_Search_Lucene_Exception('Search has to be performed first to get matched terms');
        }

        return $this->_matches;
    }

    /**
     * Constructs an appropriate Weight implementation for this query.
     *
     * @param Zend_Search_Lucene_Interface $reader
     * @return Zend_Search_Lucene_Search_Weight
     * @throws Zend_Search_Lucene_Exception
     */
    public function createWeight(Zend_Search_Lucene_Interface $reader)
    {
        throw new Zend_Search_Lucene_Exception('Range query should not be directly used for search. Use $query->rewrite($index)');
    }


    /**
     * Execute query in context of index reader
     * It also initializes necessary internal structures
     *
     * @param Zend_Search_Lucene_Interface $reader
     * @param Zend_Search_Lucene_Index_DocsFilter|null $docsFilter
     * @throws Zend_Search_Lucene_Exception
     */
    public function execute(Zend_Search_Lucene_Interface $reader, $docsFilter = null)
    {
        throw new Zend_Search_Lucene_Exception('Range query should not be directly used for search. Use $query->rewrite($index)');
    }

    /**
     * Get document ids likely matching the query
     *
     * It's an array with document ids as keys (performance considerations)
     *
     * @return array
     * @throws Zend_Search_Lucene_Exception
     */
    public function matchedDocs()
    {
        throw new Zend_Search_Lucene_Exception('Range query should not be directly used for search. Use $query->rewrite($index)');
    }

    /**
     * Score specified document
     *
     * @param integer $docId
     * @param Zend_Search_Lucene_Interface $reader
     * @return float
     * @throws Zend_Search_Lucene_Exception
     */
    public function score($docId, Zend_Search_Lucene_Interface $reader)
    {
        throw new Zend_Search_Lucene_Exception('Range query should not be directly used for search. Use $query->rewrite($index)');
    }

    /**
     * Highlight query terms
     *
     * @param integer &$colorIndex
     * @param Zend_Search_Lucene_Document_Html $doc
     */
    public function highlightMatchesDOM(Zend_Search_Lucene_Document_Html $doc, &$colorIndex)
    {
        $words = array();

        foreach ($this->_matches as $term) {
            $words[] = $term->text;
        }

        $doc->highlight($words, $this->_getHighlightColor($colorIndex));
    }

    /**
     * Print a query
     *
     * @return string
     */
    public function __toString()
    {
        // It's used only for query visualisation, so we don't care about characters escaping
		//echo "<br>===>".$this->_field;
        return (($this->_field === null)? '' : $this->_field . ':')
             . (($this->_inclusive)? '[' : '{')
             . (($this->_lowerTerm !== null)?  $this->_lowerTerm->text : 'null')
             . ' TO '
             . (($this->_upperTerm !== null)?  $this->_upperTerm->text : 'null')
             . (($this->_inclusive)? ']' : '}');
    }
}
