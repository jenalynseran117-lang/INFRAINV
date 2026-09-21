<?php

namespace App\Http\Controllers\Concerns;

use App\Models\PurchaseOrder;
use App\Models\Distribution;
use App\Models\DistributionItem;
use App\Models\User;
use Carbon\Carbon;

/**
 * Shared inventory/report/weekly-audit computations.
 *
 * This is the SAME logic SupplyController uses for Report, Weekly Audit,
 * and warehouse stock totals — extracted here so Admin Aide and Inspector
 * pages can show exactly what Supply Office sees, without re-typing (and
 * risking drift from) the same formulas in three different controllers.
 */
trait InventoryReporting
{
  /**
   * Normalize an item's placements[] array, converting old
   * single storage_location/stored_quantity records into the same
   * shape as new multi-placement records.
   */
  protected function normalizePlacements(array $item): array
  {
    $placements = $item['placements'] ?? [];
    if (empty($placements) && !empty($item['storage_location'])) {
      $placements = [[
        'location' => $item['storage_location'],
        'qty'      => (int) ($item['stored_quantity'] ?? 0),
        'at'       => $item['stored_at'] ?? null,
        'by'       => $item['stored_by'] ?? null,
      ]];
    }
    return $placements;
  }

  /**
   * Flat, name-grouped view of everything currently sitting in the
   * warehouse (across ALL storage locations).
   */
  protected function buildAvailableStock(): array
  {
    $purchaseOrders = PurchaseOrder::whereNotNull('items')->get();
    $grouped = [];

    foreach ($purchaseOrders as $po) {
      $items = is_array($po->items) ? $po->items : json_decode($po->items ?? '[]', true);

      foreach ($items as $index => $item) {
        $placements = $this->normalizePlacements($item);
        if (empty($placements)) continue;

        $name = trim($item['description'] ?? ('Item #' . ($index + 1)));
        $key  = strtolower($name);
        $unitCost = (float) ($item['unit_cost'] ?? 0);

        foreach ($placements as $pIndex => $p) {
          $qty = (int) ($p['qty'] ?? 0);
          if ($qty <= 0) continue;

          if (!isset($grouped[$key])) {
            $grouped[$key] = ['name' => $name, 'totalQty' => 0, 'sources' => []];
          }

          $grouped[$key]['totalQty'] += $qty;
          $grouped[$key]['sources'][] = [
            'po_id'           => $po->id,
            'item_index'      => $index,
            'placement_index' => $pIndex,
            'location'        => $p['location'] ?? 'Unknown',
            'qty'             => $qty,
            'unit_cost'       => $unitCost,
            'at'              => $p['at'] ?? null,
          ];
        }
      }
    }

    return collect($grouped)->values()->map(function ($g, $i) {
      $g['id'] = $i + 1;
      return $g;
    })->toArray();
  }

  /**
   * Real, current combined warehouse stock (a "right now" snapshot,
   * not scoped to any date range).
   */
  protected function computeTotalInventory(): int
  {
    return collect($this->buildAvailableStock())->sum('totalQty');
  }

  /**
   * Current warehouse stock broken down by item name — powers the
   * "Total Items in Inventory" breakdown on the Reports page, same
   * underlying data as buildAvailableStock() (a "right now" snapshot,
   * not scoped to any date range), just trimmed down to name + quantity
   * and sorted by quantity (largest first).
   */
  protected function buildInventoryBreakdown(): array
  {
    return collect($this->buildAvailableStock())
      ->map(fn($g) => ['name' => $g['name'], 'quantity' => $g['totalQty']])
      ->sortByDesc('quantity')
      ->values()
      ->toArray();
  }

