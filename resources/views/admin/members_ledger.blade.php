@extends('layouts/main')
<style>
    .underline {
        height: 4px;
        background:#2d3436;
        margin-top: 10px;
        margin-bottom: 10px;
    }
</style>
@section('content_body')
    <link href="/css/css-module/global_css/global.css" rel="stylesheet">
    <div class="container mp-container ">
        <div class="row no-gutters mp-mt5">
            <div class="col-12 mp-ph2 mp-pv2 mp-text-fs-large mp-text-c-accent">
                Members Ledger
            </div>
        </div>
        <div class="row no-gutters mp-mb4">
            <div class="col-12 ">
                <div class="row no-gutters">
                    <div class="col ">
                        <div class="mp-ph3 mp-pv4 mp-card ">
                            <div class="mp-text-no-lh">
                                <div class="mp-mb1 mp-text-c-light-gray mp-text-fs-small">Member ID</div>
                                <div class="mp-text-fs-large mp-text-fw-heavy">
                                    {{ isset($member->member_no) ? $member->member_no : '' }}
                                </div>
                            </div>
                            <div class="mp-mh2 mp-text-no-lh mp-text-word-wrap mp-dashboard__title">
                                {{ isset($member->last_name) ? $member->last_name : '' }}, {{ isset($member->first_name) ? $member->first_name : '' }} {{ isset($member->middle_name) ? $member->middle_name[0] : '' }}.
                            </div>
                            <div class="mp-text-no-lh">
                                <div class="mp-mb2 mp-text-fs-large">{{ isset($member->campus_name) ? $member->campus_name : '' }}</div>
                                <div class="mp-text-fs-large">{{ isset($member->position_id) ? $member->position_id : '' }}</div>
                            </div>
                            <br><br>
                            <div class="mp-text-no-lh">
                                <div class="mp-text-fs-large mp-text-fw-heavy">
                                    SOA Summary
                                </div>
                            </div>
                            <div class="underline"></div>
                            <div class="mp-text-no-lh">
                                <div class="mp-text-fs-large mp-text-fw-heavy">
                                    Your Member's Equity
                                </div>
                            </div>
                            <br>
                            <div class="mp-text-no-lh">
                                <div class="mp-mb1 mp-text-fs-small mp-text-fw-heavy">Total Member's Contribution</div>
                                <div class="mp-mb1 mp-text-fs-small">PHP {{ isset($contributions['membercontribution']) ? number_format($contributions['membercontribution'], 2) : '' }}</div>

                                <div class="mp-mb1 mp-text-fs-small mp-text-fw-heavy">Total UP Contribution</div>
                                <div class="mp-mb1 mp-text-fs-small">PHP {{ isset($contributions['upcontribution']) ? number_format($contributions['upcontribution'], 2) : '' }}</div>

                                <div class="mp-mb1 mp-text-fs-small mp-text-fw-heavy">
                                    Earnings on Member's Contribution
                                </div>
                                <div class="mp-mb1 mp-text-fs-small">PHP {{ isset($contributions['emcontribution']) ? number_format($contributions['emcontribution'], 2) : '' }}</div>

                                <div class="mp-mb1 mp-text-fs-small mp-text-fw-heavy">Earnings on UP Contribution</div>
                                <div class="mp-mb1 mp-text-fs-small">PHP {{ isset($contributions['eupcontribution']) ? number_format($contributions['eupcontribution'], 2) : '' }}</div>
                            </div>
                            <div class="underline"></div>

                            
                            <div class="mp-text-no-lh">
                                <div class="mp-mb1 mp-text-fs-small">Total Equity Balance</div>
                                <div class="mp-mb1 mp-text-fs-small mp-text-fw-heavy">PHP {{ isset($totalcontributions) ? number_format($totalcontributions, 2) : '' }}</div>

                                @if (!empty($outstandingloans))
                                    <div class="mp-mb1 mp-text-fs-small">Your Outstanding Loans</div>
                                    @foreach ($outstandingloans as $oloans)
                                        <div class="mp-mb1 mp-text-fs-small">{{ $oloans->type }}</div>
                                        <div class="mp-mb1 mp-text-fs-small mp-text-fw-heavy">PHP {{ number_format($oloans->balance, 2) }}</div>
                                    @endforeach
                            </div>
                                <div class="underline"></div>
                                <div class="mp-text-no-lh">
                                    <div class="mp-mb1 mp-text-fs-small">Total Outstanding Loan Balance</div>
                                    <div class="mp-mb1 mp-text-fs-small mp-text-fw-heavy">PHP {{ number_format($totalloanbalance, 2) }}</div>
                                </div>
                                @endif
                            <div class="underline"></div>
                            <div class="mp-text-no-lh">
                                <div class="mp-text-fs-large mp-text-fw-heavy">
                                    Members Equity History
                                </div>
                                <br>
                                <div class="mp-overflow-x">
                                    <table class="mp-table table_style">
                                        <thead>
                                            <tr class="custom_table_header">
                                                <th>Date</th>
                                                <th>Transaction</th>
                                                <th>Account</th>
                                                <th class="mp-text-right">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($recentcontributions as $contribution)
                                                <tr>
                                                    <td>{{ date('m/d/Y', strtotime($contribution->date)) }}</td>
                                                    <td>{{ $contribution->reference_no }}</td>
                                                    <td>{{ $contribution->name }}</td>
                                                    <td class="mp-text-right">PHP {{ number_format($contribution->amount, 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <br>
                            <div class="underline"></div>
                            <br>
                            <div class="mp-text-no-lh">
                                <div class="mp-text-fs-large mp-text-fw-heavy">
                                    Loan Transactions
                                </div>
                                <br>
                                <div class="mp-overflow-x">
                                    <table class="mp-table table_style">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Account</th>
                                                <th class="mp-text-center">Monthly Amort.</th>
                                                <th class="mp-text-center">Amount</th>
                                                <th class="mp-text-right">Principal Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $date = ''; ?>
                                            @foreach ($recentloans as $loans)
                                                <?php
                                                $samedate = true;
                                                if ($date == date('m/d/Y', strtotime($loans->date))) {
                                                    $samedate = false;
                                                } else {
                                                    $samedate = true;
                                                }
                                                $date = date('m/d/Y', strtotime($loans->date));
                                                ?>
                                                <tr>
                                                    <td>{{ date('m/d/Y', strtotime($loans->date)) }}</td>
                                                    <td class="mp-text-center">{{ $loans->name }}</td>
                                                    <td class="mp-text-center">
                                                        {{ $loans->amortization == 0 ? '' : 'PHP ' . number_format($loans->amortization, 2) }}
                                                    </td>
                                                    <td class="mp-text-center">{{ 'PHP ' . number_format($loans->amount, 2) }}
                                                    </td>
                                                    <td class="mp-text-right">
                                                        {{ !$samedate ? '' : 'PHP ' . number_format($loans->balance, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <br>
                            <div class="underline"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection
