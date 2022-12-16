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
    <div class="container mp-container loan_container">

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
                        <div class="mp-ph4 mp-pv4 ft-card border-bottom-0 border-top-left-0">
                            <div class="row mp-pv4">
                                <label for="" class="mp-text-fs-xlarge mp-text--c-white ">Filtering Section</label>
                            </div>
                            <div class="row items-between mp-pv4">
                                <div class="col-md-12 col-xl-6">
                                    <div class="row field-filter">
                                        <div class="col-md-12">
                                            <label for="row" class="mp-text--c-white">Filter By Campus</label>
                                            <select name="" class="radius-1 outline select-field"
                                                style="width: 100%; height: 30px" id="campuses_select">
                                                <option value="">Show All</option>
                                                @foreach ($campuses as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="row" class="mp-text--c-white">Filter By Loan Type</label>
                                            <select name="" class="radius-1 outline select-field"
                                                style="width: 100%; height: 30px" id="loan_select">
                                                <option value="">Show All</option>
                                                @foreach ($LoanType as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-12">
                                            <label for="row" class="mp-text--c-white">Filter By Application Type</label>
                                            <select name="" class="radius-1 outline select-field"
                                                style="width: 100%; height: 30px" id="application_select">
                                                <option value="">Show All</option>
                                                @foreach ($application as $row)
                                                    <option value="{{ $row->type }}">{{ $row->type }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="row" class="mp-text--c-white">Filter By Status</label>
                                            <select name="" class="radius-1 outline select-field"
                                                style="width: 100%; height: 30px" id="status_select">
                                                <option value="">Show All</option>
                                                @foreach ($status as $row)
                                                    <option value="{{ $row->status }}">{{ $row->status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        


                                    </div>
                                </div>
                                <div class="col-md-12 col-xl-5">

                                    <div class="row mp-text--c-white">
                                        <label for="row">Filter by Date Applied</label>
                                    </div>
                                    <div class="row date_range">
                                        <input type="date" id="from" class="radius-1 border-1 date-input outline"
                                            style="height: 30px;">
                                        <span for="" class="self_center mh-1 mp-text--c-white">to</span>
                                        <input type="date" id="to" class="radius-1 border-1 date-input outline"
                                            style="height: 30px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mp-ph4 mp-pv4 tb-card border-top-0">

                            <div class="mp-text-fs-medium {{ Session::has('error') or (Session::has('success') ? 'mp-mb4' : '') }}"
                                align="center">
                                @if (Session::has('error'))
                                    <span style="color:red"><strong>{{ Session::get('error') }}</strong></span>
                                @endif
                                @if (Session::has('success'))
                                    <span style="color:green"><strong>{{ Session::get('success') }}</strong></span>
                                @endif
                            </div>
                            <div
                                style="display: flex; flex-direction: row; gap: 10px; justify-content: right; margin-bottom: 15px">
                                <span>
                                    <a id="export_loanapplication"
                                        class="mp-ml2 text_link mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small up-button">
                                        Export Data
                                    </a>
                                </span>
                            </div>
                            <div class="flex-right">
                                <input type="text" id="search_value"
                                    class="radius-1 border-1 date-input outline search_field "
                                    placeholder="Search By Last Name and First Name">
                            </div>

                            <div class="mp-overflow-x">
                                <table class="mp-table mp-text-fs-small" id="tableLoans" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="mp-text-center">Action</th>
                                            <th>Date Applied</th>
                                            <th>Member Number</th>
                                            <th>Loan Application Number</th>
                                            <th>Full Name</th>
                                            <th>Campus</th>
                                            <th>Loan Type</th>
                                            <th>Application Type</th>
                                            <th>Loan Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>

                                </table>
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
                        data.application = $('#application_select').val();
                        data.status = $('#status_select').val();
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
            $('#application_select').on('change', function() {
                tableLoans.draw();
            });
            $('#status_select').on('change', function() {
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

            $(document).on('click', '#export_loanapplication', function(e) {
                if ($('#campuses_select').val() != "") {
                    var camp_id = $('#campuses_select').val();
                } else {
                    var camp_id = 0;
                }

                if ($('#loan_select').val() != "") {
                    var loan_id = $('#loan_select').val();
                } else {
                    var loan_id = 0;
                }

                if ($('#application_select').val() != "") {
                    var app = $('#application_select').val();
                } else {
                    var app = 0;
                }

                if ($('#status_select').val() != "") {
                    var stat = $('#status_select').val();
                } else {
                    var stat = 0;
                }

                if ($('#from').val() != "" && $('#to').val() != "") {
                    var dt_from = $('#from').val();
                    var dt_to = $('#to').val();
                } else {
                    var dt_from = 0;
                    var dt_to = 0;
                }
                // console.log(id);
                var url = "{{ URL::to('/admin/export_loanapplication') }}" + '/' + camp_id + '/' +
                    loan_id + '/' + dt_from + '/' + dt_to + '/' + app + '/' + stat; //YOUR CHANGES HERE...
                window.open(url, '_blank');
            });
        });
    </script>
@endsection
