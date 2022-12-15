<?php

namespace App\Http\Controllers;

use App\Member;
use App\User;
use App\ContributionTransaction;
use App\LoanTransaction;
use Auth;
use DB;
use PDF;
use Hash;
use Illuminate\Http\Request;

class MemberController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

      $member= User::where('users.id',Auth::user()->id)
      ->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name')
      ->leftjoin('member','users.id','=','member.user_id')
      ->leftjoin('campus','member.campus_id','=','campus.id')
      ->first();


      $recentcontributions=ContributionTransaction::select('*')   
      ->leftjoin('contribution','contribution_transaction.contribution_id', 'contribution.id')
      ->leftjoin('contribution_account','contribution_transaction.account_id', 'contribution_account.id')
      ->where('contribution.member_id','=',$member->member_id)
      ->Where('contribution_transaction.amount','<>',0.00)
      ->orderBy('contribution.date','desc')
      ->orderBy('contribution.reference_no','desc')
      ->limit(3)
      ->get();

      $contributions=array();

      $membercontribution=ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution','contribution_transaction.contribution_id','contribution.id')
      ->where('contribution_transaction.account_id','=',2)
      ->where('contribution.member_id','=',$member->member_id)
      ->first();
      $contributions['membercontribution']=$membercontribution->total;


      $upcontribution=ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution','contribution_transaction.contribution_id','contribution.id')
      ->where('contribution_transaction.account_id','=',1)
      ->where('contribution.member_id','=',$member->member_id)
      ->first();
      $contributions['upcontribution']=$upcontribution->total;


      $eupcontribution=ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution','contribution_transaction.contribution_id','contribution.id')
      ->where('contribution_transaction.account_id','=',3)
      ->where('contribution.member_id','=',$member->member_id)
      ->first();
      $contributions['eupcontribution']=$eupcontribution->total;


      $emcontribution=ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution','contribution_transaction.contribution_id','contribution.id')
      ->where('contribution_transaction.account_id','=',4)
      ->where('contribution.member_id','=',$member->member_id)
      ->first();
      $contributions['emcontribution']=$emcontribution->total;


      $totalcontributions = array_sum($contributions);



      $recentloans=LoanTransaction::select('loan_transaction.id as id', 'reference_no', 'date', 'loan_id', 'amortization', 'interest', 'amount','loan_type.name', DB::raw('(select SUM(amount) from loan_transaction as lt where lt.loan_id = loan.id and lt.date<=loan_transaction.date order by date desc) as balance'))
      ->leftjoin('loan','loan_transaction.loan_id','loan.id')
      ->leftjoin('member','loan.member_id','member.id')
      ->leftjoin('loan_type','loan.type_id','loan_type.id')
      ->where('loan.member_id','=',$member->member_id)
      ->Where('loan_transaction.amount','<>',0.00)
      ->orderBy('date','desc')
      ->limit(3)
      ->get();


      $outstandingloans=LoanTransaction::select('loan_type.name as type',DB::raw('SUM(amount) as balance'))
      ->leftjoin('loan','loan_transaction.loan_id','loan.id')
      ->leftjoin('loan_type','loan.type_id','loan_type.id')
      ->where('loan.member_id','=',$member->member_id)
      ->groupBy('loan_type.name')
      ->get();

      $totalloanbalance =0 ;
      foreach ($outstandingloans as $loan) {
        $totalloanbalance += $loan->balance;
      }




      // dd($recentloans);
      return view('member.dashboard',array('member'=>$member, 'recentcontributions'=>$recentcontributions,'recentloans'=>$recentloans,'contributions'=>$contributions,'totalcontributions'=> $totalcontributions, 'outstandingloans'=>$outstandingloans,'totalloanbalance'=>$totalloanbalance));
    }


    public function equity()
    {

      
      $member= User::where('users.id',Auth::user()->id)
      ->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name')
      ->leftjoin('member','users.id','=','member.user_id')
      ->leftjoin('campus','member.campus_id','=','campus.id')
      ->first();

      $equity=ContributionTransaction::select('contribution_transaction.id as id', DB::raw('ABS(amount) as abs'), 'date', 'account_id', 'contribution_id', 'reference_no', 'amount','contribution_account.name', DB::raw('(select SUM(amount) from contribution_transaction as ct left join contribution as c on ct.contribution_id = c.id where c.member_id=contribution.member_id and c.date<=contribution.date order by date desc, contribution_transaction.id desc) as balance'))
      ->leftjoin('contribution','contribution_transaction.contribution_id','contribution.id')
      ->leftjoin('member','contribution.member_id','member.id')
      ->leftjoin('contribution_account','contribution_transaction.account_id','contribution_account.id')
      ->where('contribution.member_id','=',$member->member_id)
      ->where('contribution_transaction.amount','<>',0.00)
      ->orderBy('date','desc')
      ->orderBy('contribution.reference_no','desc')
      ->orderBy('abs','desc')
      ->orderBy('contribution_transaction.id','desc')
      
      ->paginate(10); 



        // dd($equity);
      return view('member.equity',array('equity'=>$equity));

    }


    public function loans()
    {
      $member= User::where('users.id',Auth::user()->id)
      ->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name')
      ->leftjoin('member','users.id','=','member.user_id')
      ->leftjoin('campus','member.campus_id','=','campus.id')
      ->first();

      $loans=LoanTransaction::select('loan_transaction.id as id', 'reference_no', 'date', 'loan_id', 'amortization', 'interest', 'amount','loan_type.name', DB::raw('(select SUM(amount) from loan_transaction as lt where lt.loan_id = loan.id and lt.date<=loan_transaction.date  order by date desc) as balance'))
      ->leftjoin('loan','loan_transaction.loan_id','loan.id')
      ->leftjoin('member','loan.member_id','member.id')
      ->leftjoin('loan_type','loan.type_id','loan_type.id')
      ->where('loan.member_id','=',$member->member_id)
      ->Where('loan_transaction.amount','<>',0.00)
      ->orderBy('loan.type_id', 'ASC')
      ->orderBy('date','desc')
      ->paginate(10); 

        // dd($loans);
      return view('member.loans',array('loans'=>$loans));

    }


    public function generatesoa()
    {
      $member= User::where('users.id',Auth::user()->id)
      ->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name')
      ->leftjoin('member','users.id','=','member.user_id')
      ->leftjoin('campus','member.campus_id','=','campus.id')
      ->first();


      $contributions=array();

      $membercontribution=ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution','contribution_transaction.contribution_id','contribution.id')
      ->where('contribution_transaction.account_id','=',2)
      ->where('contribution.member_id','=',$member->member_id)
      ->first();
      $contributions['membercontribution']=$membercontribution->total;


      $upcontribution=ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution','contribution_transaction.contribution_id','contribution.id')
      ->where('contribution_transaction.account_id','=',1)
      ->where('contribution.member_id','=',$member->member_id)
      ->first();
      $contributions['upcontribution']=$upcontribution->total;


      $eupcontribution=ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution','contribution_transaction.contribution_id','contribution.id')
      ->where('contribution_transaction.account_id','=',3)
      ->where('contribution.member_id','=',$member->member_id)
      ->first();
      $contributions['eupcontribution']=$eupcontribution->total;


      $emcontribution=ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution','contribution_transaction.contribution_id','contribution.id')
      ->where('contribution_transaction.account_id','=',4)
      ->where('contribution.member_id','=',$member->member_id)
      ->first();
      $contributions['emcontribution']=$emcontribution->total;


      $totalcontributions = array_sum($contributions);



      $outstandingloans=LoanTransaction::select('loan_type.name as type',DB::raw('SUM(amount) as balance'))
      ->leftjoin('loan','loan_transaction.loan_id','loan.id')
      ->leftjoin('loan_type','loan.type_id','loan_type.id')
      ->where('loan.member_id','=',$member->member_id)
      ->groupBy('loan_type.name')
      ->get();

      $totalloanbalance =0 ;
      foreach ($outstandingloans as $loan) {
        $totalloanbalance += $loan->balance;
      }

      $data['totalloanbalance']=$totalloanbalance;
      $data['outstandingloans']=$outstandingloans;
      $data['totalcontributions']=$totalcontributions;
      $data['emcontribution']=$emcontribution->total;
      $data['eupcontribution']=$eupcontribution->total;
      $data['upcontribution']=$upcontribution->total;
      $data['membercontribution']=$membercontribution->total;
      $data['member']=$member;



      $pdf = PDF::loadView('pdf.soa', $data);
      return $pdf->stream('soa.pdf');

    }


    public function generateequity()
    {
      $member= User::where('users.id',Auth::user()->id)
      ->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name')
      ->leftjoin('member','users.id','=','member.user_id')
      ->leftjoin('campus','member.campus_id','=','campus.id')
      ->first();

      // $equity=ContributionTransaction::select('contribution_transaction.id as id', 'date', 'account_id', 'contribution_id', 'reference_no', 'amount','contribution_account.name', DB::raw('(select SUM(amount) from contribution_transaction as ct left join contribution as c on ct.contribution_id = c.id where c.member_id=contribution.member_id and c.date<=contribution.date  order by date desc, contribution_transaction.id desc) as balance'))
      // ->leftjoin('contribution','contribution_transaction.contribution_id','contribution.id')
      // ->leftjoin('member','contribution.member_id','member.id')
      // ->leftjoin('contribution_account','contribution_transaction.account_id','contribution_account.id')
      // ->where('contribution.member_id','=',$member->member_id)
      // ->Where('contribution_transaction.amount','<>',0.00)
      // ->orderBy('date','desc')
      // ->orderBy('contribution_transaction.id','desc')
      // ->get();

        $equity=ContributionTransaction::select('contribution_transaction.id as id', DB::raw('ABS(amount) as abs'), 'date', 'account_id', 'contribution_id', 'reference_no', 'amount','contribution_account.name', DB::raw('(select SUM(amount) from contribution_transaction as ct left join contribution as c on ct.contribution_id = c.id where c.member_id=contribution.member_id and c.date<=contribution.date order by date desc, contribution_transaction.id desc) as balance'))
    ->leftjoin('contribution','contribution_transaction.contribution_id','contribution.id')
    ->leftjoin('member','contribution.member_id','member.id')
    ->leftjoin('contribution_account','contribution_transaction.account_id','contribution_account.id')
    ->where('contribution.member_id','=',$member->member_id)
    ->where('contribution_transaction.amount','<>',0.00)
    ->orderBy('date','desc')
    ->orderBy('contribution.reference_no','desc')
    ->orderBy('abs','desc')
    ->orderBy('contribution_transaction.id','desc')
    ->get();



      $data['equity']=$equity;
      $data['member']=$member;



      $pdf = PDF::loadView('pdf.equity', $data);
      return $pdf->setPaper('a4', 'landscape')->stream('eqity.pdf');

    }


    public function generateloans()
    {
     $member= User::where('users.id',Auth::user()->id)
     ->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name')
     ->leftjoin('member','users.id','=','member.user_id')
     ->leftjoin('campus','member.campus_id','=','campus.id')
     ->first();

     $loans=LoanTransaction::select('loan_transaction.id as id', 'reference_no', 'date', 'loan_id', 'amortization', 'interest', 'amount','loan_type.name', DB::raw('(select SUM(amount) from loan_transaction as lt where lt.loan_id = loan.id and lt.date<=loan_transaction.date order by date desc) as balance'))
     ->leftjoin('loan','loan_transaction.loan_id','loan.id')
     ->leftjoin('member','loan.member_id','member.id')
     ->leftjoin('loan_type','loan.type_id','loan_type.id')
     ->where('loan.member_id','=',$member->member_id)
     ->Where('loan_transaction.amount','<>',0.00)
     ->orderBy('loan.type_id', 'ASC')
     ->orderBy('date','desc')
     ->get(); 


     $data['loans']=$loans;
     $data['member']=$member;



     $pdf = PDF::loadView('pdf.loans', $data);
     return $pdf->setPaper('a4', 'landscape')->stream('loan.pdf');

   }


   public function updatepw()
   {
     $member= User::where('users.id',Auth::user()->id)
     ->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name')
     ->leftjoin('member','users.id','=','member.user_id')
     ->leftjoin('campus','member.campus_id','=','campus.id')
     ->first();




     return view('member.updatepassword');
   }


   public function savepw(Request $request)
   {
     $member= User::where('users.id',Auth::user()->id)
     ->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name')
     ->leftjoin('member','users.id','=','member.user_id')
     ->leftjoin('campus','member.campus_id','=','campus.id')
     ->first();



     $newpass=$request->password;
     $confirm=Hash::check($request->currentPassword, $member->password);
     if($confirm)
     {
      $user = User::find(Auth::user()->id);
      $user->update([
        'password' => Hash::make($newpass)
      ]);
      return redirect('/member/update-password')
      ->with('success', 'Password successfully updated.');
    }
    else
    {
      return redirect('/member/update-password')
      ->with('error', 'The current password you entered is incorrect.');
    }

  }

  public function onboarding()
  {
   $member= User::where('users.id',Auth::user()->id)
   ->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name')
   ->leftjoin('member','users.id','=','member.user_id')
   ->leftjoin('campus','member.campus_id','=','campus.id')
   ->first();

   

   return view('member.onboarding');
 }

 public function saveonboarding(Request $request)
 {

   if($request->password==$request->confirmPassword)
   {
    $user = User::find(Auth::user()->id);
    $user->update([
      'password' => Hash::make($request->password),
      'password_set' => 1
    ]);
    return redirect('/member/dashboard');
    
  }
  else
  {
   return redirect('/member/onboarding')
   ->with('error', 'Passwords do not match!');
 }


}

