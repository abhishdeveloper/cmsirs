<?php

namespace App\Helpers;

use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;

class PdfGenerator {

    public static function generateInvoice(array $order): string {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        // Basic Invoice HTML Template
        $html = '
        <html>
        <head>
            <style>
                body { font-family: sans-serif; font-size: 14px; color: #333; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h1 { color: #2563eb; }
                .details { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
                .details td { padding: 5px; vertical-align: top; }
                .items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .items th, .items td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                .items th { background-color: #f3f4f6; }
                .totals { width: 50%; float: right; border-collapse: collapse; }
                .totals td { padding: 5px; text-align: right; }
                .totals .bold { font-weight: bold; }
                .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #777; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>MediCare Tax Invoice</h1>
                <p>Order #' . htmlspecialchars($order['order_number']) . '</p>
            </div>
            <table class="details">
                <tr>
                    <td>
                        <strong>Billed To:</strong><br>
                        ' . htmlspecialchars($order['user']['first_name'] . ' ' . $order['user']['last_name']) . '<br>
                        ' . htmlspecialchars($order['shipping_address']['street_address']) . '<br>
                        ' . htmlspecialchars($order['shipping_address']['city'] . ', ' . $order['shipping_address']['state']) . '<br>
                        ' . htmlspecialchars($order['shipping_address']['postal_code']) . '
                    </td>
                    <td>
                        <strong>Order Date:</strong> ' . date('d M Y', strtotime($order['created_at'])) . '<br>
                        <strong>Payment Method:</strong> Razorpay<br>
                        <strong>Payment ID:</strong> ' . htmlspecialchars($order['razorpay_payment_id'] ?? 'Pending') . '
                    </td>
                </tr>
            </table>

            <table class="items">
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($order['items'] as $item) {
            $html .= '
                    <tr>
                        <td>' . htmlspecialchars($item['product_name']) . '<br><small>SKU: ' . htmlspecialchars($item['sku']) . '</small></td>
                        <td>' . $item['quantity'] . '</td>
                        <td>₹' . number_format($item['price'], 2) . '</td>
                        <td>₹' . number_format($item['total'], 2) . '</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>

            <table class="totals">
                <tr>
                    <td>Subtotal:</td>
                    <td>₹' . number_format($order['subtotal'], 2) . '</td>
                </tr>';

        if ($order['discount_amount'] > 0) {
            $html .= '
                <tr>
                    <td>Discount:</td>
                    <td>-₹' . number_format($order['discount_amount'], 2) . '</td>
                </tr>';
        }

        $html .= '
                <tr>
                    <td>Shipping:</td>
                    <td>₹' . number_format($order['shipping_amount'], 2) . '</td>
                </tr>
                <tr>
                    <td>Tax (GST 18%):</td>
                    <td>₹' . number_format($order['tax_amount'], 2) . '</td>
                </tr>
                <tr>
                    <td class="bold">Total Amount:</td>
                    <td class="bold">₹' . number_format($order['total_amount'], 2) . '</td>
                </tr>
            </table>

            <div style="clear:both;"></div>

            <div class="footer">
                <p>Standard delivery estimated within 3-5 business days.</p>
                <p>Thank you for shopping with MediCare!</p>
            </div>
        </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        // Define directory to save PDFs securely
        $pdfDir = __DIR__ . '/../../database/invoices/';
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0777, true);
        }

        $filename = 'Invoice_' . $order['order_number'] . '.pdf';
        $filepath = $pdfDir . $filename;

        file_put_contents($filepath, $output);

        return $filepath;
    }
}
