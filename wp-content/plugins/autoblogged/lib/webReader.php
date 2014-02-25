<?php
/**
 * AutoBlogged Web Reader
 *
 * Derived from Keyvan Minoukadeh's (www.keyvan.net) PHP Readability
 * which is based on on Arc90's (www.readability.com) readability.js
 * version 1.7.1 (without multi-page support). Name changed to avoid
 * conflicts or confusion.
 *
 * Also includes JSLikeHTMLElement Class by Keyvan Minoukadeh
 * http://www.keyvan.net - keyvan@keyvan.net
 * ------------------------------------------------------
 * License: Apache License, Version 2.0
 */


/*
$url = $_GET['url'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, '1');
if (!$html = curl_exec($ch)) {
   echo 'Error: '.curl_error($ch);
}
curl_close($ch);
$html = mb_convert_encoding($html, 'utf-8');

$r = new webReader($html, $url);
$r->init();
echo $r->articleContent->innerHTML;
*/


/**
 *
 */
class webReader {
   public $articleContent;
   public $dom;
   public $debug = false;
   public $sanitizeLevel = 1; // 1 = Return most HTML; 2 = Return only basic HTML
   public $keywords;
   protected $body = null;
   protected $flags = 7; // 1 | 2 | 4;   // Start with all flags set.
   protected $success = false; // indicates whether we were able to extract or not

   /**
    * All of the regular expressions in use within webReader.
    * Defined up here so we don't instantiate them repeatedly in loops.
    *Also, use the S directive to compile patterns
    **/
   public $regexps = array(
      'unlikelyCandidates' => '/combx|comment|community|disqus|extra|foot|header|menu|remark|rss|shoutbox|sidebar|sponsor|ad-break|agegate|pagination|pager|popup|tweet|twitter|advert|nav|aside/Sui',
      'candidates' => '/and|article|body|column|main|shadow|news|doc|wysiwyg|wrap|day|doc|page|description|post/Sui',
      'positive' => '/(?:c(?:o(?:nten(?:[tu]|ido)|rpo)|uerpo)|arti(?:c(?:[ou]lo|le)|go)|p(?:ag(?:ination|e)|ost)|(?:hent|sto)ry|entr(?:ada|y)|b(?:log|ody)|ingresso|main|text)/Sui',
      'negative' => '/(?:c(?:nn_stry(?:bt(?:ntoolsbttm|mcntnt)|l(?:ctcqrelt|ftcexpbx)|spcvh[1234]|ftsbttm)|o(?:m(?:m(?:ent(?:-(?:form|big)|s)?|unityside)|bx|-)|nt(?:ent-head|act))|(?:ritic-link|urrent)s|se-branding-right)|a(?:rticle(?:-(?:icon-links-container|readers)|paging|ad)|(?:td-disqus-disclaime|vata)r|d(?:dToCartSpan|vert-space)|nzeige|2a_dd)|p(?:r(?:int(?:-or-mail-links|_sharebutton|Desc)|o(?:cedure-number|mo))|ag(?:e(?:-bookmark-links-head|title)|inate)|ostmetabox)|s(?:h(?:o(?:pping|utbox)|areArticles)|tory(?:_features|-feature)|ub(?:scribe|Head)|(?:crol|ocia)l|ide(?:bar)?|ponsor)|f(?:o(?:ot(?:note|er)?|nt16)|b(?:c-recommend|like)|l(?:at-lis|_righ)t|eatured-review|riend_reviews|an_side)|t(?:o(?:matometer_bar_help|p-critics-numbers|ol(?:box)?|c)|a(?:b(?:-active)?|g(?:line|s))|erms|itle)|r(?:e(?:cipe-feedback|plieslist|sources|lated)|at(?:e-the-book|ing_widget)|ight(?:image)?|otulo)|b(?:bccom_visibility_hidden|lo(?:ck infobox|g-link)|igger pullquote|readcrumb|ottom|c)|m(?:o(?:re(?:-with-author|comments)|dule-list)|e(?:ta(?:stuff)?|dia|nu)|asthead|inor)|i(?:n(?:fo(?:b(?:ar|ox)|link)|sideStoryAd |ner)|shinfo)|news_(?:mo(?:rearticlesincat|dify)|category|title)|l(?:e(?:arnmor|ftimag)e|ist-supporting)|g(?:oogle(?:_branding_style|Ads)|amma)|e(?:zc_comments|ditsection|xpando)|v(?:ertical-navbox|card)|o(?:neClickDiv|utbrain)|w(?:rite-review|idget)|di(?:gg-button|scuss)|h(?:(?:ead|yp)er|ook)|quantityDropdownDiv|u(?:tilities|rail)|Skyscrapper_Body|journallist|z-menu|yrail|_ad)/Sui',
      'divToPElements' => '/<(a|blockquote|dl|div|img|ol|p|pre|table|ul)/Sui',
      'replaceBrs' => '/(<br[^>]*>[ \n\r\t]*){2,}/Sui',
      'replaceFonts' => '/<(\/?)font[^>]*>/Sui',
      'normalize' => '/\s{2,}/',
      'killBreaks' => '/(<br\s*\/?>(\s|&nbsp;?)*){1,}/',
      'video' => '/http:\/\/(www\.)?(youtube|vimeo|current|ted|atom|5min|hulu|netflix|dailymotion|facebook|metacafe|veoh|yahoo|google|break|myspace|viddler|truveo|liveleak|megavideo)\.com/Sui'
   );

