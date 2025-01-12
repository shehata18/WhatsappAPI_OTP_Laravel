<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PhoneVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\WattsiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Check if phone is verified
            if (!Auth::user()->phone_verified) {
                return redirect()->route('phone.verify');
            }

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone_code' => 'required|string',
            'phone' => 'required|string',
        ]);

        try {
            Log::info('Starting user registration', ['email' => $validated['email']]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone_code' => $validated['phone_code'],
                'phone' => $validated['phone'],
                'phone_verified' => false
            ]);

            Log::info('User created successfully', ['user_id' => $user->id]);

            Auth::login($user);

            // Generate and send OTP
            $this->generateAndSendOTP($user);

            return redirect()->route('phone.verify')
                ->with('message', 'Verification code has been sent to your WhatsApp.');

        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function dashboard()
    {
        if (!Auth::user()->phone_verified) {
            return redirect()->route('phone.verify');
        }
        return view('auth.dashboard');
    }

    public function showPhoneVerification()
    {
        if (Auth::user()->phone_verified) {
            return redirect()->route('dashboard');
        }
        return view('auth.verify-phone');
    }

    public function generateAndSendOTP($user)
    {
        try {
            Log::info('Starting OTP generation process', ['user_id' => $user->id]);

            // Generate 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            Log::info('OTP generated', ['user_id' => $user->id]);

            // Store OTP in database
            $verification = PhoneVerification::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'otp' => $otp,
                    'expires_at' => now()->addMinutes(10)
                ]
            );

            // Format phone number
            $phoneNumber = $user->phone;
            $phoneCode = ltrim($user->phone_code, '+');
            $fullPhone = $phoneCode . $phoneNumber;

            Log::info('Sending OTP', [
                'user_id' => $user->id,
                'phone' => $fullPhone
            ]);

            // Create WhatsApp message
            $message = "Your verification code is: {$otp}\n\n";
            $message .= "This code will expire in 10 minutes.";

            // Send via Wattsibot
            $wattsiService = new WattsiService();

            // First check if phone is on WhatsApp
            if (!$wattsiService->checkPhone($fullPhone)) {
                throw new \Exception('Phone number is not registered on WhatsApp');
            }

            // Send the message
            if (!$wattsiService->sendMessage($fullPhone, $message)) {
                throw new \Exception('Failed to send OTP via WhatsApp');
            }

            Log::info('OTP sent successfully', ['user_id' => $user->id]);
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to generate and send OTP', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function verifyPhone(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        try {
            Log::info('Attempting phone verification', ['user_id' => auth()->id()]);

            $user = auth()->user();
            $verification = PhoneVerification::where('user_id', $user->id)
                ->where('otp', $request->otp)
                ->where('expires_at', '>', now())
                ->first();

            if (!$verification) {
                return back()->with('error', 'Invalid or expired verification code.');
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'phone_verified' => true,
                    'phone_verified_at' => now()
                ]);

            $verification->delete();

            Log::info('Phone verified successfully', ['user_id' => $user->id]);

            return redirect()->route('dashboard')
                ->with('message', 'Phone number verified successfully!');

        } catch (\Exception $e) {
            Log::error('Phone verification failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Verification failed: ' . $e->getMessage());
        }
    }

    public function resendOtp()
    {
        try {
            if (cache()->get('otp_throttle_' . auth()->id())) {
                return back()->with('error', 'Please wait before requesting another code.');
            }

            $user = auth()->user();
            $this->generateAndSendOTP($user);

            // Set throttle
            cache()->put('otp_throttle_' . auth()->id(), true, now()->addMinute());

            return back()->with('message', 'New verification code has been sent to your WhatsApp.');

        } catch (\Exception $e) {
            Log::error('Failed to resend OTP', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to send new code: ' . $e->getMessage());
        }
    }
}
