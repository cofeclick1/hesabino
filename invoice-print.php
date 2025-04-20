<?php
require_once 'includes/init.php';

// برای اطمینان از وجود مسیر vendor/autoload.php
$autoloadPaths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    'vendor/autoload.php'
];

$autoloadFound = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $autoloadFound = true;
        break;
    }
}

// بررسی و دریافت شناسه فاکتور
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('شناسه فاکتور نامعتبر است');
}

$invoiceId = (int)$_GET['id'];

// دریافت اطلاعات فاکتور
$sql = "SELECT i.*, 
               c.first_name, 
               c.last_name,
               c.company,
               c.mobile,
               c.address,
               c.national_code,
               c.economic_code,
               u.full_name as created_by_name,
               p.payment_type as last_payment_type
        FROM invoices i
        LEFT JOIN customers c ON i.customer_id = c.id
        LEFT JOIN users u ON i.created_by = u.id
        LEFT JOIN (
            SELECT invoice_id, payment_type
            FROM payments 
            WHERE id IN (
                SELECT MAX(id) 
                FROM payments 
                GROUP BY invoice_id
            )
        ) p ON p.invoice_id = i.id
        WHERE i.id = ?";

$invoice = $db->query($sql, [$invoiceId])->fetch();

if (!$invoice) {
    die('فاکتور مورد نظر یافت نشد');
}

// دریافت آیتم‌های فاکتور
$sql = "SELECT i.*, p.name as product_name, p.code as product_code, p.unit
        FROM invoice_items i
        LEFT JOIN products p ON i.product_id = p.id
        WHERE i.invoice_id = ?
        ORDER BY i.id ASC";

$items = $db->query($sql, [$invoiceId])->fetchAll();

// دریافت تنظیمات شرکت
$settings = [];
$settingsResult = $db->query("SELECT * FROM settings")->fetchAll();
foreach ($settingsResult as $setting) {
    $settings[$setting['key']] = $setting['value'];
}

// دریافت لوگو شرکت
$logo = file_exists('assets/images/logo.png') ? 'assets/images/logo.png' : '';

// تبدیل اعداد به حروف فارسی با استفاده از کتابخانه
function convertNumberToWords($number) {
    static $converter = null;
    if ($converter === null) {
        // تلاش برای استفاده از کتابخانه
        if (class_exists('\MAahn\PersianNumberToWords\PersianNumberToWords')) {
            try {
                $converter = new \MAahn\PersianNumberToWords\PersianNumberToWords();
                return $converter->convert($number);
            } catch (Exception $e) {
                // در صورت خطا از تابع پشتیبان استفاده می‌کنیم
            }
        }
        // استفاده از تابع پشتیبان
        return convertToWordsBackup($number);
    }
    try {
        return $converter->convert($number);
    } catch (Exception $e) {
        return convertToWordsBackup($number);
    }
}

