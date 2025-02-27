<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader;
use OrdersList\Date;
use Bitrix\Sale\Order;
use Bitrix\Main\UserTable;
use Bitrix\Sale\Internals\StatusLangTable;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

Loader::includeModule('custom.orderslist');
Loader::includeModule('sale');
Loc::loadMessages(__FILE__);

$isAjaxRequest = Application::getInstance()->getContext()->getRequest()->isAjaxRequest() && $_GET['AJAX'] === 'true';

$APPLICATION->SetTitle(GetMessage("ORDERS_LIST_TITLE"));

$POST_RIGHT = $APPLICATION->GetGroupRight("sale");
if ($POST_RIGHT == "D")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

$filter = [];
if ($_GET['statusOrder'] != '') {
    $filter['STATUS_ID'] = $_GET['statusOrder'];
}
try {
    if ($_GET['dateStart'] != '') {
        $filter['>=DATE_INSERT'] = (new DateTime($_GET['dateStart']))->format('d.m.Y') . ' 00:00:00';
    }
    if ($_GET['dateEnd'] != '') {
        $filter['<=DATE_INSERT'] = (new DateTime($_GET['dateEnd']))->format('d.m.Y') . ' 23:59:59';
    }
} catch (Throwable $e) {
    $error = Loc::getMessage('INVALID_FILTER_DATE');
    if ($isAjaxRequest) {
        echo json_encode([
            'type' => 'error',
            'message' => $error,
        ]);
        die();
    }
}
$accessSortType = [
    'asc',
    'desc',
];
$select = [
    'ID',
    'DATE_INSERT',
    'PRICE',
    'STATUS_ID',
    'USER_ID',
    'USER.NAME',
    'USER.SECOND_NAME',
    'USER.LAST_NAME',
    'USER.PERSONAL_PHONE',
    'STATUS.NAME'
];
$order = ['ID' => 'asc'];
$orderAroow = 'up';
if ($_GET['by'] && $_GET['order']) {
    if (in_array($_GET['by'], $select) && in_array($_GET['order'], $accessSortType)) {
        $order = [$_GET['by'] => ($_GET['order'])];
        $orderAroow = $_GET['order'] === 'asc' ? 'up' : 'down';
    } else {
        $error = Loc::getMessage('INVALID_REQUEST');
        if ($isAjaxRequest) {
            echo json_encode([
                'type' => 'error',
                'message' => $error,
            ]);
            die();
        }
    }
}
$offset = 0;
if (isset($_GET['offset']) && is_numeric($_GET['offset'])) {
    $offset = $_GET['offset'];
}
try {
    $listOrders = Order::getList([
        'select' => $select,
        'filter' => $filter,
        'order' => $order,
        'limit' => 10,
        'offset' => $offset,
        'runtime' => [
            'USER' => [
                'data_type' => UserTable::class,
                'reference' => [
                    '=this.USER_ID' => 'ref.ID',
                ]
            ],
            'STATUS' => [
                'data_type' => StatusLangTable::class,
                'reference' => [
                    '=this.STATUS_ID' => 'ref.STATUS_ID',
                ]
            ]
        ]
    ]);
} catch (Throwable) {
    $error = Loc::getMessage('INTERNAL_ERROR');
    if ($isAjaxRequest) {
        echo json_encode([
            'type' => 'error',
            'message' => $error,
        ]);
        die();
    }
}

$date = new Date();
if ($isAjaxRequest) {
    $result = [
        'type' => 'success',
        'orders' => [],
    ];
    try {
        if (isset($listOrders)) {
            while ($listOrder = $listOrders->fetchObject()) {
                    $result['orders'][] = [
                    'ID' => $listOrder->getId(),
                    'DATE_INSERT' => (new DateTime($listOrder->get('DATE_INSERT')))->format('d.m.Y'),
                    'PRICE' => $listOrder->get('PRICE'),
                    'STATUS' => $listOrder->get('STATUS')->get('NAME'),
                    'FIO' => str_replace('  ', ' ', $listOrder->get('USER')->get('NAME'). ' ' .$listOrder->get('USER')->get('SECOND_NAME'). ' ' . $listOrder->get('USER')->get('LAST_NAME')),
                    'PHONE' => $listOrder->get('USER')->get('PERSONAL_PHONE'),
                    'DATE_DELIVERY' => $date->deliveryDate(new DateTime($listOrder->get('DATE_INSERT')))->format('d.m.Y'),
                ];
            };
        }
    } catch (Throwable $e) {
        $result = [
            'type' => 'error',
            'message' => Loc::getMessage('INTERNAL_ERROR'),
        ];        
    }
    echo json_encode($result);
    die();
}

