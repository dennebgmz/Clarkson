<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class ElectionController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
      $election=DB::table('election')
      ->where('id',$id)
      ->first();

      $voted=DB::table('election_votes')
      ->where('member_no',getUserdetails()->member_no)
      ->where('election_id',$id)
      ->first();
      
      
      if(getUserdetails()->salary_grade=='1-15')
      {
       $candidates=DB::table('election_candidates')
       ->select('*','campus.name as campus_name','election_candidates.id as candidate_id')
       ->leftjoin('member','election_candidates.member_id','member.id')
       ->leftjoin('users','member.user_id','users.id')
       ->leftjoin('campus','member.campus_id','campus.id')
       ->where('election_candidates.salary_grade','1-15')
       ->where('election_candidates.election_id',$id)
       ->get();
     }
     else
     {
      $candidates=DB::table('election_candidates')
      ->select('*','campus.name as campus_name','election_candidates.id as candidate_id')
      ->leftjoin('member','election_candidates.member_id','member.id')
      ->leftjoin('users','member.user_id','users.id')
      ->leftjoin('campus','member.campus_id','campus.id')
      ->where('election_candidates.salary_grade','16-ABOVE')
      ->where('election_candidates.election_id',$id)
      ->get();
    }


    return view('member.election.election', array('candidates'=>$candidates, 'election'=>$election, 'voted'=>$voted));
  }

  public function admin_election_index()
  {

    $clusters=DB::table('cluster')->get();
    if(getUserdetails()->role=='SUPER_ADMIN')
    {
      $elections=DB::table('election')
      ->select('election.*','cluster.acronym')
      ->leftjoin('cluster','election.cluster_id','cluster.id')
      ->orderBy('election.created_at','desc')
      ->paginate(10);
    }
    else
    {
      $elections=DB::table('election')
      ->select('election.*','cluster.acronym')
      ->leftjoin('cluster','election.cluster_id','cluster.id')
      ->where('cluster.id','=',getUserdetails()->cluster_id)
      ->orderBy('election.created_at','desc')
      ->paginate(10);
    }


    return view('admin.election.index',array('clusters'=>$clusters, 'elections'=>$elections ));
  }

  public function admin_election_details($id)
  {

    $clusters=DB::table('cluster')->get();
    if(getUserdetails()->role=='SUPER_ADMIN')
    {
      $elections=DB::table('election')
      ->select('election.*','cluster.name')
      ->leftjoin('cluster','election.cluster_id','cluster.id')
      ->orderBy('election.created_at','desc')
      ->paginate(10);
    }
    else
    {
      $elections=DB::table('election')
      ->select('election.*','cluster.name')
      ->leftjoin('cluster','election.cluster_id','cluster.id')
      ->where('cluster.id','=',getUserdetails()->cluster_id)
      ->orderBy('election.created_at','desc')
      ->paginate(10);
    }

    $elect=DB::table('election')
    ->select('*','election.id as id')
    ->leftjoin('cluster','election.cluster_id','cluster.id')
    ->where('election.id',$id)
    ->first();

    $candidates15=DB::table('election_candidates')
    ->select('*','election_candidates.id as id')
    ->leftjoin('member','election_candidates.member_id','member.id')
    ->leftjoin('users','member.user_id','users.id')
    ->leftjoin('member_detail','member.member_no','member_detail.member_no')
    ->leftjoin('campus','member.campus_id','campus.id')
    ->where('election_candidates.election_id',$id)
    ->where('member_detail.salary_grade','1-15')
    ->get();

    $candidates16=DB::table('election_candidates')
    ->select('*','election_candidates.id as id')
    ->leftjoin('member','election_candidates.member_id','member.id')
    ->leftjoin('users','member.user_id','users.id')
    ->leftjoin('member_detail','member.member_no','member_detail.member_no')
    ->leftjoin('campus','member.campus_id','campus.id')
    ->where('election_candidates.election_id',$id)
    ->where('member_detail.salary_grade','16-ABOVE')
    ->get();



    $members15=DB::table('member')
    ->select('member.id', (DB::raw('CONCAT(member.member_no, " - ", users.last_name, ", ", users.first_name) AS full_name')))
    ->leftjoin('users','member.user_id','users.id')
    ->leftjoin('member_detail','member.member_no','member_detail.member_no')
    ->leftjoin('campus','member.campus_id','campus.id')
    ->where('campus.cluster_id',$elect->cluster_id)
    ->where('member_detail.salary_grade','1-15')
    ->get();

    $members16=DB::table('member')
    ->select('member.id', (DB::raw('CONCAT(member.member_no, " - ", users.last_name, ", ", users.first_name) AS full_name')))
    ->leftjoin('users','member.user_id','users.id')
    ->leftjoin('member_detail','member.member_no','member_detail.member_no')
    ->leftjoin('campus','member.campus_id','campus.id')
    ->where('campus.cluster_id',$elect->cluster_id)
    ->where('member_detail.salary_grade','16-ABOVE')
    ->get();



    return view('admin.election.detail',array('clusters'=>$clusters, 'elections'=>$elections, 'candidates15'=>$candidates15, 'candidates16'=>$candidates16, 'members15'=>$members15, 'members16'=>$members16, 'elect'=>$elect ));
  }
  

  public function get_member_details(Request $request)
  {

    $member15=DB::table('member')
    ->leftjoin('users','member.user_id','users.id')
    ->leftjoin('member_detail','member.member_no','member_detail.member_no')
    ->leftjoin('campus','member.campus_id','campus.id')
    ->where('member.id',$request->mem_id)
    ->first();

    echo json_encode($member15);
    exit();
  }

  public function remove_candidate(Request $request)
  {
    DB::table('election_candidates')->where('id', '=', $request->candidate_id)->delete();
      //    return redirect('/member/edit_beneficiaries')
      // ->with('success', 'Beneficiary Removed');

    return 1;
  }

  public function add_candidate_15(Request $request)
  {


    $candidate_id=DB::table('election_candidates')->insertGetId(
      ['election_id' => $request->election_id, 'member_id'=>$request->member15_id, 'salary_grade'=>'1-15', 'added_by'=>getUserdetails()->user_id]);

    $member=DB::table('member')
    ->leftjoin('users','member.user_id','users.id')
    ->leftjoin('member_detail','member.member_no','member_detail.member_no')
    ->leftjoin('campus','member.campus_id','campus.id')
    ->where('member.id',$request->member15_id)
    ->first();

    $target_dir = public_path()."/storage/app/election/photo/";
    $target_file = $target_dir . basename($_FILES["photo15"]["name"]);
    $temp = $_FILES["photo15"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $uploadOk = 1;


    $newfilename = 'photo15-'.$candidate_id.'-'.$member->member_no.$file_ext;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["photo15"]["tmp_name"], $target_dir.$newfilename);


    $photo=$newfilename;

    DB::table('election_candidates')
    ->where('id', $candidate_id)
    ->update(
      ['candidate_photo' => $photo]);

    $target_dir = public_path()."/storage/app/election/attachments/";
    $target_file = $target_dir . basename($_FILES["attachment15"]["name"]);
    $temp = $_FILES["attachment15"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $uploadOk = 1;


    $newfilename = 'attachment15-'.$candidate_id.'-'.$member->last_name.$file_ext;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["attachment15"]["tmp_name"], $target_dir.$newfilename);


    $attachment=$newfilename;

    DB::table('election_candidates')
    ->where('id', $candidate_id)
    ->update(
      ['file_credentials' => $attachment]);

    return redirect('admin/election/detail/'.$request->election_id)
    ->with('success', 'Candidate Added');
  }

  public function add_candidate_16(Request $request)
  {


    $candidate_id=DB::table('election_candidates')->insertGetId(
      ['election_id' => $request->election_id, 'member_id'=>$request->member16_id, 'salary_grade'=>'16-ABOVE', 'added_by'=>getUserdetails()->user_id]);

    $member=DB::table('member')
    ->leftjoin('users','member.user_id','users.id')
    ->leftjoin('member_detail','member.member_no','member_detail.member_no')
    ->leftjoin('campus','member.campus_id','campus.id')
    ->where('member.id',$request->member16_id)
    ->first();

    $target_dir = public_path()."/storage/app/election/photo/";
    $target_file = $target_dir . basename($_FILES["photo16"]["name"]);
    $temp = $_FILES["photo16"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $uploadOk = 1;


    $newfilename = 'photo16-'.$candidate_id.'-'.$member->member_no.$file_ext;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["photo16"]["tmp_name"], $target_dir.$newfilename);


    $photo=$newfilename;

    DB::table('election_candidates')
    ->where('id', $candidate_id)
    ->update(
      ['candidate_photo' => $photo]);

    $target_dir = public_path()."/storage/app/election/attachments/";
    $target_file = $target_dir . basename($_FILES["attachment16"]["name"]);
    $temp = $_FILES["attachment16"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $uploadOk = 1;


    $newfilename = 'attachment16-'.$candidate_id.'-'.$member->last_name.$file_ext;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["attachment16"]["tmp_name"], $target_dir.$newfilename);


    $attachment=$newfilename;

    DB::table('election_candidates')
    ->where('id', $candidate_id)
    ->update(
      ['file_credentials' => $attachment]);

    return redirect('admin/election/detail/'.$request->election_id)
    ->with('success', 'Candidate Added');
  }

  public function admin_election_save(Request $request)
  {
    $ed=strtotime($request->election_date);
    $election_date = date('Y-m-d H:i:s',$ed);

    if(getUserdetails()->role=='SUPER_ADMIN')
    {
      DB::table('election')->insert(
        ['cluster_id' => $request->cluster_id, 'year' => $request->year, 'status'=>'CREATED','election_date'=>$election_date, 'created_by'=>getUserdetails()->user_id]);
    }
    else
    {
     DB::table('election')->insert(
      ['cluster_id' => getUserdetails()->cluster_id, 'year' => $request->year, 'status'=>'CREATED','election_date'=>$election_date, 'created_by'=>getUserdetails()->user_id]);
   }
   

   return redirect('admin/election')
   ->with('success', 'Election Created. You can now add candidates');
 }

 public function open_election(Request $request)
 {

   DB::table('election')
   ->where('id', $request->election_id)
   ->update(
    ['status' => 'OPEN', 'time_open'=>date('Y-m-d H:i:s')]);
   
   

   return 1;
 }

 public function close_election(Request $request)
 {

  //1-15

  $result15=DB::table('election_votes')
  ->select((DB::raw('CONCAT(cand_user.first_name, " ", cand_user.last_name) AS cand_name')),DB::raw('count(election_candidates.id) as votes'))
  ->leftjoin('member','election_votes.member_no','member.member_no')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->leftjoin('election_candidates','election_votes.vote_casted','election_candidates.id')
  ->leftjoin('member as cand','election_candidates.member_id','cand.id')
  ->leftjoin('users as cand_user','cand.user_id','cand_user.id')
  ->where('election_votes.election_id',$request->election_id)
  ->where('cand_user.first_name','!=',null)
  ->where('member_detail.salary_grade','1-15')
  ->groupBy('election_candidates.id')
  ->orderBy('votes', 'desc')
  ->get();

  $member15_result_abstain=DB::table('election_votes')
  ->leftjoin('member','election_votes.member_no','member.member_no')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->leftjoin('election_candidates','election_votes.vote_casted','election_candidates.id')
  ->where('election_votes.election_id',$request->election_id)
  ->where('election_votes.abstain',1)
  ->where('member_detail.salary_grade','1-15')
  ->count();


  
  if(count($result15)<>0)
  {

    if(count($result15)>1)
    {
      if($result15[0]->votes==$result15[1]->votes)
      {
        $result15='TIE';
      }
      else
      {
        $candidate15=$result15[0];
      }

    }
    else
    {
      $candidate15=$result15[0];
    }


    if($candidate15)
    {
      if($member15_result_abstain>$candidate15->votes)
      {
        $result15='ABSTAIN';
      }
      else
      {
        $result15=$candidate15->cand_name;
      }
    }
  }
  else
  {
    if($member15_result_abstain<>0)
    {
      $result15='ABSTAIN';
    }
    else
    {
      $result15='NO VOTES';
    }
  }

  //16-ABOVE
  $result16=DB::table('election_votes')
  ->select((DB::raw('CONCAT(cand_user.first_name, " ", cand_user.last_name) AS cand_name')),DB::raw('count(election_candidates.id) as votes'))
  ->leftjoin('member','election_votes.member_no','member.member_no')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->leftjoin('election_candidates','election_votes.vote_casted','election_candidates.id')
  ->leftjoin('member as cand','election_candidates.member_id','cand.id')
  ->leftjoin('users as cand_user','cand.user_id','cand_user.id')
  ->where('election_votes.election_id',$request->election_id)
  ->where('cand_user.first_name','!=',null)
  ->where('member_detail.salary_grade','16-ABOVE')
  ->groupBy('election_candidates.id')
  ->orderBy('votes', 'desc')
  ->get();

  $member16_result_abstain=DB::table('election_votes')
  ->leftjoin('member','election_votes.member_no','member.member_no')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->leftjoin('election_candidates','election_votes.vote_casted','election_candidates.id')
  ->where('election_votes.election_id',$request->election_id)
  ->where('election_votes.abstain',1)
  ->where('member_detail.salary_grade','16-ABOVE')
  ->count();


  
  if(count($result16)<>0)
  {
    if(count($result16)>1)
    {
      if($result16[0]->votes==$result16[1]->votes)
      {
        $result16='TIE';
      }
      else
      {
        $candidate16=$result16[0];
      }

    }
    else
    {
      $candidate16=$result16[0];
    }

    if($candidate16)
    {
      if($member16_result_abstain>$candidate16->votes)
      {
        $result16='ABSTAIN';
      }
      else
      {
        $result16=$candidate16->cand_name;
      }
    }
  }
  else
  {
   if($member16_result_abstain<>0)
   {
    $result16='ABSTAIN';
  }
  else
  {
    $result16='NO VOTES';
  }
}



DB::table('election')
->where('id', $request->election_id)
->update(
  ['status' => 'CLOSED', 'sg1_15' =>$result15, 'sg16_ABOVE' => $result16, 'time_closed'=>date('Y-m-d H:i:s')]);


return 1;
}

