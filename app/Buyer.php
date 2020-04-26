<?php

namespace App;

class Buyer extends User
{
    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }
}
