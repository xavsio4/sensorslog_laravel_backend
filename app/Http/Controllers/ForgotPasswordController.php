<?php
namespace App\Http\Controllers\Api\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ForgotPasswordController extends Controller
{
    //   use SendsPasswordResetEmails, ResetsPasswords;
    /**
    * Create a new controller instance.
    */
    public function __construct()
    {
        $this->middleware('guest');
    }
    
    public function sendReset(Request $request)
    {
        $this->validateEmail($request);
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
        $request->only('email')
        );
        return $response == Password::RESET_LINK_SENT
        ? response()->json(['message' => 'Reset link sent to your email.', 'status' => true], 201)
        : response()->json(['message' => 'Unable to send reset link', 'status' => false], 401);
    }
}