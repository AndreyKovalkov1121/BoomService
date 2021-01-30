<?php

namespace App\Http\Controllers;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\category;
use App\service;
use App\partners;
use App\teams;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;


class HomeController extends Controller
{
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
    public function create_categories(Request $request)
    {
        $category_name = $request->category_name;
        $service_name1 = $request->service_name1;
        $normal_price1 = $request->normal_price1;
        $boom_price1 = $request->boom_price1;
        $service_name2 = $request->service_name2;
        $normal_price2 = $request->normal_price2;
        $boom_price2 = $request->boom_price2;
        $service_name3 = $request->service_name3;
        $normal_price3 = $request->normal_price3;
        $boom_price3 = $request->boom_price3;
        $expires = $request->expires;
        $postcodes = $request->postcodes;

        $service_name_arr = array_filter([$service_name1,$service_name2,$service_name3]);
        $normal_price_arr = array_filter([$normal_price1,$normal_price2,$normal_price3]);
        $boom_price1_arr = array_filter([$boom_price1,$boom_price2,$boom_price3]);

        $checkCategory = category::where('category_name', $request->category_name)->first();
        
        if($checkCategory)
        {
            $response = [
                'status' => false,
                'message' => 'already registed Category',
            ];
            return response()->json($response);
        }

        $categories = new category();
        $categories->category_name = $category_name;
        $categories->expires = $expires;
        $categories->postcode = $postcodes;
        $categories->status = 0; 
        if($categories->save())
        {
            $categoryid = category::select('id')->where('category_name', $request->category_name)->first();
            
            try{
                $service_data_arr = [];
                for($i=0; $i < count($service_name_arr); $i++){
                    $services = new service();
                    $services->category_id = $categoryid->id;
                    $services->service_name = $service_name_arr[$i];
                    $services->normal_price = $normal_price_arr[$i];
                    $services->boom_price = $boom_price1_arr[$i];
                    $services->save();
                    $arr = ['service'.$i => $service_name_arr[$i],'normal price'.$i =>$normal_price_arr[$i],'boom price'.$i =>$boom_price1_arr[$i]];
                    array_push($service_data_arr, $arr);
                }

                $response = [
                    'status' => true,
                    'data' => [
                        'category_name' =>$categories->category_name,
                        'services' => $service_data_arr,
                        'expires'=> $categories->expires,
                        'postcodes '=>$categories->postcode,
                        'message'=>'Categories was added successfully.'
                    ],
                ];

            }catch(\Throwable $e){
                $response = ['status' => false, 'data' => 'Could not register user.'];
                return response()->json($response, 201);
            } 
        }
        else{
            $response = ['status' => false, 'data' => 'Could not register category.'];
            return response()->json($response, 201);
        }        
        
       return response()->json($response, 201);
    }
    public function create_partners(Request $request)
    {
        $category_name = $request->category_name;
        $partner_name = $request->partner_name;
        $address = $request->address;
        $partner_email = $request->partner_email;
        $password =\Hash::make($request->password);;
        $postcodes = $request->postcodes;
        $contact_name = $request->contact_name;
        $email = $request->email;
        $tel_number = $request->telephone;
        
        $checkPartner = partners::where('partner_name', $request->partner_name)->first();         
        
        if($checkPartner)
        {
            $response = [
                'status' => false,
                'message' => 'already registed partner',
            ];
            return response()->json($response);
        }

        $checkPartner = partners::where('partner_name', $request->partner_name)->first();
        
        if($checkPartner)
        {
            $response = [
                'status' => false,
                'message' => 'already registed Category',
            ];
            return response()->json($response);
        }

        $categoryid = category::select('id')->where('category_name', $request->category_name)->first();
        
        $contact_id = User::select('id')->where('email',$request->email)->first(); 
        if($contact_id == ''){
            $response = ['status' => false, 'data' => 'Could not register partner.'];
            return response()->json($response, 201);
        }      

        $partners  = new partners();
        $partners->category_id = $categoryid->id;
        $partners->partner_name = $partner_name;
        $partners->address = $address;
        $partners->partner_email = $partner_email;
        $partners->password = $password;
        $partners->postcodes = $postcodes;
        $partners->contact_userid = $contact_id->id;
        $partners->email = $email;
        $partners->telephone = $tel_number;

        // $team_arr =  json_decode($request->team,true);
        // $a =  $request->team;
        // return $request->team;

        if($partners->save())
        {
            $response = [
                'status' => true,
                'data' => [
                    'category_name' =>$category_name,
                    'partner_name' => $partners->partner_name,
                    'address'=> $partners->address,
                    'partner_email'=> $partners->partner_email,
                    'postcodes'=> $partners->postcodes,
                    'contact_name'=> $contact_name,
                    'email'=> $partners->email,
                    'telephone'=> $partners->telephone,
                    'message'=>'partners was added successfully.'
                ],
            ];
        }
        else{
            $response = ['status' => false, 'data' => 'Could not register partner.'];
            return response()->json($response, 201);
        }
        return response()->json($response, 201);
    }
    public function categories(Request $request)
    {
        $all_data = category::all();
        if($all_data == null)
        {
            $response = ['status' => true, 'message'=>'data empty'];
            return response()->json($response, 201); 
        }

        $category_arr = [];
        foreach($all_data as $data)
        {
            $category_arr['name'] = $data->category_name;
            $category_arr['expires'] = $data->expires;
            //$category_arr['service'] = service::where('category_id',$data->id)->get();
            $category_arr['postcode'] = $data->postcode;
        }      
        
        $response = ['status' => true, 'data' => $category_arr];
        return response()->json($response, 201);

    }
    public function unlock_category(Request $request)
    { 
        $category_name = $request->category_name;
        $category = category::where('category_name',$category_name)->get()->first();
       
        do {
            $boomid = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),1,8);                    
            $current_code = category::where('boomid', $boomid)->get()->first();
        }
        while(!empty($current_code));               
        $category->status = 1;
        $category->boomid = $boomid;   
        if($category->save())
        {
            $category_arr = [];
            $category_arr['name'] = $category->category_name;
            $category_arr['expires'] = $category->expires;
            if($category->status == 1)
            {
                $category_arr['expires'] = "UnLocked"; 
            }
            $category_arr['boomID'] = $category->boomid; 
            $category_arr['service'] = service::where('category_id',$category->id)->get();
            $category_arr['postcode'] = $category->postcode;

            $response = ['status' => true, 'data' => $category_arr];
            return response()->json($response, 201);
        }
        else{
            $response = ['status' => false, 'message' => "Data is empty"];
            return response()->json($response, 201);
        }

    }
    public function partners(Request $request)
    {
        $all_data = partners::all();
        if($all_data == '')
        {
            $response = ['status' => true, 'message'=>'data empty'];
            return response()->json($response, 201); 
        }

        $partner_arr = [];
        foreach($all_data as $data)
        {
            $category_name = category::select('category_name')->where('id', $data->category_id)->first();
            $partner_arr['category_id'] = $category_name;
            $partner_arr['partner_name'] = $data->partner_name;
            $partner_arr['address'] = $data->address;
            $partner_arr['postcodes'] = $data->postcodes;
            $first_name = User::select('first_name')->where('id', $data->contact_userid)->first();
            $last_name = User::select('last_name')->where('id', $data->contact_userid)->first();
            $partner_arr['contact_userid'] = $first_name->first_name." ".$last_name->last_name;
            $partner_arr['email'] = $data->email;
            $partner_arr['telephone'] = $data->telephone; 
            $partner_arr['team'] = teams::where('partner_id',$data->id)->get();           
        }      
        
        $response = ['status' => true, 'data' => $partner_arr];
        return response()->json($response, 201);

    }
    public function checkBoomid(Request $request)
    {
        $partner_name = $request->partner_name;
        $boomid = $request->boomid;
        $partner = partners::where('partner_name',$partner_name)->get()->first();
        $category = category::where('id',$partner->category_id)->where('boomid',$boomid)->get()->first();
        if($category)
        {
            $response = [
                'status' => 'ok',
                'data' => [
                    'partner name' =>$partner->partner_name,
                    'boomid' => $category->boomid,
                    'address'=> $partner->address,                    
                ],
            ];
        }
        else
        {
            $response = ['status' => false, 'message' => "Data is empty"];           
        }
        return response()->json($response, 201);
    }
}
