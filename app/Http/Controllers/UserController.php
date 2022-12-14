<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;
use App\Models\SubCategory;
use Illuminate\Support\Facades\DB;
use Session;


class UserController extends Controller
{

   public function __construct()
    {
      $category = [];
      $categories = Category::all();

      foreach($categories as $cat){
         $subCategory = DB::select('SELECT * FROM sub_categories WHERE category_id = "'.$cat->id.'" ');
         $temp_sub_cat = [];
         foreach($subCategory as $sub_cat){
            $products = DB::select('SELECT* FROM products WHERE sub_category_id = "'.$sub_cat->id.'"');
            $sub_cat->products = $products;
            array_push($temp_sub_cat, $sub_cat);
         }


         $cat->sub_category = $temp_sub_cat;
         array_push($category, $cat);
      }
      $category_products = DB::select('SELECT p.*, c.name  AS category FROM products p INNER JOIN categories c ON p.category_id = c.id');
       View::share( 'category', $category);
       View::share( 'products', $category_products );
    }
    
   //welcome page

   public function welcome_page(Request $request){
      
      // return $category;
      return view('welcome');
   }

    //Show login form

   public function user_login_form(Request $request){
       
       return view('auth.user_login');

   }


    //Show login form

   public function user_register_form(Request $request){
       
       return view('auth.user_register');

   }

   public function view_users(Request $request){

      $users = User::all();
      return view('admin.users.viewUsers')->with('users', $users);
   }

   public function delete_user($id){
      $user = User::find($id);
      $user->delete();
      return redirect()->route('admin.view-users');
      // return redirect()->route('admin.category');
   }

    

    //user logout function

public function logout(Request $request){
     Auth::logout();
    return redirect('/');
}

   //view cart items 

   public function view_cart_items(Request $request){
      return view('user.cart.index');
   }

   //Adding to cart session

   public function add_to_cart(Request $request, $id){
      $product=Product::find($id);
      $existing_cart=$request->session()->has('cart') ? $request->session()->get('cart') : null;
      $cart=new Cart($existing_cart);
      $cart->add_to_cart($product, $product->id);
      $request->session()->put('cart', $cart);
      return redirect('/view-cart-items');
   }


// *************************************************************************************** bash code ************************

public function view_product_details(Request $request, $id){

        $product_details=Product::join('categories', 'products.category_id', '=', 'categories.id')
        ->join('sub_categories', 'products.sub_category_id', '=', 'sub_categories.id')->select('sub_categories.name as sub_category_name',)->first();

      $product_details = DB::select('SELECT p.*, C.name AS category_name,S.name AS sub_category_name FROM products P 
      INNER JOIN categories C ON P.category_id = C.id INNER JOIN sub_categories S ON P.sub_category_id = S.id WHERE P.id = "'.$id.'" ');
      // $product_details = $product_details[0];
      // return $product_details[0]->product_name;
      return view('user.products.product_details')->with("product_details", $product_details);


}


public function delete_cart(Request $request){
   if($request->session()->exists('cart'))
   {
      $request->session()->forget('cart');
      return redirect()->back();
   }
}

public function checkout(Request $request){
   return view('user.cart.checkout');
}

public function products_under_category(Request $request, $id){
   $category_products = DB::select('SELECT p.*, c.name as category_name, c.image FROM products p INNER JOIN categories c ON c.id = p.category_id WHERE c.id = '.$id);
   $total_category_products = DB::select('SELECT *, (SELECT COUNT(p.id) FROM products p WHERE p.category_id = c.id) as total_products  FROM categories c ORDER BY total_products DESC');
   $current_category = Category::find($id);

   return view('user.products.product_categories')
   ->with("category_products", $category_products)
   ->with("current_category", $current_category)
   ->with("total_category_products", $total_category_products);
}

   public function products_under_sub_category(Request $request, $id){

      $sub_category_products = DB::select('SELECT p.*, c.name as sub_category_name, c.image FROM products p INNER JOIN sub_categories c ON c.id = p.sub_category_id WHERE c.id = '.intval($id));
      $total_category_products = DB::select('SELECT *, (SELECT COUNT(p.id) FROM products p WHERE p.sub_category_id = c.id) as total_products  FROM sub_categories c ORDER BY total_products DESC');
      $current_sub_category = SubCategory::find($id);

      return view('user.products.product_sub_category')
      ->with("sub_category_products", $sub_category_products)
      ->with("total_category_products", $total_category_products)
      ->with("current_sub_category", $current_sub_category);

   }






   
}