<link href="<?=$this->getModuleUrl('static/css/shop_admin.css') ?>" rel="stylesheet">
<?php
$itemsMapper = $this->get('itemsMapper'); 
$settingsMapper = $this->get('settingsMapper'); 
?>
<?php if ($this->get('order') != ''): ?>
    <h1><?=$this->getTrans('editOrder'); ?></h1>
    <?php 
    $order = $this->get('order');
    $myDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $this->escape($order->getDatetime()));
    $orderTime = date_format($myDateTime, 'd.m.Y \u\m H:i \U\h\r');
    $orderDate = date_format($myDateTime, 'd.m.Y');
    $invoiceNr = date_format($myDateTime, 'ymd').'-'.$order->getId();
    ?>
    <?php if ($order->getStatus() == 0) { ?>
        <div class="alert alert-danger">
            <i class="fa fa-plus-square" aria-hidden="true"></i>&nbsp;
            <b><?=$this->getTrans('newBIG') ?></b>
            &emsp;|&emsp;<?=$orderTime ?>&emsp;|&emsp;<?=$this->getTrans('infoOrderOpen') ?>
        </div>
    <?php } else if ($order->getStatus() == 1) { ?>
        <div class="alert alert-warning">
            <i class="fa fa-pencil-square" aria-hidden="true"></i>&nbsp;
            <b><?=$this->getTrans('processingBIG') ?></b>
            &emsp;|&emsp;<?=$orderTime ?>&emsp;|&emsp;<?=$this->getTrans('infoOrderProcessing') ?>
        </div>
    <?php } else if ($order->getStatus() == 2) { ?>
        <div class="alert alert-info">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;
            <b><?=$this->getTrans('canceledBIG') ?></b>
            &emsp;|&emsp;<?=$orderTime ?>&emsp;|&emsp;<?=$this->getTrans('infoOrderCanceled') ?>
        </div>
    <?php } else { ?>
        <div class="alert alert-success">
            <i class="fa fa-check-square" aria-hidden="true"></i>&nbsp;
            <b><?=$this->getTrans('completedBIG') ?></b>
            &emsp;|&emsp;<?=$orderTime ?>&emsp;|&emsp;<?=$this->getTrans('infoOrderFinished') ?>
        </div>
    <?php } ?>
    <h4><?=$this->getTrans('infoBuyer') ?></h4>
    <div class="table-responsive">
        <table class="table">
            <colgroup>
                <col class="col-lg-1">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th><?=$this->getTrans('name') ?></th>
                    <td><?=$this->escape($order->getPrename()) ?> <?=$this->escape($order->getLastname()) ?></td>
                </tr>
                <tr>
                    <th><?=$this->getTrans('address') ?></th>
                    <td><?=$this->escape($order->getStreet()) ?>, <?=$this->escape($order->getPostcode()) ?> <?=$this->escape($order->getCity()) ?>, <?=$this->escape($order->getCountry()) ?></td>
                </tr>
                <tr>
                    <th><?=$this->getTrans('emailAdress') ?></th>
                    <td><a href="mailto:<?=$this->escape($order->getEmail()) ?>"><?=$this->escape($order->getEmail()) ?></a></td>
                </tr>
            </tbody>
        </table>
    </div>
    <h4><?=$this->getTrans('orderedItems') ?></h4>
    <div class="table-responsive order">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?=$this->getTrans('productImage') ?><br />&nbsp;</th>
                    <th><?=$this->getTrans('productName') ?><br /><small><?=$this->getTrans('itemNumber') ?></small></th>
                    <th><?=$this->getTrans('shippingTime') ?><br />&nbsp;</th>
                    <th><?=$this->getTrans('singlePrice') ?><br /><small><?=$this->getTrans('withoutTax') ?></small></th>
                    <th><?=$this->getTrans('taxShort') ?><br />&nbsp;</th>
                    <th><?=$this->getTrans('singlePrice') ?><br /><small><?=$this->getTrans('withTax') ?></small></th>
                    <th class="text-center"><?=$this->getTrans('entries') ?><br />&nbsp;</th>
                    <th class="text-right"><?=$this->getTrans('total') ?><br /><small>incl. <?=$this->getTrans('taxShort') ?></small></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $orderItems = json_decode(str_replace("'", '"', $order->getOrder()), true);
                $subtotal_price = 0;
                $pdfOrderNr = 1;
                foreach ($orderItems as $orderItem):
                    $itemId = $orderItem["id"];
                    $itemImg = $itemsMapper->getShopById($itemId)->getImage();
                    $itemName = $itemsMapper->getShopById($itemId)->getName();
                    $itemNumber = $itemsMapper->getShopById($itemId)->getItemnumber();
                    $itemPrice = $itemsMapper->getShopById($itemId)->getPrice();
                    $itemTax = $itemsMapper->getShopById($itemId)->getTax();
                    $itemPriceWithoutTax = round(($itemPrice / (100 + $itemTax)) * 100, 2);
                    $arrayShippingCosts[] = $itemsMapper->getShopById($itemId)->getShippingCosts();
                    $itemShippingTime = $itemsMapper->getShopById($itemId)->getShippingTime();
                    $arrayShippingTime[] = $itemShippingTime;
                    $arrayTaxes[] = $itemTax;
                    $arrayPrices[] = $itemPrice * $orderItem["quantity"];
                    $arrayPricesWithoutTax[] = $itemPriceWithoutTax * $orderItem["quantity"];
                    $shopImgPath = '/application/modules/shop/static/img/';
                    if ($itemImg AND file_exists(ROOT_PATH.'/'.$itemImg)) {
                        $img = BASE_URL.'/'.$itemImg;
                    } else {
                        $img = BASE_URL.$shopImgPath.'noimg.jpg';
                    }
                    $currency = iconv('UTF-8', 'windows-1252', $this->escape($this->get('currency')));
                    $pdfOrderData[] = array(
                        $pdfOrderNr++,
                        utf8_decode($itemName),
                        number_format($itemPriceWithoutTax, 2, '.', '').' '.$currency,
                        $itemTax.' %',
                        number_format($itemPrice, 2, '.', '').' '.$currency,
                        $orderItem["quantity"],
                        number_format($itemPrice * $orderItem["quantity"], 2, '.', '').' '.$currency,
                        utf8_decode($this->getTrans('itemNumberShort')).' '.$itemNumber);
                ?>
                <tr>
                    <td><img src="<?=$img ?>" class="item_image"> </td>
                    <td>
                        <b><?=$itemName; ?></b><br /><small><?=$itemNumber; ?></small>
                    </td>
                    <td><?=$itemShippingTime ?> <?=$this->getTrans('days') ?></td>
                    <td>
                        <?=number_format($itemPriceWithoutTax, 2, '.', '') ?> <?=$this->escape($this->get('currency')) ?>
                    </td>
                    <td><?=$itemTax ?> %</td>
                    <td>
                        <b><?=number_format($itemPrice, 2, '.', '') ?> <?=$this->escape($this->get('currency')) ?></b>
                    </td>
                    <td class="text-center">
                        <b><?=$orderItem["quantity"] ?></b>
                    </td>
                    <td class="text-right">
                        <b><?=number_format($itemPrice * $orderItem["quantity"], 2, '.', '') ?> <?=$this->escape($this->get('currency')) ?></b>
                    </td>
                </tr>
                <?php $subtotal_price += round($itemPrice * $orderItem["quantity"], 2); ?>
                <?php endforeach; ?>
                <tr>
                    <td colspan="7" class="text-right finished">
                        <b><?=$this->getTrans('deliveryCosts') ?>:</b>
                    </td>
                    <td colspan="1" class="text-right finished">
                        <?php $shipping_costs = max($arrayShippingCosts); ?>
                        <b><?=number_format($shipping_costs, 2, '.', '') ?> <?=$this->escape($this->get('currency')) ?></b>
                    </td>
                </tr>
                <tr>
                    <td colspan="7" class="text-right finish">
                        <?=$this->getTrans('subtotal') ?> <?=$this->getTrans('withTax') ?>:
                    </td>
                    <td colspan="1" class="text-right finish">
                        <?php $total_price = array_sum($arrayPrices) + $shipping_costs; ?>
                        <?=number_format($total_price, 2, '.', '') ?> <?=$this->escape($this->get('currency')) ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="7" class="text-right finish">
                        <?=$this->getTrans('subtotal') ?> <?=$this->getTrans('withoutTax') ?>:
                    </td>
                    <td colspan="1" class="text-right finish">
                        <?php $sumPricewithoutTax = array_sum($arrayPricesWithoutTax) + round(($shipping_costs / (100 + max($arrayTaxes))) * 100, 2); ?>
                        <?=number_format($sumPricewithoutTax, 2, '.', '') ?> <?=$this->escape($this->get('currency')) ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="7" class="text-right finish">
                        <?=$this->getTrans('tax') ?>:
                    </td>
                    <td colspan="1" class="text-right finish">
                        <?php $differenzTax = round($total_price - $sumPricewithoutTax, 2); ?>
                        <?=number_format($differenzTax, 2, '.', '') ?> <?=$this->escape($this->get('currency')) ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="7" class="text-right finished">
                        <b><?=$this->getTrans('totalPrice') ?>:</b>
                    </td>
                    <td colspan="1" class="text-right finished">
                        <b><?=number_format($total_price, 2, '.', '') ?> <?=$this->escape($this->get('currency')) ?></b>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <h4><?=$this->getTrans('administration') ?></h4>
    <form class="form-horizontal" method="POST" action="">
    <?=$this->getTokenField() ?>
    <input type="hidden" name="id" value="<?=$order->getId() ?>" />
        <table class="table table-striped">
            <tr>
                <th><?=$this->getTrans('adjustStatus') ?></th>
            </tr>
            <tr>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="submit" name="status" value="0" class="btn btn-sm alert-danger">
                            <i class="fa fa-plus-square" aria-hidden="true"></i>&nbsp;<?=$this->getTrans('openBIG') ?>
                        </button>
                        <button type="submit" name="status" value="1" class="btn btn-sm alert-warning">
                            <i class="fa fa-pencil-square" aria-hidden="true"></i>&nbsp;<?=$this->getTrans('processingBIG') ?>
                        </button>
                        <button type="submit" name="status" value="2" class="btn btn-sm alert-info">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;<?=$this->getTrans('canceledBIG') ?>
                        </button>
                        <button type="submit" name="status" value="3" class="btn btn-sm alert-success">
                            <i class="fa fa-check-square" aria-hidden="true"></i>&nbsp;<?=$this->getTrans('completedBIG') ?>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <th><?=$this->getTrans('invoice') ?> - <?=utf8_decode($this->getTrans('numberShort')) ?> <?=$invoiceNr ?></th>
            </tr>
            <tr>
                <td>
                    <form class="form-horizontal" method="POST" action="">
                        <?php
                        $nameInvoice = utf8_decode($this->getTrans('invoice'));
                        $shopInvoicePath = '/application/modules/shop/static/invoice/';
                        $file_location = ROOT_PATH.$shopInvoicePath.$nameInvoice.'_'.$invoiceNr.'.pdf';
                        $file_show = BASE_URL.$shopInvoicePath.$nameInvoice.'_'.$invoiceNr.'.pdf';
                        if (file_exists($file_location)) { ?>
                            <a href="<?=$file_show ?>" target="_blank" class="btn btn-sm alert-success">
                                <i class="fas fa-file-pdf" aria-hidden="true"></i>&nbsp;<?=$this->getTrans('showPDF') ?>
                            </a>
                            <button type="submit" name="PDF" value="delete" class="btn btn-sm alert-danger">
                                <i class="fas fa-minus-square" aria-hidden="true"></i>&nbsp;<?=$this->getTrans('deletePDF') ?>
                            </button>
                        <?php } else { ?>
                            <button type="submit" name="PDF" value="create" class="btn btn-sm alert-default">
                                <i class="fas fa-file-pdf" aria-hidden="true"></i>&nbsp;<?=$this->getTrans('createPDF') ?>
                            </button>
                        <?php } ?>
                    </form>
                </td>
            </tr>
            <tr>
                <th><?=$this->getTrans('delOrder') ?></th>
            </tr>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="submit" name="delete" value="1" class="btn btn-sm alert-default delete_button">
                            <i class="far fa-trash-alt" aria-hidden="true"></i>&nbsp;<?=$this->getTrans('deleteBIG') ?>
                        </button>
                        <span class="btn btn-sm alert-default">
                            <i class="fas fa-info"></i> <?=$this->getTrans('infoDeleteOrder') ?>
                        </span>
                    </div>
                </td>
            </tr>
        </table>
        <a class="btn btn-default" href="<?=$this->getUrl(['action' => 'index']) ?>">
            <i class="fas fa-backward"></i> <?=$this->getTrans('back') ?>
        </a>
    </form>

    <?php
    /* A4: 210mm x 297mm with border 20mm */
    if (isset($_POST['PDF']) && $_POST['PDF'] == 'create') {
        ob_end_clean();
        require(ROOT_PATH.'/application/modules/shop/static/class/fpdf/fpdf.php');
        
        class PDF extends FPDF
        {
            function Header()
            {
                $this->SetMargins(20, 20, 20);
                $this->Image($this->shopLogo, 140, 15, 50);
            }
            function InvoiceHead()
            {
                $this->SetFont('Arial', 'U', 8);
                $this->SetTextColor(100, 100, 100);
                $this->Cell(130, 5, $this->shopName.' | '.$this->shopStreet.' | '.$this->shopPLZ.' '.$this->shopCity, 0, 0, 'L');
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('Arial', 'B', 11);
                $this->Cell(40, 5, $this->nameDateInvoice, 0, 1, 'R');
                $this->SetFont('Arial', '', 11);
                $this->Cell(130, 5, $this->ReceiverPrename.' '.$this->ReceiverLastname, 0, 0, 'L');
                $this->SetFont('Arial', '', 10);
                $this->Cell(40, 5, $this->DateInvoice, 0, 1, 'R');
                $this->SetFont('Arial', '', 11);
                $this->Cell(170, 5, $this->ReceiverStreet, 0, 1, 'L');
                $this->Cell(130, 5, $this->ReceiverPostcode.' '.$this->ReceiverCity, 0, 0, 'L');
                $this->SetFont('Arial', 'B', 11);
                $this->Cell(40, 5, $this->nameDeliveryDate, 0, 1, 'R');
                $this->SetFont('Arial', '', 11);
                $this->Cell(130, 5, $this->ReceiverCountry, 0, 0, 'L');
                $this->SetFont('Arial', '', 10);
                $this->Cell(40, 5, $this->DeliveryDate, 0, 1, 'R');
                $this->Ln(2);
                $this->SetFont('Arial', 'I', 9);
                $this->Cell(170, 5, $this->nameByEmail.': '.$this->ReceiverEmail, 0, 1, 'L');
            }
            function TitleLine() 
            {
                $this->SetFont('Arial', 'B', 14);
                $this->SetTextColor(0, 0, 0);
                $this->Cell(0, 8, $this->nameInvoice.' - '.$this->nameNumber.' '.$this->invoiceNr, 0, 1, 'L');
                $this->SetFont('Arial', 'I', 10);
                $this->Cell(0, 5, $this->nameOrder.' '.$this->nameFrom.' '.$this->orderDate, 0, 1, 'L');
                $this->SetFont('Arial', '', 11);
                $this->Ln(15);
                $this->MultiCell(170, 5, $this->invoiceTextTop);
            }
            function OrderTable()
            {
                $this->SetFillColor(220, 220, 220);
                $this->SetTextColor(0);
                $this->SetDrawColor(100, 100, 100);
                $this->SetLineWidth(.1);
                $this->SetFont('Arial', 'B', 9);
                $w = array(5, 65, 26, 10, 26, 12, 26);
                $this->Cell($w[0], 6, $this->OrderHeader[0], 1, 0, 'R', true);
                $this->Cell($w[1], 6, $this->OrderHeader[1], 1, 0, 'L', true);
                $this->Cell($w[2], 6, $this->OrderHeader[2], 1, 0, 'R', true);
                $this->Cell($w[3], 6, $this->OrderHeader[3], 1, 0, 'R', true);
                $this->Cell($w[4], 6, $this->OrderHeader[4], 1, 0, 'R', true);
                $this->Cell($w[5], 6, $this->OrderHeader[5], 1, 0, 'R', true);
                $this->Cell($w[6], 6, $this->OrderHeader[6], 1, 0, 'R', true);
                $this->Ln();
                $this->SetFillColor(240, 240, 240);
                $this->SetTextColor(0);
                $fill = false;
                foreach($this->OrderData as $row) {
                    $this->SetFont('Arial', '', 9);
                    $this->Cell($w[0], 6, $row[0], 'LR', 0, 'R', $fill);
                    $this->Cell($w[1], 6, $row[1], 'LR', 0, 'L', $fill);
                    $this->Cell($w[2], 6, $row[2], 'LR', 0, 'R', $fill);
                    $this->Cell($w[3], 6, $row[3], 'LR', 0, 'R', $fill);
                    $this->Cell($w[4], 6, $row[4], 'LR', 0, 'R', $fill);
                    $this->Cell($w[5], 6, $row[5], 'LR', 0, 'R', $fill);
                    $this->SetFont('Arial', 'B', 9);
                    $this->Cell($w[6], 6, $row[6], 'LR', 0, 'R', $fill);
                    $this->Ln();
                    $this->SetFont('Arial', 'I', 7);
                    $this->Cell($w[0], 4,      '', 'LR', 0, 'R', $fill);
                    $this->Cell($w[1], 4, $row[7], 'LR', 0, 'L', $fill);
                    $this->Cell($w[2], 4,      '', 'LR', 0, 'R', $fill);
                    $this->Cell($w[3], 4,      '', 'LR', 0, 'R', $fill);
                    $this->Cell($w[4], 4,      '', 'LR', 0, 'R', $fill);
                    $this->Cell($w[5], 4,      '', 'LR', 0, 'R', $fill);
                    $this->Cell($w[6], 4,      '', 'LR', 0, 'R', $fill);
                    $this->Ln();
                    $fill = !$fill;
                }
                $this->Cell(170, 0, '', 'T');
                $this->Ln(1);
                $this->SetFont('Arial', 'B', 9);
                $this->SetDrawColor(100, 100, 100);
                $this->SetLineWidth(.1);
                $this->Cell(144, 5, $this->nameDeliveryCosts, 'TL', 0, 'R');
                $this->Cell(26, 5, $this->DeliveryCosts.' '.$this->OrderCurrency, 'TR', 1, 'R');
                $this->SetFont('Arial', '', 9);
                $this->Cell(144, 5, $this->nameSubTotalTax, 'L', 0, 'R');
                $this->Cell(26, 5, $this->OrderTotalPrice.' '.$this->OrderCurrency, 'R', 1, 'R');                
                $this->Cell(144, 5, $this->nameSubTotalWithoutTax, 'L', 0, 'R');
                $this->Cell(26, 5, $this->OrderPriceWithoutTax.' '.$this->OrderCurrency, 'R', 1, 'R');               
                $this->Cell(144, 5, $this->nameTax, 'L', 0, 'R');
                $this->Cell(26, 5, $this->OrderDifferenzTax.' '.$this->OrderCurrency, 'R', 1, 'R');
                $this->Cell(170, 0, '', 'T', true);
                $this->Ln(1);
                $this->Cell(170, 0, '', 'T', true);
                $this->SetFont('Arial', 'B', 9);
                $this->SetFillColor(240, 240, 240);
                $this->SetLineWidth(.1);
                $this->Cell(144, 6, $this->nameTotalPrice, 'TL', 0, 'R', true);
                $this->Cell(26, 6, $this->OrderTotalPrice.' '.$this->OrderCurrency, 'TR', 1, 'R', true);
                $this->Cell(170, 0, '', 'T', true);             
            }
            function payInfo()
            {
                $this->SetFont('Arial', '', 11);
                $this->MultiCell(170, 5, $this->invoiceTextBottom);
                $this->Ln(5);
                $this->Cell(170, 5, $this->payInfoGreetings, 0, 1);
                $this->SetFont('Times', 'I', 14);
                $this->SetTextColor(100, 100, 100);
                $this->Cell(170, 10, $this->shopName, 0, 1);
            }
            function Footer()
            {   
                $this->SetY(-30);
                $this->SetTextColor(75, 75, 75);
                $this->SetFont('Courier', 'I', 8);
                $this->Cell(170, 6, $this->nameSite.' '.$this->PageNo().' / {nb}', 0, 1, 'R');
                $this->SetFont('Courier', '', 8);
                $this->SetDrawColor(150, 150, 150);
                $this->SetLineWidth(.1);
                $this->Cell(170, 1, '', 'T', 1);
                $this->Cell(55, 4, $this->shopName, 0, 0, 'L');
                $this->Cell(10, 4, 'TEL:', 0, 0, 'L');
                $this->Cell(50, 4, $this->shopTel, 0, 0, 'L');
                $this->Cell(10, 4, 'BANK:', 0, 0, 'L');
                $this->Cell(45, 4, $this->bankName, 0, 1, 'L');
                $this->Cell(55, 4, $this->shopStreet, 0, 0, 'L');
                $this->Cell(10, 4, 'FAX:', 0, 0, 'L');
                $this->Cell(50, 4, $this->shopFax, 0, 0, 'L');
                $this->Cell(10, 4, 'IBAN:', 0, 0, 'L');
                $this->Cell(45, 4, $this->bankIBAN, 0, 1, 'L');
                $this->Cell(55, 4, $this->shopPLZ.' '.$this->shopCity, 0, 0, 'L');
                $this->Cell(10, 4, 'MAIL:', 0, 0, 'L');
                $this->Cell(50, 4, $this->shopMail, 0, 0, 'L');
                $this->Cell(10, 4, 'BIC:', 0, 0, 'L');
                $this->Cell(45, 4, $this->bankBIC, 0, 1, 'L');
                $this->Cell(55, 4, 'Ust-IdNr.: '.$this->shopStNr, 0, 0, 'L');
                $this->Cell(10, 4, 'WEB:', 0, 0, 'L');
                $this->Cell(50, 4, $this->shopWeb, 0, 0, 'L');
                $this->Cell(10, 4, 'INH:', 0, 0, 'L');
                $this->Cell(45, 4, $this->bankOwner, 0, 1, 'L');
                $this->Cell(170, 1, '', 'B', 1);
            }
        }

        // render
        $pdf = new PDF('P','mm','A4');
        // data
        if ($settingsMapper->getSettings()->getShopLogo() && file_exists(ROOT_PATH.'/'.$settingsMapper->getSettings()->getShopLogo())) {
            $pdf->shopLogo = BASE_URL.'/'.$settingsMapper->getSettings()->getShopLogo();
        } else {
            $pdf->shopLogo = BASE_URL.'/application/modules/shop/static/img/empty.jpg';
        }
        $pdf->nameDateInvoice = utf8_decode($this->getTrans('dateOfInvoice'));
        $pdf->DateInvoice = $dateInvoice = date("d.m.Y", time());
        $pdf->nameDeliveryDate = utf8_decode($this->getTrans('expectedDelivery'));
              $maxDeliveryTime = max($arrayShippingTime);
              $sumDeliveryTime = time() + ($maxDeliveryTime * 24 * 60 * 60);
        $pdf->DeliveryDate = utf8_decode($this->getTrans('approx')).' '.date("d.m.Y", $sumDeliveryTime);
        $pdf->ReceiverPrename = utf8_decode($this->escape($order->getPrename()));
        $pdf->ReceiverLastname = utf8_decode($this->escape($order->getLastname()));
        $pdf->ReceiverStreet = utf8_decode($this->escape($order->getStreet()));
        $pdf->ReceiverPostcode = $this->escape($order->getPostcode());
        $pdf->ReceiverCity = utf8_decode($this->escape($order->getCity()));
        $pdf->ReceiverCountry = utf8_decode($this->escape($order->getCountry()));
        $pdf->nameByEmail = utf8_decode($this->getTrans('byEmail'));
        $pdf->ReceiverEmail = $this->escape($order->getEmail());
        $pdf->nameInvoice = strtoupper($nameInvoice);
        $pdf->nameOrder = $nameOrder = utf8_decode($this->getTrans('order'));
        $pdf->nameFrom = $nameFrom = utf8_decode($this->getTrans('from'));
        $pdf->orderDate = $orderDate;
        $pdf->nameNumber = $nameNumber = utf8_decode($this->getTrans('numberShort'));
        $pdf->invoiceNr = $invoiceNr;
        $pdf->invoiceTextTop = utf8_decode($settingsMapper->getSettings()->getInvoiceTextTop());
        $pdf->OrderHeader = array('#',
              utf8_decode($this->getTrans('productName')),
              utf8_decode($this->getTrans('singlePrice')),
              utf8_decode($this->getTrans('taxShort')),
              utf8_decode($this->getTrans('singlePrice')),
              utf8_decode($this->getTrans('entries')),
              utf8_decode($this->getTrans('total')));
        $pdf->OrderData = $pdfOrderData;
        $pdf->OrderCurrency = iconv('UTF-8', 'windows-1252', $this->escape($this->get('currency')));
        $pdf->nameDeliveryCosts = utf8_decode($this->getTrans('deliveryCosts')).':';
        $pdf->DeliveryCosts = number_format($shipping_costs, 2, '.', '');
        $pdf->nameSubTotalTax = utf8_decode($this->getTrans('subtotal')).' '.utf8_decode($this->getTrans('withTax')).':';
        $pdf->OrderTotalPrice = number_format($total_price, 2, '.', '');
        $pdf->nameSubTotalWithoutTax = utf8_decode($this->getTrans('subtotal')).' '.utf8_decode($this->getTrans('withoutTax')).':';
        $pdf->OrderPriceWithoutTax = number_format($sumPricewithoutTax, 2, '.', '');
        $pdf->nameTax = utf8_decode($this->getTrans('tax')).':';
        $pdf->OrderDifferenzTax = number_format($differenzTax, 2, '.', '');
        $pdf->nameTotalPrice = utf8_decode($this->getTrans('totalPrice')).':';
        $pdf->invoiceTextBottom = utf8_decode($settingsMapper->getSettings()->getInvoiceTextBottom());
        $pdf->payInfoGreetings = utf8_decode($this->getTrans('greetings'));
        $pdf->nameSite = utf8_decode($this->getTrans('site'));
        $pdf->shopName = $shopName = utf8_decode($settingsMapper->getSettings()->getShopName());
        $pdf->shopStreet = utf8_decode($settingsMapper->getSettings()->getShopStreet());
        $pdf->shopPLZ = utf8_decode($settingsMapper->getSettings()->getShopPLZ());
        $pdf->shopCity = utf8_decode($settingsMapper->getSettings()->getShopCity());
        $pdf->shopStNr = utf8_decode($settingsMapper->getSettings()->getShopStNr());
        $pdf->shopTel = utf8_decode($settingsMapper->getSettings()->getShopTel());
        $pdf->shopFax = utf8_decode($settingsMapper->getSettings()->getShopFax());
        $pdf->shopMail = utf8_decode($settingsMapper->getSettings()->getShopMail());
        $pdf->shopWeb = $shopWeb = utf8_decode($settingsMapper->getSettings()->getShopWeb());
        $pdf->bankName = utf8_decode($settingsMapper->getSettings()->getBankName());
        $pdf->bankIBAN = utf8_decode($settingsMapper->getSettings()->getBankIBAN());
        $pdf->bankBIC = utf8_decode($settingsMapper->getSettings()->getBankBIC());
        $pdf->bankOwner = utf8_decode($settingsMapper->getSettings()->getBankOwner());
        //
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->Ln(40);
        $pdf->InvoiceHead();
        $pdf->Ln(20);
        $pdf->TitleLine();
        $pdf->Ln(5);
        $pdf->OrderTable();
        $pdf->Ln(7);
        $pdf->payInfo();
        // document
        $pdf->SetTitle($nameInvoice.' '.$nameNumber.' '.$invoiceNr);
        $pdf->SetSubject($nameOrder.' '.$nameFrom.' '.$orderDate);
        $pdf->SetAuthor($shopName.' ('.$shopWeb.')');
        $pdf->SetCreator('Shop-Modul by ilch.de');
        $pdf->SetKeywords($nameInvoice.', '.$nameOrder);
        $pdf->Output($file_location,'F');
        header('Location: '.$order->getId());
        exit();
    } elseif (isset($_POST['PDF']) && $_POST['PDF'] == 'delete') {
        unlink($file_location);
        header('Location: '.$order->getId());
        exit();
    }?>

<?php else: ?>
    <h1><?=$this->getTrans('menuOrder'); ?></h1>
    <div class="alert alert-warning"><?=$this->getTrans('noOrderSelected') ?></div>
    <a class="btn btn-default" href="<?=$this->getUrl(['action' => 'index']) ?>">
        <i class="fas fa-backward"></i> <?=$this->getTrans('back') ?>
    </a>
<?php endif; ?>