   /* constants */
   const FLAG_STRIP_UNLIKELYS = 1;
   const FLAG_WEIGHT_CLASSES = 2;
   const FLAG_CLEAN_CONDITIONALLY = 4;

   /**
    * Create instance of webReader
    *
    * @param string UTF-8 encoded string
    */
   function __construct($html) {
      /* Turn all double br's into p's */
      $html = preg_replace($this->regexps['replaceBrs'], '</p><p>', $html);
      if (function_exists('mb_convert_encoding')) $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
      $this->dom = new DOMDocument();
      $this->dom->preserveWhiteSpace = false;
      $this->dom->registerNodeClass('DOMElement', 'JSLikeHTMLElement');
      if (trim($html) == '') $html = '<html></html>';
      @$this->dom->loadHTML($html);
   }

   /**
    * Get article content element
    * @return DOMElement
    */
   public function getContent() {
      return $this->articleContent;
   }

   /**
    * Runs webReader.
    *
    * @return boolean true if we found content, false otherwise
    **/
   public function init() {
      if (!isset($this->dom->documentElement)) return false;
      $this->removeScripts($this->dom);

      // Assume successful outcome
      $this->success = true;

      $bodyElems = $this->dom->getElementsByTagName('body');
      if ($bodyElems->length > 0) {
         if ($this->bodyCache == null) {
            $this->bodyCache = $bodyElems->item(0)->innerHTML;
         }
         if ($this->body == null) {
            $this->body = $bodyElems->item(0);
         }
      }

      $this->prepDocument();

      // Build webReader's DOM tree
      $overlay = $this->dom->createElement('div');
      $innerDiv = $this->dom->createElement('div');
      $articleContent = $this->grabArticle();

      if (!$articleContent) {
         $this->success = false;
         //$articleContent = $this->dom->createElement('div');
         //$articleContent->setAttribute('id', 'webReader-content');
         //$articleContent->innerHTML = '<p>webReader was unable to parse this page for content.</p>';
      }

      $overlay->setAttribute('id', 'readOverlay');
      $innerDiv->setAttribute('id', 'readInner');

      // Glue the structure of our document together.
      $innerDiv->appendChild($articleContent);
      $overlay->appendChild($innerDiv);

      // Clear the old HTML, insert the new content.
      $this->body->innerHTML = '';
      $this->body->appendChild($overlay);

      if ($this->sanitizeLevel == 2) {
         $this->body->removeAttribute('style');
      }

      // Set title and content instance variables
      $this->articleContent = $articleContent;

      return $this->success;
   }

   /**
    * Prepare the HTML document for webReader to scrape it.
    * This includes things like stripping javascript, CSS, and handling terrible markup.
    *
    * @return void
    **/
   protected function prepDocument() {
      // If there is no body, create a new body node and append it to the document.

      if ($this->body == null) {
         $this->body = $this->dom->createElement('body');
         $this->dom->documentElement->appendChild($this->body);
      }
      $this->body->setAttribute('id', 'webReaderBody');

      // Remove all style tags in head
      $styleTags = $this->dom->getElementsByTagName('style');
      for ($i = $styleTags->length - 1; $i >= 0; $i--)
      {
         $styleTags->item($i)->parentNode->removeChild($styleTags->item($i));
      }
   }

   /**
    * Reverts P elements with class 'webReader-styled'
    * to text nodes - which is what they were before.
    *
    * @param DOMElement
    *
    * @return void
    */
   function revertwebReaderStyledElements($articleContent) {
      $xpath = new DOMXPath($articleContent->ownerDocument);
      $elems = $xpath->query('.//p[@class="webReader-styled"]', $articleContent);
      for ($i = $elems->length - 1; $i >= 0; $i--) {
         $e = $elems->item($i);
         $e->parentNode->replaceChild($articleContent->ownerDocument->createTextNode($e->textContent), $e);
      }
   }

