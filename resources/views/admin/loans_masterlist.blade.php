@extends('layouts/main')
<style>
    #loansTable td:nth-child(5),
    #loansTable td:nth-child(6),
    #loansTable td:nth-child(7),
    #loansTable td:nth-child(8) {
        text-align: center;
    }
</style>
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
                Active Loan Masterlist
            </div>
        </div>
        <div class="row no-gutters mp-mb4">
            <div class="col-12 mp-ph2 mp-pv2">
                <div class="row no-gutters">
                    <div class="col">
                        <div class="mp-ph4 mp-pv4 mp-card">
                            <div class="mp-overflow-x">
                                <table class="mp-table mp-text-fs-small" id="loansTable" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Loan Type</th>
                                            <th>Member ID</th>
                                            <th>Member Name</th>
                                            <th class="mp-text-center">Last Transaction Date</th>
                                            <th class="mp-text-center">Balance</th>
                                            <th class="mp-text-center">Start Amort Date</th>
                                            <th class="mp-text-center">End Amort Date</th>
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
@endsection
@section('scripts')
    <script src="{{ asset('dist/adminActiveLoans.js') }}"></script>
    <script type="text/javascript">
        $('#loading').show();
        $(window).load(function() {
            $('#loading').hide();
        });
        $(document).ready(function() {
            $('#loading').show();
            $('#loansTable').DataTable({
                language: {
                    search: '',
                    searchPlaceholder: "Search Here...",
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><br>Loading...',
                },
                "ordering": false,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "<?= route('loanData') ?>",
                    "type": "POST",
                    "data": {
                        "_token": "<?= csrf_token() ?>"
                    }
                },
            });

            $(document).on('click', '.view_member', function(e) {
                var id = $(this).attr('id');
                console.log(id);
                var url = "{{ URL::to('/admin/member_soa/') }}" + '/' + id; //YOUR CHANGES HERE...
                window.location.href = url;
            });
        });
    </script>
@endsection
