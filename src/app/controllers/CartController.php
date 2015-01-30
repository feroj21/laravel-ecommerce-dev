<?php
class CartController extends \BaseController {
    
    public function index(){  
        $data = array();
        $product_list = Cart::instance('shopping')->content();
        $count = Cart::instance('shopping')->count(false);
        $total_price = Cart::instance('shopping')->total();
    
        $data = array(
                    'response'          => 'OK',
                    'message'           => 'Cart list returned successfully.',
                    'products'          => $product_list,
                    'total_price'       => $total_price,
                    'count'             => $count,
                    'code'              => 200,
                );
        return $data;
    }
    
    public function store(){  
        $data = array();
        $product_id = Input::get('id');
        //$p_value = Product::first(['_id' => $product_id]);
        
        //Cart::add(array('id' => $p_value->_id, 'name' => $p_value->name, 'qty' => Input::get('qty'), 'price' => $p_value->price, 'options' => array('size' => Input::get('size'),'color' => $p_value->color)));
        Cart::instance('shopping')->add(array('id' => '294ad', 'name' => 'Product 1', 'qty' => 2, 'price' => 9.99, 'options' => array('size' => 'large')));
    
        $data = array(
                    'response'          => 'OK',
                    'message'           => 'Product successfully added to cart.',
                    'code'              => 200,
                );
        return $data;
    }

    public function destroy($id) {  
        $data = array();
        $row_id = $id;
        Cart::instance('shopping')->remove($row_id);
        
        $data = array(
                    'response'          => 'OK',
                    'message'           => 'Product successfully removed from cart.',
                    'code'              => 200,
                );
        return $data;
    }
    
    public function update($id){  
        $data = array();
        $row_id = $id;
        Cart::instance('shopping')->update($row_id, array('qty' => Input::get('qty')));
        
        $data = array(
                    'response'          => 'OK',
                    'message'           => 'Cart successfully updated.',
                    'code'              => 200,
                );
        return $data;
    }
    
    
    /*
     * Wishlist sector started from here....
     */
    
    public function addToWishlist(){  
        $data = array();
        $product_id = Input::get('id');
        //$p_value = Product::first(['_id' => $product_id]);
        
        //Cart::add(array('id' => $p_value->_id, 'name' => $p_value->name, 'qty' => Input::get('qty'), 'price' => $p_value->price, 'options' => array('size' => Input::get('size'),'color' => $p_value->color)));
        Cart::instance('wishlist')->add(array('id' => '294ad', 'name' => 'Product 1', 'qty' => 2, 'price' => 9.99, 'options' => array('size' => 'large')));
    
        $data = array(
                    'response'          => 'OK',
                    'message'           => 'Product successfully added to your wishlist.',
                    'code'              => 200,
                );
        return $data;
    }
    
    public function showWishlistProducts(){  
        $data = array();
        $product_list = Cart::instance('wishlist')->content();
        $count = Cart::instance('wishlist')->count(false);
        $total_price = Cart::instance('wishlist')->total();
    
        $data = array(
                    'response'          => 'OK',
                    'message'           => 'Wishlist returned successfully.',
                    'products'          => $product_list,
                    'total_price'       => $total_price,
                    'count'             => $count,
                    'code'              => 200,
                );
        return $data;
    }
    
    public function deleteWishlistProduct(){  
        $data = array();
        $row_id = Input::get('rowid');
        Cart::instance('wishlist')->remove($row_id);
        
        $data = array(
                    'response'          => 'OK',
                    'message'           => 'Product successfully removed from wishlist.',
                    'code'              => 200,
                );
        return $data;
    }
}
