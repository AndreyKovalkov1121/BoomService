<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\RegistrationFormRequest;
use Illuminate\Support\Str;
use App\partners;
use App\category;
use App\service;
use App\teams;

class APIController extends Controller
{
    /**
     * @var bool
     */
    public $loginAfterSignUp = true;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
 
    private function getToken($email, $password)
    {
        $token = null;
        //$credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt(['email' => $email, 'password' => $password])) {
                return response()->json([
                    'response' => 'error',
                    'message' => 'Password or email is invalid..',
                    'token' => $token,
                ]);
            }
        } catch (JWTAuthException $e) {
            return response()->json([
                'response' => 'error',
                'message' => 'Token creation failed',
            ]);
        }
        return $token;
    }

    
    public function login(Request $request)
    {        
        $email = $request->email;
        $password = $request->password;

        $token = self::getToken($email, $password);

        try{
            if (!is_string($token)) {
                return response()->json(['status' => false, 'data' => 'Token generation failed'], 201);
            }
            if($token != null)
            {
                $checklogin = User::where('email', $request->email)->where('password', $request->password)->get();
                if($checklogin)
                {  
                    $user = User::where('email', $request->email)->get()->first();
                    // $user->token = $token;
                    // $user->save();

                    $response = [
                        'status' => true,
                        'data' => [
                            'id' => $user->id,
                            'token' => $token,                        
                            'email' => $user->email, 
                            'message' => "login sucess",
                        ],
                    ];     
                }
                               
            }
            else{
                $response = [
                    'status' => true,
                    'data' => [
                        'id' => $user->id,
                        'token' => $token,                        
                        'email' => $user->email, 
                        'message' => "login failed",
                    ],
                ];

            }
            return response()->json($response, 201);  

        }catch(\Throwable $e){
            $response = ['status' => false, 'data' => 'Couldnt login user.'];
            return response()->json($response, 201);
        }    
        
    }

    public function address_verify(Request $request)
    {        
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $address = $request->address;
        $email = $request->email;

        try{

            $checklatitude = User::where('latitude', $request->latitude)->where('email', $request->email);
            $checklongitude = User::where('longitude', $request->longitude)->where('email', $request->email);
            $checkaddress = User::where('address', $request->address)->where('email', $request->email);           

            if($checklatitude && $checklongitude && $checkaddress)
            {
                $user = User::where('email', $request->email)->get()->first();                         
                 
                $user->status = 1;

                /************* check lock ***************/
                if($user->status == 1){
                    $lock_status = "Home Boom button is UnLocked";
                }
                else{
                    $lock_status = "Home Boom button is Locked";
                }
                    
                if($user->save())
                {
                    $response = [
                        'status' => true,
                        'data' => [
                            'id' => $user->id,
                            'address' => $user->address,
                            'longitude' => $user->longitude,
                            'latitude' => $user->latitude,                        
                            'email' => $user->email,                    
                            'status' => $lock_status,
                            'message' => "This is my home address",
                        ],
                    ];
                }
                else{
                    $response = ['status' => false, 'data' => 'Couldnt register user'];
                }
            }
            else
            {
                $response = [
                    'status' => true,
                    'data' => [
                        'id' => $user->id,
                        'address' => $user->address,
                        'longitude' => $user->longitude,
                        'latitude' => $user->latitude,                        
                        'email' => $user->email,                    
                        'status' => $lock_status,
                        'message' => "I’m not at home",
                    ],
                ];

            }
                 

        }catch(\Throwable $e){
            $response = ['status' => false, 'data' => 'Couldnt register user.'];
            return response()->json($response, 201);
        }

        return response()->json($response, 201);

    }

    public function register(Request $request)
    {    
        $checkEmail = User::where('email', $request->email)->first();        
        $checkPhone = User::where('mobile', $request->mobile)->first(); 
        if ($checkPhone || $checkEmail) {
            $response = [
                'status' => false,
                'message' => 'already registed user',
            ];
            return response()->json($response);
        }
        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        // $user->postcode = $request->postcode;
        $user->address = $request->address;
        $user->longitude = $request->longitude;
        $user->latitude = $request->latitude;
        $user->password = \Hash::make($request->password);
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->status = 0;    
        
        $token = self::getToken($request->email, $request->password);    
        
        try{
            
            /********* check validate ********/
            $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:4'],
                'mobile' => ['required'],
            ]);        


            if($user->save()){             

                //generate token
                $token = self::getToken($request->email, $request->password);

                if (!is_string($token)) {
                    return response()->json(['status' => false, 'data' => 'Token generation failed'], 201);
                }
                $user = User::where('email', $request->email)->get()->first();

                //update user token
                
                //$user->token = $token;                 

                do {
                    $boomid = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),1,6);                    
                    $current_code = User::where('homebtnid', $boomid)->get()->first();
                }
                while(!empty($current_code));               

                $user->homebtnid = $boomid;              

                $user->save();
                // return response
                $response = [
                    'status' => true,
                    'data' => [
                        'id' => $user->id,
                        'token' => $token,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        // 'postcode' => $user->postcode,
                        'address' => $user->address,
                        'longitude' => $user->longitude,
                        'latitude' => $user->latitude,
                        'mobile' => $user->mobile,                        
                        'email' => $user->email,                    
                        'status' => $user->status,
                        'boomid' => $boomid,
                    ],
                ];

            }
            else{
                $response = ['status' => false, 'data' => 'Couldnt register user'];
            }

        }catch(\Throwable $e){
            $response = ['status' => false, 'data' => 'Couldnt register user.'];
            return response()->json($response, 201);
        }

        return response()->json($response, 201);
    }

    
    public function invite_users(Request $request)
    {   
        $checkEmail = User::where('email', $request->email)->first();        
        $checkPhone = User::where('mobile', $request->mobile)->first(); 
        if ($checkPhone || $checkEmail) {
            $response = [
                'status' => false,
                'message' => 'already registed user',
            ];
            return response()->json($response);
        }

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        // $user->postcode = $request->postcode;
        $user->address = $request->address;
        $user->longitude = $request->longitude;
        $user->latitude = $request->latitude;
        $user->password = \Hash::make($request->password);
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->status = 0;
        $user->homebtnid = $request->boomid;
        
        
        $token = self::getToken($request->email, $request->password);       
        
        try{            
            /********* check validate ********/
            $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8'],
                'mobile' => ['required'],
            ]);             


            if($user->save()){                              

                //generate token
                $token = self::getToken($request->email, $request->password);

                if (!is_string($token)) {
                    return response()->json(['status' => false, 'data' => 'Token generation failed'], 201);
                }
                
                // return response
                $response = [
                    'status' => true,
                    'data' => [
                        'id' => $user->id,
                        'token' => $token,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        // 'postcode' => $user->postcode,
                        'address' => $user->address,
                        'longitude' => $user->longitude,
                        'latitude' => $user->latitudelatitudelatitudelatitudelatitude,
                        'mobile' => $user->mobile,                        
                        'email' => $user->email,                    
                        'status' => $user->status,
                        'boomid' => $user->homebtnid,
                    ],
                ];

            }
            else{
                $response = ['status' => false, 'data' => 'Couldnt register user'];
            }

        }catch(\Throwable $e){
            $response = ['status' => false, 'data' => 'Couldnt register user.'];
            return response()->json($response, 201);
        }

        return response()->json($response, 201);
    }

    public function partner_login(Request $request)
    {
        $partner_email = $request->partner_email;
        $password = $request->password;

        $token = self::getToken($partner_email, $password);   
        return $token;        

        try{
            if (!is_string($token)) {
                return response()->json(['status' => false, 'data' => 'Token generation failed'], 201);
            }
            
            if($token != null)
            {
                $checklogin = partners::where('partner_email', $request->partner_email)->where('password', $request->password)->get();
                if($checklogin)
                {       
                    $user = partners::where('partner_email', $request->partner_email)->get()->first();
                    // $user->token = $token;
                    // $user->save();

                    $response = [
                        'status' => true,
                        'data' => [
                            'id' => $user->id,
                            'token' => $token,                        
                            'email' => $user->partner_email, 
                            'message' => "login sucess",
                        ],
                    ];     
                }
                               
            }
            else{
                $response = [
                    'status' => true,
                    'data' => [
                        'id' => $user->id,
                        'token' => $token,                        
                        'email' => $user->partner_email, 
                        'message' => "login failed",
                    ],
                ];

            }
            return response()->json($response, 201);  

        }catch(\Throwable $e){
            $response = ['status' => false, 'data' => 'Couldnt login user.'];
            return response()->json($response, 201);
        }       
    }    
}
