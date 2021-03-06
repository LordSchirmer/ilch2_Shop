<?php
/**
 * @copyright Ilch 2
 * @package ilch
 */

namespace Modules\Shop\Controllers\Admin;

use Modules\Shop\Mappers\Category as CategoryMapper;
use Modules\Shop\Mappers\Currency as CurrencyMapper;
use Modules\Shop\Mappers\Items as ItemsMapper;
use Modules\Shop\Mappers\Orders as OrdersMapper;
use Modules\Shop\Mappers\Settings as SettingsMapper;
use Modules\Shop\Models\Items as ItemsModel;
use Ilch\Validation;

class Items extends \Ilch\Controller\Admin
{
    public function init()
    {
        $items = [
            [
                'name' => 'menuOverwiev',
                'active' => false,
                'icon' => 'fas fa-store-alt',
                'url' => $this->getLayout()->getUrl(['controller' => 'index', 'action' => 'index'])
            ],
            [
                'name' => 'menuItems',
                'active' => false,
                'icon' => 'fas fa-tshirt',
                'url' => $this->getLayout()->getUrl(['controller' => 'items', 'action' => 'index']),
                [
                    'name' => 'add',
                    'active' => false,
                    'icon' => 'fa fa-plus-circle',
                    'url' => $this->getLayout()->getUrl(['controller' => 'items', 'action' => 'treat'])
                ]
            ],
            [
                'name' => 'menuOrders',
                'active' => false,
                'icon' => 'fas fa-cart-arrow-down',
                'url' => $this->getLayout()->getUrl(['controller' => 'orders', 'action' => 'index'])
            ],
            [
                'name' => 'menuCats',
                'active' => false,
                'icon' => 'fas fa-list-alt',
                'url' => $this->getLayout()->getUrl(['controller' => 'cats', 'action' => 'index'])
            ],
            [
                'name' => 'menuCurrencies',
                'active' => false,
                'icon' => 'fas fa-money-bill-alt',
                'url' => $this->getLayout()->getUrl(['controller' => 'currency', 'action' => 'index'])
            ],
            [
                'name' => 'menuSettings',
                'active' => false,
                'icon' => 'fa fa-cogs',
                'url' => $this->getLayout()->getUrl(['controller' => 'settings', 'action' => 'index'])
            ],
            [
                'name' => 'menuNote',
                'active' => false,
                'icon' => 'fas fa-info-circle',
                'url' => $this->getLayout()->getUrl(['controller' => 'note', 'action' => 'index'])
            ]
        ];

        if ($this->getRequest()->getActionName() === 'treat') {
            $items[1][0]['active'] = true;
        } else {
            $items[1]['active'] = true;
        }

        $this->getLayout()->addMenu
        (
            'menuShops',
            $items
        );
    }

    public function indexAction()
    {
        $categoryMapper = new CategoryMapper();
        $currencyMapper = new CurrencyMapper();
        $ordersMapper = new OrdersMapper();
        $itemsMapper = new ItemsMapper();

        $currency = $currencyMapper->getCurrencyById($this->getConfig()->get('shop_currency'))[0];

        $this->getLayout()->getAdminHmenu()
            ->add($this->getTranslator()->trans('menuShops'), ['controller' => 'index', 'action' => 'index'])
            ->add($this->getTranslator()->trans('menuItems'), ['controller' => 'items', 'action' => 'index']);

        if ($this->getRequest()->getPost('action') === 'delete' && $this->getRequest()->getPost('check_shops')) {
            foreach ($this->getRequest()->getPost('check_shops') as $itemId) {
                $itemInUse = 0;
                foreach ($ordersMapper->getOrders() as $key => $val) {
                    $orderItems = json_decode(str_replace("'", '"', $val->getOrder()), true);
                    foreach ($orderItems as $keyOrder => $valOrder) {
                        if ($valOrder['id'] == $itemId) {
                            $itemInUse = 1;
                        }
                    }
                }
                if ($itemInUse == 0) {
                    $itemsMapper->delete($itemId);
                } 
            }
            if ($itemInUse == 0) {
                    $this->addMessage('deleteSuccess');
                } else {
                    $this->addMessage('deleteItemsFailed', 'danger');
            }
        }

        $this->getView()->set('categoryMapper', $categoryMapper);
        $this->getView()->set('currency', $currency->getName());
        $this->getView()->set('shopItems', $itemsMapper->getShopItems());
    }

