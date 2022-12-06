<?php
use Illuminate\Support\Facades\DB;

function getUserdetails()
{
	if(Auth::check())
    {
	$user_type=\App\Member::where('user_id',Auth::user()->id)->select('*')->first();

	if($user_type==null)
	{

		$user_type='admin';
	}
	else
	{
		$user_type='member';
	}
	if($user_type=='member')
	{
	$user=\App\User::where('users.id',Auth::user()->id)
	->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name','position.name as position_name')
	->leftjoin('member','users.id','=','member.user_id')
	->leftjoin('member_detail','member.member_no','=','member_detail.member_no')
	->leftjoin('campus','member.campus_id','=','campus.id')
	->leftjoin('position','member.position_id','=','position.id')
	->first();
	 $user['usertype']=$user_type;
	}
	else
	{
	$user=\App\User::where('users.id',Auth::user()->id)
	->select('*','admin.id as admin_id','users.id as user_id')
	->leftjoin('admin','users.id','=','admin.user_id')
	->leftjoin('cluster','admin.cluster_id','=','cluster.id')
	->first();
	$user['usertype']=$user_type;
	}
	return $user;
    }
    else
    {
    	 return redirect('login');
    }


}

function election()
{
  
  $active_election=false;
	if(Auth::check())
    {
	
	$user=\App\User::where('users.id',Auth::user()->id)
	->select('*')
	->leftjoin('member','users.id','=','member.user_id')
	->leftjoin('campus','member.campus_id','=','campus.id')
	->leftjoin('position','member.position_id','=','position.id')
	->first();
	 
	$active_election=DB::table('election')
	->where('cluster_id',$user->cluster_id)
	->where('status','OPEN')
	->first();
	
    	 return $active_election;
    }
	return $active_election;


}

function loan_app_count()
{
 
	$admin= DB::table('admin')->where('user_id',Auth::user()->id)->first();

	if($admin->role=='SUPER_ADMIN')
	{
		$count=DB::table('loan_applications')->where('status','PROCESSING')->count();
	}
	else
	{
		$count=DB::table('loan_applications')
		->leftjoin('member','loan_applications.member_no','=','member.member_no')
		->leftjoin('campus','member.campus_id','=','campus.id')
		->where('campus.cluster_id',$admin->cluster_id)
		->where('loan_applications.status','PROCESSING')->count();
	}



	return $count;


}

function prme_pr()
{
 
	$activeprmepr = DB::table('prme_pr')->where('status',1)->first();
	

	return $activeprmepr;


}

