@extends('layouts/main')
@section('content_body')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
    <style type="text/css">
        ul.pagination {
            list-style-type: none;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        ul.pagination li {
            display: inline;
            padding: 2px 5px 0;
            text-align: center;
        }

        ul.pagination li a {
            padding: 2px;
        }

        #tableLoans td:nth-child(5),
        #tableLoans td:nth-child(9) {
            font-weight: 600;
        }
    </style>
    <div class="container mp-container">

        <div class="row no-gutters mp-mt5">
            <div class="col-12 mp-ph2 mp-pv2 mp-text-fs-large mp-text-c-accent">
                Loan Application
            </div>
        </div>


        <div class="row no-gutters mp-mb4">
            <div class="col-12 mp-ph2 mp-pv2">
                <div class="row no-gutters">

                    <div class="col-6 col-lg-3">
                        <div class="mp-tab mp-tab--active">
                            <a class="mp-tab__link" href="{{ url('/member/loan-app') }}">
                                Loan Applications
                            </a>
                        </div>
                    </div>


                    <!--   <div class="col-6 col-lg-3">
                              <div class="mp-tab--accent ">
                                <a class="mp-tab__link" href="{{ url('/member/coborrower') }}">
                                 CBL
                                </a>
                              </div>
                            </div> -->

                    <!-- <div class="col-6 col-lg-3">
                              <div class="mp-tab--accent ">
                                <a class="mp-tab__link" href="{{ url('/member/coborrower') }}">
                                 BTL
                                </a>
                              </div>
                            </div> -->

                </div>
                <div class="row no-gutters">
                    <div class="col">
                        <div class="mp-ph4 mp-pv4 mp-card mp-card--tabbed">

                            <div class="mp-text-fs-medium {{ Session::has('error') or (Session::has('success') ? 'mp-mb4' : '') }}"
                                align="center">
                                @if (Session::has('error'))
                                    <span style="color:red"><strong>{{ Session::get('error') }}</strong></span>
                                @endif
                                @if (Session::has('success'))
                                    <span style="color:green"><strong>{{ Session::get('success') }}</strong></span>
                                @endif
                            </div>

                            {{-- <div class="row no-gutters mp-search-header">

              <div class="col-lg-8 col-md-4 d-sm-none d-md-block"></div>
              <div class="col-lg-4 col-md-8 col-sm-12 mp-pb3 mp-input-search__container">
                <div class="mp-input-search__input_div">


                  <input class="mp-input-search__input" type="text" id="search" placeholder="Search" value="{{ isset($_GET['q']) ? $_GET['q'] : '' }}"/>

                </div>
                <button class="mp-input-search__button mp-button mp-button--accent" id="search_btn_albums" type="button">  <i class="mp-icon icon-magnifier mp-text-c-white mp-text-fw-xheavy mp-text-fs-large"></i></button>

              </div>

            </div> --}}
                            <select name="" class="radius-1 outline select-field" style="width: 100%; height: 30px"
                                id="campuses_select">
                                <option value="">Filter By Campus</option>
                                @foreach ($campuses as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                            <select name="" class="radius-1 outline select-field" style="width: 100%; height: 30px"
                                id="loan_select">
                                <option value="">Filter By Loan Type</option>
                                @foreach ($LoanType as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                            


                            <input type="date" id="from" class="radius-1 border-1 date-input outline"
                                style="height: 30px;">
                            <span for="" class="self_center mv-1">to</span>
                            <input type="date" id="to" class="radius-1 border-1 date-input outline"
                                style="height: 30px;">

                            <input type="text" id="search_value" placeholder="Serach By Member No and Last Name">
                            
                            <div class="mp-overflow-x">
                                <table class="mp-table mp-text-fs-small" id="tableLoans" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="mp-text-center">Action</th>
                                            <th class="mp-text-center">Date Applied</th>
                                            <th class="mp-text-center">Member Number</th>
                                            <th class="mp-text-center">Loan Application Number</th>
                                            <th class="mp-text-center">Full Name</th>
                                            <th class="mp-text-center">Campus</th>
                                            <th class="mp-text-center">Loan Type</th>
                                            <th class="mp-text-center">Application Type</th>
                                            <th class="mp-text-center">Loan Status</th>


                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>

                                {{-- <table class="mp-table mp-text-fs-small">
                <thead>
                  <tr>
                    <th class="mp-text-center">Date Applied</th>
                    <th class="mp-text-center">Member Number</th>
                    <th class="mp-text-center">Loan Application Number</th>
                    <th class="mp-text-center">Full Name</th>
                    <th class="mp-text-center">Campus</th>
                    <th class="mp-text-center">Loan Type</th>
                    <th class="mp-text-center">Application Type</th>
                    <th class="mp-text-center">Loan Status</th>
                    <th class="mp-text-center"></th>

                  </tr>
                </thead>
                <tbody>

                 @foreach ($loans as $loan)
                 <tr>
                  <td class="mp-text-center">{{date("m/d/Y h:i A", strtotime($loan->date_created))}}</td>
                  <td class="mp-text-center"><strong>{{$loan->member_no}}</strong></td>
                  <td class="mp-text-center"><strong>{{$loan->control_number}}</strong></td>
                  <td class="mp-text-center"><strong>{{$loan->full_name}}</strong></td>
                  <td class="mp-text-center">{{$loan->campus}}</td>
                  <td >{{'('.$loan->name.')'}}</td>
                  <td class="mp-text-center"><strong>{{$loan->application_type}}</strong></td>
                  @if ($loan->status == 'SUBMITTED')
                  <td class="mp-text-center" style="color:#feb236;"><strong>{{$loan->status}}</strong></td>
                  @endif

                  @if ($loan->status == 'PROCESSING')
                  <td class="mp-text-center" style="color:#82b74b;"><strong>{{$loan->status}}</strong></td>
                  @endif

                  @if ($loan->status == 'DONE')
                  <td class="mp-text-center" style="color:#034f84;"><strong>FOR MEMBER CONFIRMATION</strong></td>
                  @endif

                  @if ($loan->status == 'CANCELLED')
                  <td class="mp-text-center" style="color:#d64161;"><strong>{{$loan->status}}</strong></td>
                  @endif

                  @if ($loan->status == 'CONFIRMED')
                  <td class="mp-text-center" style="color:#894168"><strong>{{$loan->status}}</strong></td>
                  @endif

                  <td class="mp-text-center">
                    <a data-md-tooltip="View Details" href="{{url('/admin/loan-app-details').'/'.$loan->id}}">
                      <i class="mp-icon md-tooltip icon-book-open mp-text-c-primary mp-text-fs-large"></i>
                    </a>
                    
                  </td>
                  
                  


                </tr>
                @endforeach

              </tbody> --}}
                                </table>
                            </div>
                            {{-- 
          @if (isset($_GET['q']))
                {!! $loans->appends(['q' => $_GET['q']])->links('pagination.default') !!} 
                @else
                {!! $loans->links('pagination.default') !!}
                @endif --}}
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
        // $('#search_btn_albums').click(function() {
        //     keyword = $('#search').val();
        //     location.href = "loan-app?q=" + keyword;
        // });
        $('#loading').show();
        $(window).load(function() {
            $('#loading').hide();
        });
        $(document).ready(function() {
            var tableLoans = $('#tableLoans').DataTable({
                language: {
                    search: '',
                    searchPlaceholder: "Search Here...",
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><br>Loading...',
                },
                "ordering": false,
                "searching": false,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('dataLoans') }}",
                    "data": function(data) {
                        data.campus = $('#campuses_select').val();
                        data.loanType = $('#loan_select').val();
                        data.dt_from = $('#from').val();
                        data.dt_to = $('#to').val();
                        data.searchValue = $('#search_value').val();
                    }
                },
            });
            $('#campuses_select').on('change', function() {
                tableLoans.draw();
            });
            $('#search_value').on('change', function() {
              tableLoans.draw();
            });
            $('#loan_select').on('change', function() {
                tableLoans.draw();
            });
            $('#from').on('change', function() {
                if ($('#from').val() > $('#to').val() && $('#to').val() != '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Invalid Date Range,Please Check the date. Thank you!',
                    });
                    $('#from').val('');
                } else {
                  tableLoans.draw();
                }

            });
            $('#to').on('change', function() {
                if ($('#to').val() < $('#from').val()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Invalid Date Range,Please Check the date. Thank you!',
                    });
                    $('#to').val('');
                } else {
                  tableLoans.draw();
                }
            });

            $(document).on('click', '.view_details', function(e) {
                var id = $(this).attr('id');
                console.log(id);
                var url = "{{ URL::to('/admin/loan-app-details') }}" + '/' + id; //YOUR CHANGES HERE...
                window.open(url, '_blank');
            });
        });
    </script>
@endsection
