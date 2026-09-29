<?php

namespace App\Http\Controllers\Supply;

use App\Models\Admin\management;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InventoryReporting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\PurchaseOrder;
use App\Models\Project;
use App\Models\Distribution;
use App\Models\DistributionItem;

class SupplyController extends Controller
{
    use InventoryReporting;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pendingPRs = management::whereDoesntHave('purchaseOrders')->orderBy('created_at', 'desc')->get();
        $newPRCount = management::whereDoesntHave('purchaseOrders')->count();

        // Fetch POs pending inspection
        $pendingPOs = PurchaseOrder::where('status', 'pending_inspection')->get();
        $pendingPOCount = $pendingPOs->count();

        // Count total items across all POs awaiting inspection
        $totalItemsForInspection = 0;
        foreach ($pendingPOs as $po) {
            $items = is_array($po->items) ? $po->items : json_decode($po->items ?? '[]', true);
            $totalItemsForInspection += count($items);
        }

        return view('supply.dashboard', compact(
            'pendingPRs',
            'newPRCount',
            'pendingPOCount',
            'totalItemsForInspection'
        ));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function PreIndex()
    {
        // 1. CRITICAL FIX: Kunin lamang ang mga PR na HINDI PA nagagawaan ng Purchase Order
        // Ito ang magtatanggal sa PR #9 sa listahan kapag na-finalize na ang transaction.
        $prs = management::whereDoesntHave('purchaseOrders')->orderBy('created_at', 'desc')->get();
        $purchaseOrders = PurchaseOrder::all();

        // Pass to the view
        return view('supply.precurement.PreIndex', compact('prs', 'purchaseOrders'));
    }

