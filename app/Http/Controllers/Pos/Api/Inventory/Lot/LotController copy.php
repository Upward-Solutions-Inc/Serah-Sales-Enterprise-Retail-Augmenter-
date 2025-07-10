<?php

namespace App\Http\Controllers\Pos\Api\Inventory\Lot;

use App\Exceptions\GeneralException;
use App\Filters\Pos\Inventory\LotFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\Inventory\Lot\LotRequest;
use App\Models\Pos\Inventory\Lot\Lot;
use App\Repositories\Core\Status\StatusRepository;
use App\Services\Pos\Inventory\Lot\LotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Retail\RecipeService;

class LotController extends Controller
{
    protected $recipeService;

    public function __construct(LotService $lotServices, LotFilter $lotFilter, RecipeService $recipeService)
    {
        $this->service = $lotServices;
        $this->filter = $lotFilter;
        $this->recipeService = $recipeService;
    }

    public function index()
    {
        return $this->service
            ->loadLotLists($this->filter);
    }

    public function store(LotRequest $request)
    {
        $lotVariants = $request->get('lotVariants', []);
        foreach ($lotVariants as $variant) {
            if (\App\Models\Retail\Recipe\Recipe::where('product_id', $variant['variant_id'])->exists()) {
                $maxQty = $this->recipeService->getMaxProducibleQuantity($variant['variant_id']);
                if ($variant['unit_quantity'] > $maxQty) {
                    throw new GeneralException("Insufficient ingredients for product ID {$variant['variant_id']}. Maximum producible quantity is {$maxQty}.");
                }
            }
        }
        DB::transaction(function () use ($request) {
            $this->service
                ->setAttrs($request->all())
                ->storeLotData()
                ->storeLotVariantData()
                ->updateStockForAddingNewLotAsDelivered()
            ;
        });

        return created_responses('lot');
    }


    public function show(Lot $lot): Lot
    {
        return $lot->load($this->service->relations());
    }

    public function update(LotRequest $request, Lot $lot)
    {
        $status_delivered_id = resolve(StatusRepository::class)->purchaseDelivered();

        //Delivered /received lot can't update
        throw_if($lot->status_id === $status_delivered_id, new GeneralException(__t('delivered_lot_not_allowed_to_update')));

        $lotVariants = $request->get('lotVariants', []);
        foreach ($lotVariants as $variant) {
            if (\App\Models\Retail\Recipe\Recipe::where('product_id', $variant['variant_id'])->exists()) {
                $maxQty = $this->recipeService->getMaxProducibleQuantity($variant['variant_id']);
                if ($variant['unit_quantity'] > $maxQty) {
                    throw new GeneralException("Insufficient ingredients for product ID {$variant['variant_id']}. Maximum producible quantity is {$maxQty}.");
                }
            }
        }

        DB::transaction(function () use ($request, $lot, $status_delivered_id) {
            $this->service
                ->setModel($lot)
                ->setAttrs($request->all())
                ->updateLot(
                    $request->only('branch_or_warehouse_id', 'supplier_id', 'purchase_date','total_amount',
                        'status_id', 'other_charge', 'discount_amount', 'created_by', 'reference_no', 'note')
                )
                ->UpdateLotVariant()
                ->updateStock($status_delivered_id);
        });

        return updated_responses('lot');
    }

    public function destroy(Lot $lot)
    {
        $status_delivered_id = resolve(StatusRepository::class)->purchaseDelivered();

        //Delivered /received lot can't delete
        throw_if($lot->status_id === $status_delivered_id, new GeneralException(__t('delivered_lot_not_allowed_to_delete')));

        DB::transaction(function () use ($lot) {
            $this->service
                ->setModel($lot)
                ->deleteLotVariant()
                ->deleteLot();
        });

        return deleted_responses('lot');
    }


    //if lot status is delivered then update stock too
    public function changeLotStatus(Request $request, $lot_id)
    {
        $request->validate([
            'status_id' => 'required'
        ]);

        $lot = $this->service->find($lot_id);
        $status_delivered_id = resolve(StatusRepository::class)->purchaseDelivered();

        //Delivered status can't change to others
        throw_if($lot->status_id == $status_delivered_id, new GeneralException(__t('delivered_status_not_allowed_to_change')));

        DB::transaction(function () use ($status_delivered_id, $lot, $request) {
            $this->service
                ->setModel($lot)
                ->setAttrs($request->all())
                ->updateLotStatus()
                ->updateStock($status_delivered_id);
        });

        return updated_responses('status');
    }

}
