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
    <div class="container mp-container">
        <div class="row no-gutters mp-mt5">
            <div class="col-12 mp-ph2 mp-pv2 mp-text-fs-large mp-text-c-accent">
                <a class="mp-link mp-link--accent" href="{{ url('/admin/loans') }}">
                    <i class="mp-icon icon-arrow-left mp-mr1 mp-text-fs-medium"></i>
                    Back to Active Loan Masterlist
                </a>
            </div>
        </div>

        <div class="row no-gutters mp-mb4">
            <div class="col-12 mp-ph2 mp-pv2">
                <div class="row no-gutters">
                    <div class="col">
                        <div class="mp-ph4 mp-pv4 mp-card mp-card--plain">
                            <div class="row">
                                <div class="col">
                                    <div class="mp-mb4">
                                        <div>
                                            <span class="mp-text-fw-heavy">
                                                {{ $member->last_name . ', ' . $member->first_name . ' ' . $member->middle_name }}
                                            </span>
                                        </div>
                                        <div>
                                            Member ID: {{ $member->member_no }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-col-right mp-pv3">
                                    <span class="span-1">
                                        <a href="#" id="export_loanDetails"
                                            class="mp-ml2 mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small up-button up-button-width"
                                            title="Export Data based on Date range and Loan Type"> Export Data </a>
                                    </span>
                                    <span class="span-1">
                                        <a href="{{ url('/admin/generate/loanspertype/' . $loan->loan_id) }}" target="_blank"
                                            class="mp-ml2 mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small up-button up-button-width">
                                            Download PDF
                                        </a>
                                    </span>
                                </div>
                            </div>

                            <div class="row bg-card mb-1">
                                <div class="col">
                                    <div class="row items-between p-1 ">
                                        <div class="col-sm-12 col-md-7">
                                            <div class="row mp-text--c-white">
                                                <label for="row">Filter By Date</label>
                                            </div>
                                            <div class="row date_range">
                                                <input type="date" id="from" class="radius-1 border-1 date-input outline"
                                                    style="height: 30px;">
                                                <span for="" class="self_center mh-1 mp-text--c-white">to</span>
                                                <input type="date" id="to" class="radius-1 border-1 date-input outline"
                                                    style="height: 30px;">
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-4 ph-0 flex-right">
                                            {{-- <input type="text" id="search_member"
                                            class="radius-1 border-1 date-input outline search_field input-height-1 self-end input-responsive-1"
                                            placeholder="Search By Member ID"> --}}
                                            <input type="text" id="search_value"
                                                class="radius-1 border-1 date-input outline search_field input-height-1 self-end input-responsive-1"
                                                placeholder="Search By Transaction No">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mp-overflow-x">
                                <table class="mp-table mp-text-fs-small" id="detailsTable" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Transaction</th>
                                            <th>Account</th>
                                            <th>Monthly Amortization</th>
                                            <th>Interest</th>
                                            <th>Amount</th>
                                            <th>Principal Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                            <div class="mp-card__footer__pair">
                                <div class="mp-card__footer__split mp-text-left">

                                    
                                </div>
                                <input type="hidden" id="id" value="{{ Request::segment(3) }}">
                                <div>
                                    {{-- {{$loans->links('pagination.default')}} --}}
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
    <script type="text/javascript">
        $('#loading').show();
        $(window).load(function() {
            $('#loading').hide();
        });
        $(document).ready(function() {
           var id = $('#id').val();
            var detailsTable = $('#detailsTable').DataTable({
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
                    "url": "{{ route('getLoanDetails') }}",
                    "data": function(data) {
                        data.id = $('#id').val();
                        // data.loan_type = $('#loan_type').val();
                        data.dt_from = $('#from').val();
                        data.dt_to = $('#to').val();
                        data.searchValue = $('#search_value').val();
                    }
                },
            });

            $('#search_value').on('change', function() {
                detailsTable.draw();
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
                    detailsTable.draw();
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
                    detailsTable.draw();
                }
            });

            $(document).on('click', '#export_loanDetails', function(e) {
                if ($('#from').val() != "" && $('#to').val() != "") {
                    var dt_from = $('#from').val();
                    var dt_to = $('#to').val();
                } else {
                    var dt_from = 0;
                    var dt_to = 0;
                }
                var url = "{{ URL::to('/admin/export_loanDetails') }}" + '/' + id + '/' + dt_from + '/' + dt_to; //YOUR CHANGES HERE...
                window.open(url, '_blank');
            });
        });
    </script>
@endsection
