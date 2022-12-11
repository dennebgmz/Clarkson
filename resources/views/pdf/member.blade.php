<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <!DOCTYPE html>
    <html>

    <head>
        <style>
            table {
                font-family: arial, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

            td,
            th {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
            }

            tr:nth-child(even) {
                background-color: #dddddd;
            }
        </style>
    </head>

    <body>

        <h2>HTML Table</h2>

        <table>
            <tr>
                <th>Member ID</th>
                <th>Name</th>
                <th>Membership Date</th>
                <th>Campus</th>
                <th>Position</th>
            </tr>
            <tbody>
                @if (count($member))
                    @foreach ($member as $row)
                        <tr>
                            <td>{{$row->member_no}}</td>
                            <td>{{$row->last_name . ', ' . $row->first_name . ' ' . $row->middle_name}}</td>
                            <td>{{date("D M j, Y", strtotime($row->memdate))}}</td>
                            <td>{{$row->campus}}</td>
                            <td>{{$row->position_id}}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5">No Data Found</td>
                    </tr>
                @endif
            </tbody>
        </table>

    </body>

    </html>


</body>

</html>
