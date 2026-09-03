@extends('layouts.user')

@section('title', 'Setting')

@section('content')
    <div class="card np-card">
        <div class="card-header p-0">
            <ul class="nav nav-tabs card-header-tabs px-2 pt-2" id="settingTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-business-btn" data-bs-toggle="tab" data-bs-target="#tab-business" type="button" role="tab">IP Business Details</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-whitelist-btn" data-bs-toggle="tab" data-bs-target="#tab-whitelist" type="button" role="tab">IP WhiteList</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-webhook-btn" data-bs-toggle="tab" data-bs-target="#tab-webhook" type="button" role="tab">WebHook</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-apikey-btn" data-bs-toggle="tab" data-bs-target="#tab-apikey" type="button" role="tab">API KEY</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-help-btn" data-bs-toggle="tab" data-bs-target="#tab-help" type="button" role="tab">Help Document</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="settingTabsContent">

                {{-- IP Business Details - read-only, straight off UserMaster / User_Tax_Mst --}}
                <div class="tab-pane fade show active" id="tab-business" role="tabpanel">
                    <div class="card np-card">
                        <div class="card-header">Business Info</div>
                        <table class="table mb-0">
                            <tbody>
                                <tr><th class="text-muted fw-normal" style="width:260px">Authorized Signatory Name:</th><td class="fw-semibold">{{ $business->Name ?? '' }}</td></tr>
                                <tr><th class="text-muted fw-normal">Business Name:</th><td class="fw-semibold">{{ $business->BusinessName ?? '' }}</td></tr>
                                <tr>
                                    <th class="text-muted fw-normal">Business Status:</th>
                                    <td>
                                        @if (($business->Status ?? null) == 1)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr><th class="text-muted fw-normal">Business Type:</th><td class="fw-semibold">{{ $business->Business_Type ?? '' }}</td></tr>
                                <tr><th class="text-muted fw-normal">Business Category:</th><td class="fw-semibold">{{ $business->Business_Category ?? '' }}</td></tr>
                                <tr><th class="text-muted fw-normal">Business Sub Category:</th><td class="fw-semibold">{{ $business->Business_Sub_Category ?? '' }}</td></tr>
                                <tr><th class="text-muted fw-normal">WebApp/Url:</th><td class="fw-semibold">{{ $business->WebAppURL ?? '' }}</td></tr>
                                <tr><th class="text-muted fw-normal">Company PAN Number:</th><td class="fw-semibold">{{ $business->PAN ?? '' }}</td></tr>
                                <tr><th class="text-muted fw-normal">Company GST No:</th><td class="fw-semibold">{{ $business->GSTNo ?? '' }}</td></tr>
                                <tr><th class="text-muted fw-normal">Address:</th><td class="fw-semibold">{{ $business->Address ?? '' }}</td></tr>
                                <tr><th class="text-muted fw-normal">Pincode:</th><td class="fw-semibold">{{ $business->Pincode ?? '' }}</td></tr>
                                <tr><th class="text-muted fw-normal">City:</th><td class="fw-semibold">{{ $business->City ?? '' }}</td></tr>
                                <tr><th class="text-muted fw-normal">State:</th><td class="fw-semibold">{{ $business->State ?? '' }}</td></tr>
                                <tr><td colspan="2" class="fw-semibold pt-3">Transcation Info</td></tr>
                                <tr><th class="text-muted fw-normal">Payin Tax Rate:</th><td class="fw-semibold">{{ $tax->Tax ?? '0.00' }}</td></tr>
                                <tr><th class="text-muted fw-normal">Payout Tax Rate:</th><td class="fw-semibold">{{ $tax->Pay_Tax ?? '0.00' }}</td></tr>
                                <tr><th class="text-muted fw-normal">Payin Callback URL:</th><td class="fw-semibold">{{ $business->Callback_URL ?? '' }}</td></tr>
                                <tr><th class="text-muted fw-normal">Payout Callback URL:</th><td class="fw-semibold">{{ $business->Payout_Url ?? '' }}</td></tr>
                                <tr>
                                    <th class="text-muted fw-normal">Real Time Sattlement ON:</th>
                                    <td>
                                        @if (($business->RTSettlementOn ?? 0) == 1)
                                            <span class="badge bg-success">On</span>
                                        @else
                                            <span class="badge bg-secondary">Off</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr><td colspan="2" class="fw-semibold pt-3">Banking Info</td></tr>
                                <tr><th class="text-muted fw-normal">Account Holder Name:</th><td class="fw-semibold">{{ $business->AccountHolderName ?? '' }}</td></tr>
                                <tr><th class="text-muted fw-normal">Bank Name:</th><td class="fw-semibold">{{ $business->BankName ?? '' }}</td></tr>
                                <tr><th class="text-muted fw-normal">Account No:</th><td class="fw-semibold">{{ $business->Account ?? '' }}</td></tr>
                                <tr><th class="text-muted fw-normal">IFSC:</th><td class="fw-semibold">{{ $business->ifsc ?? '' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- IP WhiteList --}}
                <div class="tab-pane fade" id="tab-whitelist" role="tabpanel">
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addIpModal">Add IP Address</button>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>S No</th><th>Whitelist IP</th><th>Createtion Datetime</th><th></th></tr>
                            </thead>
                            <tbody>
                                @forelse ($whitelist as $i => $ip)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $ip->WhitelistIP }}</td>
                                        <td>{{ $ip->Created_On }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('user.setting.ip-whitelist.delete', $ip->WhitelistIPId) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center">No whitelisted IPs</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- WebHook --}}
                <div class="tab-pane fade" id="tab-webhook" role="tabpanel">
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#editWebhookModal">Edit Webhook</button>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>Tag</th><th>Callback</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Payin</td><td>{{ $webhook->Callback_URL ?? '' }}</td></tr>
                                <tr><td>Payout</td><td>{{ $webhook->Payout_Url ?? '' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- API KEY --}}
                <div class="tab-pane fade" id="tab-apikey" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th>Client ID</th><th></th><th></th></tr></thead>
                            <tbody>
                                <tr>
                                    <td class="align-middle">{{ $apiKey->ClientID ?? '' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('user.setting.api-key.regenerate') }}" onsubmit="return confirm('Regenerating will invalidate the current key immediately. Continue?');">
                                            @csrf
                                            <button type="submit" class="btn btn-primary">Regenerate Key</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('user.setting.api-key.view') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-primary">View Keys</button>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if ($revealedKeys)
                        <div class="alert alert-warning mt-3">
                            <strong>These values are shown once - copy them now.</strong>
                            <table class="table table-sm mb-0 mt-2">
                                <tbody>
                                    @foreach ($revealedKeys as $label => $value)
                                        <tr><th>{{ $label }}</th><td class="text-break">{{ $value }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Help Document --}}
                <div class="tab-pane fade" id="tab-help" role="tabpanel">
                    <div class="card np-card">
                        <div class="card-body">
                            <h5 class="mb-3">Download Integration Document</h5>
                            <button type="button" class="btn text-white" style="background:#17a2b8" disabled title="No integration document has been uploaded yet">
                                Click here Download Integration Document
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Add IP Address modal --}}
    <div class="modal fade" id="addIpModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('user.setting.ip-whitelist.add') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add IP Address</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">IP Address</label>
                        <input type="text" name="whitelist_ip" class="form-control" placeholder="e.g. 103.21.58.10" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Webhook modal --}}
    <div class="modal fade" id="editWebhookModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('user.setting.webhook.update') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Webhook</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Payin Callback URL</label>
                            <input type="text" name="payin_callback" class="form-control" value="{{ $webhook->Callback_URL ?? '' }}" maxlength="1000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payout Callback URL</label>
                            <input type="text" name="payout_callback" class="form-control" value="{{ $webhook->Payout_Url ?? '' }}" maxlength="1000">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
