<?php

namespace App\Http\Controllers\Core;

use DateTime;
use IntlDateFormatter;
use App\Models\Core\Order;
use Illuminate\Http\Request;
use App\Models\Core\Declination;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;
use App\Core\Trait\Concerns\CustomerTrait;

class InvoiceController extends Controller
{
    use CustomerTrait;
    public function index(){
        //
    }

    public function show(Order $order){
        if(!$order){
            abort(404);
        }
        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
        $date = new DateTime($order->created_at);

        // Customer
        $customerInfo = ["fullname" => "", "phone" => null];
        if($order->customer){
            $customer = $order->customer;
            $customerInfo["fullname"] = $order->customer->getFullnameAttribute();
            if(self::hasCustomerAddress($customer)){
                if($customer->addressCustomers->first()->address->phone){
                    $address = $customer->addressCustomers->first()->address;
                    $customerInfo["phone"] = $address->phone_code + $address->phone;
                }
            }
        }

        // Order Product
        $listOrderProducts = null;
        if($orderProducts = $order->orderProducts){
            foreach($orderProducts as $orderProduct){
                $listOrderProducts[] = [
                    "products" => [
                        "name" => $orderProduct->product->name,
                        "article" => null,
                        "description" => strip_tags($orderProduct->product->description),
                        "cover_image" => $orderProduct->product->getFirstMedia() ? $orderProduct->product->getFirstMedia()->getUrl("thumb") : null,
                    ],
                    "declinations" => self::productDeclination($orderProduct->declinations),
                    "price_unit" => number_format($orderProduct->sub_totals / $orderProduct->quantity, 2, '.', ''),
                    "sub_totals" => number_format($orderProduct->sub_totals, 2, '.', ''),
                    "quantity" => $orderProduct->quantity
                ];
            }
        }else{
            abort(404);
        }

        // Delivery Date
        $delivery_date = null;
        if($order->delivery && $order->has_delivery){
            if($order->delivery->delivery_date){
                $delivery_date = (new DateTime($order->delivery->delivery_date))->format("d F Y");
            }
        }

        return view("orders.print", [
            "created_at" => $date->format("d F Y"),
            "customer" => $customerInfo,
            "orderProducts" => $listOrderProducts,
            "order" => [
                "reference_order" => $order->reference_order,
                "total_amount" => number_format($order->total_amount_order, 2, '.', ''),
                "discount" => number_format($order->discount, 2, '', ''),
                "versement" => number_format($order->order_amount, 2, '.', ''),
                "balance" => number_format($order->total_amount_order - $order->order_amount, 2, '.', '')
            ],
            "delivery" => ["delivery_date" => $delivery_date],
            "currency" => $order->currency
        ]);
    }

    public function productDeclination($declination) : array{
        $result = [];
        if($declination != null){
            $values =  Declination::find($declination->id)->values;
            foreach($values as $value){
                $result[] = [
                        $value->attribute->name => $value->value,
                ];
            }
        }
        return $result;
    }
}
