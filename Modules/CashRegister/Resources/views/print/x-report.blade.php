<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ isRtl() ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ restaurant()->name }} - X-Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        [dir="rtl"] {
            text-align: right;
        }

        [dir="ltr"] {
            text-align: left;
        }

        .receipt {
            width: {{ ($width ?? 80) - 5 }}mm;
            padding: {{ $thermal ? '1mm' : '6.35mm' }};
            page-break-after: always;
        }

        .header {
            text-align: center;
            margin-bottom: 3mm;
        }

        .restaurant-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .report-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 2mm;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 2mm 0;
        }

        .info-section {
            margin-bottom: 3mm;
            font-size: 9pt;
        }

        .info-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-section table td {
            padding: 0;
            margin: 0;
            vertical-align: top;
        }

        .info-section table td:first-child {
            text-align: left;
        }

        .info-section table td:last-child {
            text-align: right;
        }

        [dir="rtl"] .info-section table td:first-child {
            text-align: right;
        }

        [dir="rtl"] .info-section table td:last-child {
            text-align: left;
        }

        .financial-section {
            margin-bottom: 3mm;
            font-size: 9pt;
        }

        .financial-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .financial-section table td {
            padding: 0;
            margin: 0;
            vertical-align: top;
            padding-bottom: 1mm;
        }

        .financial-section table td:first-child {
            text-align: left;
        }

        .financial-section table td:last-child {
            text-align: right;
        }

        [dir="rtl"] .financial-section table td:first-child {
            text-align: right;
        }

        [dir="rtl"] .financial-section table td:last-child {
            text-align: left;
        }

        .financial-line {
            margin-bottom: 1mm;
        }

        .financial-line table {
            width: 100%;
            border-collapse: collapse;
        }

        .financial-line table td {
            padding: 0;
            margin: 0;
            vertical-align: top;
        }

        .financial-line table td:first-child {
            text-align: left;
        }

        .financial-line table td:last-child {
            text-align: right;
        }

        [dir="rtl"] .financial-line table td:first-child {
            text-align: right;
        }

        [dir="rtl"] .financial-line table td:last-child {
            text-align: left;
        }

        .total-line {
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 1mm;
            margin-top: 1mm;
            font-size: 11pt;
        }

        .total-line table {
            width: 100%;
            border-collapse: collapse;
        }

        .total-line table td {
            padding: 0;
            margin: 0;
            vertical-align: top;
        }

        .total-line table td:first-child {
            text-align: left;
        }

        .total-line table td:last-child {
            text-align: right;
        }

        [dir="rtl"] .total-line table td:first-child {
            text-align: right;
        }

        [dir="rtl"] .total-line table td:last-child {
            text-align: left;
        }

        .footer {
            text-align: center;
            margin-top: 3mm;
            font-size: 9pt;
            padding-top: 2mm;
            border-top: 1px dashed #000;
            padding-bottom: 5mm;
        }

        @media print {
            @page {
                margin: 0;
                size: 80mm auto;
            }
        }
    </style>
</head>

<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="restaurant-name">{{ restaurant()->name }}</div>
            <div class="report-title">@lang('cashregister::app.xReport')</div>
        </div>

        <div class="separator"></div>

        <!-- Report Information -->
        <div class="info-section">
            <table>
                <tr>
                    <td>@lang('cashregister::app.generatedOn')</td>
                    <td>{{ $reportData['generated_at']->timezone(timezone())->format(dateFormat() . ' ' . timeFormat()) }}</td>
                </tr>
                <tr>
                    <td>@lang('cashregister::app.branch')</td>
                    <td>{{ $reportData['session']->branch->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>@lang('cashregister::app.register')</td>
                    <td>{{ $reportData['session']->register->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>@lang('cashregister::app.cashier')</td>
                    <td>{{ $reportData['session']->cashier->name ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <div class="separator"></div>

        <!-- Financial Data -->
        <div class="financial-section">
            <table>
                <tr>
                    <td>@lang('cashregister::app.openingFloat')</td>
                    <td>{{ currency_format($reportData['opening_float'], restaurant()->currency_id) }}</td>
                </tr>
                <tr>
                    <td>@lang('cashregister::app.cashSales')</td>
                    <td>{{ currency_format($reportData['cash_sales'], restaurant()->currency_id) }}</td>
                </tr>
                <tr>
                    <td>@lang('cashregister::app.cashIn')</td>
                    <td>{{ currency_format($reportData['cash_in'], restaurant()->currency_id) }}</td>
                </tr>
                <tr>
                    <td>@lang('cashregister::app.cashOut')</td>
                    <td>{{ currency_format($reportData['cash_out'], restaurant()->currency_id) }}</td>
                </tr>
                <tr>
                    <td>@lang('cashregister::app.safeDrops')</td>
                    <td>{{ currency_format($reportData['safe_drops'], restaurant()->currency_id) }}</td>
                </tr>
                <tr>
                    <td>@lang('cashregister::app.refunds')</td>
                    <td>{{ currency_format($reportData['refunds'], restaurant()->currency_id) }}</td>
                </tr>
            </table>
        </div>

        <!-- Expected Cash Total -->
        <div class="financial-line total-line">
            <table>
                <tr>
                    <td>@lang('cashregister::app.expectedCash')</td>
                    <td>{{ currency_format($reportData['expected_cash'], restaurant()->currency_id) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>@lang('cashregister::app.thankYou')</div>
        </div>
    </div>
    <script>
        window.onload = function () {
            try { window.print(); } catch (e) { /* noop */ }
        }
    </script>
</body>

</html>
