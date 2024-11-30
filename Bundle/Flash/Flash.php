<?php

namespace Module\Dashboard\Bundle\Flash;

use Module\Dashboard\Bundle\Flash\Abstract\AbstractFlash;
use Module\Dashboard\Bundle\Flash\Modal\Graphics\ModalGUI;
use Module\Dashboard\Bundle\Flash\Modal\Modal;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Module\Dashboard\Bundle\Flash\Toast\ToastGUI;
use Uss\Component\Trait\SingletonTrait;

/** 
 * use SQLCipher to encrypt SQLite
 * 
 * This way, even if someone gains access to the file, they won't be able to read its contents without the encryption key.
 */
class Flash extends AbstractFlash
{
    use SingletonTrait;

    
    public function addModal(Modal $modal, ?string $name = null): self
    {
        $modalGUI = new ModalGUI();
        
        $this->projectOnEscapeLogic($modal, $modalGUI);

        $bootboxComponents = $modalGUI->createBootboxComponents($modal); // (array|null)

        if($bootboxComponents) 
        {
            $javascriptObject = $modalGUI->createJavascriptObject($bootboxComponents, null); // (string)
            
            $key = session_id();
            $delay = $modal->getDelay();
            $content = "<script>$(() => setTimeout(() => console.log(bootbox.dialog({$javascriptObject})), {$delay}));</script>";
            
            $modalContext = [
                'timestamp' => time(),
                'content' => $content,
            ];
            
            $this->flash->{$key} ??= [];
            $this->flash->{$key}['created'] ??= time();
            $this->flash->{$key}['modal'] ??= [];
            
            $name !== null ?
                $this->flash->{$key}['modal'][$name] = $modalContext :
                $this->flash->{$key}['modal'][] = $modalContext;

            $this->flash->save();
        }

        return $this;
    }

    public function addToast(Toast $toast, ?string $name = null): self
    {
        $toastGUI = new ToastGUI();

        $toastifyComponents = $toastGUI->createToastifyComponents($toast);
        
        if($toastifyComponents) 
        {
            $javascriptObject = $toastGUI->createJavascriptObject($toastifyComponents, null); // (string)
            
            $key = session_id();
            $delay = $toast->getDelay();
            $content = "<script>$(() => setTimeout(() => console.log(Toastify({$javascriptObject}).showToast()), {$delay}));</script>";
            
            $toastContext = [
                'timestamp' => time(),
                'content' => $content
            ];

            $this->flash->{$key} ??= [];
            $this->flash->{$key}['created'] ??= time();
            $this->flash->{$key}['toast'] ??= [];

            $name !== null ?
                $this->flash->{$key}['toast'][$name] = $toastContext :
                $this->flash->{$key}['toast'][] = $toastContext;
            
            $this->flash->save();
        }

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