    public function previewPrFile($id)
    {
        $pr = management::findOrFail($id);
        $disk = Storage::disk('public');

        abort_unless($disk->exists($pr->file), 404);

        $contentType = match (strtolower(pathinfo($pr->file, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'csv' => 'text/csv',
            default => 'application/octet-stream',
        };

        return response($disk->get($pr->file), 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="' . basename($pr->file) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    // In SupplyController.php

    public function itemPhotos()
    {
        // Fetch Purchase Orders along with their items
        $deliveryQueues = PurchaseOrder::with('items.itemPhotos')
            ->where('mode_of_inspection', 'Joint')
            ->latest()
            ->get();

        return view('supply.Itemphotos', compact('deliveryQueues'));
    }


    /**
     * Display the Joint Inspection / Upload Actual Item page.
     */
    public function UploadActualItemIndex()
    {
        // Kukunin ang mga PO na nag-aantay pa ng upload ng picture ng delivery
        $deliveryQueues = PurchaseOrder::where('status', 'pending_inspection')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Ipapasa ang $deliveryQueues variable sa blade file mo
        return view('supply.UploadActualItem.Itemphotos', compact('deliveryQueues'));
    }

    public function uploadDeliverySpecs(Request $request)
    {
        // 1. Basic presence/type validation (quantity ceiling is checked below,
        //    once we know how much of this item is still left to deliver)
        $request->validate([
            'po_id'              => 'required|exists:purchase_orders,id',
            'item_index'         => 'required|integer|min:0',
            'inspected_quantity' => 'required|integer|min:1',
            'delivery_photo'     => 'required|image|mimes:jpeg,png,jpg|max:5000',
        ]);

        // 2. Hanapin ang partikular na Purchase Order
        $po = PurchaseOrder::findOrFail($request->po_id);

        // 3. Kunin ang items array (JSON column)
        $items = is_array($po->items) ? $po->items : json_decode($po->items ?? '[]', true);

        $index = (int) $request->item_index;

        if (!isset($items[$index])) {
            return redirect()->route('supply.UploadActualItem')->with('error', 'Item not found on this Purchase Order.');
        }

        // 4. Deliveries can arrive in batches (e.g. 10 units today, 8 more later).
        //    "deliveries" is a running log of every batch submitted for this item.
        $needed           = (int) ($items[$index]['quantity'] ?? 0);
        $deliveries       = $items[$index]['deliveries'] ?? [];
        $alreadyDelivered = collect($deliveries)->sum('quantity');
        $remaining        = max(0, $needed - $alreadyDelivered);

        if ($remaining <= 0) {
            return redirect()->route('supply.UploadActualItem')->with('error', 'This item has already been fully delivered.');
        }

        if ((int) $request->inspected_quantity > $remaining) {
            return back()->withErrors([
                'inspected_quantity' => "Only {$remaining} unit(s) are still pending for this item.",
            ])->withInput();
        }

        // 5. I-save ang file sa storage
        $path = $request->file('delivery_photo')->store('delivery_photos', 'public');

        // 6. I-append ang bagong batch sa deliveries log ng SPECIFIC ITEM LANG
        $deliveries[] = [
            'quantity'     => (int) $request->inspected_quantity,
            'photo'        => $path,
            'submitted_at' => now()->toDateTimeString(),
        ];

        $items[$index]['deliveries'] = $deliveries;

        $newDeliveredTotal = collect($deliveries)->sum('quantity');
        $isComplete        = $newDeliveredTotal >= $needed;

        // Keep these two legacy keys in sync (running total / most recent photo)
        // so any older code or views still reading them keep working.
        $items[$index]['inspected_quantity'] = $newDeliveredTotal;
        $items[$index]['delivery_photo']     = $path;
        $items[$index]['item_status']        = $isComplete ? 'ready_for_inspection' : 'partial';

        $po->items = $items;

        // 7. I-bump lang ang PO-level status kapag COMPLETE NA ang lahat ng items
        $allSubmitted = collect($items)->every(function ($item) {
            $need      = (int) ($item['quantity'] ?? 0);
            $delivered = collect($item['deliveries'] ?? [])->sum('quantity');
            return $delivered >= $need;
        });

        if ($allSubmitted) {
            $po->status = 'ready_for_inspection';
        }

        $po->save();

        $itemLabel = $items[$index]['description'] ?? ('Item #' . ($index + 1));

        $message = $isComplete
            ? "\"{$itemLabel}\" fully delivered ({$newDeliveredTotal}/{$needed} units). Sent to Inspector queue."
            : "\"{$itemLabel}\" partial delivery recorded ({$newDeliveredTotal}/{$needed} units). " . ($needed - $newDeliveredTotal) . " unit(s) still pending.";

        return redirect()->route('supply.UploadActualItem')->with('success', $message);
    }

    /**
     * Warehouse Management — "new arrivals" are items the inspector has
     * approved but that haven't been placed into a storage location yet.
     * "Stocks by Storage Location" is built the same way, from items that
     * HAVE been placed (warehouse_status === 'placed'), grouped by the
     * storage_location that was actually chosen in assignToWarehouse().
     * No hardcoded/sample stock — everything here reflects real item data.
     */
    public function WHIndex()
    {
        $purchaseOrders = PurchaseOrder::whereNotNull('items')->get();

        $newArrivals = [];
        $stockByLocation = []; // ['Main Warehouse' => ['items' => ['Cement' => 150, ...], 'last_updated' => '...']]

        foreach ($purchaseOrders as $po) {
            $items = is_array($po->items) ? $po->items : json_decode($po->items ?? '[]', true);

            foreach ($items as $index => $item) {
                // approved_quantity is the field the Inspector actually
                // writes — it can be a PARTIAL amount (e.g. 6 of 10 units)
                // whenever a delivered batch has been approved, even
                // before the item's full requested quantity has arrived.
                // Anything approved and not yet placed belongs here,
                // regardless of whether the item as a whole is "done".
                $approvedQty = (float) ($item['approved_quantity'] ?? 0);

                // placements[] is the running list of every storage assignment made
                // for this item, however many times it's been split across locations.
                // Older records only ever had a single storage_location/stored_quantity
                // pair (from before partial placement was supported) — treat that as
                // one legacy placement so existing data keeps working.
                $placements = $this->normalizePlacements($item);

                // Use the cumulative placed_total (never reduced by later distribution)
                // so an item that was placed and THEN distributed out doesn't reappear
                // here as if it still needs placing. Legacy items saved before this
                // field existed fall back to summing placements[] (current stock) as
                // a best-effort approximation.
                $placedQty = isset($item['placed_total'])
                    ? (float) $item['placed_total']
                    : collect($placements)->sum(fn($p) => (float) ($p['qty'] ?? 0));
                $remainingQty = max(0, $approvedQty - $placedQty);

                // Still shows in New Arrivals as long as there's unplaced quantity left —
                // this is what makes partial placements (e.g. 10 of 45 pcs) keep the
                // remaining 35 pcs visible instead of disappearing the moment ANY
                // quantity gets assigned to a location.
                if ($remainingQty > 0) {
                    $inspector = \App\Models\User::find($item['inspected_by'] ?? null);

                    $newArrivals[] = [
                        // Composite id so the frontend can send back exactly which
                        // PO + item this refers to when assigning a storage location.
                        'id'          => $po->id . '_' . $index,
                        'po_id'       => $po->id,
                        'item_index'  => $index,
                        'name'        => $item['description'] ?? ('Item #' . ($index + 1)),
                        'supplier'    => $po->supplier_name ?? $po->supplier ?? 'No supplier on record',
                        'qty'         => $remainingQty,
                        'inspector'   => $inspector->name ?? 'Inspector',
                        'verified_at' => $item['inspected_at'] ?? null,
                    ];
                }

                // Stocks by Storage Location: sum every placement (there can be more
                // than one per item now, across different locations/times).
                foreach ($placements as $p) {
                    $loc = $p['location'] ?? null;
                    if (!$loc) continue;

                    $name = $item['description'] ?? ('Item #' . ($index + 1));
                    $qty  = (int) ($p['qty'] ?? 0);

                    if (!isset($stockByLocation[$loc])) {
                        $stockByLocation[$loc] = ['items' => [], 'last_updated' => null];
                    }

                    if (!isset($stockByLocation[$loc]['items'][$name])) {
                        $stockByLocation[$loc]['items'][$name] = 0;
                    }
                    $stockByLocation[$loc]['items'][$name] += $qty;

                    $storedAt = $p['at'] ?? null;
                    if ($storedAt && (
                        !$stockByLocation[$loc]['last_updated'] ||
                        $storedAt > $stockByLocation[$loc]['last_updated']
                    )) {
                        $stockByLocation[$loc]['last_updated'] = $storedAt;
                    }
                }
            }
        }

        // Storage dropdown is seeded from locations that have actually been used —
        // no fixed/hardcoded list. New locations can still be added via the modal.
        $availableStorages = collect(array_keys($stockByLocation))
            ->values()
            ->map(fn($name, $i) => ['id' => $i + 1, 'name' => $name])
            ->toArray();

        return view('supply.Warehouse.WHIndex', compact('newArrivals', 'stockByLocation', 'availableStorages'));
    }

    /**
     * Assign one or more inspector-approved items to a storage location,
     * removing them from the "new arrivals" queue for good.
     */
    public function assignToWarehouse(Request $request)
    {
        $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.po_id'       => 'required|exists:purchase_orders,id',
            'items.*.item_index'  => 'required|integer|min:0',
            'items.*.quantity'    => 'required|integer|min:1',
            'storage_location'    => 'required|string|max:255',
        ]);

        foreach ($request->input('items') as $entry) {
            $po = PurchaseOrder::find($entry['po_id']);
            if (!$po) continue;

            $items = is_array($po->items) ? $po->items : json_decode($po->items ?? '[]', true);
            $idx = (int) $entry['item_index'];

            if (!isset($items[$idx])) continue;

            // Must match the same field WHIndex() uses to build the "New Arrivals"
            // queue (approved_quantity) — otherwise placement math drifts from what's
            // actually shown as "approved" on the card.
            $approvedQty = (int) ($items[$idx]['approved_quantity'] ?? 0);

            // Carry forward every placement made so far for this item (supports
            // splitting one approved item across multiple storage locations/times).
            $placements = $this->normalizePlacements($items[$idx]);

            // placed_total is CUMULATIVE and never goes back down — unlike
            // placements[]/qty, which represents CURRENT warehouse stock and
            // gets reduced by DistributionStore() whenever this item is later
            // released to a project. Using placements[] alone here would make
            // an already-distributed item look like it still needs placing and
            // pop back into the New Arrivals queue. Legacy items saved before
            // this field existed fall back to summing placements[] once
            // (best-effort — some of that stock may already be gone by now).
            $alreadyPlaced = isset($items[$idx]['placed_total'])
                ? (int) $items[$idx]['placed_total']
                : collect($placements)->sum(fn($p) => (int) ($p['qty'] ?? 0));

            $remaining = max(0, $approvedQty - $alreadyPlaced);

            // Never let one placement store more than what's actually left to place,
            // even if the frontend somehow sends a stale/larger number.
            $qtyToPlace = min((int) $entry['quantity'], $remaining);
            if ($qtyToPlace <= 0) continue;

            $placements[] = [
                'location' => $request->input('storage_location'),
                'qty'      => $qtyToPlace,
                'at'       => now()->toDateTimeString(),
                'by'       => Auth::id(),
            ];

            $items[$idx]['placements']   = $placements;
            $items[$idx]['placed_total'] = $alreadyPlaced + $qtyToPlace;

            // Only fully "placed" (and gone from the New Arrivals queue) once every
            // approved unit has actually been assigned to a location.
            if ($items[$idx]['placed_total'] >= $approvedQty) {
                $items[$idx]['warehouse_status'] = 'placed';
            } else {
                unset($items[$idx]['warehouse_status']);
            }

            $po->items = $items;
            $po->save();
        }

        return response()->json([
            'message' => count($request->input('items')) . ' item(s) placed in ' . $request->input('storage_location') . '.',
        ]);
    }

    /**
     * Weekly Audit Trail — real inflow (items inspected & approved into the
     * warehouse) and outflow (items released to projects) events for the
     * CURRENT week (Sunday through Saturday), grouped by day, plus the
     * Saturday end-of-week inventory summary. Delegates to the shared
     * InventoryReporting trait so Supply, Admin, and Inspector always
     * compute this identically.
     */
    public function WEEKIndex(Request $request)
    {
        return view('supply.WeekAudit.WEEKIndex', $this->computeWeeklyAudit($request->query('week')));
    }

    /**
     * Show the Projects listing — real Project records, each with
     * computed progress/value based on their linked distributions.
     */
    public function Projectindex()
    {
        $projects = Project::with('distributions.items')->orderBy('created_at', 'desc')->get();
        return view('supply.Project.ProjectIndex', compact('projects'));
    }

    /**
     * Create a Project. Used by BOTH the "Add Project" modal on the
     * Projects page (normal form POST -> redirect back) AND the inline
     * "Add Project" modal on the Distribution page (AJAX -> JSON).
     */
    public function ProjectStore(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'budget'   => 'nullable|numeric|min:0',
        ]);

        $project = Project::create([
            'name'       => $request->name,
            'location'   => $request->location,
            'budget'     => $request->budget,
            'status'     => 'active',
            'created_by' => Auth::id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'id'   => $project->id,
                'name' => $project->name,
            ]);
        }

