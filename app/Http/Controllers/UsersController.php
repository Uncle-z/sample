<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;
use Mail;

class UsersController extends Controller
{
    public function __construct(){
        $this->middleware('auth', [
            'except' => ['show','create','store','index','confirmEmail']
        ]);
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }
    public function create(){
    	return view('users.create');
    }

    public function show(User $user){
        $statuses = $user->statuses()->orderBy('created_at', 'desc')->paginate(30);
    	return view('users.show', compact('user', 'statuses'));
    }

    public function store(Request $request){
    	$this->validate($request,[
    		'name' => 'required|max:50',
    		'email' => 'required|email|unique:users|max:255',
    		'password' => 'required|confirmed'
    	]);

    	$user = User::create([
    		'name' => $request->name,
    		'email' => $request->email,
    		'password' => bcrypt($request->password),
    	]);
    	
        $this->sentEmailConfirmationTo($user);
    	session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
    	return redirect('/');
    }

    protected function sentEmailConfirmationTo($user){
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'aufree@yousails.com';
        $name = 'Aufree';
        $to = $user->email;
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    public function edit(User $user){
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update(User $user,Request $request){
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $this->authorize('update', $user);

        $data = [];
        $data['name'] = $request->name;
        if($request->password){
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);
        session()->flash('success', '个人资料更新成功！');
        // $user->update([
        //     'name' => $request->name,
        //     'password' => bcrypt($request->password)
        // ]);

        return redirect() ->route('users.show', $user->id);
    }

    public function index(){
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    public function destroy(User $user){
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户');
        return back();
    }

    public function confirmEmail($token){
        $user = User::where('activation_token', $token)->firstOrFail();
        //User 方法对数据库中的用户查找activation_token字段和对应的值$token匹配并返回第一个用户，
        //查询不到则返回404
        $user->activated = true;
        $user->activation_token = null;
        $user->save();
        
        Auth::login($user);
        session()->flash('success', '恭喜你，账号激活成功');
        return redirect()->route('users.show', [$user]);
    }
    /*
    获取用户关注的人
     */
    public function followings(User $user){
        $users = $user->followings()->paginate(30);
        $title = '关注的人';
        return view('users.show_follow', compact('users', 'title'));
    }
    /*
    获取用户粉丝
     */
    public function followers(User $user){
        $users = $user->followers()->paginate(30);
        $title = '粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }
}
