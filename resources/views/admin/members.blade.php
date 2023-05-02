@extends('layouts/main')
@section('content_body')
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
    </style>
    <link href="/css/css-module/global_css/global.css" rel="stylesheet">
    <div class="container mp-container ">
        <div class="row no-gutters mp-mt5">
            <div class="col-12 mp-ph2 mp-pv2 mp-text-fs-large mp-text-c-accent">
                Members
            </div>
        </div>
        <div class="row no-gutters mp-mb4">
            <div class="col-12 ">
                <div class="row">
                    <div class="col mp-top-button">
                        <div style="display: flex; flex-direction: row; gap: 10px; justify-content: right">
                            @if (getUserdetails()->role == 'SUPER_ADMIN')
                                <span>
                                    <a href="{{ url('/admin/summary') }}" target="_blank"
                                        class="toggle text_link mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small">Generate
                                        Summary Report</a>
                                </span>
                            @endif
                            <span>
                                <a href="#" id="exportMember"
                                    class="toggle text_link mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small">Export
                                    Data</a>
                            </span>
                            {{-- <button type="button" class="mp-button mp-button--accent" id="printMember">Print</button> --}}
                        </div>
                    </div>
                </div>
                <div class="row no-gutters custom_header">
                    <div class="col m-5">
                        <div class="container bottom-divider top-divider">

                            <div class="row">
                                <div class="col">
                                    <label for="" class="filter-text">Filtering Section</label>
                                </div>
                            </div>
                            <div class="row items-between" style="margin-left:15px; margin-right:15px;">
                                <div class="col-md-12 col-xl-6">
                                    <div class="row field-filter">
                                        <div class="col-md-12">
                                            <label for="row">Filter By Campus</label>
                                            <select name="" class="radius-1 outline select-field"
                                                style="width: 100%; height: 30px" id="campuses_select">
                                                <option value="">Show All</option>
                                                @foreach ($campuses as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="row">Filter By Department</label>
                                            <select name="" class="radius-1 outline select-field"
                                                style="width: 100%; height: 30px" id="department_select">
                                                <option value="">Show All</option>
                                                @foreach ($department as $row)
                                                    <option value="{{ $row->id }}">{{ $row->description }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 col-xl-5">
                                    <div class="row">
                                        <label for="row">Filter by Membership Date</label>
                                    </div>
                                    <div class="row date_range">
                                        <input type="date" id="from" class="radius-1 border-1 date-input outline"
                                            style="height: 30px;">
                                        <span for="" class="self_center mv-1"
                                            style="margin-left:15px; margin-right:15px;">to</span>
                                        <input type="date" id="to" class="radius-1 border-1 date-input outline"
                                            style="height: 30px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row no-gutters">
                    <div class="col ">
                        <div class="mp-ph3 mp-pv4 mp-card ">

                            <div class="">
                                <div class="row">
                                    <div class="col-4 ">
                                        <label for="" class="mp-text-c-accent mp-text-fs-large">Member List</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" id="search_value"
                                            placeholder="Search By Member ID and Last Name" class="radius-1 border-1 date-input outline search_field ">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 mp-overflow-x">
                                        <table class="mp-table mp-text-fs-small" id="membersTable" cellspacing="0"
                                            width="100%">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Action</th>
                                                    <th>Member ID</th>
                                                    <th>Member Name</th>
                                                    <th>Membership Date</th>
                                                    <th>Campus</th>
                                                    <th>Class</th>
                                                    <th>Position</th>
                                                    <th>Created At</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <hr>
                                <button class="mp-ml2 mp-button mp-button--primary mp-button--ghost mp-button--raised up-button" id="view_records">View Records</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $('#loading').show();
        $(window).load(function() {
            $('#loading').hide();
        });
        $(document).ready(function() {
            var tableMember = $('#membersTable').DataTable({
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
                    "url": "{{ route('dataProcessing') }}",
                    "data": function(data) {
                        data.campus = $('#campuses_select').val();
                        data.department = $('#department_select').val();
                        data.dt_from = $('#from').val();
                        data.dt_to = $('#to').val();
                        data.searchValue = $('#search_value').val();
                    }
                },
            });

            $('#campuses_select').on('change', function() {
                tableMember.draw();
            });
            $('#department_select').on('change', function() {
                tableMember.draw();
            });
            $('#search_value').on('change', function() {
                tableMember.draw();
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
                    tableMember.draw();
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
                    tableMember.draw();
                }
            });

            $(document).on('click', '#view_records', function(e) {
                var checkbox = $('#check:checked');
                var members = new Array();
                if (checkbox.length > 0) {
                    $(checkbox).each(function(){
                        members.push($(this).val());
                    });
                    console.log(members);
                } else {
                    alert('No selected records.');
                }
            });

            $(document).on('click', '.view_member', function(e) {
                var id = $(this).attr('id');
                console.log(id);
                var url = "{{ URL::to('/admin/member_soa/') }}" + '/' + id; //YOUR CHANGES HERE...
                window.location.href = url;
            });

            $(document).on('click', '#printMember', function() {
                var url = "{{ URL::to('/admin/printMember') }}"
                window.open(url, 'targetWindow', 'resizable=yes,width=1000,height=1000');
            });

            $(document).on('click', '#exportMember', function(e) {
                if ($('#campuses_select').val() != "") {
                    var camp_id = $('#campuses_select').val();
                } else {
                    var camp_id = 0;
                }

                if ($('#department_select').val() != "") {
                    var dept = $('#department_select').val();
                } else {
                    var dept = 0;
                }

                if ($('#from').val() != "" && $('#to').val() != "") {
                    var dt_from = $('#from').val();
                    var dt_to = $('#to').val();
                } else {
                    var dt_from = 0;
                    var dt_to = 0;
                }
                // console.log(id);
                var url = "{{ URL::to('/admin/exportMember') }}" + '/' + camp_id + '/' + dept + '/' +
                    dt_from + '/' + dt_to; //YOUR CHANGES HERE...
                window.open(url, '_blank');
            });

        });
    </script>
@endsection
