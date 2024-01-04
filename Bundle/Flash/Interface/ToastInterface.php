<?php

namespace Module\Dashboard\Bundle\Flash\Interface;

use Module\Dashboard\Bundle\Flash\Abstract\AbstractFlashConcept;

interface ToastInterface
{
    const POSITION_TOP = 1;
    const POSITION_RIGHT = 2;
    const POSITION_BOTTOM = 3;
    const POSITION_LEFT = 4;
    const POSTION_MIDDLE = 5;
    const POSITION_CENTER = 6;

    public function setPosition(int $yAxis, int $xAxis): AbstractFlashConcept;
}