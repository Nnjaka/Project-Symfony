<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

class UserExists extends Constraint
{
    public $message = "User account does't exists.";
}