   /**
    * Prepare the article node for display. Clean out any inline styles,
    * iframes, forms, strip extraneous <p> tags, etc.
    *
    * @param DOMElement
    *
    * @return void
    */
   function prepArticle($articleContent) {
      $this->cleanStyles($articleContent);
      $this->killBreaks($articleContent);
      if ($this->revertForcedParagraphElements) {
         $this->revertwebReaderStyledElements($articleContent);
      }

      // Clean out junk from the article content
      $this->cleanConditionally($articleContent, 'form');
      $this->clean($articleContent, 'object');

      if ($this->sanitizeLevel == 2) {
         $this->clean($articleContent, 'h1');

         /**
          * If there is only one h2, they are probably using it
          * as a header and not a subheader, so remove it since we already have a header.
          ***/

         if ($articleContent->getElementsByTagName('h2')->length == 1) {
            $this->clean($articleContent, 'h2');
         }
         $this->clean($articleContent, 'iframe');

         $this->cleanHeaders($articleContent);

         // Do these last as the previous stuff may have removed junk that will affect these
         $this->cleanConditionally($articleContent, 'table');
         $this->cleanConditionally($articleContent, 'ul');
         $this->cleanConditionally($articleContent, 'div');
      }


      // Remove extra paragraphs
      $articleParagraphs = $articleContent->getElementsByTagName('p');
      for ($i = $articleParagraphs->length - 1; $i >= 0; $i--)
      {
         $imgCount = $articleParagraphs->item($i)->getElementsByTagName('img')->length;
         $embedCount = $articleParagraphs->item($i)->getElementsByTagName('embed')->length;
         $objectCount = $articleParagraphs->item($i)->getElementsByTagName('object')->length;

         if ($imgCount === 0 && $embedCount === 0 && $objectCount === 0 && $this->getInnerText($articleParagraphs->item($i), false) == '') {
            $articleParagraphs->item($i)->parentNode->removeChild($articleParagraphs->item($i));
         }
      }

      try {
         $articleContent->innerHTML = preg_replace('/<br[^>]*>\s*<p/i', '<p', $articleContent->innerHTML);
      }
      catch (Exception $e) {

      }
   }

   /**
    * Initialize a node with the webReader object. Also checks the
    * class Name/id for special names to add to its score.
    *
    * @param Element
    *
    * @return void
    **/
   protected function initializeNode($node) {
      $webReader = $this->dom->createAttribute('webReader');
      $webReader->value = 0; // content score
      $node->setAttributeNode($webReader);

      switch (strtoupper($node->tagName)) {

         case 'ARTICLE':
            $webReader->value += 7;

         case 'DIV':
            $webReader->value += 5;
            break;

         case 'PRE':
         case 'TD':
         case 'BLOCKQUOTE':
            $webReader->value += 3;
            break;

         case 'ADDRESS':
         case 'OL':
         case 'UL':
         case 'DL':
         case 'DD':
         case 'DT':
         case 'LI':
         case 'FORM':
            $webReader->value -= 3;
            break;

         case 'H1':
         case 'H2':
         case 'H3':
         case 'H4':
         case 'H5':
         case 'H6':
         case 'TH':
            $webReader->value -= 5;
            break;

         // Class names
         case 'hentry':
         case 'entry':
         case 'entry-content':
         case 'instapaper_body':
            $webReader->value += 10;
            break;

         case 'instapaper_ignore':
            $webReader->value -= 5;
            break;
      }
      $webReader->value += $this->getClassWeight($node);
   }

