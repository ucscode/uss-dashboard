<?php

namespace Module\Dashboard\Bundle\Flash\Toast;

use Module\Dashboard\Bundle\Flash\Abstract\AbstractGUI;
use Module\Dashboard\Bundle\Flash\Interface\ToastInterface;

class ToastGUI extends AbstractGUI
{
    final public function createToastifyComponents(Toast $toast): ?array
    {
        if($toast->getMessage() !== null) 
        {
            $axis = $this->derivePositionAxis($toast->getPosition());
            
            $toastComponents = [
                'text' => $this->stringify($toast->getMessage()),
                'className' => $this->stringify($toast->getClassName() ?? ''),
                'avatar' => $this->stringify($toast->getAvatar() ?? ''),
                'close' => $toast->isAutoCloseEnabled(),
                'destination' => $this->stringify($toast->getDestination()),
                'newWindow' => $toast->isDestinationNewWindowEnabled(),
                'duration' => $toast->getDuration(),
                'position' => $this->stringify($axis['position']),
                'gravity' => $this->stringify($axis['gravity']),
            ];

            $callbacks = $toast->getCustomCallbacks();

            $toastComponents += array_map(function($context) {
                return $this->generateJSCallback($context['callback'], $context['value']);
            }, $callbacks);

            $styles = $toast->getStyles();

            if(!empty($styles)) {
                $toastComponents['style'] = [];
                foreach($styles as $key => $value) {
                    $toastComponents['style'][$key] = $this->stringify($value);
                }
            }

            return $toastComponents;
        };
        return null;
    }

    protected function derivePositionAxis(array $position): array
    {
        $axis = [];

        $axis['gravity'] = match($position['yAxis']) {
            ToastInterface::POSITION_BOTTOM => 'bottom',
            default => 'top',
        };

        $axis['position'] = match($position['xAxis']) {
            ToastInterface::POSITION_LEFT => 'left',
            ToastInterface::POSITION_CENTER => 'center',
            default => 'right',
        };

        return $axis;
    }
}