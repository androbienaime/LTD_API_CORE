<?php

namespace App\Core\ResourceModules\Order;

use App\Core\Class\Coupons;
use App\Core\ResourceModules\Concerns\HasOrderTotal;
use App\Core\Trait\ProductTrait;
use App\Models\Core\Coupon;
use App\Models\Core\Product;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;

class OrderCouponResource
{
    use ProductTrait, HasOrderTotal;
    /**
     * Handles coupon application and validation
     */
    public static function form() : array
    {
        return [
            Hidden::make('coupon_id'),
            TextInput::make('coupon')
                ->label(trans('Coupon'))
                ->dehydrated(false)
                ->hidden(fn($record) => $record)
                ->suffixAction(
                    Action::make('apply')
                        ->tooltip(trans('filament-ecommerce::messages.orders.actions.apply'))
                        ->icon('heroicon-s-check')
                        ->action(function (Get $get, Set $set, $livewire){
                            $coupon = Coupon::query()->where('code', $get('coupon'))->first();
                            if($coupon){
                                $total =0;
                                $discount=0;
                                $items = $get('orderProducts');

                                $productIds = [];
                                foreach ($items as $orderItem){
                                    $productIds[] = $orderItem['product_id'];
                                    $discount += self::productDiscount(Product::find($orderItem['product_id']));

                                }
                                $total = self::updateTotals($get, $set, $livewire);

                                $getCouponDiscount = (new Coupons())
                                    ->products($productIds)
                                    ->discount(code : $get("coupon"), total : $total);
                                if($getCouponDiscount){
                                    $discount += $getCouponDiscount;

                                    $set("total_discount", $discount);
                                    $set("coupon_id", $coupon->id);
                                    self::updateTotals($get, $set, $livewire);

                                    Notification::make()
                                        ->title(trans('Coupons appliquer'))
                                        ->success()
                                        ->send();
                                }else{
                                    Notification::make()
                                        ->title("Coupon non valid")
                                        ->danger()
                                        ->send();
                                }
                            }else{
                                Notification::make()
                                    ->title("Ce coupon n'existe pas")
                                    ->danger()
                                    ->send();
                            }

                        })
                )->columnSpanFull(),
        ];
    }

}
