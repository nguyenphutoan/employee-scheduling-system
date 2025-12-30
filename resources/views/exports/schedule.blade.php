<table>
    <thead>
        <tr>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000;">Mã NV</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000000; width: 200px;">Tên NV</th>
            
            @foreach($daysMap as $day)
                <th style="font-weight: bold; text-align: center; border: 1px solid #000000; width: 150px;">
                    {{ $weekDates[$day] }}
                </th>
            @endforeach

            <th style="font-weight: bold; text-align: center; border: 1px solid #000000;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
            <tr>
                <td style="text-align: center; border: 1px solid #000000;">{{ $user->UserName }}</td>
                <td style="border: 1px solid #000000;">{{ $user->FullName }}</td>

                @foreach($daysMap as $day)
                    <td style="text-align: center; border: 1px solid #000000; wrap-text: true;">
                        {{ $schedule[$user->UserID][$day] ?? '' }}
                    </td>
                @endforeach

                <td style="font-weight: bold; text-align: center; border: 1px solid #000000;">
                    {{ number_format($totalHours[$user->UserID], 1) }}
                </td>
            </tr>
        @endforeach
        
        {{-- Dòng tổng cộng --}}
        <tr>
            <td colspan="2" style="font-weight: bold; text-align: right; border: 1px solid #000000;">TOTAL</td>
            @foreach($daysMap as $day)
                <td style="font-weight: bold; text-align: center; border: 1px solid #000000;">
                    {{ number_format($dailyTotals[$day], 1) }}
                </td>
            @endforeach
            <td style="font-weight: bold; text-align: center; border: 1px solid #000000;">
                {{ number_format($grandTotal, 1) }}
            </td>
        </tr>
    </tbody>
</table>