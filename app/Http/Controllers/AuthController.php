<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailVerificationRequest;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPassword;
use App\Mail\EmailVerification;
use App\Models\EmailVerification as EmailVerificationModel;
use App\Mail\ForgetPassword;
use App\Models\PasswordReset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['login', 'register', 'forgetPassword', 'resetPassword']]);
        $this->middleware("verified", ["only" => ["userVerified"]]);
    }
    public function verifyToken()
    {
        //To verify the token in fornt end
        return ["valid" => true];
    }
    public function login()
    {
        $credentials = request(['email', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }
    public function register(RegisterRequest $request)
    {
        $user = User::create($request->input());
        // $verificationCode = Str::random(5);
        // $this->createEmailVerification([
        //     "email" => $request->email,
        //     "verification_code" => $verificationCode,
        // ]);
        // Mail::to($user->email)->send(new EmailVerification($verificationCode));
        return $this->login($request);
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        $authUserEmail = JWTAuth::parseToken()->getPayload()->get("email");
        $verificationCodeValid = $this
            ->verifyUser($authUserEmail, $request->verification_code, 15);
        if (!$verificationCodeValid) {
            return response()->json(["errorMessage" => "Verification code is not valid"], 400);
        }
    }
    public function resendVerificationCode()
    {
        $authUserEmail = JWTAuth::parseToken()->getPayload()->get("email");
        $verificationCode = Str::random(5);
        $this->updateEmailVerification([
            "email" => $authUserEmail,
            "verification_code" => $verificationCode,
        ]);
        Mail::to($authUserEmail)->send(new EmailVerification($verificationCode));
    }
    public function userVerified()
    {
        //To check if user verified
        return response()->json(["verified" => true]);
    }
    public function forgetPassword(User $user)
    {
        $token = Str::random(40);
        $this->insertResetPassword($user->email, $token);
        Mail::to($user->email)->send(new ForgetPassword(['user' => $user, 'token' => $token]));
    }
    public function resetPassword(ResetPassword $request)
    {
        $passwordReset = $this->getPasswordReset($request->token, 15);
        if (empty($passwordReset)) {
            return response()->json(["error" => "Token isn't valid"], 400);
        }
        $this->changePassword($request->password, $passwordReset->email);
        $request->merge(["email" => $passwordReset->email]);
        return $this->login($request);
    }
    public function logout()
    {
        //Make the current token invalid
        auth()->logout();
    }
    public function getCurrentUser()
    {
        return request()->user();
    }
    public function updateProfile(ProfileRequest $request)
    {
        if ($request->file("image")) {
            $request->merge(["image" => $request->file("image")->store("")]);
        }
        $authUserId = JWTAuth::parseToken()->getPayload()->get("id");
        $result = $this->_updateProfile($authUserId, $request->input());
        if ($request->file("image")) {
            Storage::delete($result["old_image"]);
        }
        return $result["user"];
    }
    //Commons
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
    public function createEmailVerification($emailVerification)
    {
        $emailVerification["created_at"] = Carbon::now();
        EmailVerificationModel::create($emailVerification);
    }
    public function updateEmailVerification($emailVerification)
    {
        $_emailVerification = EmailVerificationModel::where("email", $emailVerification["email"])->first();
        if ($_emailVerification) {
            $_emailVerification->verification_code = $emailVerification["verification_code"];
            $_emailVerification["created_at"] = Carbon::now();
            $_emailVerification->save();
        }
    }
    public function verifyUser($email, $verificationCode, $expirationTime)
    {
        $queryBuilder = EmailVerificationModel::where("email", $email)
            ->where("verification_code", $verificationCode)
            ->where('created_at', '>', Carbon::now()->subMinutes($expirationTime));
        $emailVerification = $queryBuilder->first();
        if ($emailVerification) {
            $queryBuilder->delete();
            $user = User::where("email", $email)->first();
            $user->email_verified_at =  Carbon::now();
            $user->save();
        }
        return $emailVerification;
    }
    public function insertResetPassword($email, $token)
    {
        $passwordReset = PasswordReset::where("email", $email)->first();
        if ($passwordReset) {
            $passwordReset->token = $token;
            $passwordReset->created_at =  Carbon::now();
            $passwordReset->save();
        } else {
            PasswordReset::insert(['email' => $email, 'token' => $token, 'created_at' => Carbon::now()]);
        }
        return $token;
    }
    public function getPasswordReset($token, $expirationTime)
    {
        $passwordReset = PasswordReset::where('token', $token)
            ->where('created_at', '>', Carbon::now()->subMinutes($expirationTime))->first();
        return $passwordReset;
    }
    public function changePassword($password, $email)
    {
        User::where('email', $email)
            ->update(['password' => bcrypt($password)]);
        PasswordReset::where('email', $email)->delete();
    }
    public function _updateProfile($id, $userInput)
    {
        $user = User::find($id);
        $oldImage = $user->getImageName();
        $user->first_name = $userInput["first_name"];
        $user->last_name = $userInput["last_name"];
        $user->phone = isset($userInput["phone"]) ? $userInput["phone"] : "";
        $user->address = isset($userInput["address"]) ? $userInput["address"] : "";
        $user->city = isset($userInput["city"]) ? $userInput["city"] : "";
        $user->age = isset($userInput["age"]) ? $userInput["age"] : null;
        $user->education = isset($userInput["education"]) ? $userInput["education"] : "";
        $user->job = isset($userInput["job"]) ? $userInput["job"] : "";
        $user->about_me = isset($userInput["about_me"]) ? $userInput["about_me"] : "";
        $user->image = isset($userInput["image"]) ? $userInput["image"] : $oldImage;
        $user->save();
        return ["old_image" => $oldImage, "user" => $user];
    }
}
