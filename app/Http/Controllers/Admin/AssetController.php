<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\CiRelationship;
use App\Models\ConfigurationItem;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $assets = Asset::with('customer')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('asset_tag', 'like', "%{$request->search}%")
                   ->orWhere('name', 'like', "%{$request->search}%")
                   ->orWhere('serial_number', 'like', "%{$request->search}%");
            }))
            ->latest()->paginate(20);

        return view('admin.assets.index', compact('assets'));
    }

    public function create()
    {
        $customers = User::where('role', 'customer')->where('is_active', true)->limit(200)->get();

        return view('admin.assets.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:120',
            'model' => 'nullable|string|max:120',
            'serial_number' => 'nullable|string|max:160',
            'customer_id' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'assigned_to_user' => 'nullable|exists:users,id',
            'status' => 'required|in:in_stock,deployed,maintenance,retired',
            'purchase_date' => 'nullable|date',
            'warranty_expires' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $asset = Asset::create($data);
        AuditService::log('asset.create', 'itsm', $asset, "Asset {$asset->asset_tag} created");

        return redirect()->route('admin.assets.show', $asset)->with('success', 'Asset recorded.');
    }

    public function show(Asset $asset)
    {
        $asset->load('customer', 'assignedUser', 'configurationItems');

        return view('admin.assets.show', compact('asset'));
    }

    public function update(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:120',
            'model' => 'nullable|string|max:120',
            'serial_number' => 'nullable|string|max:160',
            'customer_id' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'assigned_to_user' => 'nullable|exists:users,id',
            'status' => 'sometimes|in:in_stock,deployed,maintenance,retired',
            'purchase_date' => 'nullable|date',
            'warranty_expires' => 'nullable|date',
            'retirement_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $old = $asset->only(array_keys($data));
        $asset->update($data);
        AuditService::log('asset.update', 'itsm', $asset, "Asset {$asset->asset_tag} updated", $old, $data);

        return redirect()->back()->with('success', 'Asset updated.');
    }

    // ---- Configuration items (lightweight CMDB) ----

    public function ciIndex(Request $request)
    {
        $cis = ConfigurationItem::with('customer', 'asset')
            ->when($request->ci_type, fn ($q) => $q->where('ci_type', $request->ci_type))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('ci_number', 'like', "%{$request->search}%")
                   ->orWhere('name', 'like', "%{$request->search}%")
                   ->orWhere('identifier', 'like', "%{$request->search}%");
            }))
            ->latest()->paginate(20);
        $customers = User::where('role', 'customer')->where('is_active', true)->limit(200)->get();

        return view('admin.assets.ci-index', compact('cis', 'customers'));
    }

    public function ciStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'ci_type' => 'required|in:server,workstation,network,application,cloud,dns,backup,other',
            'customer_id' => 'nullable|exists:users,id',
            'asset_id' => 'nullable|exists:assets,id',
            'identifier' => 'nullable|string|max:255',
            'environment' => 'required|in:production,staging,development,test',
            'status' => 'required|in:active,maintenance,decommissioned',
            'criticality' => 'required|in:low,medium,high,critical',
            'owner_id' => 'nullable|exists:users,id',
        ]);
        $ci = ConfigurationItem::create($data);
        AuditService::log('ci.create', 'itsm', $ci, "CI {$ci->ci_number} created");

        return redirect()->route('admin.ci.show', $ci)->with('success', 'Configuration item recorded.');
    }

    public function ciShow(ConfigurationItem $ci)
    {
        $ci->load('customer', 'asset', 'owner', 'childRelationships.child', 'parentRelationships.parent');
        $candidates = ConfigurationItem::where('id', '!=', $ci->id)->latest()->limit(200)->get(['id', 'ci_number', 'name']);

        return view('admin.assets.ci-show', compact('ci', 'candidates'));
    }

    public function ciRelate(Request $request, ConfigurationItem $ci)
    {
        $data = $request->validate([
            'child_ci_id' => 'required|exists:configuration_items,id',
            'relationship_type' => 'required|in:depends_on,hosts,runs_on,connects_to',
        ]);
        if ((int) $data['child_ci_id'] === (int) $ci->id) {
            return redirect()->back()->withErrors(['child_ci_id' => 'A configuration item cannot relate to itself.'])->withInput();
        }
        CiRelationship::firstOrCreate([
            'parent_ci_id' => $ci->id,
            'child_ci_id' => $data['child_ci_id'],
            'relationship_type' => $data['relationship_type'],
        ]);
        AuditService::log('ci.relate', 'itsm', $ci, "CI {$ci->ci_number} linked to CI #{$data['child_ci_id']}");

        return redirect()->back()->with('success', 'CI relationship recorded.');
    }

    public function ciUnrelate(ConfigurationItem $ci, CiRelationship $relationship)
    {
        abort_unless($relationship->parent_ci_id === $ci->id, 404);
        $relationship->delete();
        AuditService::log('ci.unrelate', 'itsm', $ci, "CI relationship #{$relationship->id} removed");

        return redirect()->back()->with('success', 'Relationship removed.');
    }
}