   /***
    * grabArticle - Using a variety of metrics (content score, class name, element types), find the content that is
    *               most likely to be the stuff a user wants to read. Then return it wrapped up in a div.
    *
    * @param null $page
    *
    * @return DOMElement
    */
   protected function grabArticle($page = null) {
      $stripUnlikelyCandidates = $this->flagIsActive(self::FLAG_STRIP_UNLIKELYS);
      if (!$page) $page = $this->dom;
      $allElements = $page->getElementsByTagName('*');

      // Remove nodes that do not look interesting and turn divs into P tags in cases where they have been used unnecessarily
      $node = null;
      $nodesToScore = array();
      for ($nodeIndex = 0; ($node = $allElements->item($nodeIndex)); $nodeIndex++) {
         $tagName = strtoupper($node->tagName);
         // Remove unlikely candidates
         if ($stripUnlikelyCandidates) {
            $unlikelyMatchString = $node->getAttribute('class').$node->getAttribute('id');
            if (
               preg_match($this->regexps['unlikelyCandidates'], $unlikelyMatchString) &&
               !preg_match($this->regexps['candidates'], $unlikelyMatchString) &&
               $tagName != 'BODY'
            ) {
               $this->dbg('Removing unlikely candidate - '.$unlikelyMatchString);
               $node->parentNode->removeChild($node);
               $nodeIndex--;
               continue;
            }
         }

         if ($tagName == 'P' || $tagName == 'TD' || $tagName == 'PRE') {
            $nodesToScore[] = $node;
         }

           // Turn all divs that don't have children block level elements into p's
         if ($tagName == 'DIV') {
            if (!preg_match($this->regexps['divToPElements'], $node->innerHTML)) {
               $newNode = $this->dom->createElement('p');
               try {
                  $newNode->innerHTML = $node->innerHTML;
                  $node->parentNode->replaceChild($newNode, $node);
                  $nodeIndex--;
                  $nodesToScore[] = $node; // or $newNode?
               }
               catch (Exception $e) {
                  $this->dbg('Could not alter div to p, reverting back to div.: '.$e);
               }
            }
            else
            {
               // EXPERIMENTAL
               // TODO: change these p elements back to text nodes after processing
               for ($i = 0, $il = $node->childNodes->length; $i < $il; $i++) {
                  $childNode = $node->childNodes->item($i);
                  if ($childNode->nodeType == 3) { // XML_TEXT_NODE
                     $p = $this->dom->createElement('p');
                     $p->innerHTML = $childNode->nodeValue;
                     $p->setAttribute('style', 'display: inline;');
                     $p->setAttribute('class', 'webReader-styled');
                     $childNode->parentNode->replaceChild($p, $childNode);
                  }
               }
            }
         }
      }

      /**
       * Loop through all paragraphs, and assign a score to them based on how content-y they look.
       * Then add their score to their parent node.
       *
       * A score is determined by things like number of commas, class names, etc. Maybe eventually link density.
       **/
      $candidates = array();
      for ($pt = 0; $pt < count($nodesToScore); $pt++) {
         $parentNode = $nodesToScore[$pt]->parentNode;
         $grandParentNode = !$parentNode ? null : (($parentNode->parentNode instanceof DOMElement) ? $parentNode->parentNode : null);
         $innerText = $this->getInnerText($nodesToScore[$pt]);

         if (!$parentNode || !isset($parentNode->tagName)) {
            continue;
         }

         // If this paragraph is less than 25 characters, don't even count it.
         if (strlen($innerText) < 25) {
            continue;
         }

         // Initialize webReader data for the parent.
         if (!$parentNode->hasAttribute('webReader')) {
            $this->initializeNode($parentNode);
            $candidates[] = $parentNode;
         }

         // Initialize webReader data for the grandparent.
         if ($grandParentNode && !$grandParentNode->hasAttribute('webReader') && isset($grandParentNode->tagName)) {
            $this->initializeNode($grandParentNode);
            $candidates[] = $grandParentNode;
         }

         $contentScore = 0;

         // Add a point for the paragraph itself as a base.
         $contentScore++;

         // Add points for any commas within this paragraph
         $contentScore += count(explode(',', $innerText));

         // For every 100 characters in this paragraph, add another point. Up to 3 points.
         $contentScore += min(floor(strlen($innerText) / 100), 3);

         // Add the score to the parent. The grandparent gets half.
         $parentNode->getAttributeNode('webReader')->value += $contentScore;

         if ($grandParentNode) {
            $grandParentNode->getAttributeNode('webReader')->value += $contentScore / 2;
         }
      }

      /**
       * After we've calculated scores, loop through all of the possible candidate nodes we found
       * and find the one with the highest score.
       **/
      $topCandidate = null;
      for ($c = 0, $cl = count($candidates); $c < $cl; $c++)
      {
         /**
          * Scale the final candidates score based on link density. Good content should have a
          * relatively small link density (5% or less) and be mostly unaffected by this operation.
          **/
         $webReader = $candidates[$c]->getAttributeNode('webReader');
         $webReader->value = $webReader->value * (1 - $this->getLinkDensity($candidates[$c]));

         $this->dbg('Candidate: '.$candidates[$c]->tagName.' ('.$candidates[$c]->getAttribute('class').':'.$candidates[$c]->getAttribute('id').') with score '.$webReader->value);

         if (!$topCandidate || $webReader->value > (int)$topCandidate->getAttribute('webReader')) {
            $topCandidate = $candidates[$c];
         }
      }

      /**
       * If we still have no top candidate, just use the body as a last resort.
       * We also have to copy the body node so it is something we can modify.
       **/
      if ($topCandidate === null || strtoupper($topCandidate->tagName) == 'BODY') {
         $topCandidate = $this->dom->createElement('div');
         if ($page instanceof DOMDocument) {
            if (!isset($page->documentElement)) {
               // No body
            } else {
               $topCandidate->innerHTML = $page->documentElement->innerHTML;
               $page->documentElement->innerHTML = '';
               $page->documentElement->appendChild($topCandidate);
            }
         } else {
            $topCandidate->innerHTML = $page->innerHTML;
            $page->innerHTML = '';
            $page->appendChild($topCandidate);
         }
       }

       /**
       * Now that we have the top candidate, look through its siblings for content that might also be related.
       * Things like preambles, content split by ads that we removed, etc.
       **/
      $articleContent = $this->dom->createElement('div');
      $articleContent->setAttribute('id', 'webReader-content');
      $siblingScoreThreshold = max(10, ((int)$topCandidate->getAttribute('webReader')) * 0.2);
      $siblingNodes = $topCandidate->parentNode->childNodes;
      if (!isset($siblingNodes)) {
         $siblingNodes = new stdClass;
         $siblingNodes->length = 0;
      }

      for ($s = 0, $sl = $siblingNodes->length; $s < $sl; $s++)
      {
         $siblingNode = $siblingNodes->item($s);
         $append = false;

         $this->dbg('Looking at sibling node: '.$siblingNode->nodeName.(($siblingNode->nodeType === XML_ELEMENT_NODE && $siblingNode->hasAttribute('webReader')) ? (' with score '.$siblingNode->getAttribute('webReader')) : ''));

         if ($siblingNode === $topCandidate
         ) // or if ($siblingNode->isSameNode($topCandidate))
         {
            $append = true;
         }

         $contentBonus = 0;
         // Give a bonus if sibling nodes and top candidates have the example same class name
         if ($siblingNode->nodeType === XML_ELEMENT_NODE && $siblingNode->getAttribute('class') == $topCandidate->getAttribute('class') && $topCandidate->getAttribute('class') != '') {
            $contentBonus += ((int)$topCandidate->getAttribute('webReader')) * 0.2;
         }

         if ($siblingNode->nodeType === XML_ELEMENT_NODE && $siblingNode->hasAttribute('webReader') && (((int)$siblingNode->getAttribute('webReader')) + $contentBonus) >= $siblingScoreThreshold) {
            $append = true;
         }

         if (strtoupper($siblingNode->nodeName) == 'P') {
            $linkDensity = $this->getLinkDensity($siblingNode);
            $nodeContent = $this->getInnerText($siblingNode);
            $nodeLength = strlen($nodeContent);

            if ($nodeLength > 80 && $linkDensity < 0.25) {
               $append = true;
            }
            else {
               if ($nodeLength < 80 && $linkDensity === 0 && preg_match('/\.( |$)/', $nodeContent)) {
                  $append = true;
               }
            }
         }

         if ($append) {
            $this->dbg('Appending node: '.$siblingNode->nodeName);

            $nodeToAppend = null;
            $sibNodeName = strtoupper($siblingNode->nodeName);
            if ($sibNodeName != 'DIV' && $sibNodeName != 'P') {
               // We have a node that isn't a common block level element, like a form or td tag.
               // Turn it into a div so it doesn't get filtered out later by accident.

               $this->dbg('Altering siblingNode of '.$sibNodeName.' to div.');
               $nodeToAppend = $this->dom->createElement('div');
               try {
                  $nodeToAppend->setAttribute('id', $siblingNode->getAttribute('id'));
                  $nodeToAppend->innerHTML = $siblingNode->innerHTML;
               }
               catch (Exception $e)
               {
                  $this->dbg('Could not alter siblingNode to div, reverting back to original.');
                  $nodeToAppend = $siblingNode;
                  $s--;
                  $sl--;
               }
            } else {
               $nodeToAppend = $siblingNode;
               $s--;
               $sl--;
            }

            // To ensure a node does not interfere with webReader styles, remove its class names
            if ($this->sanitizeLevel == 2) {
               $nodeToAppend->removeAttribute('class');
            }

            // Append sibling and subtract from our list because it removes the node when you append to another node
            $articleContent->appendChild($nodeToAppend);
         }
      }

      // So we have all of the content that we need. Now we clean it up for presentation.
      $this->prepArticle($articleContent);

      /**
       * Now that we've gone through the full algorithm, check to see if we got any meaningful content.
       * If we didn't, we may need to re-run grabArticle with different flags set. This gives us a higher
       * likelihood of finding the content, and the sieve approach gives us a higher likelihood of
       * finding the -right- content.
       **/
      if (strlen($this->getInnerText($articleContent, false)) < 250) {
         // TODO: find out why element disappears sometimes, e.g. for this URL
         // in the meantime, we check and create an empty element if it's not there.
         if (!isset($this->body->childNodes)) $this->body = $this->dom->createElement('body');
         $this->body->innerHTML = $this->bodyCache;

         if ($this->flagIsActive(self::FLAG_STRIP_UNLIKELYS)) {
            $this->removeFlag(self::FLAG_STRIP_UNLIKELYS);
            return $this->grabArticle($this->body);
         }
         else {
            if ($this->flagIsActive(self::FLAG_WEIGHT_CLASSES)) {
               $this->removeFlag(self::FLAG_WEIGHT_CLASSES);
               return $this->grabArticle($this->body);
            }
            else {
               if ($this->flagIsActive(self::FLAG_CLEAN_CONDITIONALLY)) {
                  $this->removeFlag(self::FLAG_CLEAN_CONDITIONALLY);
                  return $this->grabArticle($this->body);
               }
               else {
                  return false;
               }
            }
         }
      }
      return $articleContent;
   }