  /**
   * Per-project materials summary keyed by project id — item name,
   * total quantity, per-warehouse location breakdown, and real total
   * value (quantity * unit_cost per distributed line, summed). Powers
   * the "View Materials List" modal on every role's Projects page.
   */
  protected function buildProjectMaterials($projects): array
  {
    $projectMaterials = [];

    foreach ($projects as $p) {
      $items = $p->distributions
        ->flatMap(fn($d) => $d->items)
        ->groupBy('item_name')
        ->map(function ($group) {
          $totalQty   = $group->sum('quantity');
          $totalValue = $group->sum(fn($i) => $i->quantity * $i->unit_cost);
          $avgCost    = $totalQty > 0 ? $totalValue / $totalQty : 0;

          $locations = $group
            ->groupBy('storage_location')
            ->map(fn($g2, $loc) => [
              'location' => $loc ?: 'Unknown',
              'quantity' => $g2->sum('quantity'),
            ])
            ->values();

          return [
            'name'        => $group->first()->item_name,
            'quantity'    => $totalQty,
            'unit_cost'   => (float) $avgCost,
            'total_value' => (float) $totalValue,
            'locations'   => $locations,
          ];
        })
        ->values();

      $projectMaterials[$p->id] = [
        'name'  => $p->name,
        'items' => $items,
      ];
    }

    return $projectMaterials;
  }

  /**
   * A report date range is only valid if both dates are present, parse
   * cleanly, and the end date isn't after today — we can't report on
   * inflow/outflow for days that haven't happened yet. Shared by every
   * role's Reports page so the rule can't be bypassed by editing the URL.
   */
  protected function isReportRangeValid(?string $startDate, ?string $endDate): bool
  {
    if (!$startDate || !$endDate) return false;

    try {
      $start = Carbon::parse($startDate)->startOfDay();
      $end   = Carbon::parse($endDate)->endOfDay();
    } catch (\Throwable $e) {
      return false;
    }

    return $end->lte(now()) && $start->lte(now());
  }

  /**
   * Resolve the date range the Reports page should use.
   *
   * If the request supplied a valid custom range (see isReportRangeValid),
   * that range wins. Otherwise we fall back to the current week (Sunday
   * through Saturday, same window as the Weekly Audit) so Items Received /
   * Items Distributed always show real numbers instead of "—" placeholders
   * on first page load.
   *
   * Returns [Carbon $start, Carbon $end, bool $isCustomRange].
   */
  protected function resolveReportRange(?string $startDate, ?string $endDate): array
  {
    if ($this->isReportRangeValid($startDate, $endDate)) {
      return [
        Carbon::parse($startDate)->startOfDay(),
        Carbon::parse($endDate)->endOfDay(),
        true,
      ];
    }

    $start = now()->startOfWeek(Carbon::SUNDAY)->startOfDay();
    $end   = $start->copy()->addDays(6)->endOfDay();

    return [$start, $end, false];
  }

  /**
   * Items received (inspected & approved) and items distributed
   * (released to projects), both scoped to a date range.
   * Returns [itemsReceived, itemsDistributed].
   */
  protected function computeItemsReceivedAndDistributed(Carbon $start, Carbon $end): array
  {
    $itemsReceived  = 0;
    $purchaseOrders = PurchaseOrder::whereNotNull('items')->get();

    foreach ($purchaseOrders as $po) {
      $items = is_array($po->items) ? $po->items : json_decode($po->items ?? '[]', true);

      foreach ($items as $item) {
        $approvedAt = $item['inspected_at'] ?? null;
        if (!$approvedAt || ($item['inspection_status'] ?? null) !== 'approved') continue;

        $approvedAt = Carbon::parse($approvedAt);
        if ($approvedAt->between($start, $end)) {
          $itemsReceived += (int) ($item['inspected_quantity'] ?? $item['quantity'] ?? 0);
        }
      }
    }

    $itemsDistributed = DistributionItem::whereHas('distribution', function ($q) use ($start, $end) {
      $q->whereBetween('created_at', [$start, $end]);
    })->sum('quantity');

    return [$itemsReceived, $itemsDistributed];
  }

