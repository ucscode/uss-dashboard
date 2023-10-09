<?php

# Ensure that this file is required within the Ud instantiation

if(!class_exists('\\Ud') || !isset($this) || !($this instanceof Ud)) {
    die('Ud: This file is not properly implemented');
};
