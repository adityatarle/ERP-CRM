<!DOCTYPE html>
<html>
<head>
    <title>Purchase Order - {{ $purchaseOrder->purchase_order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>MAULI</h2>
            <h3>Purchase Order</h3>
            <p>PO Number: {{ $purchaseOrder->purchase_order_number }}</p>
            <p>Date: {{ $purchaseOrder->order_date }}</p>
        </div>

        <div class="details">
            <p><strong>Party:</strong> {{ $purchaseOrder->party->name }}</p>
            <p><strong>GST/IN:</strong> {{ $purchaseOrder->party->gst_in ?? 'N/A' }}</p>
            <p><strong>Email:</strong> {{ $purchaseOrder->party->email ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $purchaseOrder->party->phone_number ?? 'N/A' }}</p>
            <p><strong>Address:</strong> {{ $purchaseOrder->party->address ?? 'N/A' }}</p>
            <p><strong>Status:</strong> {{ $purchaseOrder->status }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>
</html>