  /**
   * Same totals as computeItemsReceivedAndDistributed(), but broken down
   * by item name — powers the Reports page, which shows specifically
   * WHICH items were received and distributed in a date range, not just
   * how many items moved in total.
   *
   * Returns:
   *   [
   *     itemsReceived,     // int, same as computeItemsReceivedAndDistributed()
   *     itemsDistributed,  // int
   *     receivedBreakdown,    // [['name' => ..., 'quantity' => ...], ...] sorted by quantity desc
   *     distributedBreakdown, // [['name' => ..., 'quantity' => ...], ...] sorted by quantity desc
   *   ]
   */
  protected function computeItemsReceivedAndDistributedDetailed(Carbon $start, Carbon $end): array
  {
    $receivedByName = [];
    $purchaseOrders = PurchaseOrder::whereNotNull('items')->get();

    foreach ($purchaseOrders as $po) {
      $items = is_array($po->items) ? $po->items : json_decode($po->items ?? '[]', true);

      foreach ($items as $item) {
        $approvedAt = $item['inspected_at'] ?? null;
        if (!$approvedAt || ($item['inspection_status'] ?? null) !== 'approved') continue;

        $approvedAt = Carbon::parse($approvedAt);
        if (!$approvedAt->between($start, $end)) continue;

        $qty  = (int) ($item['inspected_quantity'] ?? $item['quantity'] ?? 0);
        $name = trim($item['description'] ?? 'Unnamed item');

        $receivedByName[$name] = ($receivedByName[$name] ?? 0) + $qty;
      }
    }

    $distributedItems = DistributionItem::whereHas('distribution', function ($q) use ($start, $end) {
      $q->whereBetween('created_at', [$start, $end]);
    })->get();

    $distributedByName = [];
    foreach ($distributedItems as $di) {
      $name = trim($di->item_name ?: 'Unnamed item');
      $distributedByName[$name] = ($distributedByName[$name] ?? 0) + $di->quantity;
    }

    $toSortedList = function (array $byName): array {
      return collect($byName)
        ->map(fn($qty, $name) => ['name' => $name, 'quantity' => $qty])
        ->values()
        ->sortByDesc('quantity')
        ->values()
        ->toArray();
    };

    return [
      array_sum($receivedByName),
      array_sum($distributedByName),
      $toSortedList($receivedByName),
      $toSortedList($distributedByName),
    ];
  }

