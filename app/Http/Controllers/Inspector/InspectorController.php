<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InventoryReporting;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\InspectionLog;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class InspectorController extends Controller
{
    use InventoryReporting;

    /**
     * Inspector Dashboard
     */
    public function index()
    {
        $hasRemoteCol = Schema::hasColumn('purchase_orders', 'is_remote');

        $allOrders = PurchaseOrder::all();

        $stats = [
            'pending'  => $allOrders->filter(fn($po) => in_array(strtolower($po->status ?? ''), ['pending', 'pending_inspection', 'for_inspection', 'ready_for_inspection', '']) || is_null($po->status))->count(),
            'approved' => $allOrders->filter(fn($po) => strtolower($po->status ?? '') === 'approved')->count(),
            'rejected' => $allOrders->filter(fn($po) => strtolower($po->status ?? '') === 'rejected')->count(),
            'remote'   => $hasRemoteCol ? $allOrders->filter(fn($po) => (bool)($po->is_remote ?? false))->count() : 0,
        ];

        return view('inspector.dashboard', compact('stats'));
    }

    /**
     * Inspection Listing view
     */
    public function InspectIndex(Request $request)
    {
        $statusFilter = $request->query('status', 'all');

        // Hide POs where Supply hasn't submitted a delivery photo for ANY
        // item yet — there's nothing for the Inspector to act on, so
        // showing them here is just noise.
        $purchaseOrders = PurchaseOrder::latest()->get()->filter(function ($po) {
            $items = is_array($po->items) ? $po->items : json_decode($po->items ?? '[]', true);

            return collect($items)->contains(fn ($item) => !empty($item['delivery_photo'] ?? null));
        })->values();

        return view('inspector.Inspect.InspectIndex', compact('purchaseOrders', 'statusFilter'));
    }

    /**
     * Approved Purchase Orders view
     */
    public function approveIndex()
    {
        $purchaseOrders = PurchaseOrder::where('status', 'approved')->latest()->get();
        return view('inspector.approve', compact('purchaseOrders'));
    }

    /**
     * Rejected Purchase Orders view
     */
    public function rejectIndex()
    {
        $purchaseOrders = PurchaseOrder::where('status', 'rejected')->latest()->get();
        return view('inspector.reject', compact('purchaseOrders'));
    }

    /**
     * Update PO Status
     */
    public function updateStatus(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);

        $request->validate([
            'status'  => 'required|in:approved,rejected',
            'remarks' => 'nullable|string',
        ]);

        $status = $request->input('status');

        $po->update([
            'status' => $status,
        ]);

        if ($request->filled('remarks')) {
            InspectionLog::create([
                'purchase_order_id' => $po->id,
                'inspector_id'      => Auth::id(),
                'decision'          => $status,
                'remarks'           => $request->remarks,
            ]);
        }

        return back()->with('success', "PO {$po->po_number} status updated to " . strtoupper($status) . ".");
    }

    /**
     * Approve/Reject a single item within a PO's items JSON column.
     *
     * IMPORTANT: An item can be delivered in multiple batches (Supply
     * submits partial quantities over time). Approval must therefore be
     * quantity-aware — we track how much has been APPROVED separately
     * from how much has been DELIVERED so far. Approving the item only
     * ever "catches up" approved_quantity to whatever has actually been
     * delivered; it never silently marks a not-yet-reviewed later batch
     * as approved.
     */
    public function updateItemStatus(Request $request, $id, $index)
    {
        $request->validate([
            'status'  => 'required|in:approved',
            'remarks' => 'nullable|string',
        ]);

        $po = PurchaseOrder::findOrFail($id);
        $items = is_array($po->items) ? $po->items : json_decode($po->items ?? '[]', true);
        $index = (int) $index;

        if (!isset($items[$index])) {
            return response()->json(['message' => 'Item not found on this Purchase Order.'], 404);
        }

        if (empty($items[$index]['delivery_photo'])) {
            return response()->json(['message' => 'This item cannot be approved until Supply submits its delivery photo.'], 422);
        }

        $neededQty = (float) ($items[$index]['quantity'] ?? 0);

        // How much has ACTUALLY been delivered so far (sum of all batches),
        // falling back to inspected_quantity for legacy items with no
        // deliveries[] array.
        $deliveredQty = (isset($items[$index]['deliveries']) && is_array($items[$index]['deliveries']))
            ? collect($items[$index]['deliveries'])->sum(fn ($d) => (float) ($d['quantity'] ?? 0))
            : (float) ($items[$index]['inspected_quantity'] ?? 0);

        $alreadyApprovedQty = (float) ($items[$index]['approved_quantity'] ?? 0);

        if ($deliveredQty <= $alreadyApprovedQty) {
            return response()->json(['message' => 'There is no newly delivered quantity to approve for this item.'], 422);
        }

        $remarks = $request->input('remarks');

        // Approve everything that has been delivered up to now.
        $items[$index]['approved_quantity']  = $deliveredQty;
        $items[$index]['inspection_status']  = $deliveredQty >= $neededQty ? 'approved' : 'partial';
        $items[$index]['inspection_remarks'] = $remarks;
        $items[$index]['inspected_by']       = Auth::id();
        $items[$index]['inspected_at']       = now()->toDateTimeString();

        $po->items = $items;

        // A PO is fully approved once every item that actually needs
        // delivery has its approved_quantity caught up to its needed
        // quantity. Items with no/zero quantity on record don't block
        // approval — they simply have nothing to catch up on. A small
        // epsilon absorbs float rounding from repeated batch sums.
        $allApproved = collect($items)->every(function ($item) {
            $need     = (float) ($item['quantity'] ?? 0);
            $approved = (float) ($item['approved_quantity'] ?? 0);
            if ($need <= 0) {
                return true;
            }
            return $approved >= $need - 0.001;
        });
        if ($allApproved) {
            $po->status = 'approved';
        }

        $po->save();

        InspectionLog::create([
            'purchase_order_id' => $po->id,
            'inspector_id'      => Auth::id(),
            'decision'          => 'approved',
            'remarks'           => ($items[$index]['description'] ?? ('Item #' . ($index + 1)))
                . ' (' . $deliveredQty . ' of ' . $neededQty . ' units): ' . ($remarks ?? ''),
        ]);

        return response()->json([
            'message'   => ($items[$index]['description'] ?? 'Item') . ' approved and sent to the Warehouse queue.',
            'po_status' => $po->status,
        ]);
    }

    /**
     * Reports — Inspector's Generate Report page. Mirrors Supply's
     * ReportIndex exactly: same date-range logic, same
     * items-received/items-distributed/total-inventory figures.
     */
    public function ReportIndex(Request $request)
    {
        $totalInventory = $this->computeTotalInventory();
        $inventoryBreakdown = $this->buildInventoryBreakdown();

        $rangeWasRejected = ($request->filled('start_date') || $request->filled('end_date'))
            && !$this->isReportRangeValid($request->input('start_date'), $request->input('end_date'));

        [$rangeStart, $rangeEnd, $isCustomRange] = $this->resolveReportRange(
            $request->input('start_date'),
            $request->input('end_date')
        );

        [$itemsReceived, $itemsDistributed, $receivedBreakdown, $distributedBreakdown] =
            $this->computeItemsReceivedAndDistributedDetailed($rangeStart, $rangeEnd);

        return view('inspector.Report.ReportIndex', compact(
            'itemsReceived',
            'itemsDistributed',
            'totalInventory',
            'isCustomRange',
            'rangeWasRejected',
            'rangeStart',
            'rangeEnd',
            'receivedBreakdown',
            'distributedBreakdown',
            'inventoryBreakdown'
        ));
    }

    /**
     * Projects — Inspector's Project listing. Same real Project records
     * (with their distributions/items) that Supply Office sees.
     *
     * NOTE: Inspector intentionally has no Weekly Audit page/route.
     */
    public function ProjectIndex()
    {
        $projects = Project::with('distributions.items')->orderBy('created_at', 'desc')->get();
        $projectMaterials = $this->buildProjectMaterials($projects);

        return view('inspector.Project.ProjectIndex', compact('projects', 'projectMaterials'));
    }
}