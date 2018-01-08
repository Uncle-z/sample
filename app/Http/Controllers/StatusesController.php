<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Status;
use Auth;

class StatusesController extends Controller
{
    public function __construct(){
    	$this->middleware('auth');
    }

    /*
    微博动态保存
     */
    public function store(Request $request){
    	$this->validate($request, [
    		'content' => 'required|max:150'
    	]);

    	Auth::user()->statuses()->create([
    		'content' => $request->content
    	]);

    	return redirect()->back();
    }

    public function destroy(Status $status){
        $this->authorize('destroy', $status);
        $status->delete();
        session()->flash('success', '微博已彻底删除！');
        return redirect()->back();
    }
}