public function profile()
{
  // dd(getUserdetails());
  $member= User::where('users.id',Auth::user()->id)
  ->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name','position.name as position_name')
  ->leftjoin('member','users.id','=','member.user_id')
  ->leftjoin('campus','member.campus_id','=','campus.id')
  ->leftjoin('position','member.position_id','=','position.id')
  ->first();
  $details = DB::table('member_detail')->where('member_no','=',$member->member_no)->first();
  $beneficiaries = DB::table('beneficiaries')->where('member_no','=',$member->member_no)->get();
// dd($member);
  return view('member.profile.profile', array('member'=>$member, 'details'=>$details,'beneficiaries'=>$beneficiaries));
}




    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit_details()
    {
      $member= User::where('users.id',Auth::user()->id)
      ->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name','position.name as position_name')
      ->leftjoin('member','users.id','=','member.user_id')
      ->leftjoin('campus','member.campus_id','=','campus.id')
      ->leftjoin('position','member.position_id','=','position.id')
      ->first();
      $details = DB::table('member_detail')->where('member_no','=',$member->member_no)->first();

      return view('member.profile.details_form', array('member'=>$member, 'details'=>$details));
    }

    public function save_details(Request $request)
    {

      // dd($request->all());
      $member= User::where('users.id',Auth::user()->id)
      ->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name','position.name as position_name')
      ->leftjoin('member','users.id','=','member.user_id')
      ->leftjoin('campus','member.campus_id','=','campus.id')
      ->leftjoin('position','member.position_id','=','position.id')
      ->first();
      $result = DB::table('member_detail')
      ->select('member_detail.member_no', 'users.contact_no','member_detail.landline', 'member_detail.gender','member_detail.employee_no', 'member_detail.appointment_status', 'member_detail.tin', 'member_detail.permanent_address','member_detail.current_address', 'member_detail.birth_date',DB::raw('CAST(member_detail.monthly_salary AS DECIMAL(18,2)) as monthly_salary'))
      ->leftjoin('member','member_detail.member_no','=','member.member_no')
      ->leftjoin('users','member.user_id','=','users.id')
      ->where('member_detail.member_no','=',$member->member_no)->first();
      $details = (array) $result;

      unset($request['_token']);
      // unset($request['contact_no']);
      $request['member_no']=$member->member_no;
      $birth_date = date("Y-m-d", strtotime($request->birth_date));
      $request['birth_date']=$birth_date;
      $mem_details=$request->all();

      $to_save=array_diff_assoc($mem_details, $details);

   

      if($details)
      {
        if($to_save)
        {
          $log=array();
          foreach($to_save as $key=> $value)
          {
           array_push($log, $key);
         }
         if(array_key_exists('contact_no', $to_save))
         {
          $contact_no=$to_save['contact_no'];
          unset($to_save['contact_no']);

          DB::table('users')
          ->where('id','=',$member->user_id)
          ->update(['contact_no'=> $contact_no]);
        }
        if(array_key_exists('birth_date', $to_save))
        {
          $birth_date = date("Y-m-d", strtotime($to_save['birth_date']));
          $to_save['birth_date']=$birth_date;
        }
        $to_save['updated_by']=$member->user_id;
        $to_save['date_updated']=date("Y-m-d");

        DB::table('member_detail')
        ->where('member_no','=',$member->member_no)
        ->update($to_save);

        $tolog=array();
        $tolog['member_no']=$member->member_no;
        $tolog['update_log']=json_encode($log);
        $tolog['created_by']=$member->user_id;

        DB::table('log_member_detail')
        ->insert([$tolog]);
        return redirect('/member/edit_details')
        ->with('success', 'Details successfully updated.');

      }
      else
      {
        return redirect('/member/edit_details')
        ->with('error', 'No Changes Made');
      }

    }
    else
    {
      $log=array();

      if(array_key_exists('contact_no', $to_save))
      {
        $contact_no=$to_save['contact_no'];
        if($member->contact_no != $contact_no)
        {
         DB::table('users')
         ->where('id','=',$member->user_id)
         ->update(['contact_no'=> $contact_no]);
         array_push($log, 'contact_no');
         unset($to_save['contact_no']);
       }
       else
       {
         unset($to_save['contact_no']);
       }

       foreach($to_save as $key=> $value)
       {
         array_push($log, $key);
       }



     }   

     DB::table('member_detail')->insert(
      [$to_save]
    );

     $tolog=array();
     $tolog['member_no']=$member->member_no;
     $tolog['update_log']=json_encode($log);
     $tolog['created_by']=$member->user_id;

     DB::table('log_member_detail')
     ->insert([$tolog]);

     return redirect('/member/edit_details')
     ->with('success', 'Details successfully updated.');
   }


 }

 public function edit_beneficiaries()
 {
  $member= User::where('users.id',Auth::user()->id)
  ->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name','position.name as position_name')
  ->leftjoin('member','users.id','=','member.user_id')
  ->leftjoin('campus','member.campus_id','=','campus.id')
  ->leftjoin('position','member.position_id','=','position.id')
  ->first();

  $beneficiaries= DB::table('beneficiaries')->where('member_no','=',$member->member_no)->get();
  return view('member.profile.beneficiaries_form' , array('member'=>$member, 'beneficiaries'=> $beneficiaries));
}

