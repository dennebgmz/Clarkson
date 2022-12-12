 <link href="//cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.4.1/css/simple-line-icons.css" rel="stylesheet">
<style type="text/css">
table {
    border-collapse: collapse;
}
th {
color: #414042!important;
font-family: Fira Sans,sans-serif;
font-size: 15px;
}
tr {
color: #636569!important;
font-family: Fira Sans,sans-serif;
font-size: 15px;
}
</style>

    <div class="">
      <div class="">
        <div class="">
          <img src="{!! asset('assets/images/uppfi-logo-sm.png') !!}" alt="UPPFI">
          <span class="" style="vertical-align: middle; font-size: 25px; color: #414042!important;">
            University of the Philippines Provident Fund Inc.
          </span>
        </div>
      </div>
    </div>
    <div class="">
    <div style="padding: 30px;">
      <div class="" style="color: #414042!important; font-family: Fira Sans,sans-serif; font-size: 15px;">
        Statement Date: {{ date("m/d/Y") }}
      </div>
    </div>

      <div align="center" class="">
        <div class="" style="color: #414042!important; font-family: Fira Sans,sans-serif; font-size: 20px;">
          Report for {{ $campusname }} 
        </div>
        <table width="100%" class="" cellspacing="1000" style="padding: 30px; padding-bottom: 0px!important;">
          <tr>
            <th class="">Campus Equity</th>
            <th></th>
          </tr>
          <tr>
            <td class="">
            Total Members
            </td>
            <td class="" style="text-align: right;">
               {{ $memberscount }} 
            </td>
          </tr>
          <tr>
            <td class="">Total Loans Granted</td>
            <td class="" style="text-align: right;">PHP {{ number_format($upcontri,2) }} </td>
          </tr>
          <tr>
            <td class="">Total Member Contribution</td>
            <td class="" style="text-align: right;">PHP {{ number_format($membercontri,2) }}</td>
          </tr>
          <tr>
            <td class="">Earnings from UP Contribution</td>
            <td class="" style="text-align: right;">PHP {{ number_format($earningsUP,2) }}</td>
          </tr>
          <tr>
          <td class="">Earnings on Member Contributions</td>
            <td class="" style="text-align: right;">PHP {{ number_format($earningsMember,2) }}</td>
          </tr>
          <tr>
            <td class="">Total Members' Outstanding Loans</td>
            <td class="" style="text-align: right;">PHP {{ number_format($totalloansgranted,2) }}</td>
          </tr>
          <tr class="">
            <th>Total Members' Equity</th>
            <th class="" style="text-align: right;">PHP {{ number_format($totalequity,2) }}</th>
          </tr>
        </table>
        
         <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <p style="color:red!important; font-size:12px!important">Note: This is a computer generated document.<br>No signature required. For questions or clarifications, please contact us at www.upprovidentfund.com</p>
      </div>
    </div>
    <script src="//ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js"></script>  
<script>
  WebFont.load({
    google: {
      families: ['Fira Sans:300,400,500,600,700']
    }
  });
</script>
<script src="{{ asset('/dist/vendor.js') }}"></script>