  /**
   * Weekly Audit Trail — real inflow (items inspected & approved into the
   * warehouse) and outflow (items released to projects) events for a
   * given week, Sunday through Saturday, grouped by day. Once the week
   * reaches Saturday (its last day), an end-of-week inventory summary (totals + per-project
   * breakdown) is also computed.
   *
   * $week is any date string inside the desired week (e.g. '2026-08-02').
   * Defaults to the current week when omitted. Past weeks can be viewed
   * this way, but a future week is never allowed — a request for one
   * silently clamps back to the current week instead.
   *
   * Returns the exact array of view data WEEKIndex-style blades expect:
   * ['weekStart', 'weekEnd', 'days', 'showSummary', 'weekSummary',
   *  'prevWeekStart', 'nextWeekStart', 'isCurrentWeek']
   */
  protected function computeWeeklyAudit(?string $week = null): array
  {
    $currentWeekStart = now()->startOfWeek(Carbon::SUNDAY)->startOfDay();

    $weekStart = $currentWeekStart->copy();
    if ($week) {
      try {
        $requested = Carbon::parse($week)->startOfWeek(Carbon::SUNDAY)->startOfDay();
        if ($requested->lte($currentWeekStart)) {
          $weekStart = $requested;
        }
        // A requested week later than the current one is ignored —
        // $weekStart just stays at the current week (no future weeks).
      } catch (\Throwable $e) {
        // Invalid date string — fall back to the current week.
      }
    }

    $weekEnd = $weekStart->copy()->addDays(6)->endOfDay(); // Sun..Sat window (7 days)

    $days = [];
    for ($i = 0; $i < 7; $i++) {
      $date = $weekStart->copy()->addDays($i);
      $days[$date->toDateString()] = [
        'date'   => $date,
        'label'  => $date->format('l, M j'),
        'events' => [],
      ];
    }

    // --- OUTFLOW: items released to projects this week ---
    $distributions = Distribution::with(['items', 'project'])
      ->whereBetween('created_at', [$weekStart, $weekEnd])
      ->get();

    foreach ($distributions as $dist) {
      $key = $dist->created_at->toDateString();
      if (!isset($days[$key])) continue;

      $itemsLabel = $dist->items
        ->map(fn($i) => "{$i->quantity} {$i->item_name}")
        ->implode(', ');

      $releasedBy = User::find($dist->released_by);

      $days[$key]['events'][] = [
        'type'       => 'outflow',
        'time'       => $dist->created_at,
        'title'      => 'Released ' . ($itemsLabel ?: 'items'),
        'subtitle'   => 'Destination: ' . ($dist->project->name ?? 'Unknown Project'),
        'handled_by' => $releasedBy->name ?? 'Supply Officer',
      ];
    }

    // --- INFLOW: items inspected & approved into the warehouse this week ---
    $purchaseOrders = PurchaseOrder::whereNotNull('items')->get();

    foreach ($purchaseOrders as $po) {
      $items = is_array($po->items) ? $po->items : json_decode($po->items ?? '[]', true);

      foreach ($items as $item) {
        $approvedAt = $item['inspected_at'] ?? null;
        if (!$approvedAt || ($item['inspection_status'] ?? null) !== 'approved') continue;

        $approvedAt = Carbon::parse($approvedAt);
        $key = $approvedAt->toDateString();
        if (!isset($days[$key])) continue;

        $qty       = (int) ($item['inspected_quantity'] ?? $item['quantity'] ?? 0);
        $inspector = User::find($item['inspected_by'] ?? null);

        $days[$key]['events'][] = [
          'type'       => 'inflow',
          'time'       => $approvedAt,
          'title'      => "Received {$qty} " . ($item['description'] ?? 'item(s)'),
          'subtitle'   => 'Source: PO-' . ($po->po_number ?? $po->id),
          'handled_by' => $inspector->name ?? 'Inspector',
        ];
      }
    }

    foreach ($days as $key => $day) {
      usort($day['events'], fn($a, $b) => $a['time'] <=> $b['time']);
      $days[$key]['events'] = $day['events'];
    }

    // --- SATURDAY (last day of the week): end-of-week inventory summary ---
    $showSummary = now()->greaterThanOrEqualTo($weekEnd);
    $weekSummary = null;

    if ($showSummary) {
      [$itemsReceived, $itemsDistributed] = $this->computeItemsReceivedAndDistributed($weekStart, $weekEnd);

      $weekDistributionItems = DistributionItem::whereHas('distribution', function ($q) use ($weekStart, $weekEnd) {
        $q->whereBetween('created_at', [$weekStart, $weekEnd]);
      })->with('distribution.project')->get();

      $byProject = $weekDistributionItems
        ->groupBy(fn($di) => $di->distribution->project->name ?? 'Unknown Project')
        ->map(function ($group) {
          return [
            'quantity' => $group->sum('quantity'),
            'value'    => $group->sum(fn($i) => $i->quantity * $i->unit_cost),
          ];
        });

      $weekSummary = [
        'items_received'    => $itemsReceived,
        'items_distributed' => $itemsDistributed,
        'by_project'        => $byProject,
      ];
    }

    return [
      'weekStart'     => $weekStart,
      'weekEnd'       => $weekEnd,
      'days'          => array_values($days),
      'showSummary'   => $showSummary,
      'weekSummary'   => $weekSummary,
      'prevWeekStart' => $weekStart->copy()->subWeek(),
      'nextWeekStart' => $weekStart->copy()->addWeek()->lte($currentWeekStart)
        ? $weekStart->copy()->addWeek()
        : null,
      'isCurrentWeek' => $weekStart->equalTo($currentWeekStart),
    ];
  }
}