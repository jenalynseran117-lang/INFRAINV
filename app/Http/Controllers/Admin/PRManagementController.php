<?php    //PRManagementController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InventoryReporting;
use Illuminate\Http\Request;

use App\Models\Admin\management;
use App\Models\PrManagement;
use Illuminate\Support\Facades\Auth;


class PRManagementController extends Controller
{
    use InventoryReporting;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.PRManagement.Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function IssueIndex()
    {
        return view('admin.IssueMaterial.issueindex');
    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * Weekly Audit Trail — Admin Aide's view. Mirrors Supply's WEEKIndex
     * exactly: same Mon-Fri inflow/outflow events, same Friday
     * end-of-week summary.
     */
    public function WeeklyIndex(Request $request)
    {
        return view('admin.weeklyaudit.weeklyindex', $this->computeWeeklyAudit($request->query('week')));
    }


    /**
     * Display the specified resource.
     */
    public function PRstore(Request $request)
    {
        $request->validate([
            'pr_management' => 'required|mimes:jpg,jpeg,png,pdf,xlsx,xls,csv|max:5120',
            'pr_number' => 'required|regex:/^SO_A_\d{4}_\d{2}_\d{3}$/|unique:pr_management,pr_number',
        ]);

        $file = $request->file('pr_management');
        $path = $file->store('pr_management', 'public');

        $fileType = match ($file->extension()) {
            'jpg', 'jpeg', 'png' => 'image',
            'xlsx', 'xls', 'csv' => 'spreadsheet',
            default => 'document',
        };

        PrManagement::create([
            'user_id' => Auth::id(),
            'pr_number' => $request->pr_number,
            'file' => $path,
            'file_type' => $fileType,
        ]);

        return back()->with('success', 'File uploaded successfully!');
    }

    /**
     * Check whether a PR number already exists (used by the live AJAX check on the form).
     */
    public function checkPrNumber(Request $request)
    {
        $request->validate([
            'pr_number' => 'required|string',
        ]);

        $exists = PrManagement::where('pr_number', $request->pr_number)->exists();

        return response()->json(['exists' => $exists]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}