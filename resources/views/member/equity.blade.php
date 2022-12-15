@extends('layouts/main')
@section('content_body')
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
<link href="/css/css-module/global_css/global.css" rel="stylesheet">
<div class="container mp-container">
 
  <div class="row no-gutters mp-mt5">
    <div class="col-6 mp-ph2 mp-pv2 mp-text-fs-large mp-text-c-primary">
      Your Account History
    </div>
    <div class="col-6">
      <div class=" mp-top-button" style="display: flex; flex-direction: row; gap: 10px; justify-content: right; margin-right:30px; ">
                                @if (getUserdetails()->role == 'SUPER_ADMIN')
                                <span>
                                    <a href="{{ url('/admin/summary') }}" class="toggle text_link mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small">Generate Summary Report</a>
                                </span>
                                @endif
                                <span>
                                    <a href="{{ url('/admin/exportMember') }}" class="toggle text_link mp-button mp-button--primary mp-button--ghost mp-button--raised mp-button--mini mp-text-fs-small">Export Data</a>
                                    </span>
                                {{-- <button type="button" class="mp-button mp-button--accent" id="printMember">Print</button> --}}
        </div>
    </div>
  </div>
    
   

  <div class="row no-gutters mp-mb4">
    <div class="col-12 mp-ph2 mp-pv2">
      
      <div class="row no-gutters">
        <div class="col">
              <div class="container" >
           <!-- filter section  -->
          <div class="row no-gutters mp-mb4">
            <div class="col-12 ">
              <div class="row no-gutters">
                <div class="col-6 col-lg-3">
                  <div class="mp-tab active-tab">
                    <a class="mp-tab__link" href="{{ url('/member/equity') }}">
                      Member's Equity History
                    </a>
                  </div>
                </div>

                <div class="col-6 col-lg-3">
                  <div class="mp-tab unactive-tab">
                    <a class="mp-tab__link" href="{{ url('/member/loans') }}">
                      Loan Transactions
                    </a>
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
                                <div class="row items-between " style ="margin:15px">
                                    <div class="col-md-12 col-xl-6">
                                        <div class="row">
                                           <label for="row">Fields</label>
                                        </div>
                                        <div class="row field-filter">
                                                <select name="" class="radius-1 outline select-field" style="width: 100%; height: 30px"
                                                id="department_select">
                                                <option value="">Filter By Account</option>
                                               <!-- loop here -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-xl-5">
                                        <div class="row">
                                           <label for="row">Date Range</label>
                                        </div>
                                        <div class="row date_range">
                                            <input type="date" id="from" class="radius-1 border-1 date-input outline" style="height: 30px;">
                                            <span for="" class="self_center mv-1" style="margin:5px;">to</span>
                                            <input type="date" id="to" class="radius-1 border-1 date-input outline" style="height: 30px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
                <div class="row no-gutters">
                    <div class="col ">
                        <div class="mp-ph4 mp-pv4 mp-card mp-card--tabbed">

                            <div class="mp-overflow-x">
                              <table class="mp-table mp-text-fs-small">
                                <thead>
                                  <tr>
                                    <th>Date</th>
                                    <th>Transaction</th>
                                    <th>Account</th>
                                    <th class="mp-text-center">Debit</th>
                                    <th class="mp-text-center">Credit</th>
                                    <th class="mp-text-center">Balance</th>
                                  </tr>
                                </thead>
                                <tbody>
                                <?php
                                
                                $curdate="";
                                $amount="";
                                $reference="";
                                foreach ($equity as $key => $value) {
                            
                                  
                                  if($curdate==$value->date && number_format($value->amount,2)==$amount&&$reference==$value->reference_no)
                                  {
                                    unset($equity[$key-1]); 
                                    unset($equity[$key]);
                                  
                                  }
                              
                                  $curdate=$value->date;
                                  $amount=number_format(abs($value->amount),2);
                                }
                                ?>
                                  <?php
                                $curdate=''; 
                                $reference="";?>
                                @foreach($equity as $contri)
                                
                                <tr>
                                  <td>{{ date("m/d/Y", strtotime($contri->date)) }}</td>
                                  <td>{{ $contri->reference_no }}</td>
                                  <td>{{ $contri->name }}</td>
                                  <td class="mp-text-right">
                                    {{ $contri->amount < 0 ? 'PHP '.number_format(abs($contri->amount),2) : '' }}
                                  </td>
                                  <td class="mp-text-right">
                                    {{ $contri->amount >= 0 ? 'PHP '.number_format($contri->amount,2) : '' }}
                                  </td>
                                  @if($curdate==$contri->date)
                                  <td class="mp-text-right"></td>
                                  
                                    @else
                                    <td class="mp-text-right">{{ 'PHP '.number_format($contri->balance,2) }}</td>
                                    <?php
                                    $curdate=$contri->date;
                                    ?>
                                    @endif
                                  </tr>
                                  
                                  @endforeach
                              </tbody>
                            </table>
                          </div>
                          
                          <div class="mp-card__footer__pair">
                            <div class="mp-card__footer__split mp-text-left">
                              <a href="{{url('/generate/equity')}}" target="_blank" class="mp-link mp-link--primary">
                                Download PDF
                              </a>
                            </div>
                            <div>
                            
                              {{$equity->links('pagination.default')}}
                        
                            </div>
                          </div>
                          
                        </div>
                    </div>
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

@section('script')
<script src="{{ asset('/dist/dashboard.js') }}"></script>   
@endsection
