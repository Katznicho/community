<?php

namespace App\Http\Controllers;

use App\Traits\ResponseTrait;
use App\Traits\SessionTrait;
use App\Traits\UserTrait;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    use ResponseTrait, SessionTrait, UserTrait;

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
        try {
            //code...
            // return $this->writeResponse($request->text ?? "no text", true);
            if ($this->text == "") {
                return $this->welcomeUser($request);
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
                            //account
                            return $this->myAccount($request);
                        } elseif ($this->text == "6") {
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
                        //extract  pin
                        $pin = substr($this->text, 0, 4);
                        $pinRes = $this->checkPin($pin);
                        if ($pinRes) {
                            $bal = $this->getAccountBalance($request->phoneNumber);
                            return $this->writeResponse("Your account balance is $bal", true);
                        } else {
                            return $this->writeResponse("You entered an invalid pin", true);
                        }
                        break;
                    case "Deposit":
                        //extract  pin
                        $amount = $this->text;
                        return $this->writeResponse("You have deposited $amount", true);
                        break;
                    case "Withdrawal":
                        //extract  pin
                        $amount = $this->text;
                        return $this->writeResponse("You have withdrawn $amount", true);
                        break;
                    case "Suggest Improvements":
                        //extract  pin
                        $suggestion = $this->text;
                        return $this->writeResponse("Thank you for your suggestion $suggestion", true);
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

    private function welcomeUser(Request $request)
    {
        $response  = "Welcome to CommunityHub:\n";
        $response .= "1. Report an Issue\n";
        $response .= "2. Suggest Improvement\n";
        $response .= "3. Community Events\n";
        $response .= "4. Announcements\n";
        $response .= "5. My Account\n";
        $response .= "6. Help\n";

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
