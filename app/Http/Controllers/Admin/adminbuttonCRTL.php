<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InventoryReporting;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\Project;
use Carbon\Carbon;

class adminbuttonCRTL extends Controller
{
    use InventoryReporting;

    /**
     * Display a listing of the resource.
     */
    public function DTIndex()
    {
        return view('admin.DraftTable.DTIndex');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function PSIndex()
    {
        return view('admin.ProjectSelection.PSIndex');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function FSIndex(Request $request)
    {
        return view('admin.FinalSubmit.FSIndex');
    }

    /**
     * Display the specified resource.
     */
    

    /**
     * Show the form for editing the specified resource.
     *
     * Receiving Portal — cards show real counts (For Receiving / Under
     * Inspection / Received), and the table below can be filtered by the
     * same three buckets and searched by PO number, description, or
     * stock number.
     *
     * Bucket definitions (no separate "under_inspection" status exists
     * on purchase_orders — status is only pending_inspection/approved/
     * rejected):
     *   - For Receiving:   status = pending_inspection, delivery_photo not yet uploaded
     *   - Under Inspection: status = pending_inspection, delivery_photo already uploaded
     *   - Received:        status = approved
     */
    public function ReceiveIndex(Request $request)
    {
        $forReceivingCount = (clone $this->baseReceivingQuery())
            ->where('status', 'pending_inspection')
            ->whereNull('delivery_photo')
            ->count();

        $underInspectionCount = (clone $this->baseReceivingQuery())
            ->where('status', 'pending_inspection')
            ->whereNotNull('delivery_photo')
            ->count();

        $receivedCount = (clone $this->baseReceivingQuery())
            ->where('status', 'approved')
            ->count();

        $filter = $request->input('filter', 'all');
        $search = trim((string) $request->input('search', ''));

        $query = $this->baseReceivingQuery();

        if ($filter === 'for_receiving') {
            $query->where('status', 'pending_inspection')->whereNull('delivery_photo');
        } elseif ($filter === 'under_inspection') {
            $query->where('status', 'pending_inspection')->whereNotNull('delivery_photo');
        } elseif ($filter === 'received') {
            $query->where('status', 'approved');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('stock_no', 'like', "%{$search}%");
            });
        }

        $latestDeliveries = $query->latest()->get();

        return view('admin.Receiving.ReceiveIndex', compact(
            'latestDeliveries',
            'forReceivingCount',
            'underInspectionCount',
            'receivedCount',
            'filter',
            'search'
        ));
    }

    /**
     * Shared starting point for every Receiving Portal query (counts,
     * filtered table) so the bucket definitions can't drift between them.
     */
    protected function baseReceivingQuery()
    {
        return PurchaseOrder::query();
    }

    /**
     * Update the specified resource in storage.
     */
    public function AuditIndex()
    {
        return view('admin.AuditTrial.AuditIndex');
    }


    /**
     * Remove the specified resource from storage.
     */

    /**
     * Reports — Admin Aide's Generate Report page. Mirrors Supply's
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

        return view('admin.report.ReportIndex', compact(
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
     * Projects — Admin Aide's Project listing. Same real Project records
     * (with their distributions/items) that Supply Office sees.
     */
    public function ProjectIndex()
    {
        $projects = Project::with('distributions.items')->orderBy('created_at', 'desc')->get();
        $projectMaterials = $this->buildProjectMaterials($projects);

        return view('admin.Project.ProjectIndex', compact('projects', 'projectMaterials'));
    }
}