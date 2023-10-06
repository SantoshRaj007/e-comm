<?php

namespace App\Http\Controllers\admin;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\ResetPasswordEmail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminLoginController extends Controller
{
    public function index(){
        return view('admin.login');
    }

    public function authenticate(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
        ]); 

        if ($validator->passes()) {
            if(Auth::guard('admin')
            ->attempt(['email' => $request->email,'password' => $request->password],$request->get('remember'))){

                $admin = Auth::guard('admin')->user();

                if($admin->role == 2){
                    return redirect()->route('admin.dashboard');
                } else {
                    Auth::guard('admin')->logout();
                    return redirect()->route('admin.login')->with('error','You are not authorized to access admin panel.');
                }
                
            } else {
                return redirect()->route('admin.login')->with('error','Either Email/Password is incorrect');
            }
        } else {
            return redirect()->route('admin.login')
            ->withErrors($validator)
            ->withInput($request->only('email'));
        }
    } 

    public function forgotPassword() {
        return view('admin.forgot-password');
    }

    public function processForgotPassword(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email|exists:users,email'
        ]);

        if($validator->fails()){
            return redirect()->route('admin.forgotPassword')->withInput()->withErrors($validator);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->where('email',$request->email)->delete();

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now()
        ]);

        // send email here

        $user = User::where('email',$request->email)->first();

        $formData = [
            'token' => $token,
            'user' => $user,
            'mailSubject' => 'You have requested to reset your password'
        ];
        Mail::to($request->email)->send(new ResetPasswordEmail($formData));
        return redirect()->route('admin.forgotPassword')->with('success','Please check your inbox to reset your password:');
        
    }

    public function resetPassword($token) {

        $tokenExist = DB::table('password_reset_tokens')->where('token',$token)->first();
        if($tokenExist == null){
            return redirect()->route('admin.forgotPassword')->with('error','Link Expired');
        }

        return view('admin.reset-password',[
            'token' => $token
        ]);
    }

    public function processResetPassword(Request $request) {
        $token = $request->token;

        $tokenExist = DB::table('password_reset_tokens')->where('token',$token)->first();

        if($tokenExist == null){
            return redirect()->route('admin.forgotPassword')->with('error','Invalid request');
        }

        $user = User::where('email',$tokenExist->email)->first();

        $validator = Validator::make($request->all(),[
            'new_password' => 'required|min:5',
            'confirm_password' => 'required|same:new_password'
        ]);

        if($validator->fails()){
            return redirect()->route('admin.resetPassword',$token)->withErrors($validator);
        }

        User::where('id',$user->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        DB::table('password_reset_tokens')->where('email',$user->email)->delete();

        return redirect()->route('admin.login')->with('success','You have successfully update your password');
    }
}