function convertToWordsBackup($number) {
    $ones = array(
        0 => "", 1 => "یک", 2 => "دو", 3 => "سه", 4 => "چهار", 5 => "پنج",
        6 => "شش", 7 => "هفت", 8 => "هشت", 9 => "نه", 10 => "ده",
        11 => "یازده", 12 => "دوازده", 13 => "سیزده", 14 => "چهارده", 
        15 => "پانزده", 16 => "شانزده", 17 => "هفده", 18 => "هجده", 19 => "نوزده"
    );
    
    $tens = array(
        2 => "بیست", 3 => "سی", 4 => "چهل", 5 => "پنجاه",
        6 => "شصت", 7 => "هفتاد", 8 => "هشتاد", 9 => "نود"
    );
    
    $hundreds = array(
        1 => "یکصد", 2 => "دویست", 3 => "سیصد", 4 => "چهارصد", 5 => "پانصد",
        6 => "ششصد", 7 => "هفتصد", 8 => "هشتصد", 9 => "نهصد"
    );
    
    $majorUnits = array(
        0 => "", 1 => "هزار", 2 => "میلیون", 3 => "میلیارد"
    );

    if ($number == 0) return "صفر";
    
    $numStr = (string)intval($number);
    $numLen = strlen($numStr);
    
    // تقسیم عدد به گروه‌های سه‌تایی
    $groups = str_split(str_pad($numStr, ceil($numLen/3)*3, "0", STR_PAD_LEFT), 3);
    $result = array();
    
    foreach ($groups as $index => $group) {
        if ($group == "000") continue;
        
        $groupWords = array();
        
        // صدگان
        if ($group[0] != "0") {
            $groupWords[] = $hundreds[$group[0]];
        }
        
        // دهگان و یکان
        $twoDigit = (int)substr($group, 1);
        if ($twoDigit < 20 && $twoDigit > 0) {
            $groupWords[] = $ones[$twoDigit];
        } else {
            if ($group[1] != "0") {
                $groupWords[] = $tens[$group[1]];
            }
            if ($group[2] != "0") {
                $groupWords[] = $ones[$group[2]];
            }
        }
        
        if (!empty($groupWords)) {
            $groupIndex = count($groups) - $index - 1;
            $result[] = implode(" و ", $groupWords) . 
                       ($groupIndex > 0 ? " " . $majorUnits[$groupIndex] : "");
        }
    }
    
    return implode(" و ", $result);
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاکتور فروش <?php echo $invoice['invoice_number']; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <style>
        @font-face {
            font-family: 'iran-sans';
            src: url('assets/fonts/IRANSansWeb.woff2') format('woff2');
        }
        body {
            font-family: 'iran-sans', tahoma, arial;
            background: #f8f9fa;
            font-size: 14px;
        }
        .invoice-container {
            width: 210mm;  /* اندازه استاندارد A4 */
            min-height: 297mm; /* اندازه استاندارد A4 */
            margin: 20px auto;
            padding: 20mm;  /* حاشیه استاندارد چاپ */
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .bismillah {
            text-align: center;
            font-size: 20px;
            margin-bottom: 20px;
            color: #0d6efd;
            font-family: 'traditional-arabic', serif;
        }
        .invoice-header {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .logo {
            max-height: 80px;
            max-width: 160px;
        }
        .invoice-title {
            font-size: 20px;
            color: #0d6efd;
            margin-bottom: 10px;
            text-align: center;
            font-weight: bold;
        }
        .serial-box {
            border: 2px solid #0d6efd;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            background: #f8f9ff;
        }
        .company-details, .customer-details {
            font-size: 12px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f8f9fa;
            margin-bottom: 20px;
        }
        .section-title {
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 5px;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .table th {
            background-color: #0d6efd !important;
            color: white;
            font-size: 12px;
            text-align: center;
        }
        .table-items td {
            padding: 8px 6px;
            vertical-align: middle;
            font-size: 12px;
        }
        .total-section {
            background: #f8f9ff;
            border: 1px solid #0d6efd;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 12px;
        }
        .amount-in-words {
            font-size: 14px;
            color: #0d6efd;
            font-weight: bold;
        }
        .payment-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .payment-status.paid { background: #d4edda; color: #155724; }
        .payment-status.unpaid { background: #f8d7da; color: #721c24; }
        .payment-status.partial { background: #fff3cd; color: #856404; }
        .signature-section {
            margin-top: 30px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f8f9fa;
            font-size: 12px;
        }
        .qr-code {
            position: absolute;
            bottom: 15px;
            left: 15px;
            padding: 8px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .qr-code img {
            width: 80px;
            height: 80px;
        }
        .edit-mode .editable {
            border: 1px dashed #0d6efd;
            padding: 5px;
            min-height: 20px;
            cursor: text;
            border-radius: 4px;
        }
        .edit-mode .editable:hover {
            background: #f8f9ff;
        }
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            body {
                margin: 0;
                background: white;
            }
            .invoice-container {
                width: 210mm;
                min-height: 287mm; /* کمی کمتر از ارتفاع A4 برای اطمینان از عدم ایجاد صفحه اضافی */
                margin: 5mm;
                padding: 10mm;
                box-shadow: none;
            }
            .no-print {
                display: none !important;
            }
            /* برای جلوگیری از شکسته شدن محتوا بین صفحات */
            .table-responsive,
            .total-section,
            .signature-section {
                page-break-inside: avoid;
            }
            /* تنظیم اندازه فونت‌ها برای پرینت */
            body {
                font-size: 12pt;
            }
            .table {
                font-size: 10pt;
            }
        }
    </style>
</head>
<body>
    <!-- نوار ابزار چاپ -->
    <div class="container-fluid mb-4 no-print">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" class="btn btn-success me-2" onclick="window.print()">
                                <i class="fas fa-print"></i>
                                چاپ فاکتور
                            </button>
                            <button type="button" class="btn btn-primary me-2" onclick="toggleEdit()">
                                <i class="fas fa-edit"></i>
                                ویرایش فاکتور
                            </button>
                            <button type="button" class="btn btn-info me-2" onclick="saveAsPDF()">
                                <i class="fas fa-file-pdf"></i>
                                ذخیره PDF
                            </button>
                            <button type="button" class="btn btn-warning me-2" onclick="emailInvoice()">
                                <i class="fas fa-envelope"></i>
                                ارسال ایمیل
                            </button>
                        </div>
                        <a href="invoices.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right"></i>
                            بازگشت به لیست
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="invoice-container" id="invoice">
        <!-- بسم الله -->
        <div class="bismillah">بسم الله الرحمن الرحیم</div>

        <!-- هدر فاکتور -->
        <div class="invoice-header">
            <div class="row align-items-center">
                <div class="col-4">
                    <?php if ($logo): ?>
                        <img src="<?php echo $logo; ?>" alt="Logo" class="logo">
                    <?php endif; ?>
                </div>
                <div class="col-4">
                    <h1 class="invoice-title">فاکتور فروش کالا و خدمات</h1>
                </div>
                <div class="col-4">
                    <div class="serial-box">
                        <div>شماره: <?php echo isset($invoice['invoice_number']) ? substr($invoice['invoice_number'], -5) + 14040 : ''; ?></div>
                        <div>تاریخ: <?php echo jdate('Y/m/d', strtotime($invoice['created_at'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- اطلاعات فروشنده و خریدار -->
        <div class="row">
            <div class="col-6">
                <div class="company-details">
                    <div class="section-title">مشخصات فروشنده</div>
                    <?php if (!empty($settings['company_name'])): ?>
                        <div class="editable" data-field="company_name">
                            <strong>نام شرکت:</strong> <?php echo $settings['company_name']; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($settings['company_address'])): ?>
                        <div class="editable" data-field="company_address">
                            <strong>نشانی:</strong> <?php echo $settings['company_address']; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($settings['company_phone'])): ?>
                        <div>
                            <strong>تلفن:</strong> 
                            <span class="editable" data-field="company_phone">
                                <?php echo $settings['company_phone']; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($settings['company_economic_code'])): ?>
                        <div>
                            <strong>کد اقتصادی:</strong>
                            <span class="editable" data-field="company_economic_code">
                                <?php echo $settings['company_economic_code']; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($settings['company_national_id'])): ?>
                        <div>
                            <strong>شناسه ملی:</strong>
                            <span class="editable" data-field="company_national_id">
                                <?php echo $settings['company_national_id']; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-6">
                <div class="customer-details">
                    <div class="section-title">مشخصات خریدار</div>
                    <?php if (!empty($invoice['first_name']) || !empty($invoice['last_name'])): ?>
                        <div class="editable" data-field="customer_name">
                            <strong>نام/شرکت:</strong> <?php echo $invoice['first_name'] . ' ' . $invoice['last_name']; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($invoice['company'])): ?>
                        <div class="editable" data-field="customer_company">
                            <strong>نام شرکت:</strong> <?php echo $invoice['company']; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($invoice['address'])): ?>
                        <div class="editable" data-field="customer_address">
                            <strong>نشانی:</strong> <?php echo $invoice['address']; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($invoice['mobile'])): ?>
                        <div>
                            <strong>تلفن:</strong>
                            <span class="editable" data-field="customer_mobile">
                                <?php echo $invoice['mobile']; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($invoice['national_code'])): ?>
                        <div>
                            <strong>کد ملی:</strong>
                            <span class="editable" data-field="customer_national_code">
                                <?php echo $invoice['national_code']; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($invoice['economic_code'])): ?>
                        <div>
                            <strong>کد اقتصادی:</strong>
                            <span class="editable" data-field="customer_economic_code">
                                <?php echo $invoice['economic_code']; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- جدول اقلام -->
        <div class="table-responsive mt-4">
            <table class="table table-bordered table-items">
                <thead>
                    <tr>
                        <th width="50">ردیف</th>
                        <th>شرح کالا/خدمات</th>
                        <th>کد کالا</th>
                        <th>واحد</th>
                        <th>تعداد</th>
                        <th>قیمت واحد (<?php echo $settings['currency'] ?? 'ریال'; ?>)</th>
                        <th>تخفیف</th>
                        <th>جمع کل (<?php echo $settings['currency'] ?? 'ریال'; ?>)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td class="text-center"><?php echo $index + 1; ?></td>
                            <td class="editable" data-field="item_name_<?php echo $item['id']; ?>">
                                <?php echo $item['product_name']; ?>
                            </td>
                            <td class="text-center"><?php echo $item['product_code']; ?></td>
                            <td class="text-center"><?php echo $item['unit']; ?></td>
                            <td class="text-center editable" data-field="item_quantity_<?php echo $item['id']; ?>">
                                <?php echo number_format($item['quantity']); ?>
                            </td>
                            <td class="text-center editable" data-field="item_price_<?php echo $item['id']; ?>">
                                <?php echo number_format($item['price']); ?>
                            </td>
                            <td class="text-center editable" data-field="item_discount_<?php echo $item['id']; ?>">
                                <?php echo number_format($item['discount']); ?>
                            </td>
                            <td class="text-center">
                                <?php echo number_format($item['total_amount']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- بخش جمع کل -->
        <div class="total-section">
            <div class="row">
                <div class="col-6">
                    <div class="mb-3">
                        <div class="section-title">توضیحات</div>
                        <div class="editable" data-field="description">
                            <?php echo nl2br($invoice['description'] ?? ''); ?>
                        </div>
                    </div>
                    <div class="amount-in-words">
                        <div class="section-title">مبلغ به حروف</div>
                        <?php echo convertNumberToWords($invoice['final_amount']) . ' ' . ($settings['currency'] ?? 'ریال'); ?>
                    </div>
                </div>
                <div class="col-6">
                    <div class="section-title">خلاصه حساب</div>
                    <table class="table table-borderless">
                        <tr>
                            <td>جمع کل:</td>
                            <td class="text-start">
                                <?php echo number_format($invoice['total_amount']); ?>
                                <?php echo $settings['currency'] ?? 'ریال'; ?>
                            </td>
                        </tr>
                        <?php if ($invoice['tax_rate'] > 0): ?>
                            <tr>
                                <td>مالیات (<?php echo $invoice['tax_rate']; ?>%):</td>
                                <td class="text-start">
                                    <?php echo number_format($invoice['tax_amount'] ?? 0); ?>
                                    <?php echo $settings['currency'] ?? 'ریال'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($invoice['discount_amount'] > 0): ?>
                            <tr>
                                <td>تخفیف:</td>
                                <td class="text-start text-danger">
                                    <?php echo number_format($invoice['discount_amount']); ?>
                                    <?php echo $settings['currency'] ?? 'ریال'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td><strong>مبلغ قابل پرداخت:</strong></td>
                            <td class="text-start">
                                <strong>
                                    <?php echo number_format($invoice['final_amount']); ?>
                                    <?php echo $settings['currency'] ?? 'ریال'; ?>
                                </strong>
                            </td>
                        </tr>
                        <tr>
                        <tr>
    <td>وضعیت پرداخت:</td>
    <td class="text-start">
        <?php
        $statusClasses = [
            'paid' => 'paid',
            'unpaid' => 'unpaid',
            'partial' => 'partial'
        ];
        $statusTexts = [
            'cash' => 'نقدی پرداخت شده',
            'card' => 'کارت به کارت شده',
            'cheque' => 'چک پرداخت شده',
            'installment' => 'پرداخت اقساطی',
            'unpaid' => 'پرداخت نشده',
            'partial' => 'پرداخت ناقص'
        ];
        $statusClass = $statusClasses[$invoice['payment_status']] ?? 'unpaid';
        $statusText = $invoice['payment_status'] == 'paid' ? 
            ($statusTexts[$invoice['last_payment_type']] ?? 'پرداخت شده') : 
            ($statusTexts[$invoice['payment_status']] ?? 'نامشخص');
        ?>
        <span class="payment-status <?php echo $statusClass; ?>">
            <?php echo $statusText; ?>
        </span>
    </td>
</tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- بخش امضاها -->
        <div class="signature-section">
            <div class="row text-center">
                <div class="col-4">
                    <div class="section-title">صادر کننده</div>
                    <div class="mt-4">
                        <?php echo $invoice['created_by_name']; ?>
                    </div>
                </div>
                <div class="col-4">
                    <div class="section-title">مهر شرکت</div>
                    <div class="mt-4">محل مهر</div>
                </div>
                <div class="col-4">
                    <div class="section-title">تحویل گیرنده</div>
                    <div class="mt-4">امضاء</div>
                </div>
            </div>
        </div>

        <!-- QR Code -->
        <div class="qr-code">
            <div class="section-title mb-2">کد رهگیری</div>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php 
                echo urlencode(json_encode([
                    'invoice_number' => $invoice['invoice_number'],
                    'amount' => $invoice['final_amount'],
                    'date' => $invoice['created_at'],
                    'company' => $settings['company_name'] ?? ''
                ]));
            ?>" alt="QR Code">
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        let isEditMode = false;
        const editButton = document.querySelector('.btn-primary');

        function toggleEdit() {
            isEditMode = !isEditMode;
            document.getElementById('invoice').classList.toggle('edit-mode');
            
            if (isEditMode) {
                $('.editable').attr('contenteditable', 'true');
                editButton.innerHTML = '<i class="fas fa-save"></i> ذخیره تغییرات';
                editButton.classList.replace('btn-primary', 'btn-success');
            } else {
                $('.editable').removeAttr('contenteditable');
                saveChanges();
                editButton.innerHTML = '<i class="fas fa-edit"></i> ویرایش فاکتور';
                editButton.classList.replace('btn-success', 'btn-primary');
            }
        }

        function saveChanges() {
            let changes = {};
            $('.editable').each(function() {
                let field = $(this).data('field');
                let value = $(this).text().trim();
                changes[field] = value;
            });

            // ارسال تغییرات به سرور
            $.ajax({
                url: 'ajax/update-invoice.php',
                method: 'POST',
                data: {
                    invoice_id: <?php echo $invoiceId; ?>,
                    changes: changes
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('تغییرات با موفقیت ذخیره شد');
                    } else {
                        alert('خطا در ذخیره تغییرات: ' + (response.message || 'خطای نامشخص'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('خطا در ارتباط با سرور: ' + error);
                }
            });
        }

        function saveAsPDF() {
            $('.no-print').hide();
            
            const element = document.getElementById('invoice');
            const opt = {
                margin: 10,
                filename: 'invoice-<?php echo $invoice['invoice_number']; ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().from(element).set(opt).save().then(() => {
                $('.no-print').show();
            });
        }

        function emailInvoice() {
            alert('این قابلیت در حال توسعه است');
        }
    </script>
</body>
</html>