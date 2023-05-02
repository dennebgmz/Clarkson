@extends('layouts/main')
@section('content_body')
    <link href="/css/css-module/global_css/global.css" rel="stylesheet">
    <div class="container mp-container">

        <style>
            
        </style>
        
        <div class="row no-gutters mp-mt4">
            <div class="col-12 mp-text-right" style="display: flex; flex-direction: row; justify-content: right">
                <div class="row no-gutters">
                    <div class="mp-ph2 mp-pv2">
                        <a data-target="DPA_list"
                            class="toggle mp-mr2 text_link mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small up-button">
                            Member DPA
                        </a>
                        <a data-target="myPopup"
                            class="toggle text_link mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small up-button">
                            Manage Campus
                        </a>
                        <a href="#" id="generate_summary"
                            class="mp-ml2 mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small up-button">
                            Print Report
                        </a>
                        {{-- id="generate_summary_report" --}}
                        <a data-target="myPopupReport"
                            class="toggle mp-ml2 text_link mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small up-button">
                            Generate Report Summary
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row no-gutters">
            <div class="col mp-ph2 mp-pv2">
                <div class="mp-card mp-card--plain mp-pv4">
                    <div class="row align-items-center">

                        <div class="col-lg-4">
                            <div id="campusSelector" class="mp-dropdown mp-ph3">
                                <a class="mp-dropdown__toggle mp-link mp-link--accent">
                                    <span class="mp-text-fs-xxlarge campus_title text_link_primary">
                                        All UP Campuses
                                    </span>
                                    <i class="mp-icon icon-arrow-down mp-ml2"></i>
                                </a>
                                <div class="mp-dropdown__menu">
                                    <a value=""
                                        class="text_link mp-dropdown__item mp-link mp-link--normal campus_change"
                                        style="cursor: pointer">All UP Campuses</a>
                                    @foreach ($campuses as $row)
                                        <a value="{{ $row->id }}"
                                            class="text_link mp-dropdown__item mp-link mp-link--normal campus_change"
                                            style="cursor: pointer">
                                            {{ $row->name }}
                                        </a>
                                    @endforeach

                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4" hidden>
                            <select name="" class="mp-text-field mp-ph3 mp-link mp-link--accent"
                                style="width: 100%; font-size:20px" id="campuses_select">
                                <option value="">All Campuses</option>
                                @foreach ($campuses as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <div class="mp-text-c-gray mp-text-fs-small mp-pt3">
                                Total Members
                            </div>
                            <div class="row align-items-center mp-pb3">
                                <div class="col">
                                    <span class="mp-mr2 mp-dashboard__icon">@include('layouts.icons.i-members')</span>
                                    <span class="mp-text-fs-xlarge" id="totalMember"></span>
                                </div>
                                <div class="col-auto col-lg-12 col-xl-auto mp-text-right">
                                    <a href="{{ url('/admin/members') }}"
                                        class="mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small up-button">
                                        <!-- mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small -->
                                        View All Members
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="mp-text-c-gray mp-text-fs-small mp-pt3">
                                Total Loans Granted
                                <span id="label"></span>
                            </div>
                            <div class="row align-items-center mp-pb3">
                                <div class="col">
                                    <span class="mp-mr2 mp-dashboard__icon">@include('layouts.icons.i-loans')</span>
                                    <span class="mp-text-fs-xlarge" id="totalloansgranted">

                                    </span>
                                </div>
                                <div class="col-auto col-lg-12 col-xl-auto mp-text-right">
                                    <a href="{{ url('/admin/loans') }}"
                                        class="mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small up-button">
                                        View All Loans
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row no-gutters mp-mb4">
            <div class="col-lg-4 mp-ph2 mp-pv2">
                <div class="mp-card mp-ph4 mp-pv4">
                    <div class="mp-card__header">
                        <div class="row">
                            <div class="col mp-text-c-gray mp-text-fs-small">
                                Members per Campus
                            </div>
                            <div class="col-auto mp-dashboard__icon mp-dashboard__icon--2x">
                                @include('layouts.icons.i-members')
                            </div>
                        </div>
                    </div>
                    <div class="mp-card__body mp-text-fs-medium mp-pt3">
                        @foreach ($campusmembers as $member)
                            <div class="row mp-mt1">
                                <div class="col mp-text-c-gray">{{ $member->name }}</div>
                                <div class="col-sm-auto mp-text-c-gray">{{ number_format($member->count) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="{{ getUserdetails()->role == 'SUPER_ADMIN' ? 'col-lg-8' : 'col-12' }}">

                <div class="row no-gutters">
                    <div class="col-md-6 {{ getUserdetails()->role == 'SUPER_ADMIN' ? '' : 'col-lg-4' }} mp-ph2 mp-pv2">
                        <div class="mp-card mp-ph3 mp-pv3">
                            <div class="mp-text-c-gray mp-text-fs-small">
                                Total UP Contribution
                            </div>
                            <div class="mp-card__body mp-text-fs-xlarge">
                                <span id="upcontri"></span>
                                <div id="loading-div" style="display:none;">
                                    <img id="loading-img" src="{{ asset('/dist/loading-img.gif') }}" alt="Loading..." />
                                </div>
                            </div>
                            <div class="mp-text-right mp-dashboard__icon mp-dashboard__icon--2x">
                                @include('layouts.icons.i-loans')
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 {{ getUserdetails()->role == 'SUPER_ADMIN' ? '' : 'col-lg-4' }} mp-ph2 mp-pv2">
                        <div class="mp-card mp-ph3 mp-pv3">
                            <div class="mp-text-c-gray mp-text-fs-small">
                                Total Member Contribution
                            </div>
                            <div class="mp-card__body mp-text-fs-xlarge">
                                <span id="membercontri"></span>
                            </div>
                            <div class="mp-text-right mp-dashboard__icon mp-dashboard__icon--2x">
                                @include('layouts.icons.i-loans')
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 {{ getUserdetails()->role == 'SUPER_ADMIN' ? '' : 'col-lg-4' }} mp-ph2 mp-pv2">
                        <div class="mp-card mp-ph3 mp-pv3">
                            <div class="mp-text-c-gray mp-text-fs-small">
                                Earnings on UP Contributions
                            </div>
                            <div class="mp-card__body mp-text-fs-xlarge">
                                <span id="earningsUP"></span>
                            </div>
                            <div class="mp-text-right mp-dashboard__icon mp-dashboard__icon--2x">
                                @include('layouts.icons.i-loans')
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 {{ getUserdetails()->role == 'SUPER_ADMIN' ? '' : 'col-lg-4' }} mp-ph2 mp-pv2">
                        <div class="mp-card mp-ph3 mp-pv3">
                            <div class="mp-text-c-gray mp-text-fs-small">
                                Earnings on Member Contributions
                            </div>
                            <div class="mp-card__body mp-text-fs-xlarge">
                                <span id="earningsMember"></span>
                            </div>
                            <div class="mp-text-right mp-dashboard__icon mp-dashboard__icon--2x">
                                @include('layouts.icons.i-loans')
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 {{ getUserdetails()->role == 'SUPER_ADMIN' ? '' : 'col-lg-4' }} mp-ph2 mp-pv2">
                        <div class="mp-card mp-ph3 mp-pv3">
                            <div class="mp-text-c-gray mp-text-fs-small">
                                Total Members' Outstanding Loans
                            </div>
                            <div class="mp-card__body mp-text-fs-xlarge">
                                <span id="outstandingLoans"></span>
                            </div>
                            <div class="mp-text-right mp-dashboard__icon mp-dashboard__icon--2x">
                                @include('layouts.icons.i-loans')
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 {{ getUserdetails()->role == 'SUPER_ADMIN' ? '' : 'col-lg-4' }} mp-ph2 mp-pv2">
                        <div class="mp-card mp-ph3 mp-pv3">
                            <div class="mp-text-c-gray mp-text-fs-small">
                                Total Members' Equity
                            </div>
                            <div class="mp-card__body mp-text-fs-xlarge">
                                <span id="totalequity"></span>
                            </div>
                            <div class="mp-text-right mp-dashboard__icon mp-dashboard__icon--2x">
                                @include('layouts.icons.i-loans')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

 


    <div id="myPopup" class="modal_background hide ">
        <div class="popup">
            <div class="popup-header modal_title">
            Campus Management
            <span class="close toggle" data-target="myPopup">close</span>
             </div>
        <div class="popup-body">
            <div class="container">
                
                <form id="addCampus" method="POST">
                    <div class="row">
                        <div class="col-12">
                            <a href="{{ url('/admin/exportCampus') }}" class="export_campus">Export Campus</a>
                        </div>
                    </div>

                    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>"><input type="hidden"
                        name="_token" value="<?php echo csrf_token(); ?>">

                    <div class="row gc_row">
                        <div class="col-4">
                            <label>Campus Key</label>
                        </div>
                        <div class="col-8">
                            <input class="input_style" type="text" name="campus_key" required />
                        </div>
                    </div>

                    <div class="row gc_row">
                        <div class="col-4">
                             <label>Campus Name</label>
                        </div>
                        <div class="col-8">
                            <input class="input_style" type="text" name="campus_name" required />
                        </div>
                    </div>

                    <div class="row gc_row">
                        <div class="col-4">
                             <label>Cluster</label>
                        </div>
                        <div class="col-8">
                            <select name="cluster" required class= "select_style">
                            <option value="">Select Cluster</option>
                            @foreach ($cluster as $row)
                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                            @endforeach
                        </select>
                        </div>
                    </div>
                     
                    <div class="row">
                        <div class="col-12 save-button">
                            <button type="submit" class=" button_style mp-button">
                        Save Changes</button>
                          
                        </div>
                    </div>
                </form>
                <hr>
                <div class="mp-overflow-x">
                    
                    <table class="mp-table mp-text-fs-small table_style " id="campusTable" cellspacing="0" width="100%">
                        <thead>
                           
                            <tr>
                               
                                <th>Campus Key</th>
                                <th>Campus</th>
                                <th>Cluster</th>
                                <th>Action</th>
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

    <div id="myPopupReport" class="modal_background hide ">
        <div class="popup">
            <div class="popup-header modal_title">
                Report Per Campuses
            <span class="close toggle" data-target="myPopupReport">close</span>
             </div>
        <div class="popup-body">
            <div class="container">
                    <div class="row gc_row">
                        <div class="col-4">
                             <label>Campus Name</label>
                        </div>
                        <div class="col-8">
                            <select name="select_campus" required class="select_style" id="select_campus">
                            <option value="">Select Campus</option>
                            <option value="All">All Campuses</option>
                            @foreach ($campuses as $row)
                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                            @endforeach
                        </select>
                        </div>
                    </div>
                     
                    <div class="row">
                        <div class="col-12 save-button">
                            <button type="button" class="button_style mp-button" id="generate_summary_report">
                        Generate Report</button>
                          
                        </div>
                    </div>
                <hr>
            </div>
        </div>
        </div>
    </div>

    <div id="DPA_list" class="modal_background hide ">
        <div class="popup">
            <div class="popup-header modal_title" style="font-size:20px;">
                Member Already Agree on DPA
            <span class="close toggle" data-target="DPA_list">close</span>
             </div>
             <hr>
        <div class="popup-body">
            <div class="container">
                <div class="mp-overflow-x">
                    <table class="mp-table mp-text-fs-small table_style " id="dpa_table" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Campus</th>
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

@endsection

@section('scripts')
    <script src="{{ asset('/dist/adminDashboard.js') }}"></script>

    <script>
        
        $(document).ready(function() {
            var tableMember = $('#campusTable').DataTable({
                language: {
                    search: '',
                    searchPlaceholder: "Search Here...",
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><br>Loading...',
                },
                "ordering": false,
                "lengthChange": false,
                "info": false,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('dataCampuses') }}",
                },
                //  columnDefs: [
                //     {
                //         targets: -1,
                //         className: 'dt-body-right'
                //     }
                // ]
            });

            var tableDPA = $('#dpa_table').DataTable({
                language: {
                    search: '',
                    searchPlaceholder: "Search Here...",
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><br>Loading...',
                },
                "ordering": false,
                "lengthChange": false,
                "info": false,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('dataDPA') }}",
                },
            });

            load_upcontri();

            function load_upcontri() {
                $.ajax({
                    url: "/admin/count",
                    method: "GET",
                    dataType: "json",
                    beforeSend: function() {
                        $('#loading').show();
                    },
                    success: function(response) {
                        $('#upcontri').text(response.total);
                        $('#membercontri').text(response.membercontri);
                        $('#earningsUP').text(response.earningsUP);
                        $('#earningsMember').text(response.earningsMember);
                        $('#totalMember').text(response.totalMember);
                        $('#totalloansgranted').text(response.totalloansgranted);
                        $('#outstandingLoans').text('PHP ' + response.outstandingLoans);
                        $('#totalequity').text('PHP ' + response.totalequity);
                        $('#label').text(response.label);
                    },
                    complete: function(response) {
                        $('#loading').hide();
                    }
                });
            }
        });
        var campuses_id;
        var campus_title = "ALL UP Campuses";
        $('#campuses_select').on('change', function(e) {
            campuses_id = $(this).val();
            console.log(campuses_id);
            $.ajax({
                url: "/admin/count_percampuses",
                method: "GET",
                data: {
                    'campuses_id': campuses_id
                },
                dataType: "json",
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(response) {
                    $('#upcontri').text(response.total);
                    $('#membercontri').text(response.membercontri);
                    $('#earningsUP').text(response.earningsUP);
                    $('#earningsMember').text(response.earningsMember);
                    $('#totalMember').text(response.totalMember);
                    $('#totalloansgranted').text(response.totalloansgranted);
                    $('#outstandingLoans').text(response.outstandingLoans);
                    $('#totalequity').text('PHP ' + response.totalequity);
                    $('#label').text(response.label);
                },
                complete: function(response) {
                    var select = document.querySelector('#campuses_select')
                    var output = select.options[select.selectedIndex].textContent;
                    document.querySelector('.campus_title').textContent = output;
                    $('#loading').hide();
                }
            });
        });

        $(document).on('click', '#generate_summary_report', function(e) {
            var id = $('#select_campus').val();
            var url = "{{ URL::to('/admin/report_summary/') }}" + '/' + id; //YOUR CHANGES HERE...

            if (id != '') {
                window.open(url, '_blank');
            } else {
                alert('Please select campus');
            }
        });
        $(document).on('click', '#generate_summary', function(e) {
            var id = campuses_id;
            console.log(id);
            var url = "{{ URL::to('/admin/summaryreports/') }}" + '/' + id; //YOUR CHANGES HERE...
            window.open(url, '_blank');
        });
        $(document).on('click', '.toggle', function(event) {
            event.preventDefault();
            var target = $(this).data('target');
            $('#' + target).toggleClass('hide');
        });
        $(document).on('submit', '#addCampus', function(e) {
            e.preventDefault();

            $.ajax({
                url: "/admin/addCampus",
                method: "POST",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function(data) {
                    if (data.message != '') {
                        Swal.fire('Warning!', 'Campus already exist.', 'warning');
                    } else {
                        Swal.fire(
                            'Thank you!',
                            'Successfully added.',
                            'success'
                        );
                        $('#addCampus').trigger('reset');
                        var table = $('#campusTable').DataTable();
                        table.draw();
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Something went wrong. Please try again later!', 'error');
                }
            })
        });
        $(document).on('click', '.delete_campus', function() {
            var id = $(this).attr('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/admin/deleteCampus",
                        method: "GET",
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function(data) {
                            if (data.message != '') {
                                Swal.fire('Warning!',
                                    'Cannot delete campus. There are records exist',
                                    'warning');
                            } else {
                                Swal.fire(
                                    'Thank you!',
                                    'Successfully deleted.',
                                    'success'
                                );
                                var table = $('#campusTable').DataTable();
                                table.draw();
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Something went wrong. Please try again later!',
                                'error');
                        }
                    });
                }
            })
        });

        $(document).on('change', '.edit_campusKey', function() {
            var id = $(this).data('id');
            var campus_key = $(this).val();
            $.ajax({
                url: "/admin/editCampusKey",
                method: "GET",
                data: {
                    id: id,
                    campus_key: campus_key
                },
                dataType: 'json',
                success: function(data) {
                    if (data.message != '') {
                        alert('Failed to update. Campus key already exist');
                    } else {
                        var table = $('#campusTable').DataTable();
                        table.draw();
                    }
                },
                error: function() {
                    alert('Something went wrong. Please try again later!');
                }
            });
        });
        $(document).on('change', '.edit_name', function() {
            var id = $(this).data('id');
            var campus_name = $(this).val();
            $.ajax({
                url: "/admin/editCampusName",
                method: "GET",
                data: {
                    id: id,
                    campus_name: campus_name
                },
                dataType: 'json',
                success: function(data) {
                    if (data.message != '') {
                        alert('Failed to update. Campus name already exist');
                    } else {
                        var table = $('#campusTable').DataTable();
                        table.draw();
                    }
                },
                error: function() {
                    alert('Something went wrong. Please try again later!');
                }
            });
        });
        $(document).on('change', '.edit_cluster', function() {
            var id = $(this).data('id');
            var cluster_id = $(this).val();
            $.ajax({
                url: "/admin/editCluster",
                method: "GET",
                data: {
                    id: id,
                    cluster_id: cluster_id
                },
                dataType: 'json',
                success: function(data) {
                    if (data.message != '') {
                        alert('Failed to update. Cluster already exist');
                    } else {
                        var table = $('#campusTable').DataTable();
                        table.draw();
                    }
                },
                error: function() {
                    alert('Something went wrong. Please try again later!');
                }
            });
        });





        $('.campus_change').on('click', function(e) {
            var select = document.querySelector('#campuses_select')
            select.value = e.target.getAttribute('value');
            select.dispatchEvent(new Event('change'));
        });

        $(document).on('click', '.box-input', function() {
            $(this).next('input').attr('type', 'text').focus();
            $(this).hide();
            $(this).attr('type', 'hidden');
        });
        $(document).on('focusout', '.edit_campusKey', function() {
            $(this).attr('type', 'hidden');
            $(this).prev('div').show();
        });

        $(document).on('click', '.input-name', function() {
            $(this).next('input').attr('type', 'text').focus();
            $(this).hide();
            $(this).attr('type', 'hidden');
        });
        $(document).on('focusout', '.edit_name', function() {
            $(this).attr('type', 'hidden');
            $(this).prev('div').show();
        });

        $(document).on('click', '.cluster_id', function() {
            $(this).next('div').show();
            $(this).hide();
        });
        $(document).on('focusout', '.select_cluster', function() {
            $(this).prev('div').show();
            $(this).hide();
        });
    </script>
@endsection
