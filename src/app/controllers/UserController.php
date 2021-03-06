<?php

class UserController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {

        return User::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {


        return array('message' => 'Form show.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {

        $validator = Validator::make(Input::all(), User::$rules);
        $data = array();

        if ($validator->passes()) {

            $checkDublicate = User::first(['email' => Input::get('email')]);

            if ($checkDublicate instanceOf User) {

                $data = array(
                    'response' => 'ERROR',
                    'message' => 'You have already been registared.',
                    'code' => 304,
                );
            } else {

                $user = new User;
                $value = Input::get('name') . Input::get('email');
                $token = $this->createToken($value);

                $user->name = Input::get('name');
                $user->email = Input::get('email');
                $user->password = Hash::make(Input::get('password'));
                $user->mobile = Input::get('mobile');
                $user->city = Input::get('city');
                $user->country = Input::get('country');
                $user->address = Input::get('address');
                $user->status_token = $token;
                $user->token_expire_date = Carbon::now()->addDay();
                $user->status = 0;

                $user->save(true);

                $id = $user->_id;

                $params = array(
                    'token' => $token,
                    'id' => $id,
                    'link' => 'http://codewarriors.me/#/user/verify/' . $token . '/' . $id
                );

                try {
                    Mail::send('emails.auth.verify', array('name' => Input::get('name'), 'values' => $params), function($message) {
                        $message->to(Input::get('email'), Input::get('name'))->subject('[Ergo Warriors] Please Verify Your Email');
                    });
                } catch (Exception $e) {
                    
                }

                $data = array(
                    'response' => 'OK',
                    'message' => 'You have been registered successfully. Please check your mail for confirmation link.',
                    'token' => $token,
                    'id' => $id,
                    'code' => 200,
                );
            }
        } else {

            // validation has failed, display error messages
            $data = array(
                'message' => 'Validation failed not registered',
                'code' => 400,
            );
        }

        return $data;
    }

    /**
     * For creating token.
     *
     * @param  int  $value
     * @return Response
     */
    public function createToken($value) {

        $token_value = $value . time();

        return $token = md5($token_value);
    }

    /**
     * Request For creating new token.
     *
     * @param  int  $email
     * @return Response
     */
    public function requestToken() {

        $checkExistance = User::first(['email' => Input::get('email')]);

        if ($checkExistance instanceOf User) {

            $token_value = $checkExistance->first_name . $checkExistance->email; //.$checkExistance->last_name;
            $token = $this->createToken($token_value);
            $id = $checkExistance->_id;

            if (Input::get('sector') == "email-verification") {

                $checkExistance->status_token = $token;
                $checkExistance->token_expire_date = Carbon::now()->addDay();
                $checkExistance->save(true);

                $data = array(
                    'response' => 'OK',
                    'message' => 'New token created and mailed to your email.',
                    'verification_link' => Request::server('HTTP_HOST') . '/api/v1/auth/' . $token . '/verify/' . $id,
                    'id' => $id,
                    'code' => 200,
                );
            } else if (Input::get('sector') == "forgot-password") {

                if ($checkExistance->status == 0) {

                    $data = array(
                        'response' => 'ERROR',
                        'message' => 'Your email is not verified yet. Please verify your email first.',
                        'code' => 401,
                    );
                } else {

                    $checkExistance->status_token = $token;
                    $checkExistance->token_expire_date = Carbon::now()->addDay();
                    $checkExistance->save(true);

                    $data = array(
                        'response' => 'OK',
                        'message' => 'New token created and mailed to your email.',
                        'token' => $token,
                        'id' => $id,
                        'code' => 200,
                    );
                }
            }
        } else {

            $data = array(
                'message' => 'Email address is not currect.',
                'code' => 400,
            );
        }

        return $data;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {

        $data = array();

        $user = User::find($id);
//        $user = User::find($id);
        // Get location data
        $location = $this->_location();


        if (isset($user->email)) {

            $data = $user;
            $data['location'] = $location;
//            $data  =  Country::find(1);
            $data['code'] = 200;
            $data['response'] = 'OK';
        } else {

            $data = array(
                'response' => 'ERROR',
                'message' => 'user not found',
                'code' => 400,
            );
        }

        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {

        $data = array();

        $user = User::find($id);

        $location = $this->_location();
        if (isset($user->email)) {

            $data = $user;
            $data['location'] = $location;
            $data['code'] = 200;
            $data['response'] = 'OK';
        } else {

            $data = array(
                'response' => 'ERROR',
                'message' => 'user not found',
                'code' => 400,
            );
        }

        return $data;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id) {

        $data = array();

        $user = User::first($id);

        if (isset($user->email)) {

            $user->name = Input::get('name');
            $user->email = Input::get('email');

            if (Input::get('old_password') && Input::get('new_password') != '') {

                $check_user = array('email' => Input::get('email'), 'password' => Input::get('old_password'));


                if (Auth::attempt($check_user)) {
                    $user->password = Hash::make(Input::get('new_password'));

                    $data = array(
                        'password' => 'Password has been updated successfully.'
                    );
                } else {

                    $data = array(
                        'password' => 'May be your entered wrong password.'
                    );
                }
            }

            $user->mobile = Input::get('mobile');
            $user->city = Input::get('city');
            $user->country = Input::get('country');
            $user->country = $user->country['name'];
            $user->address = Input::get('address');

            $user->save(true);

            $data = array(
                'response' => 'OK',
                'message' => 'user successfully updated.',
                'code' => 200,
            );
        } else {

            $data = array(
                'response' => 'ERROR',
                'message' => 'user not found',
                'code' => 400,
            );
        }

        return $data;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {

        $user = User::first($id);

        if (isset($user)) {

            $user->delete();

            $data = array(
                'response' => 'OK',
                'message' => 'user successfully deleted.',
                'code' => 200,
            );
        } else {

            $data = array(
                'response' => 'ERROR',
                'message' => 'user not found',
                'code' => 400,
            );
        }

        return $data;
    }

    public function login() {
        $users = array('email' => Input::get('email'), 'password' => Input::get('password'));
        $data = array();

        $user = User::first(array('email' => $users['email']));


        if ($user->status == 1) {

            if (Auth::attempt($users)) {

                $person = array(
                    'name' => $user->name,
                    'email' => $user->email,
                    'id' => $user->_id,
                );

                $data = array(
                    'response' => 'OK',
                    'message' => $person,
                    'code' => 200,
                );
            } else {

                $data = array(
                    'response' => 'ERROR',
                    'message' => 'Your username/password combination was incorrect',
                    'code' => 400,
                );
            }
        } else {

            $data = array(
                'response' => 'ERROR',
                'message' => 'You did not verify your account.',
                'verification_link' => Request::server('HTTP_HOST') . '/api/v1/auth/' . $user->status_token . '/verify/' . $user->_id,
                'code' => 400,
            );
        }

        return $data;
    }

    /**
     * For verify user.
     *
     * @param  int  $id
     * @return Response
     */
    public function verifyUser($token, $id) {

        $data = array();
        //$id             = Crypt::decrypt($id);
        $checkExistance = User::first(['_id' => $id, 'status_token' => $token]);

        if ($checkExistance instanceOf User) {

            if (Carbon::now() <= $checkExistance->token_expire_date["date"]) {

                if ($checkExistance->status == 0) {

                    $checkExistance->status = 1;
                    $checkExistance->save(true);

                    $data = array(
                        'response' => 'OK',
                        'message' => 'Email successfully verified',
                        'code' => 200,
                    );
                } else {
                    $data = array(
                        'response' => 'ERROR',
                        'message' => 'Your email is already verified.',
                        'code' => 203,
                    );
                }
            } else {

                $data = array(
                    'response' => 'ERROR',
                    'message' => 'Email validation token expired. You need to request for a new one',
                    'code' => 304,
                );
            }
        } else {
            $data = array(
                'response' => 'ERROR',
                'message' => 'Something wrong with your verification. try again later.',
                'code' => 304,
            );
        }

        return $data;
    }

    /**
     * For check userExistance for forgot password.
     *
     * @param  int  $id
     * @return Response
     */
    public function forgotPassword() {

        $data = array();
        //$id     = Crypt::decrypt(Input::get('id'));
        $token = Input::get('token');

        $checkExistance = User::first(['_id' => $id, 'status_token' => $token]);

        if ($checkExistance instanceOf User) {

            if (Carbon::now() <= $checkExistance->token_expire_date["date"]) {

                $checkExistance->password = Hash::make(Input::get('password'));
                $checkExistance->save(true);

                $data = array(
                    'response' => 'OK',
                    'message' => 'Password changed successfully.',
                    'code' => 200,
                );
            } else {

                $data = array(
                    'response' => 'ERROR',
                    'message' => 'Forgot password token expired. You need to request for a new one',
                    'code' => 403,
                );
            }
        } else {

            $data = array(
                'response' => 'ERROR',
                'message' => 'Not a valid user.',
                'code' => 401,
            );
        }

        return $data;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function logout() {
        Auth::logout();

//        $data = array();
//
//        if (Auth::logout()) {
//
//            $data = array(
//                'response'  => 'OK',
//                'message'   => 'You have been successfully logged out.',
//                'code'      => 200,
//            );
//
//        } else {
//
//            $data = array(
//                'response'  => 'ERROR',
//                'message'   => 'Problem occured when trying to logout, pleaes try again.',
//                'code'      => 400,
//            );
//        }
//        return $data;
    }

    /**
     * Function for checking user is logged in or not.
     *
     * @return boolean
     */
    public function isLogged() {
        if (Auth::check()) {
            $data = array(
                'response' => 'OK',
                'message' => 'Logged in',
                'code' => 400,
            );
        } else {
            $data = array(
                'response' => 'ERROR',
                'message' => 'Not logged in.',
                'code' => 400,
            );
        }
        return $data;
    }

    public function test() {
        // configure your available scopes
        $defaultScope = 'basic';
        $supportedScopes = array(
            'basic',
            'postonwall',
            'accessphonenumber'
        );

        $memory = new OAuth2\Storage\Memory(array(
            'default_scope' => $defaultScope,
            'supported_scopes' => $supportedScopes
        ));

        $scopeUtil = new OAuth2\Scope($memory);

        App::make('oauth2')->setScopeUtil($scopeUtil);
        App::make('storage')->setUser("a@gmail.com", "123654");
    }

//    private function _location() {
//
//        // data pupulate for city and country
//        $citys      = City::all();
//        $countries  = Country::all();
//        $data       = array();
//
//        foreach( $citys as $name ) {
//            $data['city'][] = $name->city;
//        }
//
//        foreach( $countries as $name ) {
//            $data['country'][] = $name->country;
//        }
//
//        return $data;
//    }
    private function _location() {
        $countries = Country::all();
        $finaldata = array();

        foreach ($countries as $name) {
            $data = array();
            $data['name'] = $name->country;
            $dat['cities'] = array();
            foreach ($name->cities as $city)
                $data['cities'][] = $city['name'];
            $finaldata[] = $data;
        }

        return $finaldata;
    }

    /**
     *
     * Add revew from
     */
    public function userReview() {

        $data = array();

        $user_review = UserReview::first(['user_id' => Input::get('user_id')]);

        if (isset($user_review->user_name)) {

            $review = array(
                'comment' => Input::get('user_comment'),
                'rating' => Input::get('user_rating'),
            );

            $user_review->embed('review', $review);

            $user_review->save(true);

            $data = array(
                'response' => 'OK',
                'message' => 'review successfully added in user.',
                'code' => 200,
            );
        } else {

            $user_review = new UserReview;
            $user_review->user_id = Input::get('user_id');
            $user_review->user_name = Input::get('user_name');

            $review = array(
                'comment' => Input::get('user_comment'),
                'rating' => Input::get('user_rating'),
            );

            $user_review->embed('review', $review);

            $user_review->save();

            $data = array(
                'response' => 'OK',
                'message' => 'new review successfully added.',
                'code' => 200,
            );
        }

        return $data;
    }

    public function getUserProductList($id) {
        $data = array();
        $products = Product::where(['seller_id' => $id]);
        if ($products) {
            return json_encode($products->toArray());
        } else {

            $data = array(
                'response' => 'ERROR',
                'message' => 'No product found.',
                'code' => 400,
            );
        }

        return json_encode($data);
    }
    
    public function getUserPurchasedProductList($id) {
        $data = array();
        $products = Order::where(['buyer_id' => $id]);
        if ($products) {
            return json_encode($products->toArray());
        } else {

            $data = array(
                'response' => 'ERROR',
                'message' => 'No product found.',
                'code' => 400,
            );
        }

        return json_encode($data);
    }

}