public function admin_election($id)
{
  $elect=DB::table('election')
  ->select('*','election.id as id')
  ->leftjoin('cluster','election.cluster_id','cluster.id')
  ->where('election.id',$id)
  ->first();

//all members
  $members15=DB::table('member')
  ->select('member.id', (DB::raw('CONCAT(member.member_no, " - ", users.last_name, ", ", users.first_name) AS full_name')))
  ->leftjoin('users','member.user_id','users.id')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->leftjoin('campus','member.campus_id','campus.id')
  ->where('campus.cluster_id',$elect->cluster_id)
  ->where('member_detail.salary_grade','1-15')
  ->count();

  $members16=DB::table('member')
  ->select('member.id', (DB::raw('CONCAT(member.member_no, " - ", users.last_name, ", ", users.first_name) AS full_name')))
  ->leftjoin('users','member.user_id','users.id')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->leftjoin('campus','member.campus_id','campus.id')
  ->where('campus.cluster_id',$elect->cluster_id)
  ->where('member_detail.salary_grade','16-ABOVE')
  ->count();

  $allmembers=$members15+$members16; 

  $member_per_campus15=DB::table('campus')
  ->select('campus.name',DB::raw('count(member.id) as count'))
  ->leftjoin('member','campus.id','member.campus_id')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->where('campus.cluster_id',$elect->cluster_id)
  ->where('member_detail.salary_grade','1-15')
  ->groupBy('campus.id')
  ->get();

  $member_per_campus16=DB::table('campus')
  ->select('campus.name',DB::raw('count(member.id) as count'))
  ->leftjoin('member','campus.id','member.campus_id')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->where('campus.cluster_id',$elect->cluster_id)
  ->where('member_detail.salary_grade','16-ABOVE')
  ->groupBy('campus.id')
  ->get();


  $member_per_campus=DB::table('campus')
  ->select('campus.name',DB::raw('count(member.id) as count'))
  ->leftjoin('member','campus.id','member.campus_id')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->where('campus.cluster_id',$elect->cluster_id)
  ->groupBy('campus.id')
  ->get();

  //voted
  
  $member15_voted=DB::table('campus')
  ->select('campus.name',DB::raw('count(member.id) as count'))
  ->leftjoin('member','campus.id','member.campus_id')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->rightjoin('election_votes','election_votes.member_no','=','member.member_no')
  ->where('campus.cluster_id',$elect->cluster_id)
  ->where('election_votes.election_id',$elect->id)
  ->where('member_detail.salary_grade','1-15')
  ->groupBy('campus.id')
  ->get();

  $member15_result_abstain=DB::table('election_votes')
  ->leftjoin('member','election_votes.member_no','member.member_no')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->leftjoin('election_candidates','election_votes.vote_casted','election_candidates.id')
  ->where('election_votes.election_id',$elect->id)
  ->where('election_votes.abstain',1)
  ->where('member_detail.salary_grade','1-15')
  ->count();


  $member15_result=DB::table('election_votes')
  ->select((DB::raw('CONCAT(cand_user.first_name, " ", cand_user.last_name) AS cand_name')),DB::raw('count(election_candidates.id) as votes'))
  ->leftjoin('member','election_votes.member_no','member.member_no')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->leftjoin('election_candidates','election_votes.vote_casted','election_candidates.id')
  ->leftjoin('member as cand','election_candidates.member_id','cand.id')
  ->leftjoin('users as cand_user','cand.user_id','cand_user.id')
  ->where('election_votes.election_id',$elect->id)
  ->where('member_detail.salary_grade','1-15')
  ->groupBy('election_candidates.id')
  ->get();

  $member16_result_abstain=DB::table('election_votes')
  ->leftjoin('member','election_votes.member_no','member.member_no')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->leftjoin('election_candidates','election_votes.vote_casted','election_candidates.id')
  ->where('election_votes.election_id',$elect->id)
  ->where('election_votes.abstain',1)
  ->where('member_detail.salary_grade','16-ABOVE')
  ->count();


  $member16_result=DB::table('election_votes')
  ->select((DB::raw('CONCAT(cand_user.first_name, " ", cand_user.last_name) AS cand_name')),DB::raw('count(election_candidates.id) as votes'))
  ->leftjoin('member','election_votes.member_no','member.member_no')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->leftjoin('election_candidates','election_votes.vote_casted','election_candidates.id')
  ->leftjoin('member as cand','election_candidates.member_id','cand.id')
  ->leftjoin('users as cand_user','cand.user_id','cand_user.id')
  ->where('election_votes.election_id',$elect->id)
  ->where('member_detail.salary_grade','16-ABOVE')
  ->groupBy('election_candidates.id')
  ->get();


  

  $member16_voted=DB::table('campus')
  ->select('campus.name',DB::raw('count(member.id) as count'))
  ->leftjoin('member','campus.id','member.campus_id')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->rightjoin('election_votes','election_votes.member_no','=','member.member_no')
  ->where('campus.cluster_id',$elect->cluster_id)
  ->where('election_votes.election_id',$elect->id)
  ->where('member_detail.salary_grade','16-ABOVE')
  ->groupBy('campus.id')
  ->get();

  $member_per_campus_voted=DB::table('campus')
  ->select('campus.name',DB::raw('count(member.id) as count'))
  ->leftjoin('member','campus.id','member.campus_id')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->rightjoin('election_votes','election_votes.member_no','=','member.member_no')
  ->where('campus.cluster_id',$elect->cluster_id)
  ->where('election_votes.election_id',$elect->id)
  ->groupBy('campus.id')
  ->get();

  $members15_voted=DB::table('member')
  ->select('member.id', (DB::raw('CONCAT(member.member_no, " - ", users.last_name, ", ", users.first_name) AS full_name')))
  ->leftjoin('users','member.user_id','users.id')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->leftjoin('campus','member.campus_id','campus.id')
  ->rightjoin('election_votes','election_votes.member_no','=','member.member_no')
  ->where('campus.cluster_id',$elect->cluster_id)
  ->where('election_votes.election_id',$elect->id)
  ->where('member_detail.salary_grade','1-15')
  ->count();

  $members16_voted=DB::table('member')
  ->select('member.id', (DB::raw('CONCAT(member.member_no, " - ", users.last_name, ", ", users.first_name) AS full_name')))
  ->leftjoin('users','member.user_id','users.id')
  ->leftjoin('member_detail','member.member_no','member_detail.member_no')
  ->leftjoin('campus','member.campus_id','campus.id')
  ->rightjoin('election_votes','election_votes.member_no','=','member.member_no')
  ->where('campus.cluster_id',$elect->cluster_id)
  ->where('election_votes.election_id',$elect->id)
  ->where('member_detail.salary_grade','16-ABOVE')
  ->count();

  $allmembers_voted=$members15_voted+$members16_voted; 

   //not voted





  return view('admin.election.election', array('members15'=>$members15,'members16'=>$members16,'member_per_campus'=>$member_per_campus,'member_per_campus15'=>$member_per_campus15,'member_per_campus16'=>$member_per_campus16 ,'member_per_campus_voted'=>$member_per_campus_voted,'member15_voted'=>$member15_voted,'member16_voted'=>$member16_voted, 'allmembers'=>$allmembers, 'allmembers_voted'=>$allmembers_voted, 'member15_result'=>$member15_result,'member15_result_abstain'=>$member15_result_abstain, 'member16_result'=>$member16_result,'member16_result_abstain'=>$member16_result_abstain,'elect'=>$elect));
}

