<?php 
    namespace App\Http\Controllers;
    use App\Models\test;
    use App\Models\Movie;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;

    use Symfony\Component\Console\Input\Input;

    class testController extends Controller{
        public function home(){

            return view('home',[
                'heading'=>'khong phai chao',
                'res'=>DB::select("SELECT movie.id,title,description,poster_link FROM movie 
                                    INNER JOIN movie_link on movie_link.id =movie.link_id
                                    ORDER BY created_at DESC
                                    LIMIT 0,10")
                

            ]);
        }

        public function table(){
            if(isset($msg)){
                echo $msg;
            }
            return view('tables',[
                'res'=>DB::select("SELECT movie.id,title,moviecategory.name as category from movie,moviecategory
                                    WHERE movie.category_id =moviecategory.id"),
                'category'=>DB::table('moviecategory')->distinct()->get(),
                'specialgroup'=>DB::table('specialgroup')->distinct()->get()
            ]);
        }
        public function add_movie(){
            return view('addmovie',[
                'category'=>DB::table('moviecategory')->distinct()->get(),
                'specialgroup'=>DB::table('specialgroup')->distinct()->get()
            ]);
        }
        public function post_movie(Request $request){
            $request->validate([
                'title'=>'required|max:255',
                'description'=>'required|max:255',
                'movie_link'=>'required|url',
                'poster_link'=>'nullable|mimes:png,jpg,jpeg,webp',
                'trailer_link'=>'required|url'
            ]);

            if($request->has('poster_link')){
                $file =$request->file('poster_link'); //$file variable stores the uploaded image file
               
                $extension =$file->getClientOriginalExtension(); //get jpg or png of image
                $filename =time().'.'.$extension; //file of image to store into upload directory
                $file->move('uploads/',$filename); //move $file to uploads directory with named is $filename
    

            }

            // insert into movie_link table
            $query_movie =DB::select(
                "SELECT * FROM movie_link
                WHERE movie_link='{$request->movie_link}' and trailer_link='{$request->trailer_link}' LIMIT 0,1 "
            );
            // $query_movie =DB::table('movie_link')->where('movie_link',$request->movie_link)->where('trailer_link',$request->trailer_link)->limit(1)->get();
            
            if(!$query_movie){
                $insert_link =DB::table('movie_link')->insert([
                    'movie_link'=>$request->movie_link,
                    'poster_link'=>"uploads/{$filename}",
                    'trailer_link'=>$request->trailer_link
                ]);
                
            }else{
               
                if(File::exists("uploads/{$filename}")){
                    File::delete("uploads/{$filename}");
                }
                

                
                return Redirect::to('/add-movie')->with(['msg'=>'The Movie has been existed in the database']);
            }
            if($insert_link){
                $id_link =DB::table('movie_link')->where('movie_link',$request->movie_link)->where('trailer_link',$request->trailer_link)->distinct()->get('id');
               
                $insert_movie=DB::table('movie')->insert([
                    'category_id'=>$request->category,
                    'specialgroup_id'=>$request->specialgroup,
                    'title'=>$request->title,
                    'description'=>$request->description,
                    'link_id'=>$id_link[0]->id,
                    'created_at'=>NOW(),
                    'updated_at'=>NOW()
                ]);
                if($insert_movie){
                    return Redirect::to('/add-movie')->with(['msg'=>'Movie has been created']);
                }
               
            }

            return Redirect::to('/add-movie')->with(['msg'=>'Insert Movie was not successful']);



           
        }
        public function get_movie($id){
            $respose =DB::select(
                "SELECT category_id,specialgroup_id,title,description,movie_link,poster_link,trailer_link FROM movie
                INNER JOIN movie_link ON link_id =movie_link.id
                WHERE movie.id={$id}
                ");
            return response()->json($respose);
        }

        public function update_movie(Request $request,$id){
            // return Redirect::to('/tables')->with(['msg'=>'Moive has been updated']);
            try{
                $record_movie =DB::table('movie')->find($id);
                $record_link =DB::table('movie_link')->find($record_movie->link_id);
                if($request->has('poster_link')){
                    $file =$request->file('poster_link');
                    $extension =$file->getClientOriginalExtension();
                    $filename =time().'.'.$extension;
                    $file->move("uploads/",$filename);
                    if(File::exists($record_link->poster_link)){
                        File::delete($record_link->poster_link);
                    }
                    DB::table('movie_link')->where('id',$record_movie->link_id)->limit(1)->update([
                        'movie_link'=>$request->movie_link,
                        'poster_link'=>"uploads/{$filename}",
                        'trailer_link'=>$request->trailer_link
                    ]);
                }else{
                    DB::table('movie_link')->where('id',$record_movie->link_id)->limit(1)->update([
                        'movie_link'=>$request->movie_link,
                        'trailer_link'=>$request->trailer_link
                    ]);
                }

                DB::table('movie')->where('id',$record_movie->id)->limit(1)->update([
                    'category_id'=>$request->category,
                    'specialgroup_id'=>$request->specialgroup,
                    'title'=>$request->name_movie,
                    'description'=>$request->description,
                    'updated_at'=>NOW()
                ]);

                return Redirect::to('/tables')->with(['msg'=>'Movie has been updated']);
            }catch(\Exception $e){
                return Redirect::to('/tables')->with(['msg'=>$e->getMessage()]);
            }
        }
        public function delete_movie($id){
           
            $id_link_1 =DB::table('movie')->find($id);
            $image =DB::table('movie_link')->find($id_link_1->link_id);
            
            try{
                if(File::exists($image->poster_link)){
                    File::delete($image->poster_link);
                }
                DB::table('movie')->where('id',$id)->distinct()->delete();
                DB::table('movie_link')->distinct()->delete($id_link_1->link_id);
              
                return Redirect::to('/tables')->with(['msg'=>'Delete was successfull']);

            }catch(\Exception $e){
                return Redirect::to('/tables')->with(['msg'=>$e->getMessage()]);
            }
        }
        public function voucher_management(){
            return view('voucher',[
                'res'=>DB::table('voucher')->offset(0)->limit(15)->get()
            ]);
        }

        public function live_search_voucher(Request $request){
            
            try{
                
                if($request->has('query')){
                    
                    $query_voucher =DB::table('voucher')->where('name','like',"{$request->query('query')}%")->orderBy('id')->get();
                }else{
                    $query_voucher=DB::table('voucher')->limit(10)->offset(0)->orderBy('id')->get();
                }

                return response()->json(['data'=>$query_voucher]);
            }catch(\Exception $e ){
                return response()->json(['msg'=>$e->getMessage()]);
            }
        }

        public function add_voucher(Request $request){
            $request->validate([
                'name_voucher'=>'required|max:255',
                'discount'=>'integer'
            ]);

            try{
                
                DB::table('voucher')->insert([
                    'name'=>$request->name_voucher,
                    'code'=>$request->code,
                    'discount_percentage'=>$request->discount,
                    'status'=>$request->status,
                    'voucherstart_date'=>NOW(),
                    'voucherend_date'=>NOW()

                ]);
                return Redirect::to('/voucher-management')->with(['msg'=>'Voucher was created']);
               

            }catch(\Exception $e){
                return Redirect::to('/voucher-management')->with(['msg'=>$e->getMessage()]);
            }
        }

        public function get_voucher($id){
            $query_voucher =DB::table('voucher')->where('id',$id)->distinct()->get();
            return response()->json($query_voucher);
        }
        public function delete_voucher($id){
            try{
                DB::table('voucher')->where('id',$id)->distinct()->delete();
                return Redirect::to('/voucher-management')->with(['msg'=>'Voucher was deleted']);
            }catch(\Exception $e){
                return Redirect::to('/voucher-management')->with(['msg'=>$e->getMessage()]);
            }
        }
        public function update_voucher(Request $request,$id){
            try{
                DB::table('voucher')->where('id',$id)->update([
                    'name'=>$request->name_voucher,
                    'code'=>$request->code,
                    'discount_percentage'=>$request->discount,
                    'status'=>$request->status,
                    'voucherstart_date'=>NOW(),
                    'voucherend_date'=>NOW()
                ]);
                return Redirect::to('/voucher-management')->with(['msg'=>'Voucher was Update']);
            }catch(\Exception $e){
                return Redirect::to('/voucher-management')->with(['msg'=>$e->getMessage()]);
            }
        }
        public function users_management(){
            return view('users',[
                'res'=>DB::table('user')->join('user_role',function($join){
                    $join->on('user.role_id','=','user_role.id');
                })->get(),
                'role'=>DB::table('user_role')->get(['id','role_type'])
            ]);
        }
        public function live_search_users(Request $request){
            
            try{
                
                if($request->has('query')){
                    
                    $query_user =DB::table('user')->where('fullname','like',"{$request->query('query')}%")->orderBy('id')->get();
                }else{
                    $query_user=DB::table('user')->limit(10)->offset(0)->orderBy('id')->get();
                }

                return response()->json(['data'=>$query_user]);
            }catch(\Exception $e ){
                return response()->json(['msg'=>$e->getMessage()]);
            }
        }
        public function add_user(Request $request){
            $request->validate([
                'email'=>'email:rfc,dns',
                'avartar'=>'nullable|mimes:png,jpg,jpeg,web',
                'phoneNumber'=>'integer|required'

            ]);
            try{
                if($request->has('avartar')){
                    $file_image =$request->file('avartar');
                    $file_tail =$file_image->getClientOriginalExtension();
                    $file_name =time().".".$file_tail;
                    $file_image->move('avartar/',$file_name);

                }
                DB::table('user')->insert([
                    'fullname'=>$request->fullname,
                    'dayofbirth'=>$request->dayofbirth,
                    'email'=>$request->email,
                    'phoneNumber'=>$request->phoneNumber,
                    'address'=>$request->address,
                    'avartar'=>"avartar/{$file_name}",
                    'role_id'=>$request->role_id,
                    'plan_id'=>1,
                    'created_at'=>NOW(),
                    'updated_at'=>NOW()
                ]);
                return Redirect::to('/users-management')->with(['msg'=>'User was created']);
            }catch(\Exception $e){
                return Redirect::to('/users-management')->with(['msg'=>$e->getMessage()]);
            }
        }
    };