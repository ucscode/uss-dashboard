<?php

class AdminLoginForm extends UserLoginForm
{
    protected function buildForm()
    {
        parent::buildForm();

        $mailBlock = $this->find('#reactive-mailer', 0);
        
        if($mailBlock) {
            $mailBlock->parentElement->removeChild($mailBlock);
        };
    }
}
