<?php
$itemsMapper = $this->get('itemsMapper');

/* shopcart session */
$status = '';

if (isset($_POST['action']) && $_POST['action'] == "remove") {
    if(!empty($_SESSION["shopping_cart"])) {
        foreach($_SESSION["shopping_cart"] as $key => $value) {
            if($_POST["code"] == $key) {
                unset($_SESSION["shopping_cart"][$key]);
                $status = '<div id="infobox" class="alert alert-danger" role="alert">'.$this->getTrans('theProduct').' <b>'.$_POST['name'].'</b> '.$this->getTrans('removedFromCart').'</div>';
            }
            if(empty($_SESSION["shopping_cart"])) unset($_SESSION["shopping_cart"]);
        }		
	}
}

if (isset($_POST['action']) && $_POST['action'] == "change") {
    foreach($_SESSION["shopping_cart"] as &$value) {
        if($value['code'] === $_POST["code"]) {
            $_POST["quantity"] = ($_POST["quantity"]<=0)?1:$_POST["quantity"];
            $value['quantity'] = $_POST["quantity"];
            break;
        }
    }
}

/* show shopcart */
$cart_badge = '';
if(!empty($_SESSION["shopping_cart"])) {
    $cart_count = count(array_keys($_SESSION["shopping_cart"]));
    $cart_badge = ($cart_count>0)?'<a class="activecart" href="'.$this->getUrl('shop/index/cart').'#shopAnker">'.$this->getTrans('menuCart').'<i class="fas fa-shopping-cart"><span class="badge">'.$cart_count.'</span></i></a>':'';
} 
?>

<h1>
    <?=$this->getTrans('menuCart') ?>
    <?=$cart_badge ?>
    <div id="shopAnker"></div>
</h1>

<div class="message_box">
    <?=$status; ?>