$statuses = StatusLangTable::getList();
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<? if (isset($listOrders) && is_null($error)): ?>
    <div>
        <form id="filter">
            <table>
                <tbody>
                    <tr>
                        <td>
                            <label for="statusOrder"><?=Loc::getMessage('STATUS_ORDER')?>:</label>
                        </td>
                        <td>
                            <select id="statusOrder" name="statusOrder">
                                <option value="">(<?=Loc::getMessage('ALL_STATUS')?>)</option>
                                <?php while($status = $statuses->fetch()):?>
                                    <option value="<?=$status['STATUS_ID']?>" <?= $status['STATUS_ID'] === $filter['STATUS_ID'] ? 'selected' : ''?>><?=$status['NAME']?></option>
                                <?php endwhile;?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?=Loc::getMessage('DATE')?>:</td>
                        <td>
                            <label><?=Loc::getMessage('WITH')?></label>
                            <input type="date" id="dateStart" name="dateStart">
                        </td>
                        <td>
                            <label><?=Loc::getMessage('BY')?></label>
                            <input type="date" id="dateEnd" name="dateEnd">
                        </td>
                    </tr>
                </tbody>
            </table>  
            <button type="submit" onclick="BX.CustomOrderList.List.filterTable(this.event)"><?=Loc::getMessage('SEND')?></button>      
        </form>
    </div>
    <table class="table" id="table" border="1">
        <thead class="table-list">
            <tr>
                <th id="ID" <?= $order['ID'] ? 'class="select"' : ''?> onclick="BX.CustomOrderList.List.sortTable('ID')">
                    <div><?=Loc::getMessage('ID_ORDER')?></div>
                    <?=$order['ID'] ? "<i class=\"$orderAroow\"></i>" : ''?>
                </th>
                <th id="DATE_INSERT" <?= $order['DATE_INSERT'] ? 'class="select"' : ''?> onclick="BX.CustomOrderList.List.sortTable('DATE_INSERT')">
                    <div><?=Loc::getMessage('DATE_INSERT')?></div>
                    <?=$order['DATE_INSERT'] ? "<i class=\"$orderAroow\"></i>" : ''?>
                </th>
                <th><?=Loc::getMessage('PRICE')?></th>
                <th><?=Loc::getMessage('NAME_STATUS')?></th>
                <th><?=Loc::getMessage('FIO')?></th>
                <th><?=Loc::getMessage('PHONE')?></th>
                <th><?=Loc::getMessage('DELIVERY_DATE')?></th>
            </tr>
        </thead>
        <tbody id="body-list-orders">
                <?php while($order = $listOrders->fetchObject()): ?>
                    <tr>
                        <td><?=$order->getId()?></td>
                        <td><?=(new DateTime($order->get('DATE_INSERT')))->format('d.m.Y')?></td>
                        <td><?=$order->get('PRICE')?></td>                
                        <td><?=$order->get('STATUS')->get('NAME')?></td>
                        <td><?=str_replace('  ', ' ', $order->get('USER')->get('NAME'). ' ' .$order->get('USER')->get('SECOND_NAME'). ' ' . $order->get('USER')->get('LAST_NAME'))?></td>
                        <td><?=$order->get('USER')->get('PERSONAL_PHONE')?></td>
                        <td><?=$date->deliveryDate(new DateTime($order->get('DATE_INSERT')))->format('d.m.Y');?></td>
                    </tr>
                <?php endwhile;?>
        </tbody>
    </table>
    <button type='button' id='load-button' class='load-button' onclick="BX.CustomOrderList.List.loadOrders()"><?=Loc::getMessage('MORE')?></button>
<?php else: ?>
    <?= $error ?>
<?php endif?>
<?php
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");