public function edit_details_approval()
{
 return view('member.profile.details_form_approval');
}

public function savebeneficiary(Request $request)
{

 $member= User::where('users.id',Auth::user()->id)
 ->select('*','member.id as member_id','users.id as user_id','campus.name as campus_name','position.name as position_name')
 ->leftjoin('member','users.id','=','member.user_id')
 ->leftjoin('campus','member.campus_id','=','campus.id')
 ->leftjoin('position','member.position_id','=','position.id')
 ->first();
 
 $birth_date = date("Y-m-d", strtotime($request->birth_date));
 unset($request['_token']);
 unset($request['birth_date']);
      // unset($request['contact_no']);
 $request['member_no']=$member->member_no;
 $request['added_by']=Auth::user()->id;
 $request['birth_date']=$birth_date;
 
 DB::table('beneficiaries')
 ->insert([$request->all()]);
 
 return redirect('/member/edit_beneficiaries')
 ->with('success', 'Beneficiary Added');
 

}




    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function removebeneficiary(Request $request)
    {
      DB::table('beneficiaries')->where('id', '=', $request->bene_id)->delete();
      //    return redirect('/member/edit_beneficiaries')
      // ->with('success', 'Beneficiary Removed');

      return 1;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function show(Member $member)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function edit(Member $member)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Member $member)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function destroy(Member $member)
    {
        //
    }
  }