</div>
<div class="table cart">
    <?php
    if(isset($_SESSION["shopping_cart"])) {
        $subtotal_price = 0; ?>
        <table>
            <thead>
                <tr>
                    <th scope="col" width="10%"><?=$this->getTrans('productImage') ?><br />&nbsp;</th>
                    <th scope="col" width="7%"></th>
                    <th scope="col" width="25%"><?=$this->getTrans('productName') ?><br /><small><?=$this->getTrans('itemNumber') ?></small></th>
                    <th scope="col" width="20%"><?=$this->getTrans('singlePrice') ?><br /><small><?=$this->getTrans('withTax') ?></small></th>
                    <th scope="col" width="18%" class="text-center"><?=$this->getTrans('entries') ?><br />&nbsp;</th>
                    <th scope="col" width="20%" class="text-right"><?=$this->getTrans('total') ?><br /><small>incl. <?=$this->getTrans('taxShort') ?></small></th>
                </tr>
            </thead>
            <tbody>
                <?php	
                foreach ($_SESSION["shopping_cart"] as $product) {
                    $itemId = $product["id"];
                    $itemCode = $itemsMapper->getShopById($itemId)->getCode();
                    $itemName = $itemsMapper->getShopById($itemId)->getName();
                    $itemPrice = $itemsMapper->getShopById($itemId)->getPrice();
                    $itemNumber = $itemsMapper->getShopById($itemId)->getItemnumber();
                    $itemImg = $itemsMapper->getShopById($itemId)->getImage();
                    $itemMaxStock = $itemsMapper->getShopById($itemId)->getStock();
                    $arrayShippingCosts[] = $itemsMapper->getShopById($itemId)->getShippingCosts();
                    $shopImgPath = '/application/modules/shop/static/img/';
                    if ($itemImg AND file_exists(ROOT_PATH.'/'.$itemImg)) {
                        $img = BASE_URL.'/'.$itemImg;
                    } else {
                        $img = BASE_URL.$shopImgPath.'noimg.jpg';
                    } ?>
                <tr>
                    <td data-label="<?=$this->getTrans('productImage') ?>">
                        <a href="<?=$this->getUrl('shop/index/show/id/'.$product["id"]) ?>#shopAnker">
                            <img src="<?=$img ?>" />
                        </a>
                    </td>
                    <td data-label="<?=$this->getTrans('remove') ?>" class="text-center">
                        <form method="post" action="#shopAnker">
                            <?=$this->getTokenField() ?>
                            <input type="hidden" name="code" value="<?=$itemCode; ?>" />
                            <input type="hidden" name="name" value="<?=$itemName; ?>" />
                            <input type="hidden" name="action" value="remove" />
                            <button type="submit" class="btn btn-sm btn-default far fa-trash-alt remove"></button>
                        </form>
                    </td>
                    <td data-label="<?=$this->getTrans('productName') ?>">
                        <b><?=$itemName; ?></b><br /><small><?=$itemNumber; ?></small>
                    </td>
                    <td data-label="<?=$this->getTrans('singlePrice') ?> (<?=$this->getTrans('withTax') ?>)">
                        <?=number_format($itemPrice, 2, '.', '') ?> <?=$this->escape($this->get('currency')) ?>
                    </td>
                    <td data-label="<?=$this->getTrans('entries') ?>" class="text-center">
                        <form method="post" action="#shopAnker" class="quantity">
                            <?=$this->getTokenField() ?>
                            <input type="hidden" name="code" value="<?=$itemCode; ?>" />
                            <input type="hidden" name="action" value="change" />
                            <div class="input-group">
                                <input type="hidden" name="maxStock" value="<?=$itemMaxStock; ?>" />
                                <span class="input-group-btn">
                                    <button class="btn btn-xs btn-default plus-btn" type="button" name="button"><i class="fa fa-plus"></i></button>
                                </span>
                                <input class="form-control item-quantity input-sm"
                                    type="text"
                                    id="quantity"
                                    name="quantity"
                                    onchange="this.form.submit()"
                                    value="<?=$product["quantity"] ?>"
                                    readonly>
                                <span class="input-group-btn">
                                    <button class="btn btn-xs btn-default minus-btn" type="button" name="button"><i class="fa fa-minus"></i></button>
                                </span>
                            </div>
                        </form>
                    </td>
                    <td data-label="<?=$this->getTrans('total') ?> (incl. <?=$this->getTrans('taxShort') ?>)" class="text-right">
                        <b><?=number_format($itemPrice * $product["quantity"], 2, '.', '') ?> <?=$this->escape($this->get('currency')) ?></b>
                    </td>
                </tr>
                <?php
                $subtotal_price += round($itemPrice * $product["quantity"], 2);
                }
                ?>
            </tbody>
        </table>
        <table class="sum">
            <tr>
                <th>
                    <?=$this->getTrans('deliveryCosts') ?>
                </th>
                <td data-label="<?=$this->getTrans('deliveryCosts') ?>" class="text-right">
                    <?php $shipping_costs = max($arrayShippingCosts); ?>
                    <?=number_format($shipping_costs, 2, '.', '') ?> <?=$this->escape($this->get('currency')) ?>
                </td>
            </tr>
            <tr>
                <th>
                    <b><?=$this->getTrans('totalPrice') ?></b>
                </th>
                <td data-label="<?=$this->getTrans('totalPrice') ?>" class="text-right">
                    <?php $total_price = $subtotal_price + $shipping_costs; ?>
                    <b><?=number_format($total_price, 2, '.', '') ?> <?=$this->escape($this->get('currency')) ?></b>
                </td>
            </tr>
        </table>
        <form method="post" action="order#shopAnker" class="text-right">
            <div class="btn-group btn-group-sm">
                <a class="btn btn-default" href="<?=$this->getUrl('shop/index') ?>#shopAnker"><i class="fas fa-backward"></i> <?=$this->getTrans('back') ?></a>
                <button class="btn btn-warning"><?=$this->getTrans('completePurchase') ?> <i class="fas fa-forward"></i></button>
            </div>
        </form>
    <?php } else { ?>
    <?=$this->getTrans('cartEmpty') ?>
    <div class="row space20"></div>
    <a href="<?=$this->getUrl('shop/index') ?>#shopAnker" class="btn btn-default">
        <i class="fas fa-backward"></i> <?=$this->getTrans('back') ?>
    </a>
<?php } ?>
</div>
<script>
$(document).ready(function () {
    setTimeout(function() {
        $('#infobox').slideUp("slow");
    }, 5000);
});
$('.minus-btn').on('click', function(e) {
    e.preventDefault();
    var $this = $(this);
    var $input = $this.closest('div').find('input[name="quantity"]');
    var value = parseInt($input.val());
    if (value > 2) {
        value = value - 1;
    } else {
        value = 1;
    }
    $input.val(value);
    var form = $this.closest('form');
    var url = form.attr('action');
    $.ajax({
        type: "POST",
        url: url,
        data: form.serialize()
    });
});
$('.plus-btn').on('click', function(e) {
    e.preventDefault();
    var $this = $(this);
    var $input = $this.closest('div').find('input[name="quantity"]');
    var value = parseInt($input.val());
    var maxStock = $this.closest('div').find('input[name="maxStock"]').val();
    if (value < maxStock) {
        value = value + 1;
    } else {
        value = maxStock;
    }
    $input.val(value);
    var form = $this.closest('form');
    var url = form.attr('action');
    $.ajax({
        type: "POST",
        url: url,
        data: form.serialize()
    });
});
</script>
