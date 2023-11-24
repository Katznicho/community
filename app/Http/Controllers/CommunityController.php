<?php

namespace App\Http\Controllers;

use App\Traits\MessageTrait;
use App\Traits\ResponseTrait;
use App\Traits\SessionTrait;
use App\Traits\UserTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class CommunityController extends Controller
{
    use ResponseTrait, SessionTrait, UserTrait, MessageTrait;

    private string $phoneNumber;
    private string $sessionId;
    private string $text;
    private string $networkCode;
    private string  $serviceCode;


    //create a constructor
    public function __construct(Request $request)
    {
        try {
            //code...
            $this->phoneNumber = $request->phoneNumber;
            $this->sessionId = $request->sessionId;
            $this->text = $request->text ?? "";
            $this->networkCode = $request->networkCode ?? "";
            $this->serviceCode = $request->serviceCode ?? "";
        } catch (\Throwable $th) {
            //throw $th;
            return $this->writeResponse($th->getMessage(), true);
        }
    }
    //
    public function process(Request $request)
    {
        $user = $this->checkIfUserExists($this->phoneNumber);
        if (!$user) {
            return $this->writeResponse("Your account does not exist", true);
        }
        $userCommunity = $this->getUserCommunityDetails($this->phoneNumber);
        try {

            if ($this->text == "") {
                return $this->welcomeUser($request, $userCommunity->name);
            } else {

                $userDetails = $this->getLastUserSession($this->phoneNumber);
                $this->text =  $request->text;


                switch ($userDetails->last_user_code) {
                    case '00':
                        //get user input;
                        if ($this->text == "1") {
                            return $this->communityIssues($request);
                        } elseif ($this->text == "2") {
                            $this->storeUserSession($request, "Suggest Improvements");
                            return $this->writeResponse("Enter your suggestion", false);
                            //suggestion
                        } elseif ($this->text == "3") {
                            return $this->communityEvents($request);
                        } elseif ($this->text == "4") {
                            return $this->latestAnnouncements($request);
                        } elseif ($this->text == "5") {
                            return $this->myAccount($request);
                        } else if ($this->text == "6") {
                            $this->storeUserSession($request, "Old Pin");
                            return $this->writeResponse("Enter your pin", false);
                        } elseif ($this->text == "7") {
                            return $this->writeResponse("Your to be contacted soon", true);
                        } else {
                            return $this->writeResponse("We did not understand your request 00", true);
                        }
                        break;
                    case 'Account':
                        if ($this->text == "5*1") {
                            return $this->enterPinBalance($request);
                        } elseif ($this->text == "5*2") {
                            $this->storeUserSession($request, "Deposit");
                            return $this->writeResponse("Enter amount to deposit", false);
                        } elseif ($this->text == "5*3") {
                            $this->storeUserSession($request, "Withdrawal");
                            return $this->writeResponse("Enter amount to with drawal", false);
                        } else {
                            return $this->writeResponse("We did not understand your request Account", true);
                        }
                        break;
                    case "My Account":
                        if ($this->text == "1") {
                            return $this->myAccount($request);
                        } else {
                            return $this->writeResponse("We did not understand your request", true);
                        }
                        break;

                    case "Balance":
                        $pin =  explode("*", $this->text);
                        $pin = $pin[2];
                        $pinRes = $this->checkPin($pin, $request->phoneNumber);
                        if ($pinRes) {
                            $bal = $this->getAccountBalance($request->phoneNumber);
                            return $this->writeResponse("Your account balance is $bal", true);
                        } else {
                            return $this->writeResponse("You entered an invalid pin", true);
                        }
                        break;
                    case "Deposit":
                        $amount = explode("*", $this->text);
                        $amount = $amount[2];
                        $this->deposit($request->phoneNumber, $amount);
                        return $this->writeResponse("You have deposited UGX $amount on your account", true);
                        break;
                    case "Withdrawal":
                        //extract  pin
                        $amount = explode("*", $this->text);
                        $amount = $amount[2];
                        //balance
                        $bal = $this->getAccountBalance($request->phoneNumber);
                        if (intval($bal) == 0) {
                            return $this->writeResponse("Insufficient balance", true);
                        }
                        if (intval($bal) < intval($amount)) {
                            return $this->writeResponse("Insufficient balance", true);
                        }
                        //ask for pin
                        $this->withdraw($request->phoneNumber, $amount);
                        return $this->writeResponse("You have withdrawn UGX $amount on your account", true);
                        break;
                    case "Suggest Improvements":
                        //extract  pin
                        $suggestion = explode("*", $this->text);
                        $suggestion = $suggestion[1];
                        $checkPin = $this->updateSuggestion($suggestion, $request->phoneNumber);
                        return $this->writeResponse("Thank you for your suggestion $suggestion", true);
                        break;
                    case "Old Pin":
                        //extract  pin
                        $pin = $this->text;
                        $actualPin =  explode("*", $this->text);
                        $pin = $actualPin[1];
                        $checkPin = $this->checkPin($pin, $request->phoneNumber);
                        if ($checkPin) {
                            $this->storeUserSession($request, "Reset Pin");
                            return $this->writeResponse("Enter new pin", false);
                        } else {
                            return $this->writeResponse("You entered an invalid pin", true);
                        }
                        break;
                    case "Reset Pin":
                        $pin = $this->text;
                        $actualPin =  explode("*", $this->text);
                        $pin = $actualPin[2];
                        $checkPin = $this->updatePin($pin, $request->phoneNumber);
                        if ($checkPin) {
                            return $this->writeResponse("Pin reset successfully", true);
                        } else {
                            return $this->writeResponse("You entered an invalid pin", true);
                        }
                        break;
                    default:
                        # code...
                        // break;
                        return $this->writeResponse("We did not  understand your request", true);
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            return $this->writeResponse($th->getMessage(), true);
        }
    }

    private function welcomeUser(Request $request, string $name)
    {
        $response  = "Welcome to $name:\n";
        $response .= "1. Report an Issue\n";
        $response .= "2. Suggest Improvement\n";
        $response .= "3. Community Events\n";
        $response .= "4. Announcements\n";
        $response .= "5. My Account\n";
        $response .= "6. Reset Pin\n";
        $response .= "7. Help\n";

        //store user session
        $this->storeUserSession($request, "00");

        return $this->writeResponse($response, false);
    }

    private  function myAccount(Request $request)
    {
        $response = "My Account\n";
        $response .= "1. My Balance\n";
        $response .= "2. Deposit\n";
        $response .= "3. Withdrawal\n";
        //store user session
        $this->storeUserSession($request, "Account");
        return $this->writeResponse($response, false);
    }

    private function enterPinBalance(Request $request)
    {
        //store user session
        $this->storeUserSession($request, "Balance");
        return $this->writeResponse("Enter Pin ", false);
    }

    private function communityEvents(Request $request)
    {
        $response = "Community Events\n";
        $response .= "1.  Wedding\n";
        $response .= "2. Burial\n";
        $response .= "3. Get Together\n";
        $response .= "4. Meetings\n";
        $response .= "5. Other\n";
        //store user session
        $this->storeUserSession($request, "Events");

        return $this->writeResponse($response, false);
    }

    private function communityIssues(Request $request)
    {
        $response = "Community Issues\n";
        $response .= "1. Thefty\n";
        $response .= "2. Child Abuse\n";
        $response .= "3. Death\n";
        $response .= "4. Violence\n";
        $response .= "5. Other\n";
        //store user session
        $this->storeUserSession($request, "Issues");

        return $this->writeResponse($response, false);
    }

    private function latestAnnouncements(Request $request)
    {
        $response = "Latest Announcements\n";
        $response .= "1.  Wedding\n";
        $response .= "2. Burial\n";
        $response .= "3. Get Together\n";
        $response .= "4. Meetings\n";
        $response .= "5. Other\n";
        //store user session
        $this->storeUserSession($request, "Announcements");
        return $this->writeResponse($response, false);
    }
}
