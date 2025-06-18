<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'google_id'   => 'string|nullable',
            'facebook_id' => 'string|nullable',
            'twitter_id'  => 'string|nullable',
            'apple_id'    => 'string|nullable',
            'photo'       => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            $socialId = ($request->has('google_id') && $existingUser->google_id === $request->google_id) || ($request->has('facebook_id') && $existingUser->facebook_id === $request->facebook_id) || ($request->has('twitter_id') && $existingUser->twitter_id === $request->twitter_id) || ($request->has('apple_id') && $existingUser->apple_id === $request->apple_id);
            if ($socialId) {
                $token   = JWTAuth::fromUser($existingUser);
                $success = $this->respondWithToken($token, $existingUser);
                return response()->json([
                    'status'  => true,
                    'message' => 'User login successfully.',
                    'data'    => $success,
                ], 200);
            } elseif (is_null($existingUser->google_id) && is_null($existingUser->facebook_id) && is_null($existingUser->twitter_id) && is_null($existingUser->apple_id)) {
                return response()->json([
                    'status'  => true,
                    'message' => 'User already exists. Sign in manually.',
                ], 200);
            } else {
                $existingUser->update([
                    'google_id'   => $request->google_id ?? $existingUser->google_id,
                    'facebook_id' => $request->facebook_id ?? $existingUser->facebook_id,
                    'twitter_id'  => $request->twitter_id ?? $existingUser->twitter_id,
                    'apple_id'    => $request->apple_id ?? $existingUser->apple_id,
                ]);
                $token   = JWTAuth::fromUser($existingUser);
                $success = $this->respondWithToken($token, $existingUser);
                return response()->json([
                    'status'  => true,
                    'message' => 'User login successfully.',
                    'data'    => $success,
                ], 200);
            }
        }
        $user = User::create([
            'name'              => $request->name,
            'channel_name'      => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make(Str::random(16)),
            'role'              => 'USER',
            'google_id'         => $request->google_id ?? null,
            'facebook_id'       => $request->facebook_id ?? null,
            'twitter_id'        => $request->twitter_id ?? null,
            'apple_id'          => $request->apple_id ?? null,
            'email_verified_at' => now(),
            'status'            => 'active',
        ]);
        if ($request->hasFile('photo')) {
            $image      = $request->file('photo');
            $final_name = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/user'), $final_name);
            $user->update([
                'avatar' => $final_name,
            ]);
        }
        $token   = JWTAuth::fromUser($user);
        $success = $this->respondWithToken($token, $user);
        return response()->json([
            'status'  => true,
            'message' => 'User login successfully.',
            'data'    => $success], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'channel_name' => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:4',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();

            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        $otp            = rand(100000, 999999);
        $otp_expires_at = now()->addMinutes(10);
        $avatarUrl      = 'https://ui-avatars.com/api/?name=' . urlencode($request->channel_name) . '&background=random&bold=true&size=256';
        $response       = Http::get($avatarUrl);
        $filename       = time() . '.png';
        $savePath       = public_path('uploads/user/' . $filename);
        file_put_contents($savePath, $response->body());

        $user = User::create([
            'name'           => $request->name,
            'channel_name'   => $request->channel_name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'role'           => 'USER',
            'avatar'         => $filename,
            'otp'            => $otp,
            'otp_expires_at' => $otp_expires_at,
            'status'         => 'inactive',
        ]);
        Mail::to($request->email)->send(new OtpMail($otp));
        return response()->json([
            'status'  => true,
            'message' => 'An OTP sent to your registered email.',
            'data'    => $user,
        ], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|string|min:4',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();

            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User not found.'], 404);
        }
        if ($user->email_verified_at == null) {
            return response()->json(['status' => false, 'message' => 'Your account is not verified.'], 403);
        }
        $credentials = $request->only(['email', 'password']);
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['status' => false, 'message' => 'Invalid email or password.'], 401);
        }
        $user->status = 'active';
        $user->save();
        $success = $this->respondWithToken($token);
        return response()->json(['status' => true, 'message' => 'User login successfully.', 'data' => $success], 200);
    }

    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();

            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        $user                 = User::where('email', $request->email)->first();
        $otp                  = rand(100000, 999999);
        $otp_expires_at       = now()->addMinutes(10);
        $user->otp            = $otp;
        $user->otp_expires_at = $otp_expires_at;
        $user->save();
        Mail::to($request->email)->send(new OtpMail($otp));
        return response()->json(['status' => true, 'message' => 'An OTP sent to your email.'], 200);
    }

    public function otpVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|string',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();

            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        $user = User::where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('otp_expires_at', '>=', now())
            ->first();
        if (! $user) {
            return response()->json(['status' => false, 'message' => 'OTP is incorrect or has expired.'], 400);
        }
        $user->update([
            'otp'               => null,
            'otp_expires_at'    => null,
            'email_verified_at' => $user->email_verified_at ?? now(),
            'status'            => $user->email_verified_at ? $user->status : 'active',
        ]);

        $token   = Auth::login($user);
        $success = $this->respondWithToken($token);
        return response()->json(['status' => true, 'message' => 'User login successfully.', 'data' => $success], 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'      => 'required|email|exists:users,email',
            'password'   => 'required|string|min:4|same:c_password',
            'c_password' => 'required|string|min:4',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();

            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json(['status' => true, 'message' => 'Password changed successfully.'], 200);
        }
        return response()->json(['status' => false, 'message' => 'User not found in the system.'], 400);
    }
    public function profile()
    {
        try {
            $user = auth()->user();
            if (! $user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid token.'], 401);
            }
            return response()->json([
                'status' => true,
                'data'   => $user], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'User not authenticated.',
            ], 401);
        }
    }

    public function editProfile(Request $request)
    {
        if (Auth::user()->role == 'ADMIN') {
            $validator = Validator::make($request->all(), [
                'name'  => 'required|string|max:255',
                'image' => 'sometimes|image|mimes:png,jpg,jpeg',
            ]);
            if ($validator->fails()) {
                $firstError = collect($validator->errors()->all())->first();

                return response()->json([
                    'message' => $firstError,
                    'errors'  => $validator->errors(),
                ], 422);
            }
            $user       = Auth::user();
            $user->name = $request->name ?? $user->name;
            if ($request->hasFile('image')) {
                if ($user->getRawOriginal('avatar') && $user->getRawOriginal('avatar') !== 'user/default_avatar.png') {
                    $old_photo_location = public_path('uploads/' . $user->getRawOriginal('avatar'));

                    if (File::exists($old_photo_location)) {
                        File::delete($old_photo_location);
                    }
                }

                $image      = $request->file('image');
                $final_name = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/user'), $final_name);
                $user->avatar = $final_name;
            }
            $user->save();
        } elseif (Auth::user()->role == 'USER') {
            $validator = Validator::make($request->all(), [
                'name'         => 'required|string|max:255',
                'channel_name' => 'required|string|max:255',
                'contact'      => 'required|string|max:255',
                'bio'          => 'required|string',
                'locations'    => 'required',
                'services'     => 'required',
                'avatar'       => 'sometimes|image|mimes:png,jpg,jpeg',
                'cover_image'  => 'sometimes|image|mimes:png,jpg,jpeg',
            ]);
            if ($validator->fails()) {
                $firstError = collect($validator->errors()->all())->first();
                return response()->json([
                    'message' => $firstError,
                    'errors'  => $validator->errors(),
                ], 422);
            }
            $user               = Auth::user();
            $user->name         = $request->name ?? $user->name;
            $user->channel_name = $request->channel_name ?? $user->channel_name;
            $user->contact      = $request->contact ?? $user->contact;
            $user->bio          = $request->bio ?? $user->bio;
            $user->locations    = json_encode($request->locations,true);
            $user->services    = json_encode($request->services,true);

            if ($request->hasFile('avatar')) {
                if ($user->getRawOriginal('avatar') && $user->getRawOriginal('avatar') !== 'user/default_avatar.png') {
                    $old_photo_location = public_path('uploads/' . $user->getRawOriginal('avatar'));

                    if (File::exists($old_photo_location)) {
                        File::delete($old_photo_location);
                    }
                }

                $image      = $request->file('avatar');
                $final_name = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/user'), $final_name);
                $user->avatar = $final_name;
            }
            if ($request->hasFile('cover_image')) {
                if ($user->getRawOriginal('cover_image') && $user->getRawOriginal('cover_image') !== 'cover/default_cover_image.jpg') {
                    $old_photo_location = public_path('uploads/' . $user->getRawOriginal('cover_image'));

                    if (File::exists($old_photo_location)) {
                        File::delete($old_photo_location);
                    }
                }

                $image      = $request->file('cover_image');
                $final_name = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/cover'), $final_name);
                $user->cover_image = $final_name;
            }
            $user->save();
        }
        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully',
            'data'    => $user,
        ], 200);
    }
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|max:255',
            'new_password'     => 'required|string|min:4|same:c_password',
            'c_password'       => 'required|string|min:4',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();

            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        $user = Auth::user();
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Current password is incorrect.'], 400);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        return response()->json(['status' => true, 'message' => 'Password changed successfully.'], 200);
    }
    public function logout()
    {
        $user = Auth::user();
        $user->update([
            'status' => 'inactive',
        ]);
        auth()->logout();
        return response()->json(['status' => true, 'message' => 'Successfully logged out.'], 200);
    }
    public function validateToken(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if ($token) {
                $user = JWTAuth::setToken($token)->authenticate();
                if ($user) {
                    return response()->json([
                        'token_status' => true,
                        'message'      => 'Token is valid.',
                    ]);
                } else {
                    return response()->json([
                        'token_status' => false,
                        'message'      => 'Token is valid but user is not authenticated.',
                    ]);
                }
            }
            return response()->json([
                'token_status' => false,
                'message'      => 'No token provided.',
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'token_status' => false,
                'message'      => 'Token is invalid or expired.',
            ], 401);
        }
    }

    public function refresh()
    {
        $token = $this->respondWithToken(auth()->refresh());
        return response()->json(['status' => true, 'message' => 'Token refresh successfully.', 'data' => $token], 200);
    }

    protected function respondWithToken($token, $user = null)
    {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
            'user'         => Auth::user() ?? $user,
        ];
    }
}
