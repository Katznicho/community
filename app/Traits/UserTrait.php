<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

trait UserTrait
{
    public function checkPin(string $pin, string $phoneNumber)
    {
        //check user pin
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        $hashedPin = $getUser->pin;
        if (Hash::check($pin, $hashedPin)) {
            return true;
        } else {
            return false;
        }
    }

    public function updatePin(string $pin, string $phoneNumber)
    {
        //update user pin
        $hashedPin = Hash::make($pin);
        DB::table('users')->where('phone_number', $phoneNumber)->update(['pin' => $hashedPin]);
        return true;
    }


    public function getAccountBalance(string $phoneNumber)
    {
        //get user account balance
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        return  "UGX " . "" . $getUser->account_balance;
    }

    public function checkIfUserExists(string $phoneNumber)
    {
        //check if user exists
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        if ($getUser) {
            return true;
        } else {
            return false;
        }
    }

    //get user details
    public function getUserDetails(string $phoneNumber)
    {
        return DB::table('users')->where('phone_number', $phoneNumber)->first();
    }

    //get user community details
    public function getUserCommunityDetails(string $phoneNumber)
    {
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        return DB::table('communities')->where('id', $getUser->community_id)->first();
    }

    public function deposit(string $phoneNumber, string $amount)
    {
        //deposit
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        //create a  transaction
        DB::table('transactions')->insert([
            'phone_number' => $phoneNumber,
            'amount' => $amount,
            'type' => 'credit',
            'status' => 'completed',
            'description' => 'Deposit',
            'community_id' => $getUser->community_id,
            'user_id' => $getUser->id,
        ]);
        //update the community account balance
        DB::table('communities')->where('id', $getUser->community_id)->update(['account_balance' => $getUser->account_balance + $amount]);
        //update user account balance
        DB::table('users')->where('phone_number', $phoneNumber)->update(['account_balance' => $getUser->account_balance + $amount]);
        return true;
    }

    //with draw
    public function withdraw(string $phoneNumber, string $amount)
    {
        //withdraw
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        //create a  transaction
        DB::table('transactions')->insert([
            'phone_number' => $phoneNumber,
            'amount' => $amount,
            'type' => 'debit',
            'status' => 'completed',
            'description' => 'Withdrawal',
            'community_id' => $getUser->community_id,
            'user_id' => $getUser->id,
        ]);
        //update the community account balance
        DB::table('communities')->where('id', $getUser->community_id)->update(['account_balance' => $getUser->account_balance - $amount]);
        //update user account balance
        DB::table('users')->where('phone_number', $phoneNumber)->update(['account_balance' => $getUser->account_balance - $amount]);
        return true;
    }
}