        return back()->with('success', 'Project created.');
    }

    /**
     * Reports — Generate Report. When the user submits a start_date/end_date
     * (validated client-side to be exactly 7 days, e.g. Monday-Sunday),
     * compute REAL items received (inspected & approved within that range)
     * and items distributed (released to projects within that range), the
     * same way the Weekly Audit page does. Total Items in Inventory is
     * always the real, current combined warehouse stock — it isn't range-
     * scoped, since it's a snapshot of "right now", not a historical count.
     *
     * If no valid custom range is submitted, the page defaults to the
     * current week so Items Received / Items Distributed always show real
     * numbers instead of "—" placeholders on first load.
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

        return view('supply.Report.ReportIndex', compact(
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
     * Remove the specified resource from storage.
     */
    public function PrStore(Request $request)
    {
        // One PR can now have MANY POs. The form sends everything under pos[...]:
        //   pos[uid][po_number|po_date|supplier|total_cost|signed_po]
        //   pos[uid][items][k][description|quantity|unit_cost|amount]
        $request->validate([
            'management_id'             => 'required',
            'pos'                       => 'required|array|min:1',
            'pos.*.po_number'           => 'required|string|distinct|unique:purchase_orders,po_number',
            'pos.*.po_date'             => 'required|date',
            'pos.*.supplier'            => 'required|string|max:255',
            'pos.*.total_cost'          => 'required',
            'pos.*.signed_po'           => 'required|mimes:pdf,jpg,png,jpeg,xlsx,xls,csv|max:5000',
            'pos.*.items'               => 'required|array|min:1',
            'pos.*.items.*.description' => 'required|string',
            'pos.*.items.*.quantity'    => 'required|numeric|min:1',
            'pos.*.items.*.unit_cost'   => 'required|numeric|min:0',
            'pos.*.items.*.amount'      => 'required',
        ], [], [
            'pos.*.po_number'           => 'P.O. number',
            'pos.*.po_date'             => 'P.O. date',
            'pos.*.supplier'            => 'supplier',
            'pos.*.total_cost'          => 'PO total',
            'pos.*.signed_po'           => 'signed PO file',
            'pos.*.items'               => 'items',
            'pos.*.items.*.description' => 'item description',
            'pos.*.items.*.quantity'    => 'item quantity',
            'pos.*.items.*.unit_cost'   => 'item unit cost',
            'pos.*.items.*.amount'      => 'item amount',
        ]);

        $created = 0;

        // All-or-nothing: if any PO fails to save, none of them are kept.
        DB::transaction(function () use ($request, &$created) {
            foreach ($request->input('pos') as $key => $po) {

                // Clean the money values (remove commas)
                $cleanTotal = str_replace(',', '', $po['total_cost']);

                // Build the JSON items array for THIS PO (stock # restarts at 1 per PO)
                $itemsArray = collect($po['items'])->values()->map(function ($item, $i) {
                    return [
                        'stock_no'    => $i + 1,
                        'description' => $item['description'],
                        'quantity'    => $item['quantity'] ?? 0,
                        'unit_cost'   => str_replace(',', '', $item['unit_cost'] ?? 0),
                        'amount'      => str_replace(',', '', $item['amount'] ?? 0),
                    ];
                })->toArray();

                $file = $request->file("pos.$key.signed_po");

                PurchaseOrder::create([
                    'management_id' => $request->management_id,
                    'requested_by'  => auth()->id(),
                    'po_number'     => $po['po_number'],
                    'po_date'       => $po['po_date'],
                    'supplier'      => $po['supplier'],
                    'stock_no'      => $itemsArray[0]['stock_no'] ?? null,
                    'description'   => $itemsArray[0]['description'] ?? null,
                    'quantity'      => $itemsArray[0]['quantity'] ?? 0,
                    'unit_cost'     => $itemsArray[0]['unit_cost'] ?? 0,
                    'total_cost'    => $cleanTotal,
                    'items'         => $itemsArray,
                    'status'        => 'pending_inspection',
                    'po_attachment' => $file ? $file->store('po_attachments', 'public') : null,
                ]);

                $created++;
            }
        });

        $message = $created === 1
            ? 'PO Created! Please upload the actual delivery photos.'
            : "{$created} POs Created! Please upload the actual delivery photos.";

        return redirect()->route('supply.UploadActualItem')->with('success', $message);
    }

    /**
     * Distribution & Outflow — shows the real, combined warehouse stock
     * (summed across all storage locations) and the real Projects list.
     */
    public function DistributionIndex()
    {
        $availableStock = $this->buildAvailableStock();
        $projects = Project::orderBy('name')->get(['id', 'name', 'location', 'status']);

        return view('supply.Distribution.distribution', compact('availableStock', 'projects'));
    }

    /**
     * Confirm Outflow & Deduct — the user now picks WHICH warehouse/location
     * to pull each item from (not just a total). We only deduct from sources
     * matching that location (FIFO within that location). Server-side clamp:
     * a requested quantity can never exceed what's actually available at
     * that specific location, no matter what the frontend sends.
     *
     * Returns JSON (item / location / quantity released) so the frontend
     * can print a receipt immediately after a successful outflow.
     */
    public function DistributionStore(Request $request)
    {
        $request->validate([
            'project_id'          => 'required|exists:projects,id',
            'items'               => 'required|array|min:1',
            'items.*.name'        => 'required|string',
            'items.*.location'    => 'required|string',
            'items.*.quantity'    => 'required|integer|min:1',
            'notes'               => 'nullable|string',
        ]);

        $availableStock = collect($this->buildAvailableStock())->keyBy(fn($g) => strtolower($g['name']));

        $distribution = Distribution::create([
            'project_id'  => $request->project_id,
            'released_by' => Auth::id(),
            'notes'       => $request->notes,
        ]);

        $releasedSummary = [];

        foreach ($request->input('items') as $requested) {
            $key      = strtolower(trim($requested['name']));
            $location = trim($requested['location']);
            $group    = $availableStock->get($key);
            if (!$group) continue;

            // Only pull from placements that are actually sitting in the
            // warehouse/location the user picked for this item.
            $locationSources = collect($group['sources'])
                ->filter(fn($s) => strtolower($s['location'] ?? '') === strtolower($location))
                ->sortBy('at')
                ->values(); // FIFO within this specific location

            $availableAtLocation = $locationSources->sum('qty');
            $remaining = min((int) $requested['quantity'], $availableAtLocation);
            if ($remaining <= 0) continue;

            $takenTotal = 0;

            foreach ($locationSources as $src) {
                if ($remaining <= 0) break;

                $take = min($src['qty'], $remaining);
                if ($take <= 0) continue;

                $po = PurchaseOrder::find($src['po_id']);
                if (!$po) continue;

                $items = is_array($po->items) ? $po->items : json_decode($po->items ?? '[]', true);
                $idx = $src['item_index'];
                if (!isset($items[$idx])) continue;

                $placements = $this->normalizePlacements($items[$idx]);
                if (isset($placements[$src['placement_index']])) {
                    $placements[$src['placement_index']]['qty'] =
                        max(0, (int) $placements[$src['placement_index']]['qty'] - $take);
                }
                $placements = array_values(array_filter($placements, fn($p) => (int) ($p['qty'] ?? 0) > 0));

                $items[$idx]['placements'] = $placements;
                unset($items[$idx]['storage_location'], $items[$idx]['stored_quantity']); // fully migrated na sa placements[]

                $po->items = $items;
                $po->save();

                DistributionItem::create([
                    'distribution_id'   => $distribution->id,
                    'purchase_order_id' => $src['po_id'],
                    'item_index'        => $idx,
                    'item_name'         => $group['name'],
                    'unit_cost'         => $src['unit_cost'],
                    'quantity'          => $take,
                    'storage_location'  => $src['location'],
                ]);

                $remaining  -= $take;
                $takenTotal += $take;
            }

            if ($takenTotal > 0) {
                $releasedSummary[] = [
                    'name'     => $group['name'],
                    'location' => $location,
                    'quantity' => $takenTotal,
                ];
            }
        }

        // The distribution blade calls this via fetch() with Accept: application/json
        // so it can build and print a receipt right away.
        if ($request->wantsJson()) {
            $project = Project::find($request->project_id);

            return response()->json([
                'success'         => true,
                'distribution_id' => $distribution->id,
                'project_name'    => $project->name ?? null,
                'released_at'     => optional($distribution->created_at)->format('M d, Y g:i A'),
                'items'           => $releasedSummary,
            ]);
        }

        return redirect()->route('supply.Distribution')->with('success', 'Items released successfully to the project.');
    }
}