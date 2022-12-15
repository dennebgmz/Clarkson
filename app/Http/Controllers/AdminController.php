<?php

namespace App\Http\Controllers;

use App\Admin;
use App\User;
use App\Campus;
use App\Member;
use App\Tempass;
use App\LoanTransaction;
use App\ContributionTransaction;
use Auth;
use Hash;
use DB;
use PDF;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;


class AdminController extends Controller
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
    $campusmembers = array();
    $campusmembers = DB::table('member')
      ->select('campus.name', DB::raw('COUNT(*) as count'))
      ->join('campus', 'member.campus_id', 'campus.id')
      ->groupBy('campus_id')
      ->orderBy(\DB::raw('count(campus.id)'), 'DESC')
      ->get();
    $campuses = Campus::all();
    $cluster = DB::table('cluster')->get();

    $data = array(
      'campusmembers' => $campusmembers,
      'campuses' => $campuses,
      'cluster' => $cluster
    );
    return view('admin.dashboard')->with($data);
  }

  //===============================================Eto na yung bago Code=====================================================================//
  public function getTotal()
  {
    //UP Contributions
    $upcontri = DB::table('contribution_transaction')->select('amount')
      ->where('account_id', '1')
      ->sum('amount');

    //Member Contributions
    $membercontri = DB::table('contribution_transaction')->select('amount')
      ->where('account_id', '2')
      ->sum('amount');

    //Earnings UP
    $earningsUP = DB::table('contribution_transaction')->select('amount')
      ->where('account_id', '3')
      ->sum('amount');

    //Earnings Member
    $earningsMember = DB::table('contribution_transaction')->select('amount')
      ->where('account_id', '4')
      ->sum('amount');

    //Member Count
    $memberscount = count(Member::all());

    //Loan granted
    $totalloansgranted = LoanTransaction::sum('amount');

    //Equity
    $totalequity = 0;
    $totalequity = $upcontri + $membercontri + $earningsUP + $earningsMember;

    $data = array(
      'total' => number_format($upcontri, 2),
      'membercontri' => number_format($membercontri, 2),
      'earningsUP' => number_format($earningsUP, 2),
      'earningsMember' => number_format($earningsMember, 2),
      'totalMember' => number_format($memberscount),
      'totalloansgranted' => $totalloansgranted >= 1000000 ? number_format(($totalloansgranted / 1000000), 2) : number_format($totalloansgranted, 2),
      'outstandingLoans' => number_format($totalloansgranted, 2),
      'label' => $totalloansgranted >= 1000000 ? '(in million Pesos)' : '',
      'totalequity' => number_format($totalequity),
    );

    echo json_encode($data);
  }

  public function getTotal_campuses()
  {
    DB::enableQueryLog();

    if (isset($_GET['campuses_id']) && $_GET['campuses_id'] != "") {
      $upcontri = DB::table('contribution_transaction')
        ->join('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
        ->join('member', 'contribution.member_id', 'member.id')
        ->where('member.campus_id', $_GET['campuses_id'])
        ->where('contribution_transaction.account_id', '1')
        ->groupBy('member.campus_id')
        ->groupBy('contribution_transaction.account_id')
        ->take(1)
        ->sum('contribution_transaction.amount');
    } else {
      $upcontri = DB::table('contribution_transaction')
        ->where('account_id', '1')
        ->sum('amount');
    }

    if (isset($_GET['campuses_id']) && $_GET['campuses_id'] != "") {
      $membercontri = DB::table('contribution_transaction')
        ->join('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
        ->join('member', 'contribution.member_id', 'member.id')
        ->where('member.campus_id', $_GET['campuses_id'])
        ->where('contribution_transaction.account_id', '2')
        ->groupBy('member.campus_id')
        ->groupBy('contribution_transaction.account_id')
        ->take(1)
        ->sum('contribution_transaction.amount');
    } else {
      $membercontri = DB::table('contribution_transaction')
        ->where('account_id', '2')
        ->sum('amount');
    }

    if (isset($_GET['campuses_id']) && $_GET['campuses_id'] != "") {
      $earningsUP = DB::table('contribution_transaction')
        ->join('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
        ->join('member', 'contribution.member_id', 'member.id')
        ->where('member.campus_id', $_GET['campuses_id'])
        ->where('contribution_transaction.account_id', '3')
        ->groupBy('member.campus_id')
        ->groupBy('contribution_transaction.account_id')
        ->take(1)
        ->sum('contribution_transaction.amount');
    } else {
      $earningsUP = DB::table('contribution_transaction')
        ->where('account_id', '3')
        ->sum('amount');
    }

    if (isset($_GET['campuses_id']) && $_GET['campuses_id'] != "") {
      $earningsMember = DB::table('contribution_transaction')
        ->join('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
        ->join('member', 'contribution.member_id', 'member.id')
        ->where('member.campus_id', $_GET['campuses_id'])
        ->where('contribution_transaction.account_id', '4')
        ->groupBy('member.campus_id')
        ->groupBy('contribution_transaction.account_id')
        ->take(1)
        ->sum('contribution_transaction.amount');
    } else {
      $earningsMember = DB::table('contribution_transaction')
        ->where('account_id', '4')
        ->sum('amount');
    }

    //Member Count
    if (isset($_GET['campuses_id']) && $_GET['campuses_id'] != "") {
      $memberscount = DB::table('member')
        ->where('member.campus_id', $_GET['campuses_id'])
        ->count();
    } else {
      $memberscount = DB::table('member')
        ->count();
    }

    if (isset($_GET['campuses_id']) && $_GET['campuses_id'] != "") {
      $totalloansgranted = LoanTransaction::leftjoin('loan', 'loan_transaction.loan_id', 'loan.id')
        ->leftjoin('member', 'loan.member_id', 'member.id')
        ->where('member.campus_id', $_GET['campuses_id'])
        ->groupBy('member.campus_id')
        ->take(1)
        ->sum('amount');
    } else {
      $totalloansgranted = LoanTransaction::leftjoin('loan', 'loan_transaction.loan_id', 'loan.id')
        ->leftjoin('member', 'loan.member_id', 'member.id')
        ->sum('amount');
    }

    $totalequity = 0;
    $totalequity = $upcontri + $membercontri + $earningsUP + $earningsMember;

    $query = DB::getQueryLog();
    $data = array(
      'total' => number_format($upcontri, 2),
      'membercontri' => number_format($membercontri, 2),
      'earningsUP' => number_format($earningsUP, 2),
      'earningsMember' => number_format($earningsMember, 2),
      'totalMember' => number_format($memberscount),
      'dd' => $query,
      'totalloansgranted' => $totalloansgranted >= 1000000 ? number_format(($totalloansgranted / 1000000), 2) : number_format($totalloansgranted, 2),
      'outstandingLoans' => number_format($totalloansgranted, 2),
      'label' => $totalloansgranted >= 1000000 ? '(in million Pesos)' : '',
      'totalequity' => number_format($totalequity),
    );

    echo json_encode($data);
  }

  public function generatesummary($id)
  {

    if (isset($id) && $id != "" && $id != 0) {
      $contributions = array();
      $membercontri = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
        ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
        ->join('member', 'contribution.member_id', 'member.id')
        ->where('member.campus_id', $id)
        ->where('contribution_transaction.account_id', '=', 2)
        ->first();
      $contributions['membercontri'] = $membercontri->total;


      $upcontri = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
        ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
        ->join('member', 'contribution.member_id', 'member.id')
        ->where('member.campus_id', $id)
        ->where('contribution_transaction.account_id', '=', 1)
        ->first();
      $contributions['upcontri'] = $upcontri->total;


      $earningsUP = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
        ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
        ->join('member', 'contribution.member_id', 'member.id')
        ->where('member.campus_id', $id)
        ->where('contribution_transaction.account_id', '=', 3)
        ->first();
      $contributions['earningsUP'] = $earningsUP->total;


      $earningsMember = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
        ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
        ->join('member', 'contribution.member_id', 'member.id')
        ->where('member.campus_id', $id)
        ->where('contribution_transaction.account_id', '=', 4)
        ->first();
      $contributions['earningsMember'] = $earningsMember->total;

      $memberscount = DB::table('member')
        ->where('member.campus_id', $id)
        ->count();

      $campusname = Campus::select('name')
        ->where('id', $id)
        ->first();
      $contributions['campusname'] = $campusname->name;
      // print_r($campusname );
      $totalloansgranted = LoanTransaction::select(DB::raw('SUM(amount) as total'))
        ->leftjoin('loan', 'loan_transaction.loan_id', 'loan.id')
        ->leftjoin('member', 'loan.member_id', 'member.id')
        ->where('member.campus_id', $id)
        ->groupBy('member.campus_id')
        ->first();
      $contributions['totalloansgranted'] = $totalloansgranted->total;

      $totalequity = 0;
      $totalequity = $upcontri->total + $membercontri->total + $earningsUP->total + $earningsMember->total;

      $data['campusname'] = $campusname->name;
      $data['totalequity'] = $totalequity;
      $data['membercontri'] = $membercontri->total;
      $data['upcontri'] = $upcontri->total;
      $data['earningsUP'] = $earningsUP->total;
      $data['earningsMember'] = $earningsMember->total;
      $data['memberscount'] = $memberscount;
      $data['totalloansgranted'] = $totalloansgranted->total;
    } else {

      $upcontri = DB::table('contribution_transaction')->select('amount')
        ->where('account_id', '1')
        ->sum('amount');

      //Member Contributions
      $membercontri = DB::table('contribution_transaction')->select('amount')
        ->where('account_id', '2')
        ->sum('amount');

      //Earnings UP
      $earningsUP = DB::table('contribution_transaction')->select('amount')
        ->where('account_id', '3')
        ->sum('amount');

      //Earnings Member
      $earningsMember = DB::table('contribution_transaction')->select('amount')
        ->where('account_id', '4')
        ->sum('amount');

      //Member Count
      $memberscount = count(Member::all());

      //Loan granted
      $totalloansgranted = LoanTransaction::sum('amount');
      $campusname = 'All';
      // print_r($campusname );

      $totalequity = 0;
      $totalequity = $upcontri + $membercontri + $earningsUP + $earningsMember;

      $data['campusname'] = $campusname;
      $data['totalequity'] = $totalequity;
      $data['membercontri'] = $membercontri;
      $data['upcontri'] = $upcontri;
      $data['earningsUP'] = $earningsUP;
      $data['earningsMember'] = $earningsMember;
      $data['memberscount'] = $memberscount;
      $data['totalloansgranted'] = $totalloansgranted;
    }


    $pdf = PDF::loadView('pdf.summaryreport', $data);
    return $pdf->stream('summaryreport.pdf');
  }

  //List of Member
  public function getAllCampuses(Request $request)
  {
    ## Read value
    $draw = $request->get('draw');
    $start = $request->get("start");
    $rowperpage = $request->get("length"); // Rows display per page

    $columnIndex_arr = $request->get('order');
    $columnName_arr = $request->get('columns');
    $order_arr = $request->get('order');
    $search_arr = $request->get('search');

    $columnIndex = $columnIndex_arr[0]['column']; // Column index
    // $columnName = $columnName_arr[$columnIndex]['data']; // Column name
    $columnSortOrder = $order_arr[0]['dir']; // asc or desc
    $searchValue = $search_arr['value']; // Search value

    // Total records
    // $records = Member::select('count(*) as allcount');
    $records = DB::table('campus')
      ->where('campus_key', 'like', '%' . $searchValue . '%')
      ->orWhere('name', 'like', '%' . $searchValue . '%');
    $totalRecords = $records->count();

    // Total records with filter
    $records = DB::table('campus')
      ->where('campus_key', 'like', '%' . $searchValue . '%')
      ->orWhere('name', 'like', '%' . $searchValue . '%');
    $totalRecordswithFilter = $records->count();

    // Fetch recordsx
    $records = DB::table('campus')
      ->where('campus_key', 'like', '%' . $searchValue . '%')
      ->orWhere('name', 'like', '%' . $searchValue . '%');

    $posts = $records->skip($start)
      ->take($rowperpage)
      ->get();
    $data = array();
    if ($posts) {
      foreach ($posts as $r) {
        $start++;
        $row = array();

        $cluster = '';
        $clusterDetails = DB::table('cluster')->get();
        $cluster .= "<select class='form-control form-control-sm edit_cluster' data-id=" . $r->id . ">
                      <option value=''>Select Cluster</option>";
        foreach ($clusterDetails as $cls) {
          $cluster .= "<option value=" . $cls->id . ">" . $cls->name . "</option>";
        }
        $cluster .= "</select>";
        $row[] = '<div class="box-input" title="Click to edit">' . $r->campus_key . '</div>
                  <input type="hidden" class="edit_campusKey" data-id="' . $r->id . '" value="' . $r->campus_key . '"/>';

        $row[] = '<div class="input-name" title="Click to edit">' . $r->name . '</div>
                  <input type="hidden" class="edit_name" data-id="' . $r->id . '" value="' . $r->name . '"/>';

        $row[] = '<div class="cluster_id" title="Click to edit">' . $r->cluster_id . '</div>
                  <div class="select_cluster" style="display:none;">' . $cluster . '</div>';
        $row[] = '<button class="delete_campus" id="' . $r->id . '" title="Delete Campus">Delete</button>';
        // $row[] = $r->campus_key;
        // $row[] = $r->name;
        // $row[] = $r->cluster_id;
        // $row[] = '<button class="delete_campus" id="'.$r->id.'" title="Delete Campus">Delete</button>';

        $data[] = $row;
      }
    }
    $json_data = array(
      "draw" => intval($draw),
      "recordsTotal" => intval($totalRecords),
      "recordsFiltered" => intval($totalRecordswithFilter),
      "data" => $data
    );
    echo json_encode($json_data);
  }

  //List of Member
  public function memberData(Request $request)
  {
    ## Read value
    $draw = $request->get('draw');
    $start = $request->get("start");
    $rowperpage = $request->get("length"); // Rows display per page

    $columnIndex_arr = $request->get('order');
    $columnName_arr = $request->get('columns');
    $order_arr = $request->get('order');
    $search_arr = $request->get('search');

    $columnIndex = $columnIndex_arr[0]['column']; // Column index
    // $columnName = $columnName_arr[$columnIndex]['data']; // Column name
    $columnSortOrder = $order_arr[0]['dir']; // asc or desc
    $searchValue = $search_arr['value']; // Search value

    // Custom search filter 
    $campus  = $request->get('campus');
    $department  = $request->get('department');
    $dt_from  = $request->get('dt_from');
    $dt_to  = $request->get('dt_to');
    $search  = $request->get('searchValue');

    // Total records
    // $records = Member::select('count(*) as allcount');
    $records = Member::select('users.*', DB::raw('CONCAT(users.first_name," ",users.last_name) AS full_name'), 'member.member_no as member_no', 'member.position_id', 'campus.name as campus', 'department.name as department', 'member.membership_date as memdate')
      ->leftjoin('users', 'member.user_id', 'users.id')
      ->leftjoin('campus', 'member.campus_id', 'campus.id')
      ->leftjoin('department', 'member.department_id', 'department.id')
      ->where('member_no', 'like', '%' . $search . '%');

    ## Add custom filter conditions
    if (!empty($campus)) {
      $records->where('campus_id', $campus);
    }
    if (!empty($department)) {
      $records->where('department_id', $department);
    }
    if (!empty($search)) {
      $records->orWhere('member_no', 'like', '%' . $search . '%');
      $records->orWhere('last_name', 'like', '%' . $search . '%');
    }
    if (!empty($dt_from) && !empty($dt_to)) {
      $records->whereBetween(DB::raw('DATE(membership_date)'), array($dt_from, $dt_to));
    }
    $totalRecords = $records->count();

    // Total records with filter
    $records = Member::select('users.*', DB::raw('CONCAT(users.first_name," ",users.last_name) AS full_name'), 'member.member_no as member_no', 'member.position_id', 'campus.name as campus', 'department.name as department', 'member.membership_date as memdate')
      ->leftjoin('users', 'member.user_id', 'users.id')
      ->leftjoin('campus', 'member.campus_id', 'campus.id')
      ->leftjoin('department', 'member.department_id', 'department.id')
      ->where('member_no', 'like', '%' . $search . '%');

    ## Add custom filter conditions
    if (!empty($campus)) {
      $records->where('campus_id', $campus);
    }
    if (!empty($department)) {
      $records->where('department_id', $department);
    }
    if (!empty($search)) {
      $records->orWhere('member_no', 'like', '%' . $search . '%');
      $records->orWhere('last_name', 'like', '%' . $search . '%');
    }
    if (!empty($dt_from) && !empty($dt_to)) {
      $records->whereBetween(DB::raw('DATE(membership_date)'), array($dt_from, $dt_to));
    }
    $totalRecordswithFilter = $records->count();

    // Fetch records
    $records = Member::select('users.*', DB::raw('CONCAT(users.first_name," ",users.last_name) AS full_name'), 'member.member_no as member_no', 'member.position_id', 'campus.name as campus', 'department.name as department', 'member.membership_date as memdate')
      ->leftjoin('users', 'member.user_id', 'users.id')
      ->leftjoin('campus', 'member.campus_id', 'campus.id')
      ->leftjoin('department', 'member.department_id', 'department.id')
      ->where('member_no', 'like', '%' . $search . '%');

    ## Add custom filter conditions
    if (!empty($campus)) {
      $records->where('campus_id', $campus);
    }
    if (!empty($department)) {
      $records->where('department_id', $department);
    }
    if (!empty($search)) {
      $records->orWhere('member_no', 'like', '%' . $search . '%');
      $records->orWhere('last_name', 'like', '%' . $search . '%');
    }
    if (!empty($dt_from) && !empty($dt_to)) {
      $records->whereBetween(DB::raw('DATE(membership_date)'), array($dt_from, $dt_to));
    }
    

    $posts = $records->skip($start)
      ->take($rowperpage)
      ->get();
    $data = array();
    if ($posts) {
      foreach ($posts as $r) {
        $start++;
        $row = array();
        $row[] = "<a data-md-tooltip='View Member' class='view_member md-tooltip--right' id='" . $r->id . "' style='cursor: pointer'>
                    <i class='mp-icon md-tooltip--right icon-book-open mp-text-c-primary mp-text-fs-large'></i>
                  </a>";
        $row[] = $r->member_no;
        $row[] = '<span class="mp-text-fw-heavy">' . $r->last_name . ', ' . $r->first_name . ' ' . $r->middle_name . '</span>';
        $row[] = date("D M j, Y", strtotime($r->memdate));
        $row[] = $r->campus;
        $row[] = $r->department;
        $row[] = $r->position_id;
        $row[] = date("M j, Y", strtotime($r->created_at));
        
        $data[] = $row;
      }
    }
    $json_data = array(
      "draw" => intval($draw),
      "recordsTotal" => intval($totalRecords),
      "recordsFiltered" => intval($totalRecordswithFilter),
      "data" => $data
    );
    echo json_encode($json_data);
  }
  public function loanMasterlistData(Request $request)
  {
    ## Read value
    DB::enableQueryLog();
    $totalRecords = LoanTransaction::groupBy('loan_id')->pluck('loan_id')->count();
    $draw = $request->get('draw');
    $start = $request->get("start");
    $rowperpage = $request->get("length"); // Rows display per page
    $columnIndex_arr = $request->get('order');
    $columnName_arr = $request->get('columns');
    $order_arr = $request->get('order');
    $search_arr = $request->get('search');
    $columnIndex = $columnIndex_arr[0]['column']; // Column index
    // $columnName = $columnName_arr[$columnIndex]['data']; // Column name
    $columnSortOrder = $order_arr[0]['dir']; // asc or desc
    $searchValue = $search_arr['value']; // Search value
    // Custom search filter 
    $loan_type  = $request->get('loan_type');
    $dt_from = $request->get('dt_from');
    $dt_to = $request->get('dt_to');
    
    ## Add custom filter conditions
    if (!empty($loan_type) || !empty($searchValue)) {
      $records = LoanTransaction::select('loan.id as id', 'loan_type.name as type', 'member.member_no as memberNo', 'users.first_name as firstname', 'users.middle_name as middlename', 'users.last_name as lastname', DB::raw('MAX(date) as lastTransactionDate'), DB::raw('SUM(amount) AS balance'), DB::raw('MAX(start_amort_date) AS startAmortDate'), DB::raw('MAX(end_amort_date) AS endAmortDate'))
      ->leftjoin('loan', 'loan_transaction.loan_id', '=', 'loan.id')
      ->leftjoin('loan_type', 'loan.type_id', '=', 'loan_type.id')
      ->leftjoin('member', 'loan.member_id', '=', 'member.id')
      ->leftjoin('users', 'member.user_id', '=', 'users.id')
      ->where('loan_type.id', $loan_type)
      ->Where('first_name', 'like', '%' . $searchValue . '%')
      ->orWhere('last_name', 'like', '%' . $searchValue . '%')
      ->orWhere('middle_name', 'like', '%' . $searchValue . '%')
      ->orWhere('member.member_no', 'like', '%' . $searchValue . '%')
      ->groupBy('loan.id');
      
      $totalRecordswithFilter = $records->pluck('loan.id')->count();
    }else{
      $records = LoanTransaction::select('loan.id as id', 'loan_type.name as type', 'member.member_no as memberNo', 'users.first_name as firstname', 'users.middle_name as middlename', 'users.last_name as lastname', DB::raw('MAX(date) as lastTransactionDate'), DB::raw('SUM(amount) AS balance'), DB::raw('MAX(start_amort_date) AS startAmortDate'), DB::raw('MAX(end_amort_date) AS endAmortDate'))
        ->leftjoin('loan', 'loan_transaction.loan_id', '=', 'loan.id')
        ->leftjoin('loan_type', 'loan.type_id', '=', 'loan_type.id')
        ->leftjoin('member', 'loan.member_id', '=', 'member.id')
        ->leftjoin('users', 'member.user_id', '=', 'users.id')
        ->groupBy('loan.id');
        $totalRecordswithFilter = $records->pluck('loan.id')->count();
    }
    if (!empty($dt_from) && !empty($dt_to)) {
      $records->having(DB::raw('MAX(loan_transaction.date)'), '>=', "$dt_from")->having(DB::raw('MAX(loan_transaction.date)'), '<=', "$dt_to");
      $totalRecordswithFilter = $records->pluck('loan.id')->count();
    }
    //Search Box
    
    $posts = $records->skip($start)
      ->take($rowperpage)
      ->get();
    $query = DB::getQueryLog();
    $data = array();
    if ($posts) {
      foreach ($posts as $r) {
        $start++;
        $row = array();
        $row[] = "<a title='View Loans History' class='view_loan_history' id='view_loans' data-id='". $r->id ."' href='#'>
                    <i class='mp-icon md-tooltip icon-book-open mp-text-c-primary mp-text-fs-large'></i>
                  </a>";
        $row[] = $r->type;
        $row[] = $r->memberNo;
        $row[] = '<span class="mp-text-fw-heavy">' . $r->lastname . ', ' . $r->firstname . ' ' . $r->middlename . '</span>';
        $row[] = $r->lastTransactionDate == null ? '' : date('m/d/Y', strtotime($r->lastTransactionDate));
        $row[] = 'PHP ' . number_format($r->balance, 2);
        $row[] = $r->startAmortDate == null ? '' : date('m/d/Y', strtotime($r->startAmortDate));
        $row[] = $r->endAmortDate == null ? '' : date('m/d/Y', strtotime($r->endAmortDate));
        $data[] = $row;
      }
    }
    $json_data = array(
      "draw" => intval($draw),
      "recordsTotal" => intval($totalRecords),
      "recordsFiltered" => intval($totalRecordswithFilter),
      "query" => $query ,
      "data" => $data
    );
    echo json_encode($json_data);
  }

  public function getMemberData()
  {
    $records = Member::select('users.*', DB::raw('CONCAT(users.first_name," ",users.last_name) AS full_name'), 'member.member_no as member_no', 'member.position_id', 'campus.name as campus', 'department.name as department', 'member.membership_date as memdate')
      ->leftjoin('users', 'member.user_id', 'users.id')
      ->leftjoin('campus', 'member.campus_id', 'campus.id')
      ->leftjoin('department', 'member.department_id', 'department.id')
      ->get();

    $memData = "";
    if (count($records) > 0) {
      $memData .= '
      <table>
        <tr>
          <th>Member ID</th>
          <th>Last Name</th>
          <th>First Name</th>
          <th>Middle Name</th>
          <th>Membership Date</th>
          <th>Campus</th>
          <th>Class</th>
          <th>Position</th>
        </tr>
      ';
      foreach ($records as $row) {
        $memData .= '
        <tr>
          <td>' . $row->member_no . '</td>
          <td>' . $row->last_name . '</td>
          <td>' . $row->first_name . '</td>
          <td>' . $row->middle_name . '</td>
          <td>' . date("D M j, Y", strtotime($row->memdate)) . '</td>
          <td>' . $row->campus . '</td>
          <td>' . $row->department . '</td>
          <td>' . $row->position_id . '</td>
        </tr>
        ';
      }
      $memData .= '</table>';
    }

    header('Content-Disposition: attachment; filename=List of member.xls');
    header('Content-Type: application/xls');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    echo $memData;
  }
  public function getLoanData($id,$dt_from,$dt_to)
  {
    DB::enableQueryLog();
    if(!empty($id) && $id != 0){
    $records = LoanTransaction::select('loan.id as id', 'loan_type.name as type', 'member.member_no as memberNo', 'users.first_name as firstname', 'users.middle_name as middlename', 'users.last_name as lastname', DB::raw('MAX(date) as lastTransactionDate'), DB::raw('SUM(amount) AS balance'), DB::raw('MAX(start_amort_date) AS startAmortDate'), DB::raw('MAX(end_amort_date) AS endAmortDate'))
    ->leftjoin('loan', 'loan_transaction.loan_id', '=', 'loan.id')
    ->leftjoin('loan_type', 'loan.type_id', '=', 'loan_type.id')
    ->leftjoin('member', 'loan.member_id', '=', 'member.id')
    ->leftjoin('users', 'member.user_id', '=', 'users.id')
    ->where('loan_type.id', $id)
    ->groupBy('loan.id');
    }else{
    $records = LoanTransaction::select('loan.id as id', 'loan_type.name as type', 'member.member_no as memberNo', 'users.first_name as firstname', 'users.middle_name as middlename', 'users.last_name as lastname', DB::raw('MAX(date) as lastTransactionDate'), DB::raw('SUM(amount) AS balance'), DB::raw('MAX(start_amort_date) AS startAmortDate'), DB::raw('MAX(end_amort_date) AS endAmortDate'))
    ->leftjoin('loan', 'loan_transaction.loan_id', '=', 'loan.id')
    ->leftjoin('loan_type', 'loan.type_id', '=', 'loan_type.id')
    ->leftjoin('member', 'loan.member_id', '=', 'member.id')
    ->leftjoin('users', 'member.user_id', '=', 'users.id')
    ->groupBy('loan.id');
    }
    if (!empty($dt_from) && !empty($dt_to) && $dt_from != 0 && $dt_to != 0) {
      $records->having(DB::raw('MAX(loan_transaction.date)'), '>=', "$dt_from")->having(DB::raw('MAX(loan_transaction.date)'), '<=', "$dt_to");
    }

    $loanData = "";
    $posts = $records->get();
    if (count($posts) > 0) {
      $loanData .= '
      <table>
        <tr>
          <th>Loan Type</th>
          <th>Member ID</th>
          <th>Member Name</th>
          <th>Last Transaction Date</th>
          <th>Balance</th>
          <th>Start Amort Date</th>
          <th>End Amort Date</th>
        </tr>
      ';
      foreach ($posts as $row) {
        $loanData .= '
        <tr>
          <td>' . $row->type . '</td>
          <td>' . $row->memberNo . '</td>
          <td>' . $row->lastname . ', ' . $row->firstname . ' ' . $row->middlename . '</td>
          <td>' . $row->lastTransactionDate . '</td>
          <td>' . 'PHP '.number_format($row->balance, 2) . '</td>
          <td>' . $amort1 = ($row->startAmortDate == '1970-01-01' ? '' : date('m/d/Y', strtotime($row->startAmortDate))) . '</td>
          <td>' . $amort2 = ($row->endAmortDate == '1970-01-01' ? '' : date('m/d/Y', strtotime($row->endAmortDate))) . '</td>
        </tr>
        ';
      }
      $loanData .= '</table>';
    }

    header('Content-Disposition: attachment; filename=Active Loan List.xls');
    header('Content-Type: application/xls');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    $query = DB::getQueryLog();
    echo($loanData);
  }
  public function addCampus(Request $request)
  {
    $message = '';
    $campus = DB::table('campus')
      ->where('name', $request->input('campus_name'))
      ->orWhere('campus_key', $request->input('campus_key'))
      ->count();

    if ($campus > 0) {
      $message = 'Campus exist';
    } else {
      $insertCampus = array(
        'campus_key' => $request->input('campus_key'),
        'name' => $request->input('campus_name'),
        'cluster_id' => $request->input('cluster')
      );
      DB::table('campus')->insert($insertCampus);
    }
    $output = array(
      'message' => $message,
    );

    echo json_encode($output);
  }

  public function deleteCampus(Request $request)
  {
    $message = '';
    $campus = DB::table('member')
      ->where('campus_id', $request->get('id'))
      ->count();

    if ($campus > 0) {
      $message = 'Data Found';
    } else {
      DB::table('campus')
        ->where('id', $request->get('id'))
        ->delete();
    }
    $output = array(
      'message' => $message,
    );
    echo json_encode($output);
  }

  public function editCampusKey(Request $request)
  {
    $message = '';
    $campus = DB::table('campus')
      ->where('campus_key', $request->get('campus_key'))
      ->count();

    if ($campus > 0) {
      $message = 'Data Found';
    } else {
      DB::table('campus')
        ->where('id', $request->get('id'))
        ->update(array('campus_key' => $request->get('campus_key')));
    }
    $output = array(
      'message' => $message,
    );
    echo json_encode($output);
  }
  public function editCampusName(Request $request)
  {
    $message = '';
    $campus = DB::table('campus')
      ->where('name', $request->get('campus_name'))
      ->count();

    if ($campus > 0) {
      $message = 'Data Found';
    } else {
      DB::table('campus')
        ->where('id', $request->get('id'))
        ->update(array('name' => $request->get('campus_name')));
    }
    $output = array(
      'message' => $message,
    );
    echo json_encode($output);
  }
  public function editCluster(Request $request)
  {
    $message = '';
    DB::table('campus')
      ->where('id', $request->get('id'))
      ->update(array('cluster_id' => $request->get('cluster_id')));
    $output = array(
      'message' => $message,
    );
    echo json_encode($output);
  }

  public function exportCampus()
  {
    $records = DB::table('campus')
      ->get();

    $campusData = "";
    if (count($records) > 0) {
      $campusData .= '
      <table>
        <tr>
          <th>Campus Key</th>
          <th>Campus Name</th>
          <th>Cluster ID</th>
        </tr>
      ';
      foreach ($records as $row) {
        $cluster = DB::table('cluster')
          ->where('id', $row->cluster_id)
          ->first();
        $campusData .= '
        <tr>
          <td>' . $row->campus_key . '</td>
          <td>' . $row->name . '</td>
          <td>' . $cluster->name . '</td>
        </tr>
        ';
      }
      $campusData .= '</table>';
    }

    header('Content-Disposition: attachment; filename=List of campuses.xls');
    header('Content-Type: application/xls');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    echo $campusData;
  }
  // public function printMemberData()
  // {
  //   $records = Member::select('users.*', DB::raw('CONCAT(users.first_name," ",users.last_name) AS full_name'), 'member.member_no as member_no', 'member.position_id', 'campus.name as campus', 'department.name as department', 'member.membership_date as memdate')
  //     ->leftjoin('users', 'member.user_id', 'users.id')
  //     ->leftjoin('campus', 'member.campus_id', 'campus.id')
  //     ->leftjoin('department', 'member.department_id', 'department.id')
  //     ->get();
  //   $pdf = PDF::loadView('pdf.member',['member'=>$records]);
  //   return $pdf->stream('members.pdf');
  // }
  //===============================================Eto na yung End ng bagong code=====================================================================//


  public function tempass()
  {
    if (getUserdetails()->role == "SUPER_ADMIN") {
      $tempass = Tempass::leftjoin('cluster', 'tempass.cluster', 'cluster.id')->get();
    } else {
      $tempass = Tempass::leftjoin('cluster', 'tempass.cluster', 'cluster.id')->where('tempass.cluster', '=', getUserdetails()->cluster_id)->get();
    }

    return view('admin.tempass', array('tempass' => $tempass));
  }

  public function onboarding()
  {
    return view('admin.onboarding');
  }

  public function member_add_new(Request $request)
  {

    $member_no = DB::table('member_no_series')->where('id', 1)->first();

    if ($member_no->year == date('Y')) {
      $current_member = $member_no->current_last + 1;
      $current_counter = $member_no->current_counter + 1;

      $user_id = DB::table('users')->insertGetId(
        ['first_name' => strtoupper($request->first_name), 'middle_name' => strtoupper($request->middle_name), 'last_name' => strtoupper($request->last_name), 'email' => $request->email, 'password' => "", 'contact_no' => $request->contact_no, 'archived' => 0, 'password_set' => 0]
      );

      DB::table('member_no_series')
        ->where('id', 1)
        ->update(['current_last' => $current_member, 'current_counter' => $current_counter]);

      DB::table('member')->insert(
        ['member_no' => $current_member, 'user_id' => $user_id, 'campus_id' => $request->campus_id, 'department_id' => $request->department_id, 'position_id' => strtoupper($request->position_id), 'membership_date' => '', 'original_appointment_date' => date("Y-m-d", strtotime($request->original_appointment_date)), 'with_profile' => 0, 'membership_status' => 'PENDING', 'added_by' => getUserdetails()->user_id]
      );

      DB::table('member_detail')->insert(
        ['member_no' => $current_member, 'gender' => $request->gender, 'salary_grade' => $request->salary_grade, 'monthly_salary' => $request->monthly_salary, 'appointment_status' => $request->appointment_status, 'employee_no' => $request->employee_no, 'tin' => $request->tin, 'unit_dept' => $request->unit_dept, 'civil_status' => $request->civil_status, 'permanent_address' => $request->permanent_address, 'current_address' => $request->current_address, 'landline' => $request->landline, 'birth_date' => date("Y-m-d", strtotime($request->birth_date)), 'contribution_type' => $request->contribution_type, 'contribution' => $request->contribution, 'with_cocolife_form' => $request->with_cocolife_form, 'created_by' => getUserdetails()->user_id]
      );

      DB::table('proxy_form')->insert(
        ['member_no' => $current_member, 'validity' => date("Y-m-d", strtotime($request->validity)), 'updated_by' => getUserdetails()->user_id]
      );

      return redirect('admin/add_member')
        ->with('success', 'New Member Successfully Added.');
    } else {
      $current = date('Y');
      $current .= '00000';

      $current_counter = 0;

      $current_member = $current + 1;
      $current_counter = $current_counter + 1;

      DB::table('member_no_series')
        ->where('id', 1)
        ->update(['year' => date('Y'), 'current_last' => $current_member, 'current_counter' => $current_counter]);

      $user_id = DB::table('users')->insertGetId(
        ['first_name' => strtoupper($request->first_name), 'middle_name' => strtoupper($request->middle_name), 'last_name' => strtoupper($request->last_name), 'email' => $request->email, 'password' => "", 'contact_no' => $request->contact_no, 'archived' => 0, 'password_set' => 0]
      );

      DB::table('member')->insert(
        ['member_no' => $current_member, 'user_id' => $user_id, 'campus_id' => $request->campus_id, 'department_id' => $request->department_id, 'position_id' => strtoupper($request->position_id), 'membership_date' => '', 'original_appointment_date' => date("Y-m-d", strtotime($request->original_appointment_date)), 'with_profile' => 0, 'membership_status' => 'PENDING', 'added_by' => getUserdetails()->user_id]
      );

      DB::table('member_detail')->insert(
        ['member_no' => $current_member, 'gender' => $request->gender, 'salary_grade' => $request->salary_grade, 'monthly_salary' => $request->monthly_salary, 'appointment_status' => $request->appointment_status, 'employee_no' => $request->employee_no, 'tin' => $request->tin, 'unit_dept' => $request->unit_dept, 'civil_status' => $request->civil_status, 'permanent_address' => $request->permanent_address, 'current_address' => $request->current_address, 'landline' => $request->landline, 'birth_date' => date("Y-m-d", strtotime($request->birth_date)), 'contribution_type' => $request->contribution_type, 'contribution' => $request->contribution, 'with_cocolife_form' => $request->with_cocolife_form, 'created_by' => getUserdetails()->user_id]
      );

      DB::table('proxy_form')->insert(
        ['member_no' => $current_member, 'validity' => date("Y-m-d", strtotime($request->validity)), 'updated_by' => getUserdetails()->user_id]
      );

      return redirect('admin/add_member')
        ->with('success', 'New Member Successfully Added.');
    }
  }

  public function saveonboarding(Request $request)
  {
    if ($request->password == $request->confirmPassword) {
      $user = User::find(Auth::user()->id);
      $user->update([
        'password' => Hash::make($request->password),
        'password_set' => 1
      ]);
      return redirect('/admin/dashboard');
    } else {
      return redirect('/admin/onboarding')
        ->with('error', 'Passwords do not match!');
    }
  }


  public function members()
  {
    // if (getUserdetails()->role == "SUPER_ADMIN") {

    //   if (isset($_GET['q'])) {
    //     $members = Member::select('users.*', 'member.member_no as member_no', 'campus.name as campus', 'department.name as department', 'member.position_id', 'member.membership_date as memdate')
    //       ->leftjoin('users', 'member.user_id', 'users.id')
    //       ->leftjoin('campus', 'member.campus_id', 'campus.id')
    //       ->leftjoin('department', 'member.department_id', 'department.id')
    //       ->where('member_no', 'like', '%' . $_GET['q'] . '%')
    //       ->orWhere(DB::raw('CONCAT(users.first_name," ",users.last_name)'), 'like', '%' . $_GET['q'] . '%')
    //       ->orWhere('users.first_name', 'like', '%' . $_GET['q'] . '%')
    //       ->orWhere('users.last_name', 'like', '%' . $_GET['q'] . '%')
    //       ->paginate(10);
    //   } else {
    //     $members = Member::select('users.*', DB::raw('CONCAT(users.first_name," ",users.last_name) AS full_name'), 'member.member_no as member_no', 'member.position_id', 'campus.name as campus', 'department.name as department', 'member.membership_date as memdate')
    //       ->leftjoin('users', 'member.user_id', 'users.id')
    //       ->leftjoin('campus', 'member.campus_id', 'campus.id')
    //       ->leftjoin('department', 'member.department_id', 'department.id')
    //       ->paginate(10);
    //   }
    // } else {
    //   if (isset($_GET['q'])) {
    //     $members = Member::select('users.*', 'member.member_no as member_no', 'campus.name as campus', 'department.name as department', 'member.membership_date as memdate', 'member.position_id')
    //       ->leftjoin('users', 'member.user_id', 'users.id')
    //       ->leftjoin('campus', 'member.campus_id', 'campus.id')
    //       ->leftjoin('department', 'member.department_id', 'department.id')
    //       ->where('campus.cluster_id', '=', getUserdetails()->cluster_id)
    //       ->where(function ($query) {
    //         $query->where('member_no', 'like', '%' . $_GET['q'] . '%')
    //           ->orWhere(DB::raw('CONCAT(users.first_name," ",users.last_name)'), 'like', '%' . $_GET['q'] . '%')
    //           ->orWhere('users.first_name', 'like', '%' . $_GET['q'] . '%')
    //           ->orWhere('users.last_name', 'like', '%' . $_GET['q'] . '%');
    //       })

    //       ->paginate(10);
    //   } else {
    //     $members = Member::select('users.*', 'member.member_no as member_no', 'campus.name as campus', 'department.name as department', 'member.membership_date as memdate', 'member.position_id')
    //       ->leftjoin('users', 'member.user_id', 'users.id')
    //       ->leftjoin('campus', 'member.campus_id', 'campus.id')
    //       ->leftjoin('department', 'member.department_id', 'department.id')
    //       ->where('campus.cluster_id', '=', getUserdetails()->cluster_id)
    //       ->paginate(10);
    //   }
    // }

    // return view('admin.members', array('members' => $members));
    $data['department'] = DB::table('department')
      ->get();

    $data['campuses'] = Campus::all();
    return view('admin.members')->with($data);
  }

  public function member_soa($id)
  {
    $member = User::where('users.id', $id)
      ->select('*', 'member.id as member_id', 'users.id as user_id', 'campus.name as campus_name')
      ->leftjoin('member', 'users.id', '=', 'member.user_id')
      ->leftjoin('campus', 'member.campus_id', '=', 'campus.id')
      ->first();


    $recentcontributions = ContributionTransaction::select('*')
      ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
      ->leftjoin('contribution_account', 'contribution_transaction.account_id', 'contribution_account.id')
      ->where('contribution.member_id', '=', $member->member_id)
      ->Where('contribution_transaction.amount', '<>', 0.00)
      ->orderBy('contribution.date', 'desc')
      ->orderBy('contribution.reference_no', 'desc')
      ->limit(3)
      ->get();

    $contributions = array();

    $membercontribution = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
      ->where('contribution_transaction.account_id', '=', 2)
      ->where('contribution.member_id', '=', $member->member_id)
      ->first();
    $contributions['membercontribution'] = $membercontribution->total;


    $upcontribution = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
      ->where('contribution_transaction.account_id', '=', 1)
      ->where('contribution.member_id', '=', $member->member_id)
      ->first();
    $contributions['upcontribution'] = $upcontribution->total;


    $eupcontribution = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
      ->where('contribution_transaction.account_id', '=', 3)
      ->where('contribution.member_id', '=', $member->member_id)
      ->first();
    $contributions['eupcontribution'] = $eupcontribution->total;


    $emcontribution = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
      ->where('contribution_transaction.account_id', '=', 4)
      ->where('contribution.member_id', '=', $member->member_id)
      ->first();
    $contributions['emcontribution'] = $emcontribution->total;


    $totalcontributions = array_sum($contributions);



    $recentloans = LoanTransaction::select('loan_transaction.id as id', 'reference_no', 'date', 'loan_id', 'amortization', 'interest', 'amount', 'loan_type.name', DB::raw('(select SUM(amount) from loan_transaction as lt where lt.loan_id = loan.id and lt.date<=loan_transaction.date order by date desc) as balance'))
      ->leftjoin('loan', 'loan_transaction.loan_id', 'loan.id')
      ->leftjoin('member', 'loan.member_id', 'member.id')
      ->leftjoin('loan_type', 'loan.type_id', 'loan_type.id')
      ->where('loan.member_id', '=', $member->member_id)
      ->Where('loan_transaction.amount', '<>', 0.00)
      ->orderBy('date', 'desc')
      ->limit(3)
      ->get();


    $outstandingloans = LoanTransaction::select('loan_type.name as type', DB::raw('SUM(amount) as balance'))
      ->leftjoin('loan', 'loan_transaction.loan_id', 'loan.id')
      ->leftjoin('loan_type', 'loan.type_id', 'loan_type.id')
      ->where('loan.member_id', '=', $member->member_id)
      ->groupBy('loan_type.name')
      ->get();

    $totalloanbalance = 0;
    foreach ($outstandingloans as $loan) {
      $totalloanbalance += $loan->balance;
    }




    // dd($recentloans);
    return view('admin.member_soa', array('member' => $member, 'recentcontributions' => $recentcontributions, 'recentloans' => $recentloans, 'contributions' => $contributions, 'totalcontributions' => $totalcontributions, 'outstandingloans' => $outstandingloans, 'totalloanbalance' => $totalloanbalance));
  }

  public function loansmasterlist()
  {
    // $loans = LoanTransaction::select('loan.id as id', 'loan_type.name as type', 'member.member_no as memberNo', 'users.first_name as firstname', 'users.middle_name as middlename', 'users.last_name as lastname', DB::raw('MAX(date) as lastTransactionDate'), DB::raw('SUM(amount) AS balance'), DB::raw('MAX(start_amort_date) AS startAmortDate'), DB::raw('MAX(end_amort_date) AS endAmortDate'))
    //   ->leftjoin('loan', 'loan_transaction.loan_id', '=', 'loan.id')
    //   ->leftjoin('loan_type', 'loan.type_id', '=', 'loan_type.id')
    //   ->leftjoin('member', 'loan.member_id', '=', 'member.id')
    //   ->leftjoin('users', 'member.user_id', '=', 'users.id')
    //   ->groupBy(
    //     'loan.id',
    //     'loan_type.name',
    //     'member.member_no',
    //     'users.first_name',
    //     'users.middle_name',
    //     'users.last_name'
    //   )
    //   ->orderBy('lastTransactionDate', 'desc')

    //   ->paginate(10);

    $data['loan_type'] = DB::table('loan_type')
    ->get();

    // $data['loan_type'] = LoanType::all();
    return view('admin.loans_masterlist')->with($data);
  }

  public function loandetails($id)
  {


    $loans = LoanTransaction::select('loan_transaction.id as id', 'member.*', 'reference_no', 'date', 'loan_id', 'amortization', 'interest', 'amount', 'loan_type.name', DB::raw('(select SUM(amount) from loan_transaction as lt where lt.loan_id = loan.id and lt.date<=loan_transaction.date order by date desc) as balance'))
      ->leftjoin('loan', 'loan_transaction.loan_id', 'loan.id')
      ->leftjoin('member', 'loan.member_id', 'member.id')
      ->leftjoin('loan_type', 'loan.type_id', 'loan_type.id')
      ->where('loan.id', '=', $id)
      ->Where('loan_transaction.amount', '<>', 0.00)
      ->orderBy('loan.type_id', 'ASC')
      ->orderBy('date', 'desc')
      ->paginate(10);



    $member = User::where('users.id', $loans[0]->user_id)
      ->select('*', 'member.id as member_id', 'users.id as user_id', 'campus.name as campus_name')
      ->leftjoin('member', 'users.id', '=', 'member.user_id')
      ->leftjoin('campus', 'member.campus_id', '=', 'campus.id')
      ->first();




    return view('admin.loan_details', array('loans' => $loans, 'member' => $member));
  }


  public function generatesoa($id)
  {
    $member = User::where('users.id', $id)
      ->select('*', 'member.id as member_id', 'users.id as user_id', 'campus.name as campus_name')
      ->leftjoin('member', 'users.id', '=', 'member.user_id')
      ->leftjoin('campus', 'member.campus_id', '=', 'campus.id')
      ->first();

    $contributions = array();

    $membercontribution = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
      ->where('contribution_transaction.account_id', '=', 2)
      ->where('contribution.member_id', '=', $member->member_id)
      ->first();
    $contributions['membercontribution'] = $membercontribution->total;


    $upcontribution = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
      ->where('contribution_transaction.account_id', '=', 1)
      ->where('contribution.member_id', '=', $member->member_id)
      ->first();
    $contributions['upcontribution'] = $upcontribution->total;


    $eupcontribution = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
      ->where('contribution_transaction.account_id', '=', 3)
      ->where('contribution.member_id', '=', $member->member_id)
      ->first();
    $contributions['eupcontribution'] = $eupcontribution->total;


    $emcontribution = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
      ->where('contribution_transaction.account_id', '=', 4)
      ->where('contribution.member_id', '=', $member->member_id)
      ->first();
    $contributions['emcontribution'] = $emcontribution->total;


    $totalcontributions = array_sum($contributions);



    $outstandingloans = LoanTransaction::select('loan_type.name as type', DB::raw('SUM(amount) as balance'))
      ->leftjoin('loan', 'loan_transaction.loan_id', 'loan.id')
      ->leftjoin('loan_type', 'loan.type_id', 'loan_type.id')
      ->where('loan.member_id', '=', $member->member_id)
      ->groupBy('loan_type.name')
      ->get();

    $totalloanbalance = 0;
    foreach ($outstandingloans as $loan) {
      $totalloanbalance += $loan->balance;
    }

    $data['totalloanbalance'] = $totalloanbalance;
    $data['outstandingloans'] = $outstandingloans;
    $data['totalcontributions'] = $totalcontributions;
    $data['emcontribution'] = $emcontribution->total;
    $data['eupcontribution'] = $eupcontribution->total;
    $data['upcontribution'] = $upcontribution->total;
    $data['membercontribution'] = $membercontribution->total;
    $data['member'] = $member;



    $pdf = PDF::loadView('pdf.soa', $data);
    return $pdf->stream('soa.pdf');
  }

  public function generateequity($id)
  {
    $member = User::where('users.id', $id)
      ->select('*', 'member.id as member_id', 'users.id as user_id', 'campus.name as campus_name')
      ->leftjoin('member', 'users.id', '=', 'member.user_id')
      ->leftjoin('campus', 'member.campus_id', '=', 'campus.id')
      ->first();

    // $equity=ContributionTransaction::select('contribution_transaction.id as id', 'date', 'account_id', 'contribution_id', 'reference_no', 'amount','contribution_account.name', DB::raw('(select SUM(amount) from contribution_transaction as ct left join contribution as c on ct.contribution_id = c.id where c.member_id=contribution.member_id and c.date<=contribution.date and ct.id <= contribution_transaction.id order by date desc, contribution_transaction.id desc) as balance'))
    // ->leftjoin('contribution','contribution_transaction.contribution_id','contribution.id')
    // ->leftjoin('member','contribution.member_id','member.id')
    // ->leftjoin('contribution_account','contribution_transaction.account_id','contribution_account.id')
    // ->where('contribution.member_id','=',$member->member_id)
    // ->Where('contribution_transaction.amount','<>',0.00)
    // ->orderBy('date','desc')
    // ->orderBy('contribution_transaction.id','desc')
    //->get();

    $equity = ContributionTransaction::select('contribution_transaction.id as id', DB::raw('ABS(amount) as abs'), 'date', 'account_id', 'contribution_id', 'reference_no', 'amount', 'contribution_account.name', DB::raw('(select SUM(amount) from contribution_transaction as ct left join contribution as c on ct.contribution_id = c.id where c.member_id=contribution.member_id and c.date<=contribution.date order by date desc, contribution_transaction.id desc) as balance'))
      ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
      ->leftjoin('member', 'contribution.member_id', 'member.id')
      ->leftjoin('contribution_account', 'contribution_transaction.account_id', 'contribution_account.id')
      ->where('contribution.member_id', '=', $member->member_id)
      ->where('contribution_transaction.amount', '<>', 0.00)
      ->orderBy('date', 'desc')
      ->orderBy('contribution.reference_no', 'desc')
      ->orderBy('abs', 'desc')
      ->orderBy('contribution_transaction.id', 'desc')
      ->get();
    // dd($equity);



    $data['equity'] = $equity;
    $data['member'] = $member;



    $pdf = PDF::loadView('pdf.equity', $data);
    return $pdf->setPaper('a4', 'landscape')->stream('eqity.pdf');
  }

  public function generateloans($id)
  {
    $member = User::where('users.id', $id)
      ->select('*', 'member.id as member_id', 'users.id as user_id', 'campus.name as campus_name')
      ->leftjoin('member', 'users.id', '=', 'member.user_id')
      ->leftjoin('campus', 'member.campus_id', '=', 'campus.id')
      ->first();

    $loans = LoanTransaction::select('loan_transaction.id as id', 'reference_no', 'date', 'loan_id', 'amortization', 'interest', 'amount', 'loan_type.name', DB::raw('(select SUM(amount) from loan_transaction as lt where lt.loan_id = loan.id and lt.date<=loan_transaction.date order by date desc) as balance'))
      ->leftjoin('loan', 'loan_transaction.loan_id', 'loan.id')
      ->leftjoin('member', 'loan.member_id', 'member.id')
      ->leftjoin('loan_type', 'loan.type_id', 'loan_type.id')
      ->where('loan.member_id', '=', $member->member_id)
      ->Where('loan_transaction.amount', '<>', 0.00)
      ->orderBy('loan.type_id', 'ASC')
      ->orderBy('date', 'desc')
      ->get();


    $data['loans'] = $loans;
    $data['member'] = $member;



    $pdf = PDF::loadView('pdf.loans', $data);
    return $pdf->setPaper('a4', 'landscape')->stream('loan.pdf');
  }

  public function generateloanspertype($id)
  {


    $loans = LoanTransaction::select('loan_transaction.id as id', 'member.*', 'reference_no', 'date', 'loan_id', 'amortization', 'interest', 'amount', 'loan_type.name', DB::raw('(select SUM(amount) from loan_transaction as lt where lt.loan_id = loan.id and lt.date<=loan_transaction.date order by date desc) as balance'))
      ->leftjoin('loan', 'loan_transaction.loan_id', 'loan.id')
      ->leftjoin('member', 'loan.member_id', 'member.id')
      ->leftjoin('loan_type', 'loan.type_id', 'loan_type.id')
      ->where('loan.id', '=', $id)
      ->Where('loan_transaction.amount', '<>', 0.00)
      ->orderBy('loan.type_id', 'ASC')
      ->orderBy('date', 'desc')
      ->get();

    $member = User::where('users.id', $loans[0]->user_id)
      ->select('*', 'member.id as member_id', 'users.id as user_id', 'campus.name as campus_name')
      ->leftjoin('member', 'users.id', '=', 'member.user_id')
      ->leftjoin('campus', 'member.campus_id', '=', 'campus.id')
      ->first();

    $data['loans'] = $loans;
    $data['member'] = $member;



    $pdf = PDF::loadView('pdf.loans', $data);
    return $pdf->setPaper('a4', 'landscape')->stream('loan.pdf');
  }

  public function equity($id)
  {
    $member = User::where('users.id', $id)
      ->select('*', 'member.id as member_id', 'users.id as user_id', 'campus.name as campus_name')
      ->leftjoin('member', 'users.id', '=', 'member.user_id')
      ->leftjoin('campus', 'member.campus_id', '=', 'campus.id')
      ->first();

    // $equity=ContributionTransaction::select('contribution_transaction.id as id', 'date', 'account_id', 'contribution_id', 'reference_no', 'amount','contribution_account.name', DB::raw('(select SUM(amount) from contribution_transaction as ct left join contribution as c on ct.contribution_id = c.id where c.member_id=contribution.member_id and c.date<=contribution.date  order by date desc, contribution_transaction.id desc) as balance'))
    // ->leftjoin('contribution','contribution_transaction.contribution_id','contribution.id')
    // ->leftjoin('member','contribution.member_id','member.id')
    // ->leftjoin('contribution_account','contribution_transaction.account_id','contribution_account.id')
    // ->where('contribution.member_id','=',$member->member_id)
    // ->Where('contribution_transaction.amount','<>',0.00)
    // ->orderBy('date','desc')
    // ->orderBy('contribution_transaction.id','desc')
    $equity = ContributionTransaction::select('contribution_transaction.id as id', DB::raw('ABS(amount) as abs'), 'date', 'account_id', 'contribution_id', 'reference_no', 'amount', 'contribution_account.name', DB::raw('(select SUM(amount) from contribution_transaction as ct left join contribution as c on ct.contribution_id = c.id where c.member_id=contribution.member_id and c.date<=contribution.date order by date desc, contribution_transaction.id desc) as balance'))
      ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
      ->leftjoin('member', 'contribution.member_id', 'member.id')
      ->leftjoin('contribution_account', 'contribution_transaction.account_id', 'contribution_account.id')
      ->where('contribution.member_id', '=', $member->member_id)
      ->where('contribution_transaction.amount', '<>', 0.00)
      ->orderBy('date', 'desc')
      ->orderBy('contribution.reference_no', 'desc')
      ->orderBy('abs', 'desc')
      ->orderBy('contribution_transaction.id', 'desc')
      ->paginate(10);
    // $equity=ContributionTransaction::select('contribution_transaction.id as id', 'date', 'account_id', 'contribution_id', 'reference_no', 'amount','contribution_account.name', DB::raw('(select SUM(amount) from contribution_join as c where c.member_id=contribution.member_id and c.date<=contribution.date) as balance'))
    // ->leftjoin('contribution','contribution_transaction.contribution_id','contribution.id')
    // // ->leftjoin('member','contribution.member_id','member.id')
    // ->leftjoin('contribution_account','contribution_transaction.account_id','contribution_account.id')
    // ->where('contribution.member_id','=',$member->member_id)
    // ->where('contribution_transaction.amount','<>',0.00)
    // ->orderBy('date','desc')
    // ->paginate(10);
    // dd($equity);


    return view('admin.member_equity', array('equity' => $equity, 'member' => $member));
  }

  public function loans($id)
  {
    $member = User::where('users.id', $id)
      ->select('*', 'member.id as member_id', 'users.id as user_id', 'campus.name as campus_name')
      ->leftjoin('member', 'users.id', '=', 'member.user_id')
      ->leftjoin('campus', 'member.campus_id', '=', 'campus.id')
      ->first();

    $loans = LoanTransaction::select('loan_transaction.id as id', 'reference_no', 'date', 'loan_id', 'amortization', 'interest', 'amount', 'loan_type.name', DB::raw('(select SUM(amount) from loan_transaction as lt where lt.loan_id = loan.id and lt.date<=loan_transaction.date order by date desc) as balance'))
      ->leftjoin('loan', 'loan_transaction.loan_id', 'loan.id')
      ->leftjoin('member', 'loan.member_id', 'member.id')
      ->leftjoin('loan_type', 'loan.type_id', 'loan_type.id')
      ->where('loan.member_id', '=', $member->member_id)
      ->Where('loan_transaction.amount', '<>', 0.00)
      ->orderBy('loan.type_id', 'ASC')
      ->orderBy('date', 'desc')
      ->paginate(10);

    // dd($loans);
    return view('admin.member_loan', array('loans' => $loans, 'member' => $member));
  }



  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function updatepw()
  {
    $member = User::where('users.id', Auth::user()->id)
      ->select('*')->first();

    return view('admin.updatepassword');
  }

  public function savepw(Request $request)
  {
    $member = User::where('users.id', Auth::user()->id)
      ->select('*')->first();


    $newpass = $request->password;

    $confirm = Hash::check($request->currentPassword, $member->password);
    if ($confirm) {
      $user = User::find(Auth::user()->id);
      $user->update([
        'password' => Hash::make($newpass)
      ]);
      return redirect('/admin/update-password')
        ->with('success', 'Password successfully updated.');
    } else {
      return redirect('/admin/update-password')
        ->with('error', 'The current password you entered is incorrect.');
    }
  }

  public function member_profile($id)
  {
    // $bene_array=array('asdasd','asdasd','asdasd');
    //  $bene=array('asdasd1','asdasd1','asdasd1');
    // $test_array=array();
    // array_push($test_array,$bene_array);
    // array_push($test_array,$bene);
    // $test=json_encode($test_array);
    // $tests=json_decode($test);
    // dd($tests);



    $member = User::where('users.id', $id)
      ->select('*', 'member.id as member_id', 'users.id as user_id', 'campus.name as campus_name', 'position.name as position_name')
      ->leftjoin('member', 'users.id', '=', 'member.user_id')
      ->leftjoin('campus', 'member.campus_id', '=', 'campus.id')
      ->leftjoin('position', 'member.position_id', '=', 'position.id')
      ->first();

    $details = DB::table('member_detail')->where('member_detail.member_no', '=', $member->member_no)->leftjoin('proxy_form', 'member_detail.member_no', '=', 'proxy_form.member_no')->first();
    $beneficiaries = DB::table('beneficiaries')->where('member_no', '=', $member->member_no)->get();


    return view('admin.member_profile.member_profile', array('member' => $member, 'details' => $details, 'beneficiaries' => $beneficiaries));
  }

  public function member_edit_beneficiaries($id)
  {
    // dd($id);
    $member = User::where('users.id', $id)
      ->select('*', 'member.id as member_id', 'users.id as user_id', 'campus.name as campus_name', 'position.name as position_name')
      ->leftjoin('member', 'users.id', '=', 'member.user_id')
      ->leftjoin('campus', 'member.campus_id', '=', 'campus.id')
      ->leftjoin('position', 'member.position_id', '=', 'position.id')
      ->first();

    $beneficiaries = DB::table('beneficiaries')->where('member_no', '=', $member->member_no)->get();
    return view('admin.member_profile.member_beneficiaries_form', array('member' => $member, 'beneficiaries' => $beneficiaries));
  }

  public function member_savebeneficiary(Request $request)
  {
    // dd($request->all());
    $member = User::where('users.id', $request->member_no)
      ->select('*', 'member.id as member_id', 'users.id as user_id', 'campus.name as campus_name', 'position.name as position_name')
      ->leftjoin('member', 'users.id', '=', 'member.user_id')
      ->leftjoin('campus', 'member.campus_id', '=', 'campus.id')
      ->leftjoin('position', 'member.position_id', '=', 'position.id')
      ->first();

    $birth_date = date("Y-m-d", strtotime($request->birth_date));
    unset($request['_token']);
    unset($request['member_no']);
    unset($request['birth_date']);
    // unset($request['contact_no']);
    $request['member_no'] = $member->member_no;
    $request['added_by'] = Auth::user()->id;
    $request['birth_date'] = $birth_date;

    DB::table('beneficiaries')
      ->insert([$request->all()]);

    return redirect('/admin/member_edit_beneficiaries/' . $member->user_id)
      ->with('success', 'Beneficiary Added');
  }

  public function member_removebeneficiary(Request $request)
  {
    DB::table('beneficiaries')->where('id', '=', $request->bene_id)->delete();
    //    return redirect('/member/edit_beneficiaries')
    // ->with('success', 'Beneficiary Removed');

    return 1;
  }


  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Admin  $admin
   * @return \Illuminate\Http\Response
   */
  public function manageadmin()
  {

    if (isset($_GET['q'])) {

      $admin = Admin::select('*')
        ->leftjoin('users', 'admin.user_id', 'users.id')
        ->leftjoin('cluster', 'admin.cluster_id', 'cluster.id')
        ->where(DB::raw('CONCAT(users.first_name," ",users.last_name)'), 'like', '%' . $_GET['q'] . '%')
        ->orWhere('users.first_name', 'like', '%' . $_GET['q'] . '%')
        ->orWhere('users.last_name', 'like', '%' . $_GET['q'] . '%')
        ->paginate(10);
    } else {
      $admin = Admin::select('*')
        ->leftjoin('users', 'admin.user_id', 'users.id')
        ->leftjoin('cluster', 'admin.cluster_id', 'cluster.id')
        ->paginate(10);
    }
    // dd($admin);
    return view('admin.admin', array('admins' => $admin));
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Admin  $admin
   * @return \Illuminate\Http\Response
   */
  public function adminadd()
  {

    $clusters = DB::table('cluster')
      ->get();

    return view('admin.addadmin', array('clusters' => $clusters));
  }

  public function adminsave(Request $request)
  {
    $checkemail = DB::table('users')->where('email', '=', $request->email)->first();
    if (count($checkemail) > 0) {
      return redirect('/admin/add')
        ->with('error', 'Email is already used');
    }

    $tempass_length = 10;
    $tempass = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, $tempass_length);

    $emailadd = $request->email;
    $id = DB::table('users')->insertGetId(
      ['first_name' => $request->firstName, 'last_name' => $request->lastName, 'email' => $request->email, 'password' => Hash::make($tempass), 'archived' => 0, 'password_set' => 0]
    );
    if ($request->role == 'SUPER_ADMIN') {
      DB::table('admin')->insertGetId(
        ['user_id' => $id, 'role' => $request->role, 'cluster_id' => 1]
      );
    } else {
      DB::table('admin')->insertGetId(
        ['user_id' => $id, 'role' => $request->role, 'cluster_id' => $request->cluster]
      );
    }

    Mail::send('emailTemplates.adminAccount', ['firstName' => $request->firstName, 'email' => $request->email, 'password' => $tempass], function ($message) use ($emailadd) {
      $message->subject('Admin Account');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($emailadd);
    });

    $clusters = DB::table('cluster')
      ->get();

    return redirect('/admin/add')
      ->with('success', 'Admin created successfully.');
  }

  public function member_edit_details($id)
  {

    $member = User::where('users.id', $id)
      ->select('*', 'member.id as member_id', 'member.member_no as member_no', 'users.id as user_id', 'campus.name as campus_name', 'position.name as position_name')
      ->leftjoin('member', 'users.id', '=', 'member.user_id')
      ->leftjoin('campus', 'member.campus_id', '=', 'campus.id')
      ->leftjoin('position', 'member.position_id', '=', 'position.id')
      ->first();

    $campus = Campus::select('*')->get();
    $department = DB::table('department')->get();

    $details = DB::table('member_detail')->where('member_detail.member_no', '=', $member->member_no)->leftjoin('proxy_form', 'proxy_form.member_no', '=', 'member_detail.member_no')->first();

    return view('admin.member_profile.edit_detail', array('details' => $details, 'member' => $member, 'campus' => $campus, 'department' => $department));
  }

  public function member_save_details(Request $request)
  {

    $update_field = array();
    $user_db = DB::table('users')
      ->where('id', $request->user_id)
      ->first();




    // dd($member['original_appointment_date']=date("Y-m-d",strtotime($request->original_appointment_date)));
    $user['first_name'] = strtoupper($request->first_name);
    $user['middle_name'] = strtoupper($request->middle_name);
    $user['last_name'] = strtoupper($request->last_name);
    $user['email'] = $request->email;
    $user['contact_no'] = $request->contact_no;

    foreach ($user as $key => $value) {

      if ($value != $user_db->$key) {
        array_push($update_field, $key);
      } else {
        unset($user[$key]);
      }
    }

    if (count($user) != 0) {
      DB::table('users')
        ->where('id', $request->user_id)
        ->update($user);
    }


    $member_db = DB::table('member')
      ->where('user_id', $request->user_id)
      ->first();

    $member['campus_id'] = $request->campus_id;
    $member['department_id'] = $request->department_id;
    $member['position_id'] = $request->position_id;
    $member['membership_date'] = date("Y-m-d", strtotime($request->membership_date));
    $member['original_appointment_date'] = date("Y-m-d", strtotime($request->original_appointment_date));

    foreach ($member as $key => $value) {

      if ($value != $member_db->$key) {
        array_push($update_field, $key);
      } else {
        unset($member[$key]);
      }
    }

    if (count($member) != 0) {
      DB::table('member')
        ->where('user_id', $request->user_id)
        ->update($member);
    }


    $member_det_db = DB::table('member_detail')
      ->where('member_no', $request->member_no)
      ->first();

    if ($member_det_db == null) {
      DB::table('member_detail')
        ->insert(['member_no' => $request->member_no, 'created_by' => getUserdetails()->user_id]);
    }

    $member_det_db = DB::table('member_detail')
      ->where('member_no', $request->member_no)
      ->first();




    $member_det['gender'] = $request->gender;
    $member_det['salary_grade'] = $request->salary_grade;
    $member_det['monthly_salary'] = $request->monthly_salary;
    $member_det['appointment_status'] = $request->appointment_status;
    $member_det['employee_no'] = $request->employee_no;
    $member_det['tin'] = $request->tin;
    $member_det['civil_status'] = $request->civil_status;
    $member_det['permanent_address'] = $request->permanent_address;
    $member_det['current_address'] = $request->current_address;
    $member_det['landline'] = $request->landline;
    $member_det['birth_date'] = date("Y-m-d", strtotime($request->birth_date));
    $member_det['contribution_type'] = $request->contribution_type;
    $member_det['contribution'] = $request->contribution;
    $member_det['with_cocolife_form'] = $request->with_cocolife_form;
    $member_det['updated_by'] = getUserdetails()->user_id;
    $member_det['date_updated'] = date('Y-m-d');

    foreach ($member_det as $key => $value) {

      if ($key != 'updated_by') {
        if ($key != 'date_updated') {
          if ($value != $member_det_db->$key) {
            array_push($update_field, $key);
          } else {
            unset($member_det[$key]);
          }
        }
      }
    }

    if (count($member_det) != 0) {
      $member_det['updated_by'] = getUserdetails()->user_id;
      $member_det['date_updated'] = date('Y-m-d');
      DB::table('member_detail')
        ->where('member_no', $request->member_no)
        ->update($member_det);
    }


    $proxy_form_db = DB::table('proxy_form')
      ->where('member_no', $request->member_no)
      ->first();

    if ($request->validity != null) {
      $proxy_form['validity'] = date("Y-m-d", strtotime($request->validity));



      if ($proxy_form_db == null) {
        DB::table('proxy_form')->insert(
          ['member_no' => $request->member_no, 'validity' => $proxy_form['validity'], 'updated_by' => getUserdetails()->user_id]
        );
        array_push($update_field, 'proxy_form');
      } else {
        foreach ($proxy_form as $key => $value) {

          if ($value != $proxy_form_db->$key) {
            array_push($update_field, $key);
          } else {
            unset($proxy_form[$key]);
          }
        }

        if (count($proxy_form) != 0) {
          $proxy_form['updated_by'] = getUserdetails()->user_id;

          DB::table('proxy_form')
            ->where('member_no', $request->member_no)
            ->update($proxy_form);
        }
      }
    }



    $member = User::where('users.id', $request->user_id)
      ->select('*', 'member.id as member_id', 'users.id as user_id', 'campus.name as campus_name', 'position.name as position_name')
      ->leftjoin('member', 'users.id', '=', 'member.user_id')
      ->leftjoin('campus', 'member.campus_id', '=', 'campus.id')
      ->leftjoin('position', 'member.position_id', '=', 'position.id')
      ->first();

    $campus = Campus::select('*')->get();
    $department = DB::table('department')->get();

    $details = DB::table('member_detail')->where('member_detail.member_no', '=', $member->member_no)->leftjoin('proxy_form', 'proxy_form.member_no', '=', 'member_detail.member_no')->first();


    $fields = json_encode($update_field);

    if (count($update_field) != 0) {
      DB::table('log_member_detail')->insert(
        ['member_no' => $request->member_no, 'update_log' => $fields, 'created_by' => getUserdetails()->user_id]
      );

      return redirect('/admin/member_edit_details/' . $member->user_id)
        ->with('success', 'Details Successfully Updated');
    } else {
      return redirect('/admin/member_edit_details/' . $member->user_id)
        ->with('error', 'No Data Changes');
    }


    return view('admin.member_profile.edit_detail', array('details' => $details, 'member' => $member, 'campus' => $campus, 'department' => $department));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Admin  $admin
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, Admin $admin)
  {
    //
  }

  public function summary()
  {
    $record = array('Member No', 'Name', 'Campus', 'Member', 'UP', 'E-Member', 'E-UP', 'Total Equity', ' ', 'BL', 'BTL', 'CBL', 'EML', 'PEL', 'Total Loans');
    $recordcsv = array();
    array_push($recordcsv, $record);

    $members = Member::select('member.id', 'member.member_no', 'users.first_name', 'users.last_name', 'campus.campus_key')
      ->leftjoin('users', 'member.user_id', 'users.id')
      ->leftjoin('campus', 'member.campus_id', 'campus.id')
      ->where('member.membership_status', '=', 'ACTIVE')
      ->where('campus.id', '=', 4)
      ->get();

    foreach ($members as $member) {

      $contributions = array();

      $membercontribution = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
        ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
        ->where('contribution_transaction.account_id', '=', 2)
        ->where('contribution.member_id', '=', $member->id)
        ->where('contribution.date', '<', '2021-06-01')

        ->first();
      $contributions['membercontribution'] = $membercontribution->total;

      $upcontribution = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
        ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
        ->where('contribution_transaction.account_id', '=', 1)
        ->where('contribution.member_id', '=', $member->id)
        ->where('contribution.date', '<', '2021-06-01')
        ->first();
      $contributions['upcontribution'] = $upcontribution->total;


      $eupcontribution = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
        ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
        ->where('contribution_transaction.account_id', '=', 3)
        ->where('contribution.member_id', '=', $member->id)
        ->where('contribution.date', '<', '2021-06-01')
        ->first();
      $contributions['eupcontribution'] = $eupcontribution->total;


      $emcontribution = ContributionTransaction::select(DB::raw('SUM(contribution_transaction.amount) as total'))
        ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
        ->where('contribution_transaction.account_id', '=', 4)
        ->where('contribution.member_id', '=', $member->id)
        ->where('contribution.date', '<', '2021-06-01')
        ->first();
      $contributions['emcontribution'] = $emcontribution->total;


      $totalcontributions = array_sum($contributions);

      $outstandingloans = LoanTransaction::select('loan_type.name as type', DB::raw('SUM(amount) as balance'))
        ->leftjoin('loan', 'loan_transaction.loan_id', 'loan.id')
        ->leftjoin('loan_type', 'loan.type_id', 'loan_type.id')
        ->where('loan.member_id', '=', $member->id)
        ->where('loan_transaction.date', '<', '2021-06-01')
        ->groupBy('loan_type.name')
        ->get();

      $outstandingLoanBalance = 0;

      $bl = 0.00;
      $btl = 0.00;
      $cbl = 0.00;
      $eml = 0.00;
      $pel = 0.00;
      foreach ($outstandingloans as $loan) {

        if ($loan['type'] == 'PEL') {
          $pel = $loan->balance;
        }
        if ($loan['type'] == 'BL') {
          $bl = $loan->balance;
        }
        if ($loan['type'] == 'BTL') {
          $btl = $loan->balance;
        }
        if ($loan['type'] == 'CBL') {
          $cbl = $loan->balance;
        }
        if ($loan['type'] == 'EML') {
          $eml = $loan->balance;
        }

        $outstandingLoanBalance += $loan->balance;
      }

      array_push($recordcsv, [$member['member_no'], $member['first_name'] . ' ' . $member['last_name'], $member['campus_key'], $contributions['membercontribution'],  $contributions['upcontribution'], $contributions['emcontribution'],  $contributions['eupcontribution'], $totalcontributions, ' ', $bl, $btl, $cbl, $eml, $pel, $outstandingLoanBalance]);
    }

    Excel::create('summary_report', function ($excel) use ($recordcsv) {

      $excel->sheet('Sheetname', function ($sheet) use ($recordcsv) {

        $sheet->fromArray($recordcsv, null, 'A1', false, false);
      });
    })->download('csv');
  }

  public function modules()
  {

    return view('admin.modules');
  }


  public function changestatus($id)
  {

    $member = User::where('users.id', $id)
      ->select('*', 'member.id as member_id', 'users.id as user_id', 'campus.name as campus_name', 'position.name as position_name')
      ->leftjoin('member', 'users.id', '=', 'member.user_id')
      ->leftjoin('campus', 'member.campus_id', '=', 'campus.id')
      ->leftjoin('position', 'member.position_id', '=', 'position.id')
      ->first();

    $status = DB::table('member_status_reference')
      ->get();

    return view('admin.member_profile.change_status', array('member' => $member, 'status' => $status));
  }

  public function updatestatus(Request $request)
  {
    DB::table('member')
      ->where('id', $request->member_id)
      ->update(['membership_status' => $request->status]);

    return 1;
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Admin  $admin
   * @return \Illuminate\Http\Response
   */
  public function beneencoder()
  {
    return view('admin.member_profile.bene_encoder');
  }

  public function resetpass($id)
  {
    $tempass_length = 10;
    $tempass = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, $tempass_length);
    $hashedpass = Hash::make($tempass);

    DB::table('users')
      ->where('id', $id)
      ->update(['password' => $hashedpass, 'password_set' => 0]);

    $member = Member::select('*')
      ->leftjoin('users', 'member.user_id', 'users.id')
      ->leftjoin('campus', 'member.campus_id', 'campus.id')
      ->where('users.id', '=', $id)
      ->first();

    // dd($member);

    return view('admin.member_reset_pass', array('newpass' => $tempass, 'member' => $member));
  }
  public function add_member()
  {

    if (getUserdetails()->role == "SUPER_ADMIN") {
      $campus = Campus::select('*')->get();
    } else {
      $campus = Campus::select('*')->where('cluster_id', getUserdetails()->cluster_id)->get();
    }
    $department = DB::table('department')->get();

    return view('admin.member_profile.add_new', array('campus' => $campus, 'department' => $department));
  }
}
