<?php

namespace App\Http\Controllers\API\Auth\User;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordEmailRequest;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordVerifyOtpRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\OtpVerifyRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\SignupRequest;
use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Mail\Api\V1\SendOtpMail;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthenticationController extends Controller
{
    use ApiResponse;
    /**
     * user registration
     */
    public function signup(SignupRequest $request)
    {
        try {
            // Generate a 4-digit random verification code
            // $code = rand(1000, 9999);
            //
            // $code = 1111;

            // $email = $request->validated()['email'];

            // Cache::put("verification_code_{$email}", $code, 600); // Store code for 10 minutes
            // Cache::put("signup_data_{$email}", $request->validated(), 600);
            //

            // send otp code this mail
            // Mail::to($email)->send(new SendOtpMail($code));

            // success response
            // return $this->sendResponse([], 'A verification code has been sent to your email address.'.$code, 200);

            $data = $request->validated();

            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'password'   => Hash::make($data['password']),
                'role'       => Role::USER->value,
            ]);

            $token = auth('api')->login($user);
            
            return $this->sendResponse(new UserResource($user),'User registration successful.',200,$token);
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(),[],500);
        }
    }

    /**
     * verify OTP and store data in database
     */
    public function verifyOtp(OtpVerifyRequest $request)
    {
        $validData = $request->validated();

        $code = (int) $validData['otp'];
        $email = $validData['email'];

        $cachedCode = Cache::get("verification_code_{$email}");
        $cachedData = Cache::get("signup_data_{$email}");


        if (!$cachedCode || $code !== $cachedCode) {
            return $this->sendError("The OTP you entered is incorrect or has expired. Please try again.", [], 400);
        }

        if (!$cachedData) {
            return $this->sendError("Your registration session has expired. Please sign up again.", [], 400);
        }

        try {
            // store data in database
            $user = User::create([
                'first_name' => $cachedData['first_name'],
                'last_name'  => $cachedData['last_name'],
                'email'      => $cachedData['email'],
                'password'   => Hash::make($cachedData['password']),
                'role'       => Role::USER->value,
            ]);

            $token = auth('api')->login($user);

            // Clear cached data after successful registration
            Cache::forget("verification_code_{$email}");
            Cache::forget("signup_data_{$email}");

            // success response
            return $this->sendResponse(new UserResource($user),'User registration successful.',200,$token);
        } catch (\Exception $e) {
            return $this->sendError("An error occurred while creating your account. Please try again later.", [], 500);
        }
    }

    /**
     * signing
     */
    public function login(LoginRequest $request)
    {
        $validatedData = $request->validated();

        // Attempt to log the user in
        if (!$token = JWTAuth::attempt($validatedData)) {
            return $this->sendError('Invalid credentials. Please check your email and password.', [], 401);
        }

        $user = Auth::user();

        return $this->sendResponse(new UserResource($user), 'User logged in successfully.', 200, $token);
    }

    public function forgotPasswordEmail(ForgotPasswordEmailRequest $request)
    {
        try {
            $data = $request->validated();
            // $code = rand(1000, 9999);
            $code = 1111;
            $email = $data["email"];

            // Mail::to($email)->send(new SendOtpMail($code));

            Cache::put("forgetpassword_code_{$email}", $code, 600); // Store code for 10 minutes
            Cache::put("user_data_{$email}", $request->validated(), 600);

            return $this->sendResponse([], 'A verification code has been sent to your email address.'.$code, 200);
        } catch (\Exception $e) {
            return $this->sendError("An error occurred. Please try again later.", [], 500);
        }
    }

    public function forgotPasswordVerifyOtp(ForgotPasswordVerifyOtpRequest $request)
    {
        try {
            $validData = $request->validated();

            $code = (int) $validData['otp'];
            $email = $validData['email'];

            $cachedCode = Cache::get("forgetpassword_code_{$email}");
            $cachedData = Cache::get("user_data_{$email}");

            if (!$cachedCode || $code !== $cachedCode) {
                return $this->sendError("The OTP you entered is incorrect or has expired. Please try again.", [], 400);
            }

            if (!$cachedData) {
                return $this->sendError("Your reset password session has expired. Please re-do the process again.", [], 400);
            }

            $user = User::where("email", $email)->first();

            Cache::forget("forgetpassword_code_{$email}");
            Cache::forget("user_data_{$email}");
            
            return $this->sendResponse(new UserResource($user),'OTP Verification successful.',200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $data = $request->validated();
            $user = User::where('email', $data['email'])->first();

            $user->password = Hash::make($data['password']);
            $user->save();
            return $this->sendResponse(new UserResource($user),'Password Reset Successfully',200);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }

}
