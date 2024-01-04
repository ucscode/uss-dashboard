<?php

namespace Module\Dashboard\Bundle\Flash;

use Module\Dashboard\Bundle\Flash\Abstract\AbstractFlashConcept;
use Module\Dashboard\Bundle\Flash\Interface\ToastInterface;

class Toast extends AbstractFlashConcept implements ToastInterface
{
    protected int $xAxis;
    protected int $yAxis;

    public function __construct()
    {
        parent::__construct();
        // code here
    }

    public function setPosition(int $yAxis = self::POSITION_TOP, int $xAxis = self::POSITION_RIGHT): AbstractFlashConcept
    {
        return $this;
    }
}