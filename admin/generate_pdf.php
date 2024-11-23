<?php
require('../fpdf/fpdf.php');
include '../functions/db.php';

function convertToUSD($amountInManat)
{
    $conversionRate = 0.59;
    return $amountInManat * $conversionRate;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];

    $order_query = "
        SELECT o.id, o.total, o.status, o.created_at, o.updated_at, u.email, u.phone 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    $order = $order_result->fetch_assoc();

    $product_query = "
        SELECT oi.quantity, oi.price, c.Model 
        FROM order_items oi
        JOIN carpartsdatabase c ON oi.product_id = c.id
        WHERE oi.order_id = ?";
    $stmt = $conn->prepare($product_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $product_result = $stmt->get_result();

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetDrawColor(200, 200, 200);

    $pdf->Cell(0, 10, 'Order Receipt', 0, 1, 'C', true);
    $pdf->Ln(4);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Order ID: ', 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, $order['id'], 0, 1);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Customer: ', 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, $order['email'], 0, 1);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Phone: ', 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, $order['phone'], 0, 1);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Order Date: ', 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, date("M j, Y", strtotime($order['created_at'])), 0, 1);

    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Products:', 0, 1, '', true);
    $pdf->Ln(4);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(70, 10, 'Product Name', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Price (AZN)', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Price (USD)', 1, 0, 'C', true);
    $pdf->Ln();

    $subtotal = 0;

    $pdf->SetFont('Arial', '', 12);
    while ($product = $product_result->fetch_assoc()) {
        $product_total = $product['quantity'] * $product['price'];
        $subtotal += $product_total;

        $usdAmount = convertToUSD($product_total);

        $pdf->Cell(70, 10, $product['Model'], 1, 0, 'L');
        $pdf->Cell(30, 10, $product['quantity'], 1, 0, 'C');
        $pdf->Cell(45, 10, number_format($product_total, 2) . ' AZN', 1, 0, 'R');
        $pdf->Cell(45, 10, number_format($usdAmount, 2) . ' USD', 1, 0, 'R');
        $pdf->Ln();
    }

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(190, 10, 'Subtotal: ' . number_format($subtotal, 2) . ' AZN (USD $' . number_format(convertToUSD($subtotal), 2) . ')', 0, 1, 'R');
    $tax = $subtotal * 0.20;
    $total = $subtotal + $tax;
    $pdf->Cell(190, 10, 'Tax (20%): ' . number_format($tax, 2) . ' AZN (USD $' . number_format(convertToUSD($tax), 2) . ')', 0, 1, 'R');
    $pdf->Cell(190, 10, 'Total: ' . number_format($total, 2) . ' AZN (USD $' . number_format(convertToUSD($total), 2) . ')', 0, 1, 'R');

    $pdf->Output('D', 'Order_Receipt_' . $order_id . '.pdf');
}
