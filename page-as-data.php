<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;

class pageAsDataPlugin extends Plugin
{
    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    public function onPluginsInitialized()
    {
        // hijack output so we can deliver as a different format
        // if this value isset
        if (isset($_GET['return-as']) && in_array($_GET['return-as'], array('json', 'xml', 'yaml'))) {
            $this->enable([
                    'onPageInitialized' => ['deliverFormatAs', 0]
                ]);
        }
    }

    public function deliverFormatAs()
    {
        /**
         * @var \Grav\Common\Page\Page $page
         */
        $format = $_GET['return-as'];
        $page = $this->grav['page'];
        $collection = $page->collection('content', false);
        $pageArray = $page->toArray();
        $children = array();
        foreach ($collection as $item) {
          $children[] = $item->toArray();
        }
        $pageArray['children'] = $children;
        switch ($format) {
            case 'json':
              header("Content-Type: application/json");
              echo json_encode($pageArray);
            break;
            case 'yaml':
                header("Content-Type: application/yaml");
                echo $page->toYaml();
            break;
            case 'xml':
              header("Content-Type: application/xml");
              $array2XmlConverter  = new PageAsDataXmlDomConstructor('1.0', 'utf-8');
              $array2XmlConverter->xmlStandalone   = TRUE;
              $array2XmlConverter->formatOutput    = TRUE;
              try {
                $array2XmlConverter->fromMixed( array('page' => $pageArray) );
                $array2XmlConverter->normalizeDocument ();
                $xml = $array2XmlConverter->saveXML();
                print $xml;
              }
              catch( Exception $ex )  {
                return $ex;
              }
            break;
        }
        exit();
    }
}

/**
 * Converts an array to XML
 *  http://www.devexp.eu/2009/04/11/php-domdocument-convert-array-to-xml/
 */
/**
 * Extends the DOMDocument to implement personal (utility) methods.
 * - From: http://www.devexp.eu/2009/04/11/php-domdocument-convert-array-to-xml/
 * - parent:: See http://www.php.net/manual/en/class.domdocument.php
 *
 * @throws   DOMException   http://www.php.net/manual/en/class.domexception.php
 *
 * @author Toni Van de Voorde
 */
class PageAsDataXmlDomConstructor extends \DOMDocument {
  public function fromMixed($mixed, \DOMElement $domElement = null) {
    $domElement = is_null($domElement) ? $this : $domElement;
    if (is_array($mixed)) {
      foreach ($mixed as $index => $mixedElement) {
        if ( is_int($index) ) {
          if ( $index == 0 ) {
            $node = $domElement;
          }
          else {
            $node = $this->createElement($domElement->tagName);
            $domElement->parentNode->appendChild($node);
          }
        }
        else {
          $node = $this->createElement($index);
          $domElement->appendChild($node);
        }
        $this->fromMixed($mixedElement, $node);
      }
    }
    else {
      $domElement->appendChild($this->createTextNode($mixed));
    }
  }
}