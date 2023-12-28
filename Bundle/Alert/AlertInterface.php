<?php

namespace Module\Dashboard\Bundle\Alert;

interface AlertInterface 
{
    public const SESSID = 'v-alert';
    public const TYPE_NOTIFICATION = 'notification';
    public const TYPE_MODAL = 'modal';
    public const DISPLAY_ERROR = 'error';
    public const DISPLAY_SUCCESS = 'success';
    public const DISPLAY_WARNING = 'warning';
    public const DISPLAY_INFO = 'info';
}