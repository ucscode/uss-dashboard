<?php

use Ucscode\UssElement\UssElement;

class Paginator extends \JasonGrimes\Paginator
{
    /**
     * @method getPreviousText
     */
    public function getPreviousText()
    {
        return $this->previousText;
    }

    /**
     * @method getNextText()
     */
    public function getNextText()
    {
        return $this->nextText;
    }

    /**
     * @method getElement
     */
    public function getElement(): UssElement
    {
        $nav = new UssElement(UssElement::NODE_DIV);
        $nav->setAttribute('class', 'navigation');

        $ul = new UssElement(UssElement::NODE_UL);
        $ul->setAttribute('class', 'pagination');
        $nav->appendChild($ul);

        if($this->getPrevUrl()) {
            $ul->appendChild(
                $this->newItem(
                    null, 
                    $this->getPrevUrl(), 
                    '&laquo; ' . $this->getPreviousText()
                )
            );
        }

        foreach($this->getPages() as $page) {
            if(!empty($page['url'])) {
                $item = $this->newItem(
                    $page['isCurrent'] ? 'active' : null,
                    $page['url'],
                    $page['num']
                );
            } else {
                $item = $this->newItem(
                    'disabled',
                    null,
                    $page['num']
                );
            };
            $ul->appendChild($item);
        };

        if($this->getNextUrl()) {
            $ul->appendChild(
                $this->newItem(
                    null, 
                    $this->getNextUrl(), 
                    $this->getNextText() . ' &raquo;'
                )
            );
        }
        return $nav;
    }

    /**
     * @method newItem
     */
    protected function newItem(?string $class, ?string $href, string $label): UssElement
    {
        $li = new UssElement(UssElement::NODE_LI);
        $li->setAttribute('class', 'page-item ' . $class);
        $node = new UssElement(!is_null($href) ? UssElement::NODE_A : UssElement::NODE_SPAN);
        $node->setAttribute('class', 'page-link');
        if($href) {
            $node->setAttribute('href', $href);
        }
        $node->setContent($label);
        $li->appendChild($node);
        return $li;
    }
}
