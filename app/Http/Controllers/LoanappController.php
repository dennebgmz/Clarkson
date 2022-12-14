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

class LoanappController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */

  public function add_log($loan_app_id, $control_number, $action, $message, $attachment, $message_flag, $admin, $added_by)
  {
    $logs = DB::table('loan_applications_logs')->insert(
      ['loan_app_id' => $loan_app_id, 'control_number' => $control_number, 'action' => $action, 'message' => $message, 'attachment' => $attachment, 'message_flag' => $message_flag, 'admin' => $admin, 'added_by' => $added_by]
    );
  }


  public function index()
  {
    //return view('admin.dashboard',array('campuses'=>$campuses,'totalmembers'=>$memberscount,'totalloansgranted'=>$totalloansgranted,'campusmembers'=>$campusmembers,'contributions'=>$contributions,'totalequity'=>$totalequity,'activecampus'=>$activecampus));

    // dd(getUserdetails());


    $loan_type = DB::table('loan_type')
      ->select('*')
      ->get();
    $application = DB::table('loan_applications_peb')
      ->select('type')
      ->groupBy('type')
      ->get();
    $data = array(
      'loan_type' => $loan_type,
      'application' => $application,
    );

    return view('member.loan_application.index')->with($data);
  }

  public function member_loandetails(Request $request)
  {
    ## Read value
    DB::enableQueryLog();
    $totalRecords = DB::table('loan_applications')
      ->select('loan_applications.*', 'loan_type.name', 'loan_type.description')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->where('loan_applications.member_no', getUserdetails()->member_no)
      ->orderByRaw("field(loan_applications.status,'PROCESSING','DONE','CONFIRMED','CANCELLED')")
      ->orderBy('loan_applications.loan_type', 'asc')
      ->orderBy('loan_applications.date_created', 'desc')
      ->count();
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
    $loan_status = $request->get('loan_status');
    $dt_from = $request->get('dt_from');
    $dt_to = $request->get('dt_to');

    $records = DB::table('loan_applications')
      ->select('loan_applications.*', 'loan_type.name', 'loan_type.description')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->where('loan_applications.member_no', getUserdetails()->member_no)
      ->orderByRaw("field(loan_applications.status,'PROCESSING','DONE','CONFIRMED','CANCELLED')")
      ->orderBy('loan_applications.loan_type', 'asc')
      ->orderBy('loan_applications.date_created', 'desc');
    ## Add custom filter conditions
    if (!empty($searchValue)) {
      $records->where('control_number', 'like', '%' . $searchValue . '%');
    }
    if (!empty($loan_type)) {
      $records->where('loan_type.id', $loan_type);
    }
    if (!empty($loan_status)) {
      $records->where('loan_applications.status', $loan_status);
    }
    if (!empty($dt_from) && !empty($dt_to)) {
      $records->whereBetween(DB::raw('loan_applications.date_created'), array($dt_from, $dt_to));
    }
    $totalRecordswithFilter = $records->count();
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
        $row[] = '<a data-md-tooltip="View Details" href="#" id="member_loandet" data-id="' . $r->id . '">
                  <i class="mp-icon md-tooltip icon-book-open mp-text-c-primary mp-text-fs-large"></i>
                  </a>';
        $row[] = date("m/d/Y h:i A", strtotime($r->date_created));
        $row[] = $r->control_number;
        $row[] = $r->name;
        $row[] = $r->status;
        $data[] = $row;
      }
    }
    $json_data = array(
      "draw" => intval($draw),
      "recordsTotal" => intval($totalRecords),
      "recordsFiltered" => intval($totalRecordswithFilter),
      "query" => $query,
      "data" => $data
    );
    echo json_encode($json_data);
  }

  public function exportLoanApplication($loan, $stat, $dt_from, $dt_to)
  {
    if (!empty($loan) && $loan != 0) {
      $records = DB::table('loan_applications')
        ->select('loan_applications.*', 'loan_type.name', 'loan_type.description')
        ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
        ->where('loan_applications.member_no', getUserdetails()->member_no)
        ->orderByRaw("field(loan_applications.status,'PROCESSING','DONE','CONFIRMED','CANCELLED')")
        ->orderBy('loan_applications.loan_type', 'asc')
        ->orderBy('loan_applications.date_created', 'desc')
        ->where('loan_type.id', $loan);
    } else {
      $records = DB::table('loan_applications')
        ->select('loan_applications.*', 'loan_type.name', 'loan_type.description')
        ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
        ->where('loan_applications.member_no', getUserdetails()->member_no)
        ->orderByRaw("field(loan_applications.status,'PROCESSING','DONE','CONFIRMED','CANCELLED')")
        ->orderBy('loan_applications.loan_type', 'asc')
        ->orderBy('loan_applications.date_created', 'desc');
    }
    if (!empty($stat) && $stat != 0) {
      $records->where('loan_applications.status', $stat);
    }
    if (!empty($dt_from) && !empty($dt_to)) {
      $records->whereBetween(DB::raw('loan_applications.date_created'), array($dt_from, $dt_to));
    }

    $dataLoan = "";
    $posts = $records->get();
    if (count($posts) > 0) {
      $dataLoan .= '
      <table>
        <tr>
          <th>Date Applied</th>
          <th>Loan Application Number</th>
          <th>Loan Type</th>
          <th>Loan Status</th>
        </tr>
      ';
      foreach ($posts as $r) {
        $dataLoan .= '
        <tr>
          <td>' . date("m/d/Y h:i A", strtotime($r->date_created)) . '</td>
          <td>' . $r->control_number . '</td>
          <td>' . $r->name . '</td>
          <td>' . $r->status . '</td>
        </tr>
        ';
      }
      $dataLoan .= '</table>';
    }
    header('Content-Disposition: attachment; filename=Loan Application.xls');
    header('Content-Type: application/xls');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    $query = DB::getQueryLog();
    echo ($dataLoan);
  }

  public function index_coborrower()
  {
    return view('member.loan_application.index_coborrower');
  }

  public function dataLoans(Request $request)
  {
    if (getUserdetails()->role == "SUPER_ADMIN") {
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
      $loanType  = $request->get('loanType');
      $application  = $request->get('application');
      $status  = $request->get('status');
      $dt_from  = $request->get('dt_from');
      $dt_to  = $request->get('dt_to');
      $search  = $request->get('searchValue');
      $searchMember  = $request->get('searchMember');

      $records = DB::table('loan_applications')
        ->select('loan_applications.*', 'loan_type.name', 'loan_type.description', DB::raw('CONCAT(users.last_name, ", ", users.first_name," ", users.middle_name) AS full_name'), 'loan_applications_peb.type as application_type', 'campus.name as campus')
        ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
        ->leftjoin('loan_applications_peb', 'loan_applications.id', 'loan_applications_peb.loan_app_id')
        ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
        ->leftjoin('campus', 'member.campus_id', 'campus.id')
        ->leftjoin('users', 'member.user_id', 'users.id')
        ->where('loan_applications.not_archived', 1)
        ->where('member.member_no', 'like', '%' . $search . '%')
        ->where('users.last_name', 'like', '%' . $search . '%');

      ## Add custom filter conditions
      if (!empty($campus)) {
        $records->where('member.campus_id', $campus);
      }
      if (!empty($loanType)) {
        $records->where('loan_applications.loan_type', $loanType);
      }
      if (!empty($application)) {
        $records->where('loan_applications_peb.type', $application);
      }
      if (!empty($status)) {
        $records->where('loan_applications.status', $status);
      }
      if (!empty($search)) {
        $records->orWhere('users.first_name', 'like', '%' . $search . '%');
        $records->orWhere('users.last_name', 'like', '%' . $search . '%');
      }
      if (!empty($searchMember)) {
        $records->where('loan_applications.member_no', 'like', '%' . $searchMember . '%');
      }
      if (!empty($dt_from) && !empty($dt_to)) {
        $records->whereBetween(DB::raw('DATE(loan_applications.date_created)'), array($dt_from, $dt_to));
      }
      $totalRecords = $records->count();

      // Total records with filter
      $records = DB::table('loan_applications')
        ->select('loan_applications.*', 'loan_type.name', 'loan_type.description', DB::raw('CONCAT(users.last_name, ", ", users.first_name," ", users.middle_name) AS full_name'), 'loan_applications_peb.type as application_type', 'campus.name as campus')
        ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
        ->leftjoin('loan_applications_peb', 'loan_applications.id', 'loan_applications_peb.loan_app_id')
        ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
        ->leftjoin('campus', 'member.campus_id', 'campus.id')
        ->leftjoin('users', 'member.user_id', 'users.id')
        ->where('loan_applications.not_archived', 1)
        ->where('member.member_no', 'like', '%' . $search . '%')
        ->where('users.last_name', 'like', '%' . $search . '%');

      ## Add custom filter conditions
      if (!empty($campus)) {
        $records->where('member.campus_id', $campus);
      }
      if (!empty($loanType)) {
        $records->where('loan_applications.loan_type', $loanType);
      }
      if (!empty($application)) {
        $records->where('loan_applications_peb.type', $application);
      }
      if (!empty($status)) {
        $records->where('loan_applications.status', $status);
      }
      if (!empty($search)) {
        $records->orWhere('users.first_name', 'like', '%' . $search . '%');
        $records->orWhere('users.last_name', 'like', '%' . $search . '%');
      }
      if (!empty($searchMember)) {
        $records->where('loan_applications.member_no', 'like', '%' . $searchMember . '%');
      }
      if (!empty($dt_from) && !empty($dt_to)) {
        $records->whereBetween(DB::raw('DATE(loan_applications.date_created)'), array($dt_from, $dt_to));
      }
      $totalRecordswithFilter = $records->count();

      // Fetch records
      $records = DB::table('loan_applications')
        ->select('loan_applications.*', 'loan_type.name', 'loan_type.description', DB::raw('CONCAT(users.last_name, ", ", users.first_name," ", users.middle_name) AS full_name'), 'loan_applications_peb.type as application_type', 'campus.name as campus')
        ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
        ->leftjoin('loan_applications_peb', 'loan_applications.id', 'loan_applications_peb.loan_app_id')
        ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
        ->leftjoin('campus', 'member.campus_id', 'campus.id')
        ->leftjoin('users', 'member.user_id', 'users.id')
        ->where('loan_applications.not_archived', 1)
        ->where('member.member_no', 'like', '%' . $search . '%')
        ->where('users.last_name', 'like', '%' . $search . '%');

      ## Add custom filter conditions
      if (!empty($campus)) {
        $records->where('member.campus_id', $campus);
      }
      if (!empty($loanType)) {
        $records->where('loan_applications.loan_type', $loanType);
      }
      if (!empty($application)) {
        $records->where('loan_applications_peb.type', $application);
      }
      if (!empty($status)) {
        $records->where('loan_applications.status', $status);
      }
      if (!empty($search)) {
        $records->orWhere('users.first_name', 'like', '%' . $search . '%');
        $records->orWhere('users.last_name', 'like', '%' . $search . '%');
      }
      if (!empty($searchMember)) {
        $records->where('loan_applications.member_no', 'like', '%' . $searchMember . '%');
      }
      if (!empty($dt_from) && !empty($dt_to)) {
        $records->whereBetween(DB::raw('DATE(loan_applications.date_created)'), array($dt_from, $dt_to));
      }
    } else {
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
      $loanType  = $request->get('loanType');
      $dt_from  = $request->get('dt_from');
      $dt_to  = $request->get('dt_to');
      $search  = $request->get('searchValue');
      $searchMember  = $request->get('searchMember');

      $records = DB::table('loan_applications')
        ->select('loan_applications.*', 'loan_type.name', 'loan_type.description', DB::raw('CONCAT(users.last_name, ", ", users.first_name," ", users.middle_name) AS full_name'), 'loan_applications_peb.type as application_type', 'campus.name as campus')
        ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
        ->leftjoin('loan_applications_peb', 'loan_applications.id', 'loan_applications_peb.loan_app_id')
        ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
        ->leftjoin('campus', 'member.campus_id', 'campus.id')
        ->leftjoin('users', 'member.user_id', 'users.id')
        ->where('campus.cluster_id', '=', getUserdetails()->cluster_id)
        ->where('loan_applications.not_archived', 1)
        ->where('member.member_no', 'like', '%' . $search . '%')
        ->where('users.last_name', 'like', '%' . $search . '%');

      ## Add custom filter conditions
      if (!empty($campus)) {
        $records->where('member.campus_id', $campus);
      }
      if (!empty($loanType)) {
        $records->where('loan_applications.loan_type', $loanType);
      }
      if (!empty($application)) {
        $records->where('loan_applications_peb.type', $application);
      }
      if (!empty($status)) {
        $records->where('loan_applications.status', $status);
      }
      if (!empty($dt_from) && !empty($dt_to)) {
        $records->whereBetween(DB::raw('DATE(loan_applications.date_created)'), array($dt_from, $dt_to));
      }
      //Search Box
      if (!empty($search)) {
        $records->orWhere('users.first_name', 'like', '%' . $search . '%');
        $records->orWhere('users.last_name', 'like', '%' . $search . '%');
      }
      if (!empty($searchMember)) {
        $records->where('loan_applications.member_no', 'like', '%' . $searchMember . '%');
      }
      $totalRecords = $records->count();

      // Total records with filter
      $records = DB::table('loan_applications')
        ->select('loan_applications.*', 'loan_type.name', 'loan_type.description', DB::raw('CONCAT(users.last_name, ", ", users.first_name," ", users.middle_name) AS full_name'), 'loan_applications_peb.type as application_type', 'campus.name as campus')
        ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
        ->leftjoin('loan_applications_peb', 'loan_applications.id', 'loan_applications_peb.loan_app_id')
        ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
        ->leftjoin('campus', 'member.campus_id', 'campus.id')
        ->leftjoin('users', 'member.user_id', 'users.id')
        ->where('campus.cluster_id', '=', getUserdetails()->cluster_id)
        ->where('loan_applications.not_archived', 1)
        ->where('member.member_no', 'like', '%' . $search . '%')
        ->where('users.last_name', 'like', '%' . $search . '%');

      ## Add custom filter conditions
      if (!empty($campus)) {
        $records->where('member.campus_id', $campus);
      }
      if (!empty($loanType)) {
        $records->where('loan_applications.loan_type', $loanType);
      }
      if (!empty($application)) {
        $records->where('loan_applications_peb.type', $application);
      }
      if (!empty($status)) {
        $records->where('loan_applications.status', $status);
      }
      if (!empty($dt_from) && !empty($dt_to)) {
        $records->whereBetween(DB::raw('DATE(loan_applications.date_created)'), array($dt_from, $dt_to));
      }
      //Search Box
      if (!empty($search)) {
        $records->orWhere('users.first_name', 'like', '%' . $search . '%');
        $records->orWhere('users.last_name', 'like', '%' . $search . '%');
      }
      if (!empty($searchMember)) {
        $records->where('loan_applications.member_no', 'like', '%' . $searchMember . '%');
      }
      $totalRecordswithFilter = $records->count();

      // Fetch records
      $records = DB::table('loan_applications')
        ->select('loan_applications.*', 'loan_type.name', 'loan_type.description', DB::raw('CONCAT(users.last_name, ", ", users.first_name," ", users.middle_name) AS full_name'), 'loan_applications_peb.type as application_type', 'campus.name as campus')
        ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
        ->leftjoin('loan_applications_peb', 'loan_applications.id', 'loan_applications_peb.loan_app_id')
        ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
        ->leftjoin('campus', 'member.campus_id', 'campus.id')
        ->leftjoin('users', 'member.user_id', 'users.id')
        ->where('campus.cluster_id', '=', getUserdetails()->cluster_id)
        ->where('loan_applications.not_archived', 1)
        ->where('member.member_no', 'like', '%' . $search . '%')
        ->where('users.last_name', 'like', '%' . $search . '%');

      ## Add custom filter conditions
      if (!empty($campus)) {
        $records->where('member.campus_id', $campus);
      }
      if (!empty($loanType)) {
        $records->where('loan_applications.loan_type', $loanType);
      }
      if (!empty($application)) {
        $records->where('loan_applications_peb.type', $application);
      }
      if (!empty($status)) {
        $records->where('loan_applications.status', $status);
      }
      if (!empty($dt_from) && !empty($dt_to)) {
        $records->whereBetween(DB::raw('DATE(loan_applications.date_created)'), array($dt_from, $dt_to));
      }
      //Search Box
      if (!empty($search)) {
        $records->orWhere('users.first_name', 'like', '%' . $search . '%');
        $records->orWhere('users.last_name', 'like', '%' . $search . '%');
      }
      if (!empty($searchMember)) {
        $records->where('loan_applications.member_no', 'like', '%' . $searchMember . '%');
      }
    }

    $posts = $records->skip($start)
      ->take($rowperpage)
      ->get();
    $data = array();
    if ($posts) {
      foreach ($posts as $loan) {
        $start++;
        $row = array();

        $row[] = "<a data-md-tooltip='View Details' class='view_details md-tooltip--right' id='" . $loan->id . "' style='cursor: pointer'>
                  <i class='mp-icon md-tooltip--right icon-book-open mp-text-c-primary mp-text-fs-large'></i>
                </a>";
        $row[] = date("m/d/Y h:i A", strtotime($loan->date_created));
        $row[] = $loan->member_no;
        $row[] = $loan->control_number;
        $row[] = $loan->full_name;
        $row[] = $loan->campus;
        $row[] = $loan->name;
        $row[] = $loan->application_type;

        switch ($loan->status) {
          case 'SUBMITTED':
            $row[] = '<span class="mp-text-center" style="color:#feb236;">' . $loan->status . '</span>';
            break;
          case 'PROCESSING':
            $row[] = '<span class="mp-text-center" style="color:#82b74b;">' . $loan->status . '</span>';
            break;
          case 'DONE':
            $row[] = '<span class="mp-text-center" style="color:#034f84;">' . $loan->status . '</span>';
            break;
          case 'CANCELLED':
            $row[] = '<span class="mp-text-center" style="color:#d64161;">' . $loan->status . '</span>';
            break;
          default:
            $row[] = '<span class="mp-text-center" style="color:#894168;">' . $loan->status . '</span>';
            break;
        }

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

  public function export_loanapplication($camp_id, $loan_id, $dt_from, $dt_to, $app, $stat)
  {
    DB::enableQueryLog();
    if (!empty($camp_id) && $camp_id != 0) {
      $records = DB::table('loan_applications')
        ->select('loan_applications.*', 'loan_type.name', 'loan_type.description', DB::raw('CONCAT(users.last_name, ", ", users.first_name," ", users.middle_name) AS full_name'), 'loan_applications_peb.type as application_type', 'campus.name as campus')
        ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
        ->leftjoin('loan_applications_peb', 'loan_applications.id', 'loan_applications_peb.loan_app_id')
        ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
        ->leftjoin('campus', 'member.campus_id', 'campus.id')
        ->leftjoin('users', 'member.user_id', 'users.id')
        ->where('loan_applications.not_archived', 1)
        ->where('member.campus_id', $camp_id);
    } else {
      $records = DB::table('loan_applications')
        ->select('loan_applications.*', 'loan_type.name', 'loan_type.description', DB::raw('CONCAT(users.last_name, ", ", users.first_name," ", users.middle_name) AS full_name'), 'loan_applications_peb.type as application_type', 'campus.name as campus')
        ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
        ->leftjoin('loan_applications_peb', 'loan_applications.id', 'loan_applications_peb.loan_app_id')
        ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
        ->leftjoin('campus', 'member.campus_id', 'campus.id')
        ->leftjoin('users', 'member.user_id', 'users.id')
        ->where('loan_applications.not_archived', 1);
    }
    if (!empty($loan_id) && $loan_id != 0) {
      $records->where('loan_applications.loan_type', $loan_id);
    }
    if (!empty($dt_from) && !empty($dt_to) && $dt_from != 0 && $dt_to != 0) {
      $records->whereBetween(DB::raw('DATE(loan_applications.date_created)'), array($dt_from, $dt_to));
    }
    if (!empty($app) && $app != 'noData') {
      $records->where('loan_applications_peb.type', $app);
    }
    if (!empty($stat) && $stat != 'noData') {
      $records->where('loan_applications.status', $stat);
    }

    $loanData = "";
    $posts = $records->get();
    if (count($posts) > 0) {
      $loanData .= '
      <table>
        <tr>
          <th>Date Applied</th>
          <th>Member ID</th>
          <th>Loan Application Number</th>
          <th>Member Name</th>
          <th>Campus</th>
          <th>Loan Type</th>
          <th>Application Type</th>
          <th>Loan Status</th>
        </tr>
      ';
      foreach ($posts as $loan) {
        $loanData .= '
        <tr>
          <td>' . date("m/d/Y h:i A", strtotime($loan->date_created)) . '</td>
          <td>' . $loan->member_no . '</td>
          <td>' . $loan->control_number . '</td>
          <td>' . $loan->full_name . '</td>
          <td>' . $loan->campus . '</td>
          <td>' . $loan->name . '</td>
          <td>' . $loan->application_type . '</td>
          <td>' . $loan->status . '</td>
        </tr>
        ';
      }
      $loanData .= '</table>';
    }

    header('Content-Disposition: attachment; filename=Loan Applications list.xls');
    header('Content-Type: application/xls');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    $query = DB::getQueryLog();
    echo ($loanData);
  }

  public function admin_index()
  {
    $campuses = Campus::all();
    $LoanType = DB::table('loan_type')->get();
    $application = DB::table('loan_applications_peb')
      ->select('type')
      ->groupBy('type')
      ->get();
    $status = DB::table('loan_applications')
      ->select('status')
      ->groupBy('status')
      ->get();

    $data = array(
      'campuses' => $campuses,
      'LoanType' => $LoanType,
      'application' => $application,
      'status' => $status,
    );
    // return view('admin.loan_application.index',array('loans'=>$loans));
    return view('admin.loan_application.index')->with($data);
  }

  public function new_loan()
  {

    //return view('admin.dashboard',array('campuses'=>$campuses,'totalmembers'=>$memberscount,'totalloansgranted'=>$totalloansgranted,'campusmembers'=>$campusmembers,'contributions'=>$contributions,'totalequity'=>$totalequity,'activecampus'=>$activecampus));
    $member = DB::table('contribution_transaction')->select(DB::raw('SUM(contribution_transaction.amount) as total'))
      ->leftjoin('contribution', 'contribution_transaction.contribution_id', 'contribution.id')
      ->where('contribution.member_id', '=', getUserdetails()->member_id)
      ->first();

    $appointment_date = DB::table('member')->select('original_appointment_date')
      ->where('member.id', '=', getUserdetails()->member_id)
      ->first();
    $ap = strtotime($appointment_date->original_appointment_date);
    $ad = date('Y-m-d G:i:s', $ap);
    $date = new \DateTime($ad);
    $now = new \DateTime();
    $year = $date->diff($now)->format("%y");

    $pel_count = DB::table('loan_applications')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->where('loan_applications.member_no', getUserdetails()->member_no)
      ->where('loan_type.id', 1)
      ->where('loan_applications.status', 'PROCESSING')
      ->count();

    $bl_count = DB::table('loan_applications')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->where('loan_applications.member_no', getUserdetails()->member_no)
      ->where('loan_type.id', 2)
      ->where('loan_applications.status', 'PROCESSING')
      ->count();

    $eml_count = DB::table('loan_applications')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->where('loan_applications.member_no', getUserdetails()->member_no)
      ->where('loan_type.id', 3)
      ->where('loan_applications.status', 'PROCESSING')
      ->count();

    $cbl_count = DB::table('loan_applications')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->where('loan_applications.member_no', getUserdetails()->member_no)
      ->where('loan_type.id', 4)
      ->where('loan_applications.status', 'PROCESSING')
      ->count();

    $btl_count = DB::table('loan_applications')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->where('loan_applications.member_no', getUserdetails()->member_no)
      ->where('loan_type.id', 5)
      ->where('loan_applications.status', 'PROCESSING')
      ->count();



    return view('member.loan_application.new_loan', array('equity' => $member, 'years' => $year, 'pel_count' => $pel_count, 'bl_count' => $bl_count, 'eml_count' => $eml_count, 'cbl_count' => $cbl_count, 'btl_count' => $btl_count));
  }



  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function view_loan($id)
  {
    // return abort(404);

    $loan = DB::table('loan_applications')
      ->select('loan_applications.*', 'loan_type.name', 'loan_type.description')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->where('loan_applications.member_no', getUserdetails()->member_no)
      ->where('loan_applications.id', $id)
      ->first();

    if ($loan) {
      if ($loan->name == 'PEL' || $loan->name == 'EML' || $loan->name == 'BL') {
        $loan_details = DB::table('loan_applications_peb')
          ->where('loan_app_id', $loan->id)
          ->first();

        $loan_ded = DB::table('loan_applications_deductions')
          ->where('loan_app_id', $loan->id)
          ->get();
      } elseif ($loan->name == 'CBL') {
        # code...
      } elseif ($loan->name == 'BL') {
        # code...
      }
    } else {
      return abort(404);
    }



    return view('member.loan_application.loan_details', array('loan' => $loan, 'loan_details' => $loan_details, 'loan_ded' => $loan_ded));
  }


  public function confirm_agree($id)
  {
    DB::table('loan_applications')
      ->where('id', $id)
      ->update(
        ['status' => 'CONFIRMED', 'date_closed' => date('Y-m-d H:i:s')]
      );

    $loan = DB::table('loan_applications')
      ->select('loan_applications.*', 'loan_type.name', 'loan_type.description')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->where('loan_applications.id', $id)
      ->first();

    $cluster = DB::table('cluster_email')
      ->where('cluster_id', getUserdetails()->cluster_id)
      ->first();
    $cluster_email = $cluster->email;

    $name = getUserdetails()->first_name . ' ' . getUserdetails()->last_name;

    Mail::send('emailTemplates.loanConfirmed', ['member' => $name, 'member_no' => getUserdetails()->member_no, 'loan_type' => $loan->description, 'control_number' => $loan->control_number], function ($message) use ($cluster_email) {
      $message->subject('Member Confirmed Loan Application');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($cluster_email);
    });

    return redirect('member/loan-app')
      ->with('success', 'Loan Application Successfully Confirmed');
  }

  public function app_cancel($id)
  {
    DB::table('loan_applications')
      ->where('id', $id)
      ->update(
        ['status' => 'CANCELLED', 'cancellation_reason' => 'Member Cancellation', 'date_closed' => date('Y-m-d H:i:s')]
      );

    $loan = DB::table('loan_applications')
      ->select('loan_applications.*', 'loan_type.name', 'loan_type.description')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->where('loan_applications.id', $id)
      ->first();

    $cluster = DB::table('cluster_email')
      ->where('cluster_id', getUserdetails()->cluster_id)
      ->first();
    $cluster_email = $cluster->email;

    $name = getUserdetails()->first_name . ' ' . getUserdetails()->last_name;

    Mail::send('emailTemplates.loanMembercancel', ['member' => $name, 'member_no' => getUserdetails()->member_no, 'loan_type' => $loan->description, 'control_number' => $loan->control_number], function ($message) use ($cluster_email) {
      $message->subject('Member Cancel Loan Application');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($cluster_email);
    });

    return redirect('member/loan-app')
      ->with('success', 'Loan Application Successfully Cancelled');
  }



  public function edit_loan_form($id)
  {

    if (getUserdetails()->role == "SUPER_ADMIN") {
      $loan = DB::table('loan_applications')
        ->select('loan_applications.*', 'loan_type.name', 'loan_type.description', DB::raw('CONCAT(users.last_name,", ",users.first_name) AS full_name'))
        ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
        ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
        ->leftjoin('users', 'member.user_id', 'users.id')
        ->where('loan_applications.id', $id)
        ->first();
    } else {
      $loan = DB::table('loan_applications')
        ->select('loan_applications.*', 'loan_type.name', 'loan_type.description', DB::raw('CONCAT(users.last_name,", ",users.first_name) AS full_name'))
        ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
        ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
        ->leftjoin('users', 'member.user_id', 'users.id')
        ->leftjoin('campus', 'member.campus_id', 'campus.id')
        ->where('campus.cluster_id', '=', getUserdetails()->cluster_id)
        ->where('loan_applications.id', $id)
        ->first();
    }



    if ($loan) {

      $loan_details = DB::table('loan_applications_peb')
        ->where('loan_app_id', $loan->id)
        ->first();

      $loan_ded = DB::table('loan_applications_deductions')
        ->where('loan_app_id', $loan->id)
        ->get();
    } else {
      return abort(404);
    }

    $deduction = array();
    foreach ($loan_ded as $ded) {
      $deduction[$ded->description] = $ded->amount;
    }

    $fix_deductions = array("Service Fee", "Outstanding Loan - Principal(PEL)", "Outstanding Loan - Principal(BL)", "Outstanding Loan - Principal(EML)", "Outstanding Loan - Principal(CBL)", "Interest - PEL", "Interest - BL", "Interest - EML", "Interest - CBL", "Surcharge");

    $i = 1;
    $other = array();
    foreach ($loan_ded as $ded) {
      if (!in_array($ded->description, $fix_deductions)) {
        $other['other' . $i] = ['description' => $ded->description, 'amount' => $ded->amount];
        $i++;
      }
    }

    $loan_type = DB::table('loan_type')->get();



    return view('admin.loan_application.edit_loan_details', array('loan' => $loan, 'loan_details' => $loan_details, 'loan_ded' => $deduction, 'other' => $other, 'loan_type' => $loan_type));
  }

  public function admin_peb_details($id)
  {

    if (getUserdetails()->role == "SUPER_ADMIN") {
      $loan = DB::table('loan_applications')
        ->select('loan_applications.*', 'loan_type.name', 'loan_type.description', DB::raw('CONCAT(users.last_name,", ",users.first_name) AS full_name'))
        ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
        ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
        ->leftjoin('users', 'member.user_id', 'users.id')
        ->where('loan_applications.id', $id)
        ->first();
    } else {
      $loan = DB::table('loan_applications')
        ->select('loan_applications.*', 'loan_type.name', 'loan_type.description', DB::raw('CONCAT(users.last_name,", ",users.first_name) AS full_name'))
        ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
        ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
        ->leftjoin('users', 'member.user_id', 'users.id')
        ->leftjoin('campus', 'member.campus_id', 'campus.id')
        ->where('campus.cluster_id', '=', getUserdetails()->cluster_id)
        ->where('loan_applications.id', $id)
        ->first();
    }



    if ($loan) {

      $loan_details = DB::table('loan_applications_peb')
        ->where('loan_app_id', $loan->id)
        ->first();

      $loan_ded = DB::table('loan_applications_deductions')
        ->where('loan_app_id', $loan->id)
        ->get();
    } else {
      return abort(404);
    }

    $loan_type = DB::table('loan_type')->get();


    return view('admin.loan_application.loan_details', array('loan' => $loan, 'loan_details' => $loan_details, 'loan_ded' => $loan_ded, 'loan_type' => $loan_type));
  }


  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function pel_loan_new(Request $request)
  {
    // dd(getUserdetails());
    //       dd($request->all());
    $current_year = date('Y');
    $control = DB::table('loan_app_series')->where('loan_type', 1)->first();
    if ($control->year == $current_year) {
      $current_counter = $control->current_counter + 1;
      $counter_digits = str_pad($current_counter, $control->counter_length, '0', STR_PAD_LEFT);
      $control_number = $control->loan_type_code . '-' . $control->year . '-' . date('m') . '-' . $counter_digits;
      DB::table('loan_app_series')
        ->where('loan_type', 1)
        ->update(['current_last' => $control_number, 'current_counter' => $current_counter]);
    } else {
      $year = $current_year;
      $current_counter = 0 + 1;
      $counter_digits = str_pad($current_counter, $control->counter_length, '0', STR_PAD_LEFT);
      $control_number = $control->loan_type_code . '-' . $year . '-' . date('m') . '-' . $counter_digits;
      DB::table('loan_app_series')
        ->where('loan_type', 1)
        ->update(['year' => $year, 'current_last' => $control_number, 'current_counter' => $current_counter]);
    }

    if ($request->update_profile) {
      DB::table('users')
        ->where('id', getUserdetails()->user_id)
        ->update(['email' => $request->email, 'contact_no' => $request->c_number]);
    }

    $loanapp_id = DB::table('loan_applications')->insertGetId(
      ['member_no' => getUserdetails()->member_no, 'loan_type' => 1, 'control_number' => $control_number, 'active_email' => $request->email, 'active_number' => $request->c_number, 'status' => 'PROCESSING']
    );

    $loandet_id = DB::table('loan_applications_peb')->insertGetId(
      ['bank' => $request->bank, 'loan_app_id' => $loanapp_id, 'account_number' => $request->acc_num, 'account_name' => $request->acc_name, 'type' => 'NEW', 'amount' => $request->amount]
    );


    $target_dir = public_path() . "/storage/app/loan_application/";
    $target_file = $target_dir . basename($_FILES["identification"]["name"]);
    $temp = $_FILES["identification"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $uploadOk = 1;


    $newfilename = 'id-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["identification"]["tmp_name"], $target_dir . $newfilename);


    $identification = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['p_id' => $identification]
      );

    $temp = $_FILES["payslip1"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $newfilename = 'payslip1-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["payslip1"]["tmp_name"], $target_dir . $newfilename);


    $payslip1 = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['payslip_1' => $payslip1]
      );


    $temp = $_FILES["payslip2"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $newfilename = 'payslip2-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["payslip2"]["tmp_name"], $target_dir . $newfilename);



    $payslip2 = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['payslip_2' => $payslip2]
      );

    $temp = $_FILES["passbook"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $newfilename = 'passbook-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["passbook"]["tmp_name"], $target_dir . $newfilename);



    $passbook = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['atm_passbook' => $passbook]
      );

    $this->add_log($loanapp_id, $control_number, 'PEL APPLICATION SUBMITTED', NULL, NUll, 1, 0, getUserdetails()->user_id);

    $data['loan_type'] = 'Personal Equity Loan';
    $data['control_number'] = $control_number;

    $emailadd = getUserdetails()->email;

    $name = getUserdetails()->first_name . ' ' . getUserdetails()->last_name;

    Mail::send('emailTemplates.loanSuccess', ['firstName' => $name, 'loan_type' => 'Personal Equity Loan', 'control_number' => $control_number], function ($message) use ($emailadd) {
      $message->subject('Loan Application Processing ');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($emailadd);
    });

    $cluster = DB::table('cluster_email')
      ->where('cluster_id', getUserdetails()->cluster_id)
      ->first();
    $cluster_email = $cluster->email;

    $name = getUserdetails()->first_name . ' ' . getUserdetails()->last_name;

    Mail::send('emailTemplates.loanAdminnotif', ['member' => $name, 'member_no' => getUserdetails()->member_no, 'loan_type' => 'Personal Equity Loan', 'control_number' => $control_number], function ($message) use ($cluster_email) {
      $message->subject('New Loan Application');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($cluster_email);
    });

    return view('member.loan_application.loan_success', array('loan_type' => 'Personal Equity Loan', 'control_number' => $control_number));
  }

  public function pel_loan_renew(Request $request)
  {

    $current_year = date('Y');
    $control = DB::table('loan_app_series')->where('loan_type', 1)->first();
    if ($control->year == $current_year) {
      $current_counter = $control->current_counter + 1;
      $counter_digits = str_pad($current_counter, $control->counter_length, '0', STR_PAD_LEFT);
      $control_number = $control->loan_type_code . '-' . $control->year . '-' . date('m') . '-' . $counter_digits;
      DB::table('loan_app_series')
        ->where('loan_type', 1)
        ->update(['current_last' => $control_number, 'current_counter' => $current_counter]);
    } else {
      $year = $current_year;
      $current_counter = 0 + 1;
      $counter_digits = str_pad($current_counter, $control->counter_length, '0', STR_PAD_LEFT);
      $control_number = $control->loan_type_code . '-' . $year . '-' . date('m') . '-' . $counter_digits;
      DB::table('loan_app_series')
        ->where('loan_type', 1)
        ->update(['year' => $year, 'current_last' => $control_number, 'current_counter' => $current_counter]);
    }
    if ($request->update_profile) {
      DB::table('users')
        ->where('id', getUserdetails()->user_id)
        ->update(['email' => $request->email, 'contact_no' => $request->c_number]);
    }

    // dd($request->all());
    $loanapp_id = DB::table('loan_applications')->insertGetId(
      ['member_no' => getUserdetails()->member_no, 'loan_type' => 1, 'control_number' => $control_number, 'active_email' => $request->email, 'active_number' => $request->c_number, 'status' => 'PROCESSING']
    );

    $loandet_id = DB::table('loan_applications_peb')->insertGetId(
      ['bank' => $request->bank, 'loan_app_id' => $loanapp_id, 'account_number' => $request->acc_num, 'account_name' => $request->acc_name, 'type' => 'RENEW', 'renewal_type' => $request->renewal_option]
    );

    $target_dir = public_path() . "/storage/app/loan_application/";
    $target_file = $target_dir . basename($_FILES["identification"]["name"]);
    $temp = $_FILES["identification"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $uploadOk = 1;


    $newfilename = 'id-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["identification"]["tmp_name"], $target_dir . $newfilename);


    $identification = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['p_id' => $identification]
      );


    $temp = $_FILES["payslip1"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $newfilename = 'payslip1-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["payslip1"]["tmp_name"], $target_dir . $newfilename);


    $payslip1 = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['payslip_1' => $payslip1]
      );


    $temp = $_FILES["payslip2"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $newfilename = 'payslip2-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["payslip2"]["tmp_name"], $target_dir . $newfilename);



    $payslip2 = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['payslip_2' => $payslip2]
      );

    $temp = $_FILES["passbook"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $newfilename = 'passbook-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["passbook"]["tmp_name"], $target_dir . $newfilename);

    $passbook = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['atm_passbook' => $passbook]
      );

    $this->add_log($loanapp_id, $control_number, 'PEL APPLICATION SUBMITTED', NULL, NUll, 1, 0, getUserdetails()->user_id);

    $data['loan_type'] = 'Personal Equity Loan';
    $data['control_number'] = $control_number;

    $emailadd = getUserdetails()->email;

    $name = getUserdetails()->first_name . ' ' . getUserdetails()->last_name;

    Mail::send('emailTemplates.loanSuccess', ['firstName' => $name, 'loan_type' => 'Personal Equity Loan', 'control_number' => $control_number], function ($message) use ($emailadd) {
      $message->subject('Loan Application Received');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($emailadd);
    });

    $cluster = DB::table('cluster_email')
      ->where('cluster_id', getUserdetails()->cluster_id)
      ->first();
    $cluster_email = $cluster->email;

    $name = getUserdetails()->first_name . ' ' . getUserdetails()->last_name;

    Mail::send('emailTemplates.loanAdminnotif', ['member' => $name, 'member_no' => getUserdetails()->member_no, 'loan_type' => 'Personal Equity Loan', 'control_number' => $control_number], function ($message) use ($cluster_email) {
      $message->subject('New Loan Application');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($cluster_email);
    });

    return view('member.loan_application.loan_success', array('loan_type' => 'Personal Equity Loan', 'control_number' => $control_number));
  }

  public function eml_loan_new(Request $request)
  {
    $current_year = date('Y');
    $control = DB::table('loan_app_series')->where('loan_type', 3)->first();
    if ($control->year == $current_year) {
      $current_counter = $control->current_counter + 1;
      $counter_digits = str_pad($current_counter, $control->counter_length, '0', STR_PAD_LEFT);
      $control_number = $control->loan_type_code . '-' . $control->year . '-' . date('m') . '-' . $counter_digits;
      DB::table('loan_app_series')
        ->where('loan_type', 3)
        ->update(['current_last' => $control_number, 'current_counter' => $current_counter]);
    } else {
      $year = $current_year;
      $current_counter = 0 + 1;
      $counter_digits = str_pad($current_counter, $control->counter_length, '0', STR_PAD_LEFT);
      $control_number = $control->loan_type_code . '-' . $year . '-' . date('m') . '-' . $counter_digits;
      DB::table('loan_app_series')
        ->where('loan_type', 3)
        ->update(['year' => $year, 'current_last' => $control_number, 'current_counter' => $current_counter]);
    }
    if ($request->update_profile) {
      DB::table('users')
        ->where('id', getUserdetails()->user_id)
        ->update(['email' => $request->email, 'contact_no' => $request->c_number]);
    }

    $loanapp_id = DB::table('loan_applications')->insertGetId(
      ['member_no' => getUserdetails()->member_no, 'loan_type' => 3, 'control_number' => $control_number, 'active_email' => $request->email, 'active_number' => $request->c_number, 'status' => 'PROCESSING']
    );

    $loandet_id = DB::table('loan_applications_peb')->insertGetId(
      ['bank' => $request->bank, 'loan_app_id' => $loanapp_id, 'account_number' => $request->acc_num, 'account_name' => $request->acc_name, 'type' => 'NEW', 'amount' => $request->amount]
    );


    $target_dir = public_path() . "/storage/app/loan_application/";
    $target_file = $target_dir . basename($_FILES["identification"]["name"]);
    $temp = $_FILES["identification"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $uploadOk = 1;


    $newfilename = 'id-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["identification"]["tmp_name"], $target_dir . $newfilename);


    $identification = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['p_id' => $identification]
      );


    $temp = $_FILES["payslip1"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $newfilename = 'payslip1-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["payslip1"]["tmp_name"], $target_dir . $newfilename);


    $payslip1 = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['payslip_1' => $payslip1]
      );


    $temp = $_FILES["payslip2"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $newfilename = 'payslip2-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["payslip2"]["tmp_name"], $target_dir . $newfilename);



    $payslip2 = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['payslip_2' => $payslip2]
      );

    $temp = $_FILES["passbook"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $newfilename = 'passbook-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["passbook"]["tmp_name"], $target_dir . $newfilename);

    $passbook = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['atm_passbook' => $passbook]
      );

    $this->add_log($loanapp_id, $control_number, 'EML APPLICATION SUBMITTED', NULL, NUll, 1, 0, getUserdetails()->user_id);


    $data['loan_type'] = 'Emergency Loan';
    $data['control_number'] = $control_number;

    $emailadd = getUserdetails()->email;

    $name = getUserdetails()->first_name . ' ' . getUserdetails()->last_name;

    Mail::send('emailTemplates.loanSuccess', ['firstName' => $name, 'loan_type' => 'Emergency Loan', 'control_number' => $control_number], function ($message) use ($emailadd) {
      $message->subject('Loan Application Received');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($emailadd);
    });

    $cluster = DB::table('cluster_email')
      ->where('cluster_id', getUserdetails()->cluster_id)
      ->first();
    $cluster_email = $cluster->email;

    $name = getUserdetails()->first_name . ' ' . getUserdetails()->last_name;

    Mail::send('emailTemplates.loanAdminnotif', ['member' => $name, 'member_no' => getUserdetails()->member_no, 'loan_type' => 'Emergency Loan', 'control_number' => $control_number], function ($message) use ($cluster_email) {
      $message->subject('New Loan Application');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($cluster_email);
    });

    return view('member.loan_application.loan_success', array('loan_type' => 'Emergency Loan', 'control_number' => $control_number));
  }

  // public function eml_loan_renew(Request $request)
  // {
  //   $loanapp_id=DB::table('loan_applications')->insertGetId(
  //     ['member_no' => getUserdetails()->member_no, 'loan_type' => 3, 'status' => 'PROCESSING']);

  //   $loandet_id=DB::table('loan_applications_peb')->insertGetId(
  //     ['bank' => $request->bank, 'loan_app_id' => $loanapp_id, 'account_number' => $request->acc_num, 'account_name' => $request->acc_name, 'type' => 'RENEW', 'renewal_type'=>$request->renewal_option]);

  //   $target_dir = public_path()."/storage/app/loan_application/";
  //   $target_file = $target_dir . basename($_FILES["identification"]["name"]);
  //   $temp = $_FILES["identification"]["name"];
  //   $file_ext = substr($temp, strripos($temp, '.'));
  //   $uploadOk = 1;


  //   $newfilename = 'id-'.$loanapp_id.$file_ext;
  //   $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  //   move_uploaded_file($_FILES["identification"]["tmp_name"], $target_dir.$newfilename);


  //   $identification=$newfilename;

  //   DB::table('loan_applications_peb')
  //   ->where('id', $loandet_id)
  //   ->update(
  //     ['p_id' => $identification]);

  //   $newfilename = 'payslip1-'.$loanapp_id.$file_ext;
  //   $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  //   move_uploaded_file($_FILES["payslip1"]["tmp_name"], $target_dir.$newfilename);


  //   $payslip1=$newfilename;

  //   DB::table('loan_applications_peb')
  //   ->where('id', $loandet_id)
  //   ->update(
  //     ['payslip_1' => $payslip1]);

  //   $newfilename = 'payslip2-'.$loanapp_id.$file_ext;
  //   $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  //   move_uploaded_file($_FILES["payslip2"]["tmp_name"], $target_dir.$newfilename);



  //   $payslip2=$newfilename;

  //   DB::table('loan_applications_peb')
  //   ->where('id', $loandet_id)
  //   ->update(
  //     ['payslip_2' => $payslip2]);

  //   $newfilename = 'passbook-'.$loanapp_id.$file_ext;
  //   $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  //   move_uploaded_file($_FILES["passbook"]["tmp_name"], $target_dir.$newfilename);



  //   $passbook=$newfilename;

  //   DB::table('loan_applications_peb')
  //   ->where('id', $loandet_id)
  //   ->update(
  //     ['atm_passbook' => $passbook]);

  //   $this->add_log($loanapp_id, $control_number, 'EML APPLICATION SUBMITTED', NULL, NUll, 1, 0, getUserdetails()->user_id);

  //   $data['loan_type']='Emergency Loan';
  //   $data['control_number']=$control_number;

  //   $emailadd=$loan->email;

  //   $name=getUserdetails()->first_name.' '.getUserdetails()->last_name;

  //   Mail::send('emailTemplates.loanSuccess', ['firstName' => $name,'loan_type'=>'Emergency Loan','control_number'=>$control_number], function ($message)use ($emailadd)
  //   {
  //    $message->subject('Loan Application Processing ');

  //    $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

  //    $message->to($emailadd);

  //  });

  //   return view('member.loan_application.loan_success',array('loan_type'=>'Emergency Loan','control_number'=>$control_number));
  // }

  public function bl_loan_new(Request $request)
  {

    $current_year = date('Y');
    $control = DB::table('loan_app_series')->where('loan_type', 2)->first();
    if ($control->year == $current_year) {
      $current_counter = $control->current_counter + 1;
      $counter_digits = str_pad($current_counter, $control->counter_length, '0', STR_PAD_LEFT);
      $control_number = $control->loan_type_code . '-' . $control->year . '-' . date('m') . '-' . $counter_digits;
      DB::table('loan_app_series')
        ->where('loan_type', 2)
        ->update(['current_last' => $control_number, 'current_counter' => $current_counter]);
    } else {
      $year = $current_year;
      $current_counter = 0 + 1;
      $counter_digits = str_pad($current_counter, $control->counter_length, '0', STR_PAD_LEFT);
      $control_number = $control->loan_type_code . '-' . $year . '-' . date('m') . '-' . $counter_digits;
      DB::table('loan_app_series')
        ->where('loan_type', 2)
        ->update(['year' => $year, 'current_last' => $control_number, 'current_counter' => $current_counter]);
    }

    if ($request->update_profile) {
      DB::table('users')
        ->where('id', getUserdetails()->user_id)
        ->update(['email' => $request->email, 'contact_no' => $request->c_number]);
    }

    $loanapp_id = DB::table('loan_applications')->insertGetId(
      ['member_no' => getUserdetails()->member_no, 'loan_type' => 2, 'control_number' => $control_number, 'active_email' => $request->email, 'active_number' => $request->c_number, 'status' => 'PROCESSING']
    );

    $loandet_id = DB::table('loan_applications_peb')->insertGetId(
      ['bank' => $request->bank, 'loan_app_id' => $loanapp_id, 'account_number' => $request->acc_num, 'account_name' => $request->acc_name, 'type' => 'NEW', 'amount' => $request->amount]
    );


    $target_dir = public_path() . "/storage/app/loan_application/";
    $target_file = $target_dir . basename($_FILES["identification"]["name"]);
    $temp = $_FILES["identification"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $uploadOk = 1;


    $newfilename = 'id-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["identification"]["tmp_name"], $target_dir . $newfilename);


    $identification = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['p_id' => $identification]
      );

    $temp = $_FILES["payslip1"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $newfilename = 'payslip1-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["payslip1"]["tmp_name"], $target_dir . $newfilename);


    $payslip1 = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['payslip_1' => $payslip1]
      );


    $temp = $_FILES["payslip2"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $newfilename = 'payslip2-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["payslip2"]["tmp_name"], $target_dir . $newfilename);



    $payslip2 = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['payslip_2' => $payslip2]
      );

    $temp = $_FILES["passbook"]["name"];
    $file_ext = substr($temp, strripos($temp, '.'));
    $newfilename = 'passbook-' . $loanapp_id . $file_ext;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    move_uploaded_file($_FILES["passbook"]["tmp_name"], $target_dir . $newfilename);


    $passbook = $newfilename;

    DB::table('loan_applications_peb')
      ->where('id', $loandet_id)
      ->update(
        ['atm_passbook' => $passbook]
      );

    $this->add_log($loanapp_id, $control_number, 'BL APPLICATION SUBMITTED', NULL, NUll, 1, 0, getUserdetails()->user_id);

    $data['loan_type'] = 'Bridge Loan';
    $data['control_number'] = $control_number;

    $emailadd = getUserdetails()->email;

    $name = getUserdetails()->first_name . ' ' . getUserdetails()->last_name;

    Mail::send('emailTemplates.loanSuccess', ['firstName' => $name, 'loan_type' => 'Bridge Loan', 'control_number' => $control_number], function ($message) use ($emailadd) {
      $message->subject('Loan Application Received');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($emailadd);
    });

    $cluster = DB::table('cluster_email')
      ->where('cluster_id', getUserdetails()->cluster_id)
      ->first();
    $cluster_email = $cluster->email;

    $name = getUserdetails()->first_name . ' ' . getUserdetails()->last_name;

    Mail::send('emailTemplates.loanAdminnotif', ['member' => $name, 'member_no' => getUserdetails()->member_no, 'loan_type' => 'Bridge Loan', 'control_number' => $control_number], function ($message) use ($cluster_email) {
      $message->subject('New Loan Application');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($cluster_email);
    });

    return view('member.loan_application.loan_success', array('loan_type' => 'Bridge Loan', 'control_number' => $control_number));
  }

  // public function bl_loan_renew(Request $request)
  // {
  //   $loanapp_id=DB::table('loan_applications')->insertGetId(
  //     ['member_no' => getUserdetails()->member_no, 'loan_type' => 2, 'status' => 'PROCESSING']);

  //   $loandet_id=DB::table('loan_applications_peb')->insertGetId(
  //     ['bank' => $request->bank, 'loan_app_id' => $loanapp_id, 'account_number' => $request->acc_num, 'account_name' => $request->acc_name, 'type' => 'RENEW', 'renewal_type'=>$request->renewal_option]);

  //   $target_dir = public_path()."/storage/app/loan_application/";
  //   $target_file = $target_dir . basename($_FILES["identification"]["name"]);
  //   $temp = $_FILES["identification"]["name"];
  //   $file_ext = substr($temp, strripos($temp, '.'));
  //   $uploadOk = 1;


  //   $newfilename = 'id-'.$loanapp_id.$file_ext;
  //   $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  //   move_uploaded_file($_FILES["identification"]["tmp_name"], $target_dir.$newfilename);


  //   $identification=$newfilename;

  //   DB::table('loan_applications_peb')
  //   ->where('id', $loandet_id)
  //   ->update(
  //     ['p_id' => $identification]);

  //   $newfilename = 'payslip1-'.$loanapp_id.$file_ext;
  //   $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  //   move_uploaded_file($_FILES["payslip1"]["tmp_name"], $target_dir.$newfilename);


  //   $payslip1=$newfilename;

  //   DB::table('loan_applications_peb')
  //   ->where('id', $loandet_id)
  //   ->update(
  //     ['payslip_1' => $payslip1]);

  //   $newfilename = 'payslip2-'.$loanapp_id.$file_ext;
  //   $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  //   move_uploaded_file($_FILES["payslip2"]["tmp_name"], $target_dir.$newfilename);



  //   $payslip2=$newfilename;

  //   DB::table('loan_applications_peb')
  //   ->where('id', $loandet_id)
  //   ->update(
  //     ['payslip_2' => $payslip2]);

  //   $newfilename = 'passbook-'.$loanapp_id.$file_ext;
  //   $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  //   move_uploaded_file($_FILES["passbook"]["tmp_name"], $target_dir.$newfilename);



  //   $passbook=$newfilename;

  //   DB::table('loan_applications_peb')
  //   ->where('id', $loandet_id)
  //   ->update(
  //     ['atm_passbook' => $passbook]);

  //   $this->add_log($loanapp_id, $control_number, 'BL APPLICATION SUBMITTED', NULL, NUll, 1, 0, getUserdetails()->user_id);

  //   $data['loan_type']='Bridge Loan';
  //   $data['control_number']=$control_number;

  //   $emailadd=$loan->email;

  //   $name=getUserdetails()->first_name.' '.getUserdetails()->last_name;

  //   Mail::send('emailTemplates.loanSuccess', ['firstName' => $name,'loan_type'=>'Bridge Loan','control_number'=>$control_number], function ($message)use ($emailadd)
  //   {
  //    $message->subject('Loan Application Processing ');

  //    $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

  //    $message->to($emailadd);

  //  });

  //   return view('member.loan_application.loan_success',array('loan_type'=>'Bridge Loan','control_number'=>$control_number));


  // }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function admin_loan_processing($id)
  {

    $loan = DB::table('loan_applications')
      ->select('*')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
      ->leftjoin('users', 'member.user_id', 'users.id')
      ->where('loan_applications.id', $id)
      ->first();

    //dd(date("Y-m-d H:i:s"));
    $emailadd = $loan->email;

    $name = $loan->first_name . ' ' . $loan->last_name;

    Mail::send('emailTemplates.loanProcessing', ['firstName' => $name], function ($message) use ($emailadd) {
      $message->subject('Loan Application Processing');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($emailadd);
    });

    DB::table('loan_applications')
      ->where('id', $id)
      ->update(
        ['status' => 'PROCESSING', 'date_processed' => date('Y-m-d H:i:s'), 'processed_by' => getUserdetails()->user_id]
      );

    return redirect('admin/loan-app')
      ->with('success', 'Loan Application Successfully Updated');
  }

  public function admin_loan_closed(Request $request)
  {

    // dd($request->all());

    DB::table('loan_applications_deductions')->insert([
      ['loan_app_id' => $request->loan_app_id, 'description' => 'Service Fee', 'amount' => str_replace(',', '', $request->servicefee_amount)]
    ]);


    if ($request->out_pel_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Outstanding Loan - Principal(PEL)', 'amount' => str_replace(',', '', $request->out_pel_amount)]
      ]);
    }

    if ($request->out_bl_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Outstanding Loan - Principal(BL)', 'amount' => str_replace(',', '', $request->out_bl_amount)]
      ]);
    }

    if ($request->out_eml_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Outstanding Loan - Principal(EML)', 'amount' => str_replace(',', '', $request->out_eml_amount)]
      ]);
    }

    if ($request->out_cbl_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Outstanding Loan - Principal(CBL)', 'amount' => str_replace(',', '', $request->out_cbl_amount)]
      ]);
    }

    if ($request->int_pel_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Interest - PEL', 'amount' => str_replace(',', '', $request->int_pel_amount)]
      ]);
    }

    if ($request->int_bl_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Interest - BL', 'amount' => str_replace(',', '', $request->int_bl_amount)]
      ]);
    }

    if ($request->int_eml_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Interest - EML', 'amount' => str_replace(
          ',',
          '',
          $request->int_eml_amount
        )]
      ]);
    }

    if ($request->int_cbl_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Interest - CBL', 'amount' => str_replace(',', '', $request->int_cbl_amount)]
      ]);
    }

    if ($request->surge_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Surcharge', 'amount' => str_replace(',', '', $request->surge_amount)]
      ]);
    }


    if ($request->other1_desc) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => $request->other1_desc, 'amount' => str_replace(',', '', $request->other1_amount)]
      ]);
    }

    if ($request->other2_desc) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => $request->other2_desc, 'amount' => str_replace(',', '', $request->other2_amount)]
      ]);
    }



    $cd = strtotime($request->ecd);
    $cfrom = strtotime($request->colfrom);
    $cto = strtotime($request->colto);
    $loan_type = $request->loan_type;

    $newformat = date('Y-m-d H:i:s', $cd);
    $newfrom = date('Y-m-d H:i:s', $cfrom);
    $newto = date('Y-m-d H:i:s', $cto);

    DB::table('loan_applications')
      ->where('id', $request->loan_app_id)
      ->update(
        ['status' => 'DONE', 'loan_type' => $loan_type, 'approved_amount' => str_replace(',', '', $request->approved_amount), 'crediting_date' => $newformat, 'monthly_amort' => str_replace(',', '', $request->monthly_amort), 'collect_from' => $newfrom, 'collect_to' => $newto, 'net_proceeds' => str_replace(',', '', $request->net_proceeds),  'date_closed' => date('Y-m-d H:i:s'), 'closed_by' => getUserdetails()->user_id]
      );

    $loan = DB::table('loan_applications')
      ->select('*')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
      ->leftjoin('users', 'member.user_id', 'users.id')
      ->where('loan_applications.id', $request->loan_app_id)
      ->first();

    if (isset($request->renewal_type)) {
      DB::table('loan_applications_peb')
        ->where('id', $request->loan_app_id)
        ->update(['renewal_type' => $request->renewal_type]);
    }

    $loan_details = DB::table('loan_applications_peb')
      ->where('loan_app_id', $request->loan_app_id)
      ->first();


    $emailadd = $loan->email;

    $name = $loan->first_name . ' ' . $loan->last_name;
    $loandesc = $loan->description;
    $loancontrol = $loan->control_number;
    $loanamt = $loan->approved_amount;
    $loancd = $loan->crediting_date;
    $loanamort = $loan->monthly_amort;
    $loanproceeds = $loan->net_proceeds;
    $loanbank = $loan_details->bank;
    $loanname = $loan_details->account_name;
    $loannum = $loan_details->account_number;


    $data = array();

    $data['loan'] = DB::table('loan_applications')
      ->select('*', 'loan_applications.date_created as date_created', 'loan_type.description as loan')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->leftjoin('loan_applications_peb', 'loan_applications_peb.loan_app_id', 'loan_applications.id')
      ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
      ->leftjoin('member_detail', 'member.member_no', 'member_detail.member_no')
      ->leftjoin('campus', 'member.campus_id', 'campus.id')
      ->leftjoin('users', 'member.user_id', 'users.id')
      ->where('loan_applications.id', $request->loan_app_id)
      ->first();

    $data['less'] = DB::table('loan_applications_deductions')
      ->select('*')

      ->where('loan_app_id', $request->loan_app_id)
      ->get();
    // dd($data);

    $pdf = PDF::loadView('pdf.loan_form', $data);


    Mail::send('emailTemplates.loanClosed', ['firstName' => $name, 'loandesc' => $loandesc, 'loancontrol' => $loancontrol, 'loanamt' => $loanamt, 'loancd' => $loancd, 'loanbank' => $loanbank, 'loanname' => $loanname, 'loannum' => $loannum, 'loanamort' => $loanamort, 'loanproceeds' => $loanproceeds], function ($message) use ($emailadd, $pdf) {
      $message->subject('Loan Application Approved');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($emailadd);
      $message->attachData($pdf->output(), "loan information slip.pdf");
    });



    return redirect('admin/loan-app')
      ->with('success', 'Loan Application Successfully Updated');
  }

  public function admin_loan_update(Request $request)
  {



    DB::table('loan_applications_deductions')->where('loan_app_id', $request->loan_app_id)->delete();
    DB::table('loan_applications_deductions')->insert([
      ['loan_app_id' => $request->loan_app_id, 'description' => 'Service Fee', 'amount' => str_replace(',', '', $request->servicefee_amount)]
    ]);


    if ($request->out_pel_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Outstanding Loan - Principal(PEL)', 'amount' => str_replace(',', '', $request->out_pel_amount)]
      ]);
    }

    if ($request->out_bl_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Outstanding Loan - Principal(BL)', 'amount' => str_replace(',', '', $request->out_bl_amount)]
      ]);
    }

    if ($request->out_eml_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Outstanding Loan - Principal(EML)', 'amount' => str_replace(',', '', $request->out_eml_amount)]
      ]);
    }

    if ($request->out_cbl_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Outstanding Loan - Principal(CBL)', 'amount' => str_replace(',', '', $request->out_cbl_amount)]
      ]);
    }

    if ($request->int_pel_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Interest - PEL', 'amount' => str_replace(',', '', $request->int_pel_amount)]
      ]);
    }

    if ($request->int_bl_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Interest - BL', 'amount' => str_replace(',', '', $request->int_bl_amount)]
      ]);
    }

    if ($request->int_eml_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Interest - EML', 'amount' => str_replace(',', '', $request->int_eml_amount)]
      ]);
    }

    if ($request->int_cbl_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Interest - CBL', 'amount' => str_replace(',', '', $request->int_cbl_amount)]
      ]);
    }

    if ($request->surge_amount) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => 'Surcharge', 'amount' => str_replace(',', '', $request->surge_amount)]
      ]);
    }


    if ($request->other1_desc) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => $request->other1_desc, 'amount' => str_replace(',', '', $request->other1_amount)]
      ]);
    }

    if ($request->other2_desc) {
      DB::table('loan_applications_deductions')->insert([
        ['loan_app_id' => $request->loan_app_id, 'description' => $request->other2_desc, 'amount' => str_replace(',', '', $request->other2_amount)]
      ]);
    }



    $cd = strtotime($request->ecd);
    $cfrom = strtotime($request->colfrom);
    $cto = strtotime($request->colto);
    $loan_type = $request->loan_type;

    $newformat = date('Y-m-d H:i:s', $cd);
    $newfrom = date('Y-m-d H:i:s', $cfrom);
    $newto = date('Y-m-d H:i:s', $cto);

    DB::table('loan_applications')
      ->where('id', $request->loan_app_id)
      ->update(
        ['status' => 'DONE', 'loan_type' => $loan_type, 'approved_amount' => str_replace(',', '', $request->approved_amount), 'crediting_date' => $newformat, 'monthly_amort' => str_replace(',', '', $request->monthly_amort), 'collect_from' => $newfrom, 'collect_to' => $newto, 'net_proceeds' => str_replace(',', '', $request->net_proceeds),  'date_closed' => date('Y-m-d H:i:s'), 'closed_by' => getUserdetails()->user_id]
      );

    $loan = DB::table('loan_applications')
      ->select('*')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
      ->leftjoin('users', 'member.user_id', 'users.id')
      ->where('loan_applications.id', $request->loan_app_id)
      ->first();

    if (isset($request->renewal_type)) {
      DB::table('loan_applications_peb')
        ->where('id', $request->loan_app_id)
        ->update(['renewal_type' => $request->renewal_type]);
    }

    $loan_details = DB::table('loan_applications_peb')
      ->where('loan_app_id', $request->loan_app_id)
      ->first();


    $emailadd = $loan->email;

    $name = $loan->first_name . ' ' . $loan->last_name;
    $loandesc = $loan->description;
    $loancontrol = $loan->control_number;
    $loanamt = $loan->approved_amount;
    $loancd = $loan->crediting_date;
    $loanamort = $loan->monthly_amort;
    $loanproceeds = $loan->net_proceeds;
    $loanbank = $loan_details->bank;
    $loanname = $loan_details->account_name;
    $loannum = $loan_details->account_number;


    $data = array();

    $data['loan'] = DB::table('loan_applications')
      ->select('*', 'loan_applications.date_created as date_created', 'loan_type.description as loan')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->leftjoin('loan_applications_peb', 'loan_applications_peb.loan_app_id', 'loan_applications.id')
      ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
      ->leftjoin('member_detail', 'member.member_no', 'member_detail.member_no')
      ->leftjoin('campus', 'member.campus_id', 'campus.id')
      ->leftjoin('users', 'member.user_id', 'users.id')
      ->where('loan_applications.id', $request->loan_app_id)
      ->first();

    $data['less'] = DB::table('loan_applications_deductions')
      ->select('*')

      ->where('loan_app_id', $request->loan_app_id)
      ->get();
    // dd($data);

    $pdf = PDF::loadView('pdf.loan_form', $data);


    Mail::send('emailTemplates.loanClosed', ['firstName' => $name, 'loandesc' => $loandesc, 'loancontrol' => $loancontrol, 'loanamt' => $loanamt, 'loancd' => $loancd, 'loanbank' => $loanbank, 'loanname' => $loanname, 'loannum' => $loannum, 'loanamort' => $loanamort, 'loanproceeds' => $loanproceeds], function ($message) use ($emailadd, $pdf) {
      $message->subject('Loan Application Approved (UPDATED)');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($emailadd);
      $message->attachData($pdf->output(), "loan information slip.pdf");
    });



    return redirect('admin/loan-app')
      ->with('success', 'Loan Application Successfully Updated');
  }


  public function admin_loan_cancelled(Request $request)
  {



    $loan = DB::table('loan_applications')
      ->select('*')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
      ->leftjoin('users', 'member.user_id', 'users.id')
      ->where('loan_applications.id', $request->loan_app_id)
      ->first();

    // dd(date("Y-m-d H:i:s"));
    $emailadd = $loan->email;

    $name = $loan->first_name . ' ' . $loan->last_name;
    $loancontrol = $loan->control_number;
    $loandesc = $loan->description;

    Mail::send('emailTemplates.loanCancel', ['firstName' => $name, 'loancontrol' => $loancontrol, 'loandesc' => $loandesc], function ($message) use ($emailadd) {
      $message->subject('Loan Application Cancelled');

      $message->from('information@upprovidentfund.com', 'UP Provident Fund Inc.');

      $message->to($emailadd);
    });

    DB::table('loan_applications')
      ->where('id', $request->loan_app_id)
      ->update(
        ['cancellation_reason' => $request->reason, 'status' => 'CANCELLED', 'date_closed' => date('Y-m-d H:i:s'), 'closed_by' => getUserdetails()->user_id]
      );

    return redirect('admin/loan-app')
      ->with('success', 'Loan Application Successfully Updated');
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function generate_loan_form($id)
  {
    $data = array();

    $data['loan'] = DB::table('loan_applications')
      ->select('*', 'loan_applications.date_created as date_created', 'loan_type.description as loan')
      ->leftjoin('loan_type', 'loan_applications.loan_type', 'loan_type.id')
      ->leftjoin('loan_applications_peb', 'loan_applications_peb.loan_app_id', 'loan_applications.id')
      ->leftjoin('member', 'loan_applications.member_no', 'member.member_no')
      ->leftjoin('member_detail', 'member.member_no', 'member_detail.member_no')
      ->leftjoin('campus', 'member.campus_id', 'campus.id')
      ->leftjoin('users', 'member.user_id', 'users.id')
      ->where('loan_applications.id', $id)
      ->first();

    $data['less'] = DB::table('loan_applications_deductions')
      ->select('*')

      ->where('loan_app_id', $id)
      ->get();
    // dd($data);

    $pdf = PDF::loadView('pdf.loan_form', $data);

    return $pdf->stream($data['loan']->loan_app_id . '-' . $data['loan']->last_name . ', ' . $data['loan']->first_name . '.pdf');
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    //
  }
}
