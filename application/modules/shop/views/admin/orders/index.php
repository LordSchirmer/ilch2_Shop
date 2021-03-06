<link href="<?=$this->getModuleUrl('static/css/shop_admin.css') ?>" rel="stylesheet">

<h1><?=$this->getTrans('menuOrders') ?>
    <div class="input-group input-group-sm filter">
        <span class="input-group-addon">
            <i class="fas fa-filter"></i>
        </span>
        <input type="text" id="filterInput" class="form-control" placeholder="<?=$this->getTrans('filter') ?>">
        <span class="input-group-addon">
            <span id="filterClear" class="fas fa-times"></span>
        </span>
    </div>
</h1>

<?php if ($this->get('ordersMapper')): ?>
    <form class="form-horizontal" method="POST" action="">
        <?=$this->getTokenField() ?>
        <div class="table-responsive">
            <table id="sortTable" class="table table-hover table-striped">
                <colgroup>
                    <col class="icon_width">
                    <col class="icon_width">
                    <col class="icon_width">
                    <col class="icon_width">
                    <col>
                    <col>
                    <col>
                    <col>
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <th><?=$this->getCheckAllCheckbox('check_orders') ?></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th class="sort"><?=$this->getTrans('status') ?></th>
                        <th class="sort"><?=$this->getTrans('orderDate') ?></th>
                        <th class="sort"><?=$this->getTrans('invoice').' '.$this->getTrans('numberShort') ?></th>
                        <th class="sort"><?=$this->getTrans('name') ?></th>
                        <th class="sort"><?=$this->getTrans('address') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->get('ordersMapper') as $order): ?>
                        <tr class="filter">
                            <td><?=$this->getDeleteCheckbox('check_orders', $order->getId()) ?></td>
                            <td><?=$this->getEditIcon(['action' => 'treat', 'id' => $order->getId()]) ?></td>
                            <td><?=$this->getDeleteIcon(['action' => 'delorder', 'id' => $order->getId()]) ?></td>
                            <td></td>
                            <td>
                                <?php if ($order->getStatus() == 0) { ?>
                                    <a href="<?=$this->getUrl(['action' => 'treat', 'id' => $order->getId()]) ?>" class="btn btn-sm alert-danger">
                                        <i class="fa fa-plus-square" aria-hidden="true"></i>&nbsp;<b><?=$this->getTrans('newBIG') ?></b>
                                    </a>
                                <?php } else if ($order->getStatus() == 1) { ?>
                                    <a href="<?=$this->getUrl(['action' => 'treat', 'id' => $order->getId()]) ?>" class="btn btn-sm alert-warning">
                                        <i class="fa fa-pencil-square" aria-hidden="true"></i>&nbsp;<b><?=$this->getTrans('processingBIG') ?></b>
                                    </a>
                                <?php } else if ($order->getStatus() == 2) { ?>
                                    <a href="<?=$this->getUrl(['action' => 'treat', 'id' => $order->getId()]) ?>" class="btn btn-sm alert-info">
                                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;<b><?=$this->getTrans('canceledBIG') ?></b>
                                    </a>
                                <?php } else { ?>
                                    <a href="<?=$this->getUrl(['action' => 'treat', 'id' => $order->getId()]) ?>" class="btn btn-sm alert-success">
                                        <i class="fa fa-check-square" aria-hidden="true"></i>&nbsp;<b><?=$this->getTrans('completedBIG') ?></b>
                                    </a>
                                <?php } ?>
                            </td>
                            <?php 
                            $myDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $this->escape($order->getDatetime()));
                            $orderTime = date_format($myDateTime, 'd.m.Y | H:i \U\h\r'); 
                            ?>
                            <td>
                                <?=$orderTime ?>
                            </td>
                            <?php
                            $orderDate = date_format($myDateTime, 'd.m.Y');
                            $invoiceNr = date_format($myDateTime, 'ymd').'-'.$order->getId();
                            ?>
                            <td>
                                <?=$invoiceNr ?>
                            </td>
                            <td>
                                <?=$this->escape($order->getPrename()) ?> <?=$this->escape($order->getLastname()) ?>
                            </td>
                            <td>
                                <?=$this->escape($order->getStreet()) ?>, 
                                <?=$this->escape($order->getPostcode()) ?> <?=$this->escape($order->getCity()) ?>, 
                                <?=$this->escape($order->getCountry()) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?=$this->getListBar(['delete' => 'delete']) ?>
    </form>
    <script>
    $("table").on("click", "th.sort", function () {
        var index = $(this).index(),
            rows = [],
            thClass = $(this).hasClass("asc") ? "desc" : "asc";
        $("#sortTable th.sort").removeClass("asc desc");
        $(this).addClass(thClass);
        $("#sortTable tbody tr").each(function (index, row) {
            rows.push($(row).detach());
        });
        rows.sort(function (a, b) {
            var aValue = $(a).find("td").eq(index).text(),
                bValue = $(b).find("td").eq(index).text();
            return aValue > bValue ? 1 : (aValue < bValue ? -1 : 0);
        });
        if ($(this).hasClass("desc")) {
            rows.reverse();
        }
        $.each(rows, function (index, row) {
            $("#sortTable tbody").append(row);
        });
    });
    $("#filterInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#sortTable tr.filter").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    $("#filterClear").click(function(){
        $("#sortTable tr.filter").show(function() {
            $("#filterInput").val('');
        });
    });
    </script>
<?php else: ?>
    <?=$this->getTrans('noOrders') ?>
<?php endif; ?>
