<?php

class OrderController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return Order::all();
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return array('message' => 'Form show.');
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$data = array();
                $order = new Order;
                
                $order_items = Input::get('items');
                $product_info = array();
                $grand_total = 0;
                
                foreach ($order_items as $key => $value) {
                    
                    $product = Product::first($value['pdt_id']);               
                    $subtotal = $product->price * Input::get('qty');
                    $discount = ($product->price * $product->discount)/100;
                    $row_total = $subtotal - $discount;

                    $product_info[] = array(
                        'pdt_id'        => $value['pdt_id'],
                        'pdt_name'      => $product->name,
                        'pdt_price'     => $product->price,
                        'seller_id'     => $product->seller_id,
                        'pdt_qty'       => Input::get('qty'),
                        'pdt_subtotal'  => $subtotal,
                        'pdt_discount'  => $discount,
                        'row_total'     => $row_total
                    );
                    
                    $grand_total = $grand_total + $row_total;
                }

                $order->_id = getNextSequence("orderid");
                $order->status = "Pending";
                $order->buyer_id = Input::get('user_id');
                $order->billing_address = Input::get('billing_address');
                $order->shipping_address = Input::get('shipping_address');
                $order->payment_status = "Pending";
                $order->payment_info = Input::get('payment_info');
                $order->order_items = $product_info;
                $order->grand_total = $grand_total;
                
                
                if($order->save(true)){
                    $data = array(
                        'response'  => 'OK',
                        'message'   => 'Order stored successfully and prepared for shipping.',
                        'code'      => 200,
                    );
                }else{
                    $data = array(
                        'response'  => 'Error',
                        'message'   => 'Problem occared when try to connect remote server. Please try again.',
                        'code'      => 400,
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
	public function show($id)
	{
                $data = array();
                $value = Order::first($id);
                if($value instanceOf Order){
                    $data = $value;
                    $data['code']       = 200;
                    $data['response']   = 'OK';
                }else{
                    $data = array(
                        'response'  => 'Error',
                        'message'   => 'Problem occared when try to connect remote server.',
                        'code'      => 400,
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
	public function edit($id)
	{
		return array('message' => 'Form show.');
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		return array('message' => 'Not editable.');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		return array('message' => 'Not Deletable.');
	}
        
        /*
         * For generating an auto increament order id for managing order
         */
        
        public function getNextSequence($name) {
            $value = Counter::first($name);
            $currentCounter = $value->seq + 1;
            
            $value->seq = $currentCounter;
            $value->save(true);
            
            return $currentCounter;
         }

         /*
          * show order list for both buyer and seller
          */
         public function showUserOrder(){
             $data = array();
             $user_type = Input::get('user_type');
             $user_id = Input::get('user_id');
             
             if($user_type == "Seller"){
                 $order_data = Order::where(['order_items.seller_id'=>$user_id]);
                 $data = $order_data;
             }else{
                 $order_data = Order::where(['buyer_id'=>$user_id]);
                 $data = $order_data;
             }
             $data['code']       = 200;
             $data['response']   = 'OK';
             
             return $data;
         }
         
         public function changeStatus(){
             $data = array();
             $status = Input::get('user_type');
             $order_id = Input::get('order_id');
             
             $order_data = Order::first([$order_id]);
             
             $order_data->status = $status;
             
             $data['code']       = 200;
             $data['message']    = 'Product'.$status.'Successfully';
             $data['response']   = 'OK';
             
             return $data;
         }
}
