<?php

namespace App\Http\Controllers\User;

use App\User;
use App\Mail\UserCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Transformers\UserTransformer;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class UserController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
  
        $this->middleware('transform.input:'.UserTransformer::class)->only(['store','update']);

        $this->middleware('auth:api')->except(['store','verify', 'resend']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
      
        return $this->showAll($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ];
 
        $validator = Validator::make($request->all(), $rules);
 
        if($validator->fails()){
            return $this->errorResponse($validator->errors(), 422);
        }

        $data = $request->all();
        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationCode();
        $data['admin'] = User::REGULAR_USER;

        $user = User::create($data);

        return $this->showOne($user, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    { 
        $rules = [
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'min:6|confirmed',
            'admin' => 'in:' . User::ADMIN_USER . ',' . User::REGULAR_USER,
        ];

        $validator = Validator::make($request->all(), $rules);
 
        if($validator->fails()){
            return $this->errorResponse($validator->errors(), 422);
        }
 
        if($request->has('name')) {
            $user->name = $request->name;
        }
 
        if($request->has('email') && $user->email != $request->email) {
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationCode();
            $user->email = $request->email;
        }
 
        if($request->has('password')) {
            $user->password = bcrypt($request->password);
        }
 
        if($request->has('admin')) {
            if(!$user->isVerified()) {
                return $this->errorResponse('Only Verified users can modify the admin field', 409);
            }
            $user->admin = $request->admin;
        }
 
        if(!$user->isDirty()) {
            return $this->errorResponse('You need to specify a different value to update', 422);
        }
 
        $user->save();

        return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        return $this->showOne($user);
    }

    public function verify($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();
 
        $user->verified = User::VERIFIED_USER;
        $user->verification_token = null;
 
        $user->save();
 
        return $this->showMessage('The account has been verified succesfully');
    }

    public function resend(User $user)
    {
       if($user->isVerified()){
           return $this->errorRespone('This user is already verified', 409);
       }
      
       Mail::to($user)->send(new UserCreated($user));

       return $this->showMessage('The verification email has been resend');
   }
}
