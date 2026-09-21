<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

use Gate;
use DB;

class UserController extends Controller
{


    public function __contruct()
    {
        $this->middleware('auth');
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // denies the gate if 
        if(Gate::denies('admin-access')){
            return redirect('errors.403');
        }
        
        $allusers = User::where('id','>=','3')->paginate(10); // get only records that start with id 3 and below
        // query using model eloquent 

        


        return view('admin.users.index')
        ->with('allusers',$allusers);

    }

   


    // new function added for viewing all user feedbacks 
    public function userfeedback()
    {
        $allfeedbacks = DB::table('feedbacks')
        ->select('*')
        ->paginate(10);

        return view('admin.users.feedbacks.show')
        ->with('allfeedbacks',$allfeedbacks);
    }
}
