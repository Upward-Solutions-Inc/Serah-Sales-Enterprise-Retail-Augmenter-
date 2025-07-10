<?php

namespace App\Http\Controllers\Tenant\Order;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Order\Order;
use App\Models\Tenant\Sales\Cash\CashRegister;
use App\Repositories\Core\Status\StatusRepository;
use App\Services\Tenant\Order\OrderService;
use App\Services\Retail\RecipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{

    protected $recipeService;

    public function __construct(OrderService $service, RecipeService $recipeService)
    {
        $this->service = $service;
        $this->recipeService = $recipeService;
    }

    public function index(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->service
            ->with(
                'orderProducts',
                'transactions',
                'transactions.paymentMethod',
                'orderProducts.stock',
                'orderProducts.stock.stockQuantities'
            )
            ->latest('id')
            ->paginate(
                request('per_page', 10)
            );
    }


    public function store(Request $request)
    {
        $cashCounter = CashRegister::query()->find(request('cash_register_id'));
        if(($cashCounter->status_id !== resolve(StatusRepository::class)->cash_counterOpen())) {
            throw new GeneralException(__t('please_open_counter_to_order'), 422);
        }

        DB::beginTransaction();
        try {
            $order = $this->service
                ->setAttributes($request->all())
                ->generateInvoiceId()
                ->storeOrder()
                ->storeOrderProduct()
                ->sendInvoice()
                ->makeTransactions()
                ->sendAutoSms()
                ->generateSalesInvoiceTemplate();
            DB::commit();
            return $order;
        }
        catch (\Exception $exception) {
            DB::rollBack();
            throw new GeneralException($exception->getMessage());
        }
    }

    public function holdOrderStore(Request $request): \Illuminate\Database\Eloquent\Model
    {
        DB::beginTransaction();
        try {
            $this->service
                ->setAttributes($request->all())
                ->generateInvoiceId()
                ->storeOrder()
                ->storeOrderProduct()
                ->sendInvoice();
            DB::commit();
            return $this->service->getModel();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new GeneralException($exception->getMessage());
        }
    }

    public function show(Order $order): Order
    {
        return $order;
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return deleted_responses('order');
    }


    public function onHoldOrders(Request $request): \Illuminate\Database\Eloquent\Collection|array
    {
        return $this->service->holdOrders();
    }

    public function orderPayment(Request $request, Order $order): array
    {
        DB::beginTransaction();
        try {
            $result = $this->service
                ->setModel($order)
                ->setAttributes($request->all())
                ->makeTransactions()
                ->generateSalesInvoiceTemplate();

            // Reload order with products (in case of changes)
            $order = $this->service->getModel()->load('orderProducts');
            foreach ($order->orderProducts as $orderProduct) {
                // Check if product has a recipe
                if (\App\Models\Retail\Recipe\Recipe::where('product_id', $orderProduct->order_product_id)->exists()) {
                    $deductResult = $this->recipeService->deductIngredientsOnSale($orderProduct->order_product_id, $orderProduct->quantity);
                    if (!$deductResult['success']) {
                        throw new \Exception($deductResult['message']);
                    }
                }
            }

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new GeneralException($e->getMessage());
        }
    }

    public function cancelOrder(Order $order)
    {
        $order->update([
            'status_id' => resolve(StatusRepository::class)->orderCancel(),
            'type' => 'order_cancel'
        ]);
        return updated_responses('order_cancelled');
    }

    public function holdOrderDelete(Request $request)
    {
        try {
            $this->service->setAttributes($request->all())->holdOrderDelete();
        } catch (\Exception $exception) {
            return $exception;
        }

        return deleted_responses('hold_order');
    }

    public function maxMinPrice(): array
    {
        return $this->service->maxMinPriceAmount();
    }

    public function duePaymentInfo(Order $order): Order
    {
        return $order->load('orderProducts');
    }

    public function dueReceive(Request $request, Order $order): JsonResponse
    {

        $this->service
            ->setModel($order)
            ->setDueCollectValidator()
            ->setAttributes($request->all())
            ->duePaymentReceive();

        return response()->json([
            'status' => true,
            'message' => __t('due_payment_received')
        ]);
    }

    public function generateInvoice(Order $order): array
    {

        $barcode = (new \Milon\Barcode\DNS1D)->getBarcodeHTML("{$order->last_invoice_number}", "I25+");
        $qrcode = (new \Milon\Barcode\DNS2D)->getBarcodeHTML("{$order->last_invoice_number}", "QRCODE");
        $order['barcode'] = $barcode;
        $order['qrcode'] = $qrcode;

        return $this->service->setModel($order)->generateSalesInvoiceTemplate();
    }

    public function downloadInvoicePdf(Order $order)
    {
        // Eager-load only necessary relationships
        $order->load([
            'orderProducts.variant',
            'orderProducts.unit',
            'tax',
            'customer.phoneNumbers',
            'customer.addresses',
            'branchOrWarehouse',
            'cashRegister'
        ]);

        // Prepare barcode and qrcode if needed
        $order['barcode'] = (new \Milon\Barcode\DNS1D)->getBarcodeHTML("{$order->last_invoice_number}", "I25+");
        $order['qrcode'] = (new \Milon\Barcode\DNS2D)->getBarcodeHTML("{$order->last_invoice_number}", "QRCODE");

        // Pass only what is needed to the view
        $invoiceSetting = [
            'sales_invoice_logo' => settings('sales_invoice_logo', null)
        ];

        // Render the PDF
        $pdf = Pdf::loadView('pdf.invoice_pdf', compact('order', 'invoiceSetting'))
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'defaultFont' => 'sans-serif'
            ]);

        return $pdf->download('invoice.pdf');
    }


}
