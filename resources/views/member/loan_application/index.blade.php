@extends('layouts/main')
@section('content_body')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<style type="text/css">
ul.pagination {
  list-style-type:none;
  margin:0;
  padding:0;
  text-align:center;
}

ul.pagination li {
  display:inline;
  padding:2px 5px 0;
  text-align:center;
}

ul.pagination li a {
  padding:2px;
}
</style>
<div class="container mp-container">
 
  <div class="row no-gutters mp-mt5">
    <div class="col-12 mp-ph2 mp-pv2 mp-text-fs-large mp-text-c-primary">
      Loan Application
      <span style="position: relative; float: right;">
       <a href="{{url('/member/new-loan')}}"  class="mp-button mp-button--primary">
        Apply for Loan
      </a>
    </span>
  </div>
  
</div>


<div class="row no-gutters mp-mb4">
  <div class="col-12 mp-ph2 mp-pv2">
    <div class="row no-gutters">
     
      <div class="col-6 col-lg-3">
        <div class="mp-tab mp-tab--active">
          <a class="mp-tab__link" href="{{ url('/member/loan-app') }}">
            Loan Application
          </a>
        </div>
      </div>

     <!--    <div class="col-6 col-lg-3">
          <div class="mp-tab ">
            <a class="mp-tab__link" href="{{ url('/member/coborrower') }}">
              Co-Borrower Loan
            </a>
          </div>
        </div> -->
        
      </div>
      <div class="row no-gutters">
        <div class="col">
          <div class="mp-ph4 mp-pv4 mp-card mp-card--tabbed">

            <div class="mp-text-fs-medium {{ Session::has('error') or Session::has('success') ? 'mp-mb4' : '' }}" align="center">
              @if(Session::has('error'))
              <span style="color:red"><strong>{{ Session::get('error') }}</strong></span>
              @endif
              @if(Session::has('success'))
              <span style="color:green"><strong>{{ Session::get('success') }}</strong></span>
              @endif
            </div>

            
            <br>
            
            <div class="mp-overflow-x">
            <div class="mp-ph4 mp-pv4 ft-card border-bottom-0" >
                            <div class="row mp-pv4">
                                <label for="" class="mp-text-fs-xlarge mp-text--c-white ">Filtering Section</label>
                            </div>
                            <div class="row items-between mp-pv4">
                                <div class="col-md-12 col-xl-6">
                                    <div class="row mp-text--c-white">
                                        <label for="row">Fields</label>
                                    </div>
                                    <div class="row field-filter">
                                        <select name="" class="radius-1 outline select-field" style="width: 100%; height: 30px"
                                                id="loan_type">
                                                <option value="">Filter By Loan Type</option>
                                                @foreach ($loan_type as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>    
                                            <select name="" class="radius-1 outline select-field" style="width: 100%; height: 30px"
                                                id="loan_status">
                                                <option value="">Filter By Status</option>
                                                <option value="PROCESSING">PROCESSING</option>
                                                <option value="DONE">DONE</option>
                                                <option value="CONFIRMED">CONFIRMED</option>
                                                <option value="CANCELLED">CANCELLED</option>
                                                
                                            </select>    
                                    </div>
                                </div>
                                <div class="col-md-12 col-xl-5">
                                    <div class="row mp-text--c-white">
                                        <label for="row">Date Range based on Date Applied Date</label>
                                    </div>
                                    <div class="row date_range">
                                        <input type="date" id="from" class="radius-1 border-1 date-input outline" style="height: 30px;">
                                        <span for="" class="self_center mh-1 mp-text--c-white">to</span>
                                        <input type="date" id="to" class="radius-1 border-1 date-input outline" style="height: 30px;">
                                    </div>
                                </div>
                            </div>
                        </div>
              <table class="mp-table mp-text-fs-small" id="member_loan_table" cellspacing="0" width="100%">
                <thead>
                  <tr>
                    <th class="mp-text-center"></th>
                    <th class="mp-text-center">Date Applied</th>
                    <th class="mp-text-center">Loan Application Number</th>
                    <th class="mp-text-center">Loan Type</th>
                    <th class="mp-text-center">Loan Status</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
            </table>

          </div>

          
          
          
          
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>
</div>

@endsection
@section('scripts')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript">
  $(document).ready(function() {
            var member_loan_table = $('#member_loan_table').DataTable({
                language: {
                    search: '',
                    searchPlaceholder: "Search Loan Application No.",
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><br>Loading...',
                },
                "ordering": false,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('member_loans') }}",
                    "data": function(data) {
                        // data._token = "ss";
                        data.loan_type = $('#loan_type').val();
                        data.loan_status = $('#loan_status').val();
                        data.dt_from = $('#from').val();
                        data.dt_to = $('#to').val();
                    }
                },
            });
            $(document).on('change', '#loan_type', function(e) {
                member_loan_table.draw();
            });
            $(document).on('change', '#loan_status', function(e) {
                member_loan_table.draw();
            });
            $('#from').on('change', function() {
                if($('#from').val() > $('#to').val() && $('#to').val() != '')
                {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Invalid Date Range,Please Check the date. Thank you!',  
                        });
                    $('#from').val('');
                }else{
                    member_loan_table.draw();
                }
                
            });
            $('#to').on('change', function() {
                if($('#to').val() < $('#from').val())
                {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Invalid Date Range,Please Check the date. Thank you!',                       
                        });
                    $('#to').val('');
                }else{
                    member_loan_table.draw();
                }
            });
        });
        $(document).on('click', '#member_loandet', function(e) {
            var id = $(this).attr('data-id');
            console.log(id);
            var url = "{{ URL::to('/member/loan-details') }}" + '/' + id; //YOUR CHANGES HERE...
            window.location.href = url;
        });
</script>
@endsection
