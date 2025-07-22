{{-- This is the footer partial: resources/views/invoices/pdf/_footer.blade.php --}}
<div class="invoice-footer">
    <p>Company PAN: <b>{{ $company['pan'] }}</b></p>

    <!-- Footer Layout -->
    <table class="footer-section" style="margin-top:10px;">
        <tr>
            <td style="width: 50%;">
                <p class="font-bold">Bank Details:</p>
                <p>A/c Holder Name: <b>{{ $company['account_holder'] }}</b></p>
                <p>Bank Name: <b>{{ $company['bank_name'] }}</b></p>
                <p>A/c NO.: <b>{{ $company['account_no'] }}</b></p>
                <p>Branch & IFS Code: <b>{{ $company['ifsc_code'] }}</b></p>
            </td>
            <td style="width: 50%; text-align: right;">
                <div style="margin-top: 20px;">
                    <p>For {{ $company['name'] }}</p>
                    <p style="margin-top: 50px;"><b>Authorized Signatory</b></p>
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 10px;">
        <p class="font-bold">Declaration:</p>
        <p style="font-size:10px;">
            We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.
        </p>
    </div>
</div>