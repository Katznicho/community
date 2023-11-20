<?php

namespace App\Traits;

trait UserTrait
{
    public function checkPin(string $pin)
    {
        //check user pin
        return true;
    }


    public function getAccountBalance(string $phoneNumber)
    {
        //get account balance
        return 1000;
    }

    
}
