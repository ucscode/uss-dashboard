<?php

namespace Module\Dashboard\Bundle\Flash\Abstract;

use DateTime;
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
        $timeLimit = 0.01;

        foreach($allContext as $session_id => $context) 
        {
            $isFlashForCurrentUser = session_id() == $session_id;

            if($isFlashForCurrentUser) {
                $this->flash->{$session_id}['created'] = time(); // increase active user time
            };
            
            $time = (time() - $context['created']) / 86400;
            
            if($time >= $timeLimit) {
                // flash has stay too long and should be discarded
                unset($this->flash->{$session_id});
                continue;
            }

            $indexes = ['modal', 'toast'];
            
            foreach($indexes as $index) 
            {
                $item = $context[$index] ?? [];

                foreach($item as $key => $data) 
                {
                    $time = (time() - $data['timestamp']) / 86400;
                    
                    if($time < $timeLimit && $isFlashForCurrentUser) {
                        BlockManager::instance()
                            ->getBlock("body_javascript")
                            ->addContent("{$session_id}:{$index}:{$key}", $data['content']);
                    };
                    
                    // Discard entity after pasting it
                    unset($this->flash->{$session_id}[$index][$key]);
                }
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