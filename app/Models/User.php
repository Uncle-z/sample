<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * 定义数据表名称
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public static function boot(){
        parent::boot();
        static::creating(function($user){
            $user->activation_token = str_random(30);
        });
    }
    /**
     * [gravatar 获取用户在gravatar上的头像]
     * @param  string $size [description]
     * @return [type]       [description]
     */
    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }
    /*
    *每个用户对应多条微博
     */
    public function statuses(){
        return $this->hasMany(Status::class);
    }    
    /*
    填充微博数据
     */
    public function feed(){
        $user_ids = Auth::user()->followings->pluck('id')->toArray();
        array_push($user_ids, Auth::user()->id);
        return Status::whereIn('user_id',$user_ids)->with('user')->orderBy('created_at', 'desc');
    }

    /*
    获取用户的粉丝
     */
    public function followers(){
        return $this->belongsToMany(User::Class, 'followers', 'user_id', 'follower_id');
    }

    /*
    获取用户关注的人
     */   
    public function followings(){
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }

    /*
    关注某个用户
     */
    public function follow($user_ids){
        if(!is_array($user_ids)){
            $user_ids = compact('user_ids');
        }

        $this->followings()->sync($user_ids, false); 
    }
    /*
    取消关注某个用户
     */
    public function unfollow($user_ids){
        if(!is_array($user_ids)){
            $user_ids = compact('user_ids');
        }

        $this->followings()->detach($user_ids);
    }
    /*
    判断某个用户是否关注了另外一个用户
     */
    public function isFollowing($user_id){
        return $this->followings->contains($user_id);
    }
}
