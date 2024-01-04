<?php

namespace Module\Dashboard\Bundle\Flash\Abstract;

use Module\Dashboard\Bundle\Flash\Interface\FlashInterface;
use Ucscode\LocalStorage\LocalStorage;
use Uss\Component\Block\BlockManager;

abstract class AbstractFlash implements FlashInterface
{
    protected string $filepath = __DIR__ . '/../flash.txt';
    protected LocalStorage $flash;

    public function __construct()
    {
        $this->refreshFlash();
    }

    /**
     * This effect of this method is available only to user synthetics dashboard. 
     * If you want to use the Flash on a GUI not associated with Uss Dashboard, you need to call this method explicitly
     */
    public function dump(): void
    {
        $allContext = $this->flash->getContext();

        foreach($allContext as $session_id => $context) 
        {
            // Test for online presence to sustain flash session
            if(session_id() == $session_id) {
                $this->flash->{$session_id}['created'] = time(); // increase active user time
            };

            $time = (time() - $context['created']) / 86400;

            if($time >= 0.1) {
                // flash has stay too long and should be discarded
                unset($this->flash->{$session_id});
                continue;
            }

            foreach($context['modal'] as $key => $data) 
            {
                $time = (time() - $data['timestamp']) / 86400;
                
                if($time < 0.1) {
                    // The time is appropriate
                    BlockManager::instance()
                        ->getBlock("body_javascript")
                        ->addContent("{$session_id}:{$key}", $data['content']);
                };
                
                // Discard entity after pasting it
                unset($this->flash->{$session_id}['modal'][$key]);
            }
        }

        // Save changes to flash rendering;
        $this->flash->save();
    }

    public function setFilepath(string $filepath): self
    {
        $this->filepath = $filepath;
        $this->refreshFlash();
        return $this;
    }

    protected function refreshFlash(): void
    {
        $this->flash = new LocalStorage($this->filepath);
    }
}