   /**
    * Remove script tags from document
    *
    * @param DOMElement
    *
    * @return void
    */
   public function removeScripts($doc) {
      $scripts = $doc->getElementsByTagName('script');
      for ($i = $scripts->length - 1; $i >= 0; $i--)
      {
         $scripts->item($i)->parentNode->removeChild($scripts->item($i));
      }
   }

   /**
    * Get the inner text of a node.
    * This also strips out any excess whitespace to be found.
    *
    * @param $e
    * @param boolean $normalizeSpaces (default: true)
    *
    * @internal param $DOMElement $
    * @return string
    */
   public function getInnerText($e, $normalizeSpaces = true) {
      $textContent = '';

      if (!isset($e->textContent) || $e->textContent == '') {
         return '';
      }

      $textContent = trim($e->textContent);

      if ($normalizeSpaces) {
         return preg_replace($this->regexps['normalize'], ' ', $textContent);
      } else {
         return $textContent;
      }
   }

   /**
    * Get the number of times a string $s appears in the node $e.
    *
    * @param DOMElement $e
    * @param string - what to count. Default is ","
    *
    * @return number (integer)
    **/
   public function getCharCount($e, $s = ',') {
      return substr_count($this->getInnerText($e), $s);
   }

   /**
    * Remove the style attribute on every $e and under.
    *
    * @param DOMElement $e
    *
    * @return void
    */
   public function cleanStyles($e) {
      if ($this->sanitizeLevel == 2) {
         if (!is_object($e)) return;
         $elems = $e->getElementsByTagName('*');
         foreach ($elems as $elem) {
            $elem->removeAttribute('style');
         }
      }
   }

