<?php

namespace Module\Dashboard\Bundle\Crud\Component;

enum CrudEnum:string
{
    case CREATE = 'create';
    case READ = 'read';
    case UPDATE = 'update';
    case DELETE = 'delete';
}