    public function treatAction()
    {
        $categoryMapper = new CategoryMapper();
        $currencyMapper = new CurrencyMapper();
        $itemsMapper = new ItemsMapper();
        $settingsMapper = new SettingsMapper();

        $currency = $currencyMapper->getCurrencyById($this->getConfig()->get('shop_currency'))[0];

        if ($this->getRequest()->getParam('id')) {
            $this->getLayout()->getAdminHmenu()
                    ->add($this->getTranslator()->trans('menuShops'), ['action' => 'index'])
                    ->add($this->getTranslator()->trans('menuItems'), ['controller' => 'items', 'action' => 'index'])
                    ->add($this->getTranslator()->trans('edit'), ['action' => 'treat']);
            $this->getView()->set('shopItem', $itemsMapper->getShopById($this->getRequest()->getParam('id')));
        } else {
            $this->getLayout()->getAdminHmenu()
                    ->add($this->getTranslator()->trans('menuShops'), ['action' => 'index'])
                    ->add($this->getTranslator()->trans('menuItems'), ['controller' => 'items', 'action' => 'index'])
                    ->add($this->getTranslator()->trans('add'), ['action' => 'treat']);
        }

        if ($this->getRequest()->isPost()) {
            Validation::setCustomFieldAliases([
                'catId' => 'cat',
            ]);

            $validation = Validation::create($this->getRequest()->getPost(), [
                'catId' => 'required|numeric|integer|min:1',
                'name' => 'required',
                'price' => 'required',
                'shippingCosts' => 'required',
                'shippingTime' => 'required|numeric|integer|min:1'
            ]);

            if ($validation->isValid()) {
                $model = new ItemsModel();

                if ($this->getRequest()->getParam('id')) {
                    $model->setId($this->getRequest()->getParam('id'));
                }

                $model->setCatId($this->getRequest()->getPost('catId'));
                $model->setName($this->getRequest()->getPost('name'));
                $model->setItemnumber($this->getRequest()->getPost('itemnumber'));
                $model->setStock($this->getRequest()->getPost('stock'));
                $model->setUnitName($this->getRequest()->getPost('unitName'));
                $model->setCordon($this->getRequest()->getPost('cordon'));
                $model->setCordonText($this->getRequest()->getPost('cordonText'));
                $model->setCordonColor($this->getRequest()->getPost('cordonColor'));
                $model->setPrice($this->getRequest()->getPost('price'));
                $model->setTax($this->getRequest()->getPost('tax'));
                $model->setShippingCosts($this->getRequest()->getPost('shippingCosts'));
                $model->setShippingTime($this->getRequest()->getPost('shippingTime'));
                $model->setImage($this->getRequest()->getPost('image'));
                $model->setImage1($this->getRequest()->getPost('image1'));
                $model->setImage2($this->getRequest()->getPost('image2'));
                $model->setImage3($this->getRequest()->getPost('image3'));
                $model->setInfo($this->getRequest()->getPost('info'));
                $model->setDesc($this->getRequest()->getPost('desc'));
                $model->setStatus($this->getRequest()->getPost('status'));

                $itemsMapper->save($model);
                $this->addMessage('saveSuccess');
                $this->redirect(['action' => 'index']);
            } else {
                $this->addMessage($validation->getErrorBag()->getErrorMessages(), 'danger', true);
                $this->redirect()
                  ->withInput()
                  ->withErrors($validation->getErrorBag())
                  ->to(['action' => 'treat', 'id' => $this->getRequest()->getParam('id')]);
            }
        }

        $this->getView()->set('cats', $categoryMapper->getCategories());
        $this->getView()->set('currency', $currency->getName());
        $this->getView()->set('settings', $settingsMapper->getSettings());
    }

    public function delShopAction()
    {
        if ($this->getRequest()->isSecure()) {
            $itemsMapper = new ItemsMapper();
            $ordersMapper = new OrdersMapper();
            $itemInUse = 0;
            foreach ($ordersMapper->getOrders() as $key => $val) {
                $orderItems = json_decode(str_replace("'", '"', $val->getOrder()), true);
                foreach ($orderItems as $keyOrder => $valOrder) {
                    if ($valOrder['id'] == $this->getRequest()->getParam('id')) {
                        $itemInUse = 1;
                    }
                }
            }
            if ($itemInUse == 0) {
                $itemsMapper->delete($this->getRequest()->getParam('id'));
                $this->addMessage('deleteSuccess');
            } else {
                $this->addMessage('deleteItemFailed', 'danger');
            }
        }
        $this->redirect(['action' => 'index']);
    }

}