   /**
    * Get the density of links as a percentage of the content
    * This is the amount of text that is inside a link divided by the total text in the node.
    *
    * @param DOMElement $e
    *
    * @return number (float)
    */
   public function getLinkDensity($e) {
      $links = $e->getElementsByTagName('a');
      $textLength = strlen($this->getInnerText($e));
      $linkLength = 0;
      for ($i = 0, $il = $links->length; $i < $il; $i++)
      {
         $linkLength += strlen($this->getInnerText($links->item($i)));
      }
      if ($textLength > 0) {
         return $linkLength / $textLength;
      } else {
         return 0;
      }
   }

   /**
    * Get an elements class/id weight. Uses regular expressions to tell if this
    * element looks good or bad.
    *
    * @param DOMElement $e
    *
    * @return number (Integer)
    */
   public function getClassWeight($e) {
      if (!$this->flagIsActive(self::FLAG_WEIGHT_CLASSES)) {
         return 0;
      }

      $weight = 0;

      // Look for a special class name
      if ($e->hasAttribute('class') && $e->getAttribute('class') != '') {
         if (preg_match($this->regexps['negative'], $e->getAttribute('class'))) {
            $weight -= 30;
         }
         if (preg_match($this->regexps['positive'], $e->getAttribute('class'))) {
            $weight += 25;
         }
      }

      // Look for a special ID
      if ($e->hasAttribute('id') && $e->getAttribute('id') != '') {
         if (preg_match($this->regexps['negative'], $e->getAttribute('id'))) {
            $weight -= 25;
         }
         if (preg_match($this->regexps['positive'], $e->getAttribute('id'))) {
            $weight += 25;
         }
      }
      return $weight;
   }

