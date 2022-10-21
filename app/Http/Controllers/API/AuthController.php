<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\EditUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\validateOtpRequest;
use App\Libraries\M360;
use App\Mail\NewUserCreated;
use App\Models\User;
use App\Models\UserVerification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends ApiController
{
    
    public function register(StoreUserRequest $request)
    {
    
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'password' => Hash::make($request->password)
        ]);

        $result = $this->userOtpVerification(array_merge($request->validated(), ['user_id' => $user->id]));
    
        if ($result) {
            return $this->successResponse($user, 'A verification code has sent to your mobile number', 201);
        }
    
        return $this->errorResponse([], 'Failed to register user', 400);
    }

    public function login(LoginUserRequest $request)
    {
        if (!auth()->attempt($request->validated())) {
            return $this->respondUnauthorized([]);
        }

        $user = User::where('email', $request->email)
                    ->where('account_verified_at', '!=',  NULL)
                    ->first();

        if ($user) {
            $token = $user->createToken('Api Token of ' . $user->name)->plainTextToken;
            $user = collect($user); 
            return $this->successResponse($user->put('token', $token), 'Login Successful', 200);            
        }
    }


    public function edit(User $user, EditUserRequest $request)
    {
        if ($user->update($request->validated())) {
            return $this->successResponse($user, 'Successfully update user', 200);
        } 
        return $this->respondForbidden([]);
    }

    public function destroy(User $user)
    {
        if ($user->delete()) {
            return $this->successResponse([], 'Successfully deleted a user', 200);
        } 
        return $this->respondForbidden([]);
    }

    public function logout()
    {
        $user = auth()->user();
        $tokenId = request()->bearerToken();

        if ($user->tokens()->where('id', $tokenId)->delete()) {
            return $this->successResponse([], "You've been successfully logout", 200);
        } 
        return $this->respondUnauthorized([], 'make sure you are currently logged in');
    }


    public function validateOtp(validateOtpRequest $request)
    {
        $user =  User::leftJoin('user_verifications', 'id', 'user_id')
                    ->where('mobile_number', $request->mobile_number)
                    ->where('account_verified_at',  NULL)
                    ->where('verification_code', $request->verification_code)
                    ->first();

        if (!$user) {
            return $this->errorResponse([], 'Please check if the user input is correct', 400);
        }

        if ($user->verification_code === $request->verification_code) {
            $user->account_verified_at = Carbon::now()->format('y-m-d H:i:s');
            $UserVerification = UserVerification::where('verification_code', $request->verification_code)->update(['status' => 'verified']);
            if ($user->save() && $UserVerification) {
                Mail::to('franciswillyfernandez@gmail.com')->send(new NewUserCreated());
                return $this->respondAccepted($user->fresh(), 'Verification Success');    
            }
            return $this->errorResponse([], 'Verification failed', 400);
        }
    }

    private function userOtpVerification(array $attributes)
    {
        $otp = mt_rand(100000, 999999);
        $otpMessage = 'Here is your verification code ' . $otp;
        $attributes['type'] = 'mobile';
        $attributes['verification_code'] = $otp;
        $attributes['expires_at'] = Carbon::now()->addMinute(2)->format('y-m-d H:i:s');
        $response = $this->storeUserVerification($attributes);

        if($response->wasRecentlyCreated === true){
            $this->sendMobileOTP($attributes['mobile_number'], $otpMessage, $attributes['user_id']);
            return true;
        }
        return false;
    }

    private function storeUserVerification(array $attributes)
    {
        $prepData = array(
            'user_id' =>  $attributes['user_id'],
            'type' =>  $attributes['type'],
            'value' =>  $attributes['mobile_number'],
            'verification_code' =>  $attributes['verification_code'],
            'expires_at' =>  $attributes['expires_at'],
            'status' =>  'pending',
        );
        return UserVerification::create($prepData);
    }

    private function sendMobileOTP($mobileNum, $message, $userID)
    {
        $mobileNumber =  '+63'.substr($mobileNum, 1);
        $transId = strtotime(date('Y-m-d H:i:s')).$userID;
        $m360 = new M360();
        $m360->set_msisdn($mobileNumber);
        $m360->set_content($message);
        $m360->set_rcvd_transid($transId);
        $m360->send();

        return $m360->success();
    }
}















