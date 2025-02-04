<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Open Sans, sans-serif;
            line-height: 1.4;
            font-size: 15px;
            margin: 0;
            padding: 0;
        }

        .container{
            margin: 20px 90px;
        }

        .title {
            text-align: center;
            margin: 30px 0;
            color: #343256;
        }
        .sub-title {
            color: #424d9e;
        }

        .sub-head {
            font-weight: bold;
            color: #696973;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 5px;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            color:#a48856;
        }
        td {
            background-color: #f9f9f9;
        }

        .checkbox-text{
            font-weight: normal;
            color: #0000;
        }

        .checkbox {
            display: inline-block;
            width: 30px;
            height: 10px;
            border: 1px solid #696973;
            text-align: center;
            font-size: 16px;
            color: #0A875A;
            font-weight: bolder;
        }
    </style>
    <title>Application <?= date('Y') ?></title>
</head>
<body>
<div class="container">
    <h3 class="title">Application <?= date('Y') ?></h3>
    <h3 class="sub-title">1) Company Details</h3>
    <p><span class="sub-head">Full name of your organisation:</span>&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($form_data['field_a53129c']?? '') ?></p>
    <p><span class="sub-head">VAT Number:</span>&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($form_data['vat_number']?? '') ?></p>
    <p><span class="sub-head">Legal form:</span>&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($form_data['legal_form']?? '') ?></p>

    <p><span class="sub-head">Legal address for invoicing:</span></p>
    <ul>
        <li><span class="sub-head">Street address:</span>&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($form_data['address']?? '') ?></li>
        <li><span class="sub-head">ZIP Code:</span>&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($form_data['zip_code']?? '') ?></li>
        <li><span class="sub-head">City:</span>&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($form_data['city']?? '') ?></li>
        <li><span class="sub-head">Country:</span>&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($form_data['field_83f4d35']?? '') ?></li>
    </ul>

    <p><span class="sub-head">Contact Person for Invoice:<span></p>
    <ul>
        <li><span class="sub-head">Name:<span>&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($form_data['field_f665840']?? '') ?></li>
        <li><span class="sub-head">Surname:<span>&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($form_data['field_ead47bc']?? '') ?></li>
        <li><span class="sub-head">Email:<span>&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($form_data['field_5fa8028']?? '') ?></li>
    </ul>
    <p><span class="sub-head">Does your company need a PO?<span><br><br>
                <span class="checkbox">&nbsp;<?= htmlspecialchars($form_data['field_a81e563'] ?? '') === 'Yes' ? 'X' : '&nbsp;&nbsp;' ?>&nbsp;</span> <span class="checkbox-text">Yes</span> &nbsp;
                <span class="checkbox">&nbsp;<?= htmlspecialchars($form_data['field_a81e563'] ?? '') === 'No' ? 'X' : '&nbsp;&nbsp;' ?>&nbsp;</span> <span class="checkbox-text">No</span>
        </span>
    </p>
    <?php
    if ( $form_data['field_a81e563'] ?? '' === 'Yes' ):
        ?>
        <p>
            <span class="sub-head">The PO number: </span>&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($form_data['field_954457b']?? '') ?>
        </p>
    <?php endif; ?>
    <p><span class="sub-head" style="margin-top: 4px;">Extra information</span><p>
    <p>
        <span class="sub-head">Country(ies) of manufacturing/main activity: </span>
        <?= htmlspecialchars($form_data['field_f456075']?? '') ?>
    </p>
    <p>
        <span class="sub-head">Website: </span>
        <?= htmlspecialchars($form_data['field_b4d349f']?? '') ?>
    </p>

    <p>
        <span class="sub-head">General contact email: </span>&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($form_data['field_c7f3ed2']?? '') ?>
    </p>
    <p>
        <span class="sub-head">Twitter handle: </span>&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($form_data['field_4b31123']?? '') ?>
    </p>
    <p><span class="sub-head">Company Logo:<span></p>
    <img src="<?= isset($form_data['field_c84c3ae']) && $form_data['field_c84c3ae'] !== ''? $form_data['field_c84c3ae'] : ELEMENTOR_PDF_GENERATOR_URL.'/lib/images/default/default-preview-logo.png' ?>" alt="Company Logo" width="150">



    <h3 class="sub-title">2) Company description</h3>
    <p>
        <span class="sub-head">Write a short description of your organisation:</span> <br><br>
        <?= htmlspecialchars($form_data['field_307c5ca']?? '') ?>
    </p>

    <p>
        <span class="sub-head">In which countries does your company serve?</span> <br><br>
        <?= htmlspecialchars($form_data['field_4e33103']?? '') ?>
    </p>

</div>

</body>
</html>
