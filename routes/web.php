<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();
Route::get('/logout', 'Auth\LoginController@logout');

// Auth::routes('/admin');
Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('admin', [
  'as' => 'admin',
  'uses' => 'Auth\LoginController@showLoginForm'
]);
//scripts
Route::get('/fetchposition', 'HomeController@fetchposition');
// member
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/member/dashboard', 'MemberController@index');
Route::get('/member/equity', 'MemberController@equity');
Route::get('/member/loans', 'MemberController@loans');

Route::get('/member/profile', 'MemberController@profile');
Route::get('/member/edit_details', 'MemberController@edit_details');
Route::post('/member/edit_details', 'MemberController@save_details');
Route::get('/member/edit_details_approval', 'MemberController@edit_details_approval');
Route::get('/member/edit_beneficiaries', 'MemberController@edit_beneficiaries');

Route::get('/member/election/{id}', 'ElectionController@index');
Route::post('/member/savevote', 'ElectionController@savevote');
Route::post('/member/abstainvote', 'ElectionController@savevote');
Route::get('/member/edit_details_approval', 'MemberController@edit_details_approval');

Route::get('/member/onboarding', 'MemberController@onboarding');
Route::get('/member/update-password', 'MemberController@updatepw');
Route::post('/member/update-password', 'MemberController@savepw');
Route::post('/member/onboarding', 'MemberController@saveonboarding');

Route::post('/member/deletebene', 'MemberController@removebeneficiary');
Route::post('/member/save-beneficiary', 'MemberController@savebeneficiary');

Route::get('/member/prme_pr/{id}', 'RefundController@prme_pr');
Route::post('/member/prme_pr/apply_refund/{id}', 'RefundController@submit_prme_pr');
Route::get('/generate/prme_pr/{id}', 'RefundController@generate_form');

Route::get('/generate/soa', 'MemberController@generatesoa');
Route::get('/generate/equity', 'MemberController@generateequity');
Route::get('/generate/loans', 'MemberController@generateloans');

Route::get('/member/loan-app', 'LoanappController@index');
Route::get('/member/coborrower', 'LoanappController@index_coborrower');
Route::get('/member/new-loan', 'LoanappController@new_loan');

Route::post('/member/pel-loan-new', 'LoanappController@pel_loan_new');
Route::post('/member/pel-loan-renew', 'LoanappController@pel_loan_renew');

Route::post('/member/eml-loan-new', 'LoanappController@eml_loan_new');
Route::post('/member/eml-loan-renew', 'LoanappController@eml_loan_renew');

Route::post('/member/bl-loan-new', 'LoanappController@bl_loan_new');
Route::post('/member/bl-loan-renew', 'LoanappController@bl_loan_renew');

Route::get('/member/loan-details/{id}', 'LoanappController@view_loan');
Route::get('/member/confirm_agree/{id}', 'LoanappController@confirm_agree');
Route::get('/member/app_cancel/{id}', 'LoanappController@app_cancel');





//admin
Route::get('/admin/dashboard', 'AdminController@index');
Route::get('/admin/count', 'AdminController@getTotal');
Route::get('/admin/count_percampuses', 'AdminController@getTotal_campuses')->name('campuses_id');
Route::get('/admin/summaryreports/{id}', 'AdminController@generatesummary');
Route::get('/admin/member_soa/{id}', 'AdminController@member_soa');
Route::get('/admin/members', 'AdminController@members');
Route::get('/admin/membersData', 'AdminController@memberData')->name('dataProcessing');
Route::get('/admin/campuses', 'AdminController@getAllCampuses')->name('dataCampuses');
Route::get('/admin/exportMember', 'AdminController@getMemberData');
Route::get('/admin/printMember', 'AdminController@printMemberData');
Route::get('/admin/onboarding', 'AdminController@onboarding');
Route::post('/admin/onboarding', 'AdminController@saveonboarding');

//Campus management
Route::post('/admin/addCampus', 'AdminController@addCampus');
Route::get('/admin/deleteCampus', 'AdminController@deleteCampus');
Route::get('/admin/editCampusKey', 'AdminController@editCampusKey');
Route::get('/admin/editCampusName', 'AdminController@editCampusName');
Route::get('/admin/editCluster', 'AdminController@editCluster');
Route::get('/admin/exportCampus', 'AdminController@exportCampus');

