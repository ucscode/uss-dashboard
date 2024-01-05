<?php

namespace Module\Dashboard\Bundle\Flash;

use Module\Dashboard\Bundle\Flash\Abstract\AbstractFlash;
use Module\Dashboard\Bundle\Flash\Modal\Button;
use Module\Dashboard\Bundle\Flash\Modal\Graphics\ModalGUI;
use Module\Dashboard\Bundle\Flash\Modal\Modal;
use stdClass;
use Uss\Component\Block\BlockManager;
use Uss\Component\Trait\SingletonTrait;

/** 
 * use SQLCipher to encrypt SQLite
 * 
 * This way, even if someone gains access to the file, they won't be able to read its contents without the encryption key.
 */
class Flash extends AbstractFlash
{
    use SingletonTrait;

    
    public function addModal(string $name, Modal $modal): self
    {
        $modalGUI = new ModalGUI();
        
        $this->projectOnEscapeLogic($modal, $modalGUI);

        $bootboxComponents = $modalGUI->createBootboxComponents($modal); // (array)
        $javascriptObject = $modalGUI->createJavascriptObject($bootboxComponents, null); // (string)
        
        $key = session_id();
        $delay = $modal->getDelay();
        $content = "<script>$(() => setTimeout(() => bootbox.dialog({$javascriptObject}), {$delay}));</script>";
        
        $this->flash->{$key} ??= [];
        $this->flash->{$key}['created'] ??= time();
        $this->flash->{$key}['modal'] ??= [];
        $this->flash->{$key}['modal'][] = [
            'timestamp' => time(),
            'content' => $content
        ];

        //$this->flash->save();

        return $this;
    }

    public function addToast(string $name, Toast $toast): self
    {
        return $this;
    }

    private function projectOnEscapeLogic(Modal $modal, ModalGUI $modalGUI): void
    {
        $escapeCallback = $modalGUI->validateJSCallback($modal->getCustomCallback("onEscape"));
        $defaultButton = $modal->getButton(Modal::DEFAULT_BUTTON);
        if(!empty($escapeCallback) && $defaultButton) {
            if($defaultButton->getCallback() === null) {
                $defaultButton->setCallback($escapeCallback);
                $defaultButton->setCallbackValue($modal->getCustomCallback('onEscape', true));
            };
        }
    }
}
