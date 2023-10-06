<?php

class Paginator extends JasonGrimes\Paginator
{
    public function getPreviousText()
    {
        return $this->previousText;
    }

    public function getNextText()
    {
        return $this->nextText;
    }

}
