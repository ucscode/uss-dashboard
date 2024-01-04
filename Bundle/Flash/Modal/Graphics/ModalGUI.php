<?php

namespace Module\Dashboard\Bundle\Flash\Modal\Graphics;

use Module\Dashboard\Bundle\Flash\Abstract\AbstractGUI;
use Module\Dashboard\Bundle\Flash\Modal\Button;
use Module\Dashboard\Bundle\Flash\Modal\Modal;

class ModalGUI extends AbstractGUI
{
    public function createBootboxComponents(Modal $modal): ?array
    {
        if(!empty($modal->getMessage())) 
        {
            $bootboxObject = [
                'message' => $this->stringify($modal->getMessage()),
                'title' => $this->stringify($modal->getTitle()),
                'size' => $this->stringify($modal->getSize()),
                'closeButton' => $modal->isCloseButtonEnabled(),
                'keyboard' => $modal->isKeyboardEnabled(),
                'buttons' => [],
            ];
            
            $bootboxObject['backdrop'] = 
                $modal->isBackdropEnabled() ? 
                    ($modal->isBackdropStaticEnabled() ? $this->stringify('static') : true) : false;
            
            $buttons = $modal->getButtons();
            
            array_walk($buttons, function(Button $button, $name) use (&$bootboxObject)
            {
                $bootboxObject['buttons'][$name] = [
                    'label' => $this->stringify($button->getLabel()),
                    'className' => $this->stringify($button->getClassName()),
                ];

                $callback = $this->validateJSCallback($button->getCallback());

                if(!empty($callback)) {
                    $bootboxObject['buttons'][$name]['callback'] = 'event => ' . $callback . '(event)';
                }
            });

            $callbacks = $modal->getCustomCallbacks();

            $bootboxObject += array_map(function($value) {
                return 'event => ' . $this->validateJSCallback($value) . '(event)';
            }, $callbacks);

            return $bootboxObject;
        }
        return null;
    }
}