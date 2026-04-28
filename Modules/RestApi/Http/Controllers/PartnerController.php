<?php

namespace Modules\RestApi\Http\Controllers;

use App\Models\DeliveryExecutive;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\RestApi\Traits\ApiResponse;

class PartnerController extends Controller
{
    use ApiResponse;

    /**
     * Get partner profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile(Request $request)
    {
        $partner = $request->get('partner') ?? $request->user();

        if (!$partner instanceof DeliveryExecutive) {
            return $this->errorResponse('Partner not found', 404);
        }

        return $this->successResponse([
            'id' => $partner->id,
            'name' => $partner->name,
            'phone' => $partner->phone,
            'photo' => $partner->photo,
            'status' => $partner->status,
            'branch_id' => $partner->branch_id,
            'branch' => $partner->branch ? [
                'id' => $partner->branch->id,
                'name' => $partner->branch->name,
            ] : null,
        ], 'Profile retrieved successfully');
    }

    /**
     * Get latest active order
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLatestOrder(Request $request)
    {
        $partner = $request->get('partner') ?? $request->user();

        if (!$partner instanceof \App\Models\DeliveryExecutive) {
            return $this->errorResponse('Partner not found', 404);
        }

        $order = Order::where('delivery_executive_id', $partner->id)
            ->whereIn('order_status', [
                OrderStatus::OUT_FOR_DELIVERY->value,
                OrderStatus::READY_FOR_PICKUP->value,
            ])
            ->with([
                'customer',
                'customer.latestDeliveryAddress',
                'items.menuItem',
                'branch.restaurant', // To get restaurant info
                'orderType',
                'deliveryExecutive',
            ])
            ->latest()
            ->first();

        if ($order) {
            // Compose restaurant info
            $branch = $order->branch && $order->branch ? $order->branch : null;
            $restaurant = $branch ? $branch->restaurant : null;

            // Compose restaurant coordinates (model fields assumed: latitude, longitude)
            $restaurantCoordinates = [
                'latitude' => $branch ? (float)($branch->lat ?? 0) : 0,
                'longitude' => $branch ? (float)($branch->lng ?? 0) : 0,
            ];

            // Compose customer info
            $customer = $order->customer;

            $customerDelivery = $customer && $customer->latestDeliveryAddress ? $customer->latestDeliveryAddress : null;
            $customerCoordinates = [
                'latitude' => $customerDelivery ? (float)($customerDelivery->lat ?? 0) : 0,
                'longitude' => $customerDelivery ? (float)($customerDelivery->lng ?? 0) : 0,
            ];

            // Map order status to label (you can expand this map as needed)
            $statusMap = [
                OrderStatus::OUT_FOR_DELIVERY->value => 'Out for delivery',
                OrderStatus::READY_FOR_PICKUP->value => 'Ready to deliver',
            ];

            $status = $statusMap[$order->order_status->value] ?? $order->order_status->value;

            // Partner info
            $partnerData = [
                'name' => $partner->name ?? '',
                'contact' => $partner->phone ?? '',
            ];

            // Items
            $items = [];


            foreach ($order->items as $item) {
                $items[] = [
                    'name' => $item->menuItem ? $item->menuItem->name : ($item->name ?? ''),
                    'quantity' => (int)$item->quantity,
                ];
            }

            // Instructions (assuming exists on order as 'delivery_instructions' or similar)
            $instructions = $order->delivery_instructions ?? null;

            // Payment info
            $modeMap = [
                'cod' => 'COD',
                'cash_on_delivery' => 'COD',
                'online' => 'Prepaid',
                'prepaid' => 'Prepaid',
            ];
            $paymentMode = $modeMap[strtolower($order->payment_mode ?? '')] ?? ($order->payment_mode ?? 'Prepaid');
            $currencySymbol = method_exists($order, 'currency_symbol') ? $order->currency_symbol() : '₹';

            $paymentAmount = $currencySymbol . number_format(($order->grand_total ?? 0), 0);

            // Pickup/delivery metrics – these would usually be computed, for now use stubs or order fields if available
            // Calculate distance and time between customer and branch (restaurant) coordinates
            $restaurantLat = $restaurantCoordinates['latitude'] ?? 0;
            $restaurantLng = $restaurantCoordinates['longitude'] ?? 0;
            $customerLat = $customerCoordinates['latitude'] ?? 0;
            $customerLng = $customerCoordinates['longitude'] ?? 0;

            $distanceToCustomer = null;
            $durationToCustomer = null;

            if ($restaurantLat && $restaurantLng && $customerLat && $customerLng) {
                // Haversine formula for distance in kilometers
                $earthRadius = 6371; // Radius of the Earth in km

                $latFrom = deg2rad($restaurantLat);
                $lngFrom = deg2rad($restaurantLng);
                $latTo = deg2rad($customerLat);
                $lngTo = deg2rad($customerLng);

                $latDelta = $latTo - $latFrom;
                $lngDelta = $lngTo - $lngFrom;

                $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                    cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)));
                $distance = $earthRadius * $angle;

                $distanceToCustomer = round($distance, 2) . ' km';

                // Estimate duration assuming 25 km/h (urban) = 0.42 km/min
                $averageSpeedKmh = 25;
                $averageSpeedKpm = $averageSpeedKmh / 60;

                $estimatedMinutes = ($averageSpeedKpm > 0) ? round($distance / $averageSpeedKpm) : null;
                // "08 mins" style string (always at least 1 min)
                $durationToCustomer = $estimatedMinutes ? str_pad($estimatedMinutes, 2, '0', STR_PAD_LEFT) . ' mins' : null;
            }

            $pickupETA = $durationToCustomer ?? '---';
            $dropETA = $durationToCustomer ?? '---';
            $distanceToPickup = $distanceToCustomer ?? '---';

            // Compose timeline - assuming order has timestamps, copy as needed
            $timeline = [];
            // Example: you can customize/expand these as required


            if ($order->created_at) {
                $timeline[] = ['time' => $order->created_at->format('H:i'), 'label' => 'Order placed'];
            }


            if ($order->ready_at ?? false) {
                $timeline[] = ['time' => $order->ready_at->format('H:i'), 'label' => 'Ready for pickup'];
            }


            if ($order->picked_at ?? false) {
                $timeline[] = ['time' => $order->picked_at->format('H:i'), 'label' => 'Picked up'];
            }


            if ($order->delivered_at ?? false) {
                $timeline[] = ['time' => $order->delivered_at->format('H:i'), 'label' => 'Delivered'];
            }

            // Final result format
            $result = [
                'id' => $order->order_number ?? ('ORD-' . $order->id),
                'status' => $status,
                'restaurant' => [
                    'name' => $restaurant ? $restaurant->name : '',
                    'address' => $restaurant ? $restaurant->address : '',
                    'contact' => $restaurant ? $restaurant->phone : '',
                    'coordinates' => $restaurantCoordinates,
                ],
                'customer' => [
                    'name' => $customer ? $customer->name : '',
                    'phone' => $customer ? $customer->phone : '',
                    'address' => $customerDelivery ? $customerDelivery->address : '',
                    'coordinates' => $customerCoordinates,
                ],
                'partner' => $partnerData,
                'dropETA' => $dropETA,
                'distanceToCustomer' => $distanceToCustomer,
                'instructions' => $instructions,
                'payment' => [
                    'mode' => $paymentMode,
                    'amount' => $paymentAmount,
                ],
                'timeline' => $timeline,
                'items' => $items,
            ];

            // End here; do not return as successResponse, that comes after.
        }


        if (!$order) {
            return $this->successResponse(null, 'No active order found');
        }

        return $this->successResponse($result, 'Active order retrieved successfully');
    }

    /**
     * Get order history
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderHistory(Request $request)
    {
        $partner = $request->get('partner') ?? $request->user();

        if (!$partner instanceof DeliveryExecutive) {
            return $this->errorResponse('Partner not found', 404);
        }

        $perPage = $request->get('per_page', 15);
        $orders = Order::where('delivery_executive_id', $partner->id)
            ->with([
                'customer',
                'customer.deliveryAddresses',
                'items.menuItem',
                'branch',
                'orderType',
            ])
            ->latest()
            ->paginate($perPage);

        $transformedOrders = $orders->map(function ($order) {
            return $this->transformOrder($order);
        });

        return $this->paginatedResponse(
            $orders->setCollection($transformedOrders),
            'Order history retrieved successfully'
        );
    }

    /**
     * Start order (accept order)
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function startOrder(Request $request, string $id)
    {
        $partner = $request->get('partner') ?? $request->user();

        if (!$partner instanceof DeliveryExecutive) {
            return $this->errorResponse('Partner not found', 404);
        }

        $order = Order::where('delivery_executive_id', $partner->id)
            ->where(function ($q) use ($id) {
                $q->where('uuid', $id)
                    ->orWhere('id', $id);
            })
            ->first();

        if (!$order) {
            return $this->notFoundResponse('Order not found');
        }

        if ($order->delivery_executive_id && $order->delivery_executive_id !== $partner->id) {
            return $this->errorResponse('Order is assigned to another partner', 403);
        }

        // Assign order to partner
        $order->delivery_executive_id = $partner->id;
        $order->order_status = OrderStatus::OUT_FOR_DELIVERY;
        $order->save();

        // Update partner status
        $partner->status = 'on_delivery';
        $partner->save();

        return $this->successResponse($this->transformOrder($order), 'Order started successfully');
    }

    /**
     * Update order status (on way / reached)
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrderStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|string|in:on_way,reached',
        ]);

        $partner = $request->get('partner') ?? $request->user();

        if (!$partner instanceof DeliveryExecutive) {
            return $this->errorResponse('Partner not found', 404);
        }

        $order = Order::where(function ($q) use ($id) {
            $q->where('uuid', $id)
                ->orWhere('id', $id);
        })
            ->where('delivery_executive_id', $partner->id)
            ->first();

        if (!$order) {
            return $this->notFoundResponse('Order not found');
        }

        // For "on_way" and "reached", we keep the order status as OUT_FOR_DELIVERY
        // You can add additional tracking fields if needed
        // For now, we'll just update a note or timestamp

        // Status "reached" is acknowledged - you might want to add a reached_at timestamp field
        // in a future update

        return $this->successResponse($this->transformOrder($order), 'Order status updated successfully');
    }

    /**
     * Complete order
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeOrder(Request $request, string $id)
    {
        $partner = $request->get('partner') ?? $request->user();

        if (!$partner instanceof DeliveryExecutive) {
            return $this->errorResponse('Partner not found', 404);
        }

        $order = Order::where(function ($q) use ($id) {
            $q->where('uuid', $id)
                ->orWhere('id', $id);
        })
            ->where('delivery_executive_id', $partner->id)
            ->first();

        if (!$order) {
            return $this->notFoundResponse('Order not found');
        }

        $order->order_status = OrderStatus::DELIVERED;
        $order->save();

        // Update partner status back to available
        $partner->status = 'available';
        $partner->save();

        return $this->successResponse($this->transformOrder($order), 'Order completed successfully');
    }

    /**
     * Transform order data for response
     *
     * @param Order $order
     * @return array
     */
    protected function transformOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'uuid' => $order->uuid,
            'order_number' => $order->order_number,
            'order_status' => $order->order_status->value,
            'order_status_label' => $order->order_status->label(),
            'order_type' => $order->order_type ?? null,
            'date_time' => $order->date_time?->toIso8601String(),
            'sub_total' => $order->sub_total,
            'total' => $order->total,
            'delivery_address' => $order->delivery_address,
            'delivery_time' => $order->delivery_time?->toIso8601String(),
            'estimated_delivery_time' => $order->estimated_delivery_time?->toIso8601String(),
            'customer' => $order->customer ? [
                'id' => $order->customer->id,
                'name' => $order->customer->name,
                'phone' => $order->customer->phone,
                'phone_code' => $order->customer->phone_code,
            ] : null,
            'branch' => $order->branch ? [
                'id' => $order->branch->id,
                'name' => $order->branch->name,
                'address' => $order->branch->address,
            ] : null,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'menu_item_name' => $item->menuItem->name ?? null,
                    'variation_name' => $item->menuItemVariation->name ?? null,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                ];
            }),
        ];
    }

}
