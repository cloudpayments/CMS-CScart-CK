<?php

namespace Tygh;

class CloudKassir
{
    const RECEIPT_TYPE_INCOME = 'Income';
    const RECEIPT_TYPE_INCOME_RETURN = 'IncomeReturn';

    private $public_id;
    private $secret_key;
    private $inn;
    private $taxation_system;

    /**
     * CloudKassir constructor.
     * @param $public_id
     * @param $secret_key
     * @param $inn
     * @param $taxation_system
     */
    public function __construct($public_id, $secret_key, $inn, $taxation_system)
    {
        $this->public_id = $public_id;
        $this->secret_key = $secret_key;
        $this->inn = $inn;
        $this->taxation_system = $taxation_system;
    }


    /**
     * @param $order_info
     * @param $receipt_type
     */
    public function sendReceipt($order_info, $receipt_type)
    {
        $receipt_data = array(
            'Inn' => $this->inn,
            'Type' => $receipt_type,
            'CustomerReceipt' => array(
                'Items' => $this->getInventoryItems($order_info),
                'taxationSystem' => $this->taxation_system,
                'email' => $order_info['email'],
                'phone' => $order_info['phone']
            ),
            'InvoiceId' => $order_info['order_id'],
            'AccountId' => $order_info['email'],
        );

        $this->makeRequest('kkt/receipt', $receipt_data);
    }

    /**
     * @param $order_info
     * @return array
     */
    protected function getInventoryItems($order_info)
    {
        $map_taxes           = fn_get_schema('cloudkassir', 'map_taxes');
        $inventory_items = array();

        /** @var \Tygh\Addons\RusTaxes\ReceiptFactory $receipt_factory */
        $receipt_factory = Tygh::$app['addons.rus_taxes.receipt_factory'];
        $receipt         = $receipt_factory->createReceiptFromOrder($order_info, CART_PRIMARY_CURRENCY);

        if ($receipt) {
            foreach ($receipt->getItems() as $item) {
                $inventory_items[] = array(
                    'label'    => $item->getName(),
                    'price'    => $item->getPrice(),
                    'quantity' => $item->getQuantity(),
                    'amount'   => $item->getTotal(),
                    'vat'      => isset($map_taxes[$item->getTaxType()]) ? $map_taxes[$item->getTaxType()] : $map_taxes[TaxType::NONE]
                );
            }
        }

        return $inventory_items;
    }

    /**
     * @param $location
     * @param $data
     * @return mixed
     */
    protected function makeRequest($location, $data)
    {
        $extra = array(
            'basic_auth' => array(
                $this->public_id,
                $this->secret_key
            ),
            'headers' => array(
                'content-type: application/json'
            )
        );

        return Http::post('https://api.cloudpayments.ru/' . $location, json_encode($data), $extra);
    }
}