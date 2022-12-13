@extends('layouts/main')
@section('content_body')
    <div class="container mp-container">
        <link href="/css/css-module/global_css/global.css" rel="stylesheet">
        <div class="row row no-gutters mp-mt4">
            <div class="col-12 mp-text-right"  style="display: flex; flex-direction: row; justify-content: right">
                <div class="row no-gutters">
                    <div class="mp-ph2 mp-pv2">
                        <a data-target="myPopup" class="toggle text_link mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small">
                            Manage Campus
                        </a>
                        <a href="#" id="generate_summary" class="mp-ml2 mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small">
                            Print Report
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
                                <a value="All" class="text_link mp-dropdown__item mp-link mp-link--normal campus_change" style="cursor: pointer">All UP Campuses</a>
                                @foreach ($campuses as $row)
                                <a value="{{ $row->id }}" class="text_link mp-dropdown__item mp-link mp-link--normal campus_change" style="cursor: pointer">
                                    {{ $row->name }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4" hidden>
                        <select name="" class="mp-text-field mp-ph3 mp-link mp-link--accent"
                            style="width: 100%; font-size:20px" id="campuses_select">
                            <option value="All">All Campuses</option>
                            @foreach ($campuses as $row)
                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                            @endforeach
                        </select>
                    </div>
                        <!-- <div class="col-lg-4">
                                        <div class="row align-items-center">
                                             <div class ="col-md-5 col-lg-7">
                                              <select name="" class="mp-text-field mp-ph3 mp-link mp-link--accent"
                                                 style="width: 100%; font-size:20px" id="campuses_select">
                                                     <option value="">All Campuses</option>
                                                    @foreach ($campuses as $row)
    <option value="{{ $row->id }}">{{ $row->name }}</option>
    @endforeach
                                             </select>
                                        </div>
                                        <div class ="col-md-7  col-lg-5 ">
                                             <button class="button_style bypass_padding toggle button "
                                             data-target="myPopup">Manage Campus</button>
                                        </div>
                                        </div>
                                    </div> -->

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
                                    <a href="{{ url('/admin/members') }}" class="mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small">
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
                                    <a href="{{ url('/admin/loans') }}" class="mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small">
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



    <div id="myPopup" class="popup hide">
        <div class="popup-header">
            Campus Management
            <span class="close toggle" data-target="myPopup">close</span>
        </div>
        <div class="popup-body">
            <div class="container">
                <form id="addCampus" method="POST">
                    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>"><input type="hidden"
                        name="_token" value="<?php echo csrf_token(); ?>">
                    <div>
                        <label>Campus Key</label>
                        <input type="text" name="campus_key" required />
                    </div>
                    <div>
                        <label>Campus Name</label>
                        <input type="text" name="campus_name" required />
                    </div>
                    <div>
                        <label>Cluster</label>
                        <select name="cluster" required>
                            <option value="">Select Cluster</option>
                            @foreach ($cluster as $row)
                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit">Save Changes</button>
                </form>
                <div class="mp-overflow-x">
                    <table class="mp-table mp-text-fs-small" id="campusTable" cellspacing="0" width="100%">
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
        $(document).on('click', '#generate_summary', function(e) {
            var id = campuses_id;
            console.log(id);
            var url = "{{ URL::to('/admin/summaryreports/') }}" + '/' + id; //YOUR CHANGES HERE...
            // window.location.href = url;
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
                                Swal.fire('Warning!', 'Cannot delete campus. There are records exist', 'warning');
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
        $('.campus_change').on('click', function(e) { 
            var select = document.querySelector('#campuses_select')
            select.value = e.target.getAttribute('value');
            select.dispatchEvent(new Event('change'));
        });
    </script>
@endsection
