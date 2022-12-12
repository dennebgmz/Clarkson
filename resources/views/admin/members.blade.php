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
                Members
            </div>
        </div>
        <div class="row no-gutters mp-mb4">
            <div class="col-12 mp-ph2 mp-pv2">
                <div class="row no-gutters">
                    <div class="col">
                        <div class="mp-ph4 mp-pv4 mp-card">
                            <div>
                                @if (getUserdetails()->role == 'SUPER_ADMIN')
                                    <a href="{{ url('/admin/summary') }}" class="mp-button mp-button--accent">Generate
                                        Summary
                                        Report</a>
                                @endif
                            </div>

                            <select name="" class="mp-link mp-link--accent" style="width: 100%;"
                                id="campuses_select">
                                <option value="">Filter By Campus</option>
                                @foreach ($campuses as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>

                            <select name="" class="mp-link mp-link--accent" style="width: 100%;"
                                id="department_select">
                                <option value="">Filter By Department</option>
                                @foreach ($department as $row)
                                    <option value="{{ $row->id }}">{{ $row->description }}</option>
                                @endforeach
                            </select>

                            <a href="{{ url('/admin/exportMember') }}" class="mp-button mp-button--accent">Export Data</a>
                            {{-- <button type="button" class="mp-button mp-button--accent" id="printMember">Print</button> --}}

                            <input type="date" id="from">
                            <input type="date" id="to">

                            <hr>
                            <div class="mp-overflow-x">
                                <table class="mp-table mp-text-fs-small" id="membersTable" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th></th>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(window).load(function() {
            $('#loading').hide();
        });
        $(document).ready(function() {
            $('#loading').show();
            var tableMember = $('#membersTable').DataTable({
                language: {
                    search: '',
                    searchPlaceholder: "Search Here...",
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><br>Loading...',
                },
                "ordering": false,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "{{ route('dataProcessing') }}",
                    "data": function(data) {
                        data.campus = $('#campuses_select').val();
                        data.department = $('#department_select').val();
                        data.dt_from = $('#from').val();
                        data.dt_to = $('#to').val();
                    }
                },
            });

            $('#campuses_select').on('change', function() {
                tableMember.draw();
            });
            $('#department_select').on('change', function() {
                tableMember.draw();
            });
            $('#from').on('change', function() {
                if($('#from').val() < $('#to').val())
                {
                    alert('Invalid date please check date');
                }else{
                    tableMember.draw();
                }
                
            });
            $('#to').on('change', function() {
                if($('#from').val() < $('#to').val())
                {
                    alert('Invalid date please check date');
                    $('#to').val() = '';
                }else{
                    tableMember.draw();
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


        });
    </script>
@endsection