   /**
    * Remove extraneous break tags from a node.
    *
    * @param DOMElement $node
    *
    * @return void
    */
   public function killBreaks($node) {
      $html = $node->innerHTML;
      $html = preg_replace($this->regexps['killBreaks'], '<br />', $html);
      $node->innerHTML = $html;
   }

   /**
    * Clean a node of all elements of type "tag".
    * (Unless it's a popular video site)
    *
    * @param DOMElement $e
    * @param string $tag
    *
    * @return void
    */
   public function clean($e, $tag) {
      $targetList = $e->getElementsByTagName($tag);
      $isEmbed = ($tag == 'object' || $tag == 'embed');

      for ($y = $targetList->length - 1; $y >= 0; $y--) {
         /* Allow popular video sites through as people usually want to see those. */

         if ($isEmbed && $this->sanitizeLevel == 1) {
            $attributeValues = '';
            for ($i = 0, $il = $targetList->item($y)->attributes->length; $i < $il; $i++) {
               $attributeValues .= $targetList->item($y)->attributes->item($i)->value.'|';
            }

            /* First, check the elements attributes to see if any of them contain the name of a video site
            */
            if (preg_match($this->regexps['video'], $attributeValues)) {
               continue;
            }

            /* Then check the elements inside this element for the same. */
            if (preg_match($this->regexps['video'], $targetList->item($y)->innerHTML)) {
               continue;
            }
         }
         $targetList->item($y)->parentNode->removeChild($targetList->item($y));
      }
   }

   /**
    * Clean an element of all tags of type "tag" if they look fishy.
    * "Fishy" is an algorithm based on content length, class names,
    * link density, number of images & embeds, etc.
    *
    * @param DOMElement $e
    * @param string $tag
    *
    * @return void
    */
   public function cleanConditionally($e, $tag) {
      if (!$this->flagIsActive(self::FLAG_CLEAN_CONDITIONALLY)) {
         return;
      }

      $tagsList = $e->getElementsByTagName($tag);
      $curTagsLength = $tagsList->length;

      /**
       * Gather counts for other typical elements embedded within.
       * Traverse backwards so we can remove nodes at the same time without effecting the traversal.
       *
       * TODO: Consider taking into account original contentScore here.
       */
      for ($i = $curTagsLength - 1; $i >= 0; $i--) {
         $weight = $this->getClassWeight($tagsList->item($i));
         $contentScore = ($tagsList->item($i)->hasAttribute('webReader')) ? (int)$tagsList->item($i)->getAttribute('webReader') : 0;

         $this->dbg('Cleaning Conditionally '.$tagsList->item($i)->tagName.' ('.$tagsList->item($i)->getAttribute('class').':'.$tagsList->item($i)->getAttribute('id').')'.(($tagsList->item($i)->hasAttribute('webReader')) ? (' with score '.$tagsList->item($i)->getAttribute('webReader')) : ''));

         if ($weight + $contentScore < 0) {
            $tagsList->item($i)->parentNode->removeChild($tagsList->item($i));
         }
         else {
            if ($this->getCharCount($tagsList->item($i), ',') < 10) {
               /**
                * If there are not very many commas, and the number of
                * non-paragraph elements is more than paragraphs or other ominous signs, remove the element.
                **/
               $p = $tagsList->item($i)->getElementsByTagName('p')->length;
               $img = $tagsList->item($i)->getElementsByTagName('img')->length;
               $li = $tagsList->item($i)->getElementsByTagName('li')->length - 100;
               $input = $tagsList->item($i)->getElementsByTagName('input')->length;

               $embedCount = 0;
               $embeds = $tagsList->item($i)->getElementsByTagName('embed');
               for ($ei = 0, $il = $embeds->length; $ei < $il; $ei++) {
                  if (preg_match($this->regexps['video'], $embeds->item($ei)->getAttribute('src'))) {
                     $embedCount++;
                  }
               }

               $linkDensity = $this->getLinkDensity($tagsList->item($i));
               $contentLength = strlen($this->getInnerText($tagsList->item($i)));
               $toRemove = false;

               if ($img > $p) {
                  $toRemove = true;
               } else {
                  if ($li > $p && $tag != 'ul' && $tag != 'ol') {
                     $toRemove = true;
                  } else {
                     if ($input > floor($p / 3)) {
                        $toRemove = true;
                     } else {
                        if ($contentLength < 25 && ($img === 0 || $img > 2)) {
                           $toRemove = true;
                        } else {
                           if ($weight < 25 && $linkDensity > 0.2) {
                              $toRemove = true;
                           } else {
                              if ($weight >= 25 && $linkDensity > 0.5) {
                                 $toRemove = true;
                              } else {
                                 if (($embedCount == 1 && $contentLength < 75) || $embedCount > 1) {
                                    $toRemove = true;
                                 }
                              }
                           }
                        }
                     }
                  }
               }

               if ($toRemove) {
                  $tagsList->item($i)->parentNode->removeChild($tagsList->item($i));
               }
            }
         }
      }
   }

