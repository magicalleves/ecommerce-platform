<?php
include '../functions/db.php';

// Get the current year or adjust for a specific year
$current_year = date('Y');

// Fetch sales data for all months in the current year
$sales_data_query = "
    SELECT MONTHNAME(STR_TO_DATE(month_num, '%m')) AS month, IFNULL(SUM(o.total), 0) AS total_sales
    FROM (
        SELECT 1 AS month_num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8
        UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
    ) AS months
    LEFT JOIN orders o ON MONTH(o.created_at) = months.month_num AND YEAR(o.created_at) = $current_year
    GROUP BY months.month_num
    ORDER BY months.month_num";
$sales_data_result = $conn->query($sales_data_query);

// Initialize arrays for months and sales
$months = [];
$sales = [];
while ($row = $sales_data_result->fetch_assoc()) {
    $months[] = $row['month'];
    $sales[] = $row['total_sales'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Graph</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.2.0/dist/chart.umd.js"></script>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .graph-container {
            width: 90%;
            max-width: 800px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="graph-container">
        <h2>Sales Graph for <?php echo $current_year; ?></h2>
        <canvas id="salesGraph"></canvas>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const months = <?php echo json_encode($months); ?>;
            const sales = <?php echo json_encode($sales); ?>;

            const ctx = document.getElementById('salesGraph').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Monthly Sales',
                        data: sales,
                        borderColor: '#003F62',
                        backgroundColor: 'rgba(0, 63, 98, 0.2)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Months'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Sales (₼)'
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>