Route::get('/admin/loans', 'AdminController@loansmasterlist');
Route::post('/admin/loanData', 'AdminController@loanMasterlistData')->name('loanData');
Route::get('/admin/loan-details/{id}', 'AdminController@loandetails');

Route::get('/admin/member_equity/{id}', 'AdminController@equity');
Route::get('/admin/member_loans/{id}', 'AdminController@loans');
Route::get('/admin/member_profile/{id}', 'AdminController@member_profile');
Route::get('/admin/member_edit_beneficiaries/{id}', 'AdminController@member_edit_beneficiaries');
Route::post('/admin/member-save-beneficiary', 'AdminController@member_savebeneficiary');
Route::post('/admin/deletebene', 'AdminController@member_removebeneficiary');
Route::get('/admin/member_edit_details/{id}', 'AdminController@member_edit_details');
Route::post('/admin/member_save_details', 'AdminController@member_save_details');
Route::get('/admin/resetpass/{id}', 'AdminController@resetpass');
Route::get('/admin/beneficiary-encoder', 'AdminController@beneencoder');
Route::post('/admin/member_add_new', 'AdminController@member_add_new');


Route::get('/admin/summary', 'AdminController@summary');

Route::get('/admin/update-password', 'AdminController@updatepw');
Route::post('/admin/update-password', 'AdminController@savepw');

Route::get('/admin/manage-admin', 'AdminController@manageadmin');
Route::get('/admin/add', 'AdminController@adminadd');
Route::post('/admin/save', 'AdminController@adminsave');

Route::get('/admin/generate/soa/{id}', 'AdminController@generatesoa');
Route::get('/admin/generate/equity/{id}', 'AdminController@generateequity');
Route::get('/admin/generate/loans/{id}', 'AdminController@generateloans');
Route::get('/admin/generate/loanspertype/{id}', 'AdminController@generateloanspertype');

Route::get('/admin/import_member', 'ImportController@import_member');
Route::post('/admin/import_member', 'ImportController@member_action');

Route::get('/admin/import_equity', 'ImportController@import_equity');
Route::post('/admin/import_equity', 'ImportController@equity_action');

Route::get('/admin/import_loan', 'ImportController@import_loan');
Route::post('/admin/import_loan', 'ImportController@loan_action');

Route::get('/admin/tempass', 'AdminController@tempass');
Route::get('/admin/election', 'ElectionController@admin_election_index');
Route::post('/admin/election/create', 'ElectionController@admin_election_save');
Route::get('/admin/election/detail/{id}', 'ElectionController@admin_election_details');
Route::get('/admin/election/on-going/{id}', 'ElectionController@admin_election');
Route::post('/admin/election/add_candidate_15', 'ElectionController@add_candidate_15');
Route::post('/admin/election/add_candidate_16', 'ElectionController@add_candidate_16');
//ajax
Route::post('/admin/election/get_member_details', 'ElectionController@get_member_details');
Route::post('/admin/election/remove_candidate', 'ElectionController@remove_candidate');
Route::post('/admin/election/open_election', 'ElectionController@open_election');
Route::post('/admin/election/close_election', 'ElectionController@close_election');
Route::post('/admin/election/is_valid', 'ElectionController@is_valid');



Route::get('/admin/modules', 'AdminController@modules');
Route::get('/admin/prme_pr', 'RefundController@index');
Route::get('/admin/prme_pr/{id}', 'RefundController@prme_pr_det');
Route::post('/admin/prmepr_generate', 'RefundController@prmepr_generate');

Route::get('/admin/add_member', 'AdminController@add_member');

Route::get('/admin/loan-app', 'LoanappController@admin_index');
Route::get('/admin/loan-app-details/{id}', 'LoanappController@admin_peb_details');
Route::get('/admin/loan-for-processing/{id}', 'LoanappController@admin_loan_processing');
Route::post('/admin/loan-app-closed', 'LoanappController@admin_loan_closed');
Route::post('/admin/loan-app-cancel', 'LoanappController@admin_loan_cancelled');
Route::post('/admin/loan-app-update', 'LoanappController@admin_loan_update');
Route::get('/admin/loan-form/{id}', 'LoanappController@generate_loan_form');
Route::get('/admin/edit-loan-form/{id}', 'LoanappController@edit_loan_form');

Route::get('/admin/change_status/{id}', 'AdminController@changestatus');
Route::post('/admin/change_status/update', 'AdminController@updatestatus');