   /**
    * Clean out spurious headers from an Element. Checks things like class names and link density.
    *
    * @param DOMElement $e
    *
    * @return void
    */
   public function cleanHeaders($e) {
      for ($headerIndex = 1; $headerIndex < 3; $headerIndex++) {
         $headers = $e->getElementsByTagName('h'.$headerIndex);
         for ($i = $headers->length - 1; $i >= 0; $i--) {
            if ($this->getClassWeight($headers->item($i)) < 0 || $this->getLinkDensity($headers->item($i)) > 0.33) {
               $headers->item($i)->parentNode->removeChild($headers->item($i));
            }
         }
      }
   }

   /**
    * @param $flag
    *
    * @return bool
    */
   public function flagIsActive($flag) {
      return ($this->flags & $flag) > 0;
   }

   /**
    * @param $flag
    */
   public function addFlag($flag) {
      $this->flags = $this->flags | $flag;
   }

   /**
    * @param $flag
    */
   public function removeFlag($flag) {
      $this->flags = $this->flags & ~$flag;
   }

   /**
    * Debug
    */
   protected function dbg($msg) {
      if ($this->debug) echo '* ',$msg, '<br />', "\n";
   }
}

/**
 * JavaScript-like HTML DOM Element
 *
 * This class extends PHP DOMElement to allow
 * users to get and set the innerHTML property of
 * HTML elements in the same way it's done in
 * JavaScript.
 *
 * @author Keyvan Minoukadeh - http://www.keyvan.net - keyvan@keyvan.net
 * @see http://fivefilters.org (the project this was written for)
 */
class JSLikeHTMLElement extends DOMElement {
   /**
    * Used for setting innerHTML like it's done in JavaScript:
    * @code
    * $div->innerHTML = '<h2>Chapter 2</h2><p>The story begins...</p>';
    * @endcode
    *
    * @param $name
    * @param $value
    */
   public function __set($name, $value) {
      if ($name == 'innerHTML') {
         // first, empty the element
         for ($x = $this->childNodes->length - 1; $x >= 0; $x--) {
            $this->removeChild($this->childNodes->item($x));
         }
         // $value holds our new inner HTML
         if ($value != '') {
            $f = $this->ownerDocument->createDocumentFragment();
            // appendXML() expects well-formed markup (XHTML)
            $result = @$f->appendXML($value); // @ to suppress PHP warnings
            if ($result) {
               if ($f->hasChildNodes()) $this->appendChild($f);
            } else {
               // $value is probably ill-formed
               $f = new DOMDocument();
               $value = mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8');
               // Using <htmlfragment> will generate a warning, but so will bad HTML
               // (and by this point, bad HTML is what we've got).
               // We use it (and suppress the warning) because an HTML fragment will
               // be wrapped around <html><body> tags which we don't really want to keep.
               // Note: despite the warning, if loadHTML succeeds it will return true.
               $result = @$f->loadHTML('<htmlfragment>'.$value.'</htmlfragment>');
               if ($result) {
                  $import = $f->getElementsByTagName('htmlfragment')->item(0);
                  foreach ($import->childNodes as $child) {
                     $importedNode = $this->ownerDocument->importNode($child, true);
                     $this->appendChild($importedNode);
                  }
               } else {
                  // this element is now empty
               }
            }
         }
      } else {
         $trace = debug_backtrace();
         trigger_error('Undefined property via __set(): '.$name.' in '.$trace[0]['file'].' on line '.$trace[0]['line'], E_USER_NOTICE);
      }



   }

   /**
    * Used for getting innerHTML like it is done in JavaScript:
    * @code
    * $string = $div->innerHTML;
    * @endcode
    *
    * @param $name
    *
    * @return null|string
    */
   public function __get($name) {
      if ($name == 'innerHTML') {
         $inner = '';
         foreach ($this->childNodes as $child) {
            $inner .= $this->ownerDocument->saveXML($child);
         }
         return $inner;
      }

      $trace = debug_backtrace();
      trigger_error('Undefined property via __get(): '.$name.' in '.$trace[0]['file'].' on line '.$trace[0]['line'], E_USER_NOTICE);
      return null;
   }

   /**
    * @return string
    */
   public function __toString() {
      return '['.$this->tagName.']';
   }

}