public function savevote(Request $request)
{



  if($request->vote<>'ABSTAIN')
  {
    DB::table('election_votes')->insert(
      ['member_no' => getUserdetails()->member_no, 'election_id'=>$request->election_id, 'salary_grade'=>$request->sg, 'vote_casted' => $request->vote]
    );

    $voted= DB::table('election_candidates')
    ->select('*','campus.name as campus_name','election_candidates.id as candidate_id')
    ->leftjoin('member','election_candidates.member_id','member.id')
    ->leftjoin('users','member.user_id','users.id')
    ->leftjoin('campus','member.campus_id','campus.id')
    ->where('election_candidates.id',$request->vote)
    ->first();


    $casted=$voted->first_name.' '.$voted->last_name;

  }
  else
  {
    DB::table('election_votes')->insert(
      ['member_no' => getUserdetails()->member_no, 'election_id'=>$request->election_id, 'salary_grade'=>$request->sg, 'abstain' => 1]
    );

    $casted='ABSTAIN';
  }




  echo json_encode($casted);

}

public function abstainvote(Request $request)
{

 if($request->sg<=15)
 {
  DB::table('electionsg1-15')->insert(
   ['member_no' => getUserdetails()->member_no, 'vote_casted' => $request->vote]
 );
}
else
{
  DB::table('electionsg16-above')->insert(
   ['member_no' => getUserdetails()->member_no, 'vote_casted' => $request->vote]
 );
}

return 1;

}

public function is_valid(Request $request)
{

 $valid=1;

 $active_election=DB::table('election')
 ->where('cluster_id',$request->cluster)
 ->where(function($query) {
        $query->where('status', 'CREATED')
            ->orWhere('status', 'OPEN');
    })
 ->count();

if($active_election>0)
{
$valid=2;
}

return $valid;

}

}
