@extends('layouts.admin')

@section('title', 'User List')

@push('head')
<script>
    function Allvalidate() {
        var Validatectrl = '';
        Validatectrl += validateName();
        Validatectrl += validateBizType();
        Validatectrl += validateBizCategory();
        Validatectrl += validateSubBizCategory();
        Validatectrl += validateBusinessName();
        Validatectrl += validateEmail();
        Validatectrl += validatePhone();
        Validatectrl += validateBirthday();
        Validatectrl += validateCity();
        Validatectrl += validateState();
        Validatectrl += validatePincode();
        Validatectrl += validatePassword();
        Validatectrl += validatePAN();
        Validatectrl += validateAddress();
        Validatectrl += validateURL();
        if (Validatectrl != '') { alert(Validatectrl); return false; }
        return true;
    }
    function validateBizType() {
        var v = document.getElementById('txt_business_type').value;
        var val = /^[a-zA-Z0-9 ]+$/;
        if (v == '') return 'Please Enter Business Type\n';
        return val.test(v) ? '' : 'Business Type accepts only spaces,charcters and number\n';
    }
    function validateBizCategory() {
        var v = document.getElementById('txt_business_category').value;
        var val = /^[a-zA-Z0-9 ]+$/;
        if (v == '') return 'Please Enter Business Category\n';
        return val.test(v) ? '' : 'Business Category accepts only spaces,charcters and number\n';
    }
    function validateSubBizCategory() {
        var v = document.getElementById('txt_business_sub_category').value;
        var val = /^[a-zA-Z0-9 ]+$/;
        if (v == '') return 'Please Enter Sub Business Category\n';
        return val.test(v) ? '' : 'Sub Business Category accepts only spaces,charcters and number\n';
    }
    function validURL(str) {
        var pattern = new RegExp('^(https?:\\/\\/)?' +
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' +
            '((\\d{1,3}\\.){3}\\d{1,3}))' +
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' +
            '(\\?[;&a-z\\d%_.~+=-]*)?' +
            '(\\#[-a-z\\d_]*)?$', 'i');
        return !!pattern.test(str);
    }
    function validateURL() {
        var v = document.getElementById('txt_webappURL').value;
        if (v == '') return 'Please Enter Website URL\n';
        return validURL(v) ? '' : 'Please Enter Vaild Website URL\n';
    }
    function validateEmail() {
        var v = document.getElementById('txt_Email').value;
        var val = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (v == '') return 'Please Enter Email Id\n';
        return val.test(v) ? '' : 'Email format should be: xyz@abc.com\n';
    }
    function validatePhone() {
        var v = document.getElementById('txt_mobile').value;
        var val = /^[0-9]+$/;
        if (v == '') return 'Please Enter PhoneNo\n';
        return val.test(v) ? '' : 'PhoneNo should be only in digits\n';
    }
    function validateBusinessName() {
        var v = document.getElementById('txt_BusinessName').value;
        var val = /^[a-zA-Z0-9 ]+$/;
        if (v == '') return 'Please Enter Business Name\n';
        return val.test(v) ? '' : 'Business Name accepts only spaces,charcters and number\n';
    }
    function validateName() {
        var v = document.getElementById('txt_userName').value;
        var val = /^[a-zA-Z0-9 ]+$/;
        if (v == '') return 'Please Enter  Name\n';
        return val.test(v) ? '' : 'Name accepts only spaces,charcters and number\n';
    }
    function isValidDate(dateString) {
        var regEx = /^\d{4}-\d{2}-\d{2}$/;
        return dateString.match(regEx) != null;
    }
    function validateBirthday() {
        var v = document.getElementById('txt_Dob').value;
        if (v == '') return 'Please Enter Dob\n';
        return isValidDate(v) ? '' : 'BirthDate format should be: DD-MM-YYYY\n';
    }
    function validateCity() {
        var v = document.getElementById('txt_City').value;
        if (v == '') return 'Please Enter City Name\n';
        return '';
    }
    function validateState() {
        var v = document.getElementById('txt_State').value;
        var val = /^[a-zA-Z ]+$/;
        if (v == '') return 'Please Enter State Name\n';
        return val.test(v) ? '' : 'State accepts only spaces,charcters and number\n';
    }
    function validatePincode() {
        var v = document.getElementById('txt_Pincode').value;
        var val = /^\d{6}$/;
        if (v == '') return 'Please Enter Pincode\n';
        return val.test(v) ? '' : 'Pin must be 6 digits!\n';
    }
    function validatePassword() {
        var v = document.getElementById('txt_Password').value;
        var val = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (v == '') return 'Please Enter Password\n';
        return val.test(v) ? '' : 'Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters!\n';
    }
    function validatePAN() {
        var v = document.getElementById('txt_PAN').value;
        var val = /[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
        if (v == '') return 'Please Enter PAN Number, only capital letter allowed!\n';
        return val.test(v) ? '' : 'Invalid PAN no, only capital letter allowed!\n';
    }
    function validateAddress() {
        var v = document.getElementById('txt_Address').value;
        if (v == '') return 'Please Enter Address\n';
        return '';
    }
</script>
@endpush

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalPopUp">
            <i class="ri-add-line"></i> Add User
        </a>
    </div>

    {{-- Add User modal - same fields/order as the source's UserList.aspx
         modal form. No server-side validation here, only the client JS
         above (Allvalidate()) - matches the source exactly, which never
         validated this on the server either. --}}
    <div id="modalPopUp" class="modal fade" role="dialog">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.users.store') }}" onsubmit="return Allvalidate();" autocomplete="off">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">User Login</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Name:</label>
                            <div class="col-sm-4"><input type="text" id="txt_userName" name="user_name" class="form-control" autocomplete="off" maxlength="200"></div>
                            <label class="col-sm-2 col-form-label">Business Name:</label>
                            <div class="col-sm-4"><input type="text" id="txt_BusinessName" name="business_name" class="form-control" autocomplete="off" maxlength="200"></div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Business Type:</label>
                            <div class="col-sm-4"><input type="text" id="txt_business_type" name="business_type" class="form-control" autocomplete="off" maxlength="10"></div>
                            <label class="col-sm-2 col-form-label">Business Category:</label>
                            <div class="col-sm-4"><input type="text" id="txt_business_category" name="business_category" class="form-control" autocomplete="off" maxlength="2000"></div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Business Sub Category:</label>
                            <div class="col-sm-4"><input type="text" id="txt_business_sub_category" name="business_sub_category" class="form-control" autocomplete="off" maxlength="2000"></div>
                            <label class="col-sm-2 col-form-label">WebSite URL:</label>
                            <div class="col-sm-4"><input type="text" id="txt_webappURL" name="webapp_url" class="form-control" autocomplete="off" maxlength="2000"></div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Date Of Birth:</label>
                            <div class="col-sm-4"><input type="date" id="txt_Dob" name="dob" class="form-control" autocomplete="off"></div>
                            <label class="col-sm-2 col-form-label">PAN:</label>
                            <div class="col-sm-4"><input type="password" id="txt_PAN" name="pan" class="form-control" autocomplete="off" maxlength="10"></div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">State:</label>
                            <div class="col-sm-4"><input type="text" id="txt_State" name="state" class="form-control" autocomplete="off" maxlength="100"></div>
                            <label class="col-sm-2 col-form-label">City:</label>
                            <div class="col-sm-4"><input type="text" id="txt_City" name="city" class="form-control" autocomplete="off" maxlength="100"></div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Mobile:</label>
                            <div class="col-sm-4"><input type="text" id="txt_mobile" name="mobile" class="form-control" autocomplete="off" maxlength="15"></div>
                            <label class="col-sm-2 col-form-label">Email:</label>
                            <div class="col-sm-4"><input type="text" id="txt_Email" name="email" class="form-control" autocomplete="off" maxlength="100"></div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Pincode:</label>
                            <div class="col-sm-4"><input type="text" id="txt_Pincode" name="pincode" class="form-control" autocomplete="off" maxlength="6"></div>
                            <label class="col-sm-2 col-form-label">Password:</label>
                            <div class="col-sm-4"><input type="password" id="txt_Password" name="password" class="form-control" autocomplete="off" maxlength="20"></div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Gender:</label>
                            <div class="col-sm-4">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" value="M" checked> <label class="form-check-label">Male</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" value="F"> <label class="form-check-label">Female</label>
                                </div>
                            </div>
                            <label class="col-sm-2 col-form-label">Status:</label>
                            <div class="col-sm-4">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" value="1" checked> <label class="form-check-label">Active</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" value="0"> <label class="form-check-label">InActive</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Address:</label>
                            <div class="col-sm-4"><textarea id="txt_Address" name="address" class="form-control" autocomplete="off" maxlength="200"></textarea></div>
                            <label class="col-sm-2 col-form-label">SID:</label>
                            <div class="col-sm-4"><input type="text" id="txt_SID" name="sid" class="form-control" maxlength="20"></div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Bank Name:</label>
                            <div class="col-sm-4"><input type="text" id="txt_bank_name" name="bank_name" class="form-control" autocomplete="off" maxlength="500"></div>
                            <label class="col-sm-2 col-form-label">Account Holder Name:</label>
                            <div class="col-sm-4"><input type="text" id="txt_account_holder_name" name="account_holder_name" class="form-control" autocomplete="off" maxlength="500"></div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Account Number:</label>
                            <div class="col-sm-4"><input type="text" id="txt_account_no" name="account_no" class="form-control" autocomplete="off" maxlength="50"></div>
                            <label class="col-sm-2 col-form-label">IFSC Code:</label>
                            <div class="col-sm-4"><input type="text" id="txt_ifsc" name="ifsc" class="form-control" autocomplete="off" maxlength="15"></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6 form-check">
                                <input type="checkbox" class="form-check-input" id="ck_payout_flag_a" name="payout_flag_a" value="1" checked>
                                <label class="form-check-label">Payout Activated</label>
                            </div>
                            <div class="col-sm-6 form-check">
                                {{-- Rendered for visual fidelity only - the source never
                                     reads this checkbox's value anywhere in btnSubmit_Click. --}}
                                <input type="checkbox" class="form-check-input" id="ck_rl_time_st_on_a" checked disabled>
                                <label class="form-check-label">Realtime Settlement ON</label>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-1 col-form-label">PayIN Channel:</label>
                            <div class="col-sm-2">
                                <select id="pay_in_channel" name="pay_in_channel" class="form-select">
                                    <option value="Coinnected">NGM</option>
                                    <option value="Paytara">PT</option>
                                </select>
                            </div>
                            <label class="col-sm-1 col-form-label">PayOUT Channel:</label>
                            <div class="col-sm-2">
                                <select id="pay_out_channel" name="pay_out_channel" class="form-select">
                                    <option value="2">RazorPay</option>
                                    <option value="3">SafexPay</option>
                                    <option value="12">MizorPay</option>
                                    <option value="13">ICASH</option>
                                </select>
                            </div>
                            <label class="col-sm-1 col-form-label">Payin Tax:</label>
                            <div class="col-sm-2"><input type="text" id="txt_payin_tax" name="payin_tax" class="form-control"></div>
                            <label class="col-sm-1 col-form-label">Payout Tax:</label>
                            <div class="col-sm-2"><input type="text" id="txt_payout_tax" name="payout_tax" class="form-control"></div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-1 col-form-label">Attached Agent:</label>
                            <div class="col-sm-5">
                                <select id="dp_agent" name="agent_id" class="form-select">
                                    @foreach ($agents as $agent)
                                        <option value="{{ $agent->ID }}">{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label class="col-sm-1 col-form-label">Jio Working Key:</label>
                            <div class="col-sm-5"><input type="text" name="working_key" class="form-control" maxlength="200"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card np-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th>#</th>
                            <th>Status</th>
                            <th>PayOUT Status</th>
                            <th>Real Time Settlement On</th>
                            <th>User Id</th>
                            <th>Name</th>
                            <th>Business Name</th>
                            <th>Payin Channel</th>
                            <th>Payout Channel</th>
                            <th>Agent</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $i => $user)
                            <tr>
                                <td>
                                    <img alt="expand" style="cursor:pointer" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24'%3E%3Cpath d='M12 5v14M5 12h14' stroke='%23405189' stroke-width='2' fill='none'/%3E%3C/svg%3E"
                                         onclick="document.getElementById('childRow{{ $user->UserId }}').style.display='table-row'; this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                    <img alt="collapse" style="cursor:pointer;display:none" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24'%3E%3Cpath d='M5 12h14' stroke='%23c0392b' stroke-width='2' fill='none'/%3E%3C/svg%3E"
                                         onclick="document.getElementById('childRow{{ $user->UserId }}').style.display='none'; this.style.display='none'; this.previousElementSibling.style.display='inline';">
                                </td>
                                <td>{{ $i + 1 }}</td>
                                <td class="{{ $user->StatusLabel === 'Active' ? 'text-success' : 'text-danger' }}">{{ $user->StatusLabel }}</td>
                                <td class="{{ $user->PayoutFlagLabel === 'Active' ? 'text-success' : 'text-danger' }}">{{ $user->PayoutFlagLabel }}</td>
                                <td class="{{ $user->RTSettlementLabel === 'On' ? 'text-success' : 'text-danger' }}">{{ $user->RTSettlementLabel }}</td>
                                <td>{{ $user->UserId }}</td>
                                <td>{{ $user->Name }}</td>
                                <td>{{ $user->BusinessName }}</td>
                                <td>{{ $user->PayinChnlLabel }}</td>
                                <td>{{ $user->PayoutChnlLabel }}</td>
                                <td>{{ $user->AName }}</td>
                                <td><a class="btn btn-sm btn-primary" href="{{ route('admin.users.edit', $user->UserId) }}">Edit</a></td>
                                <td><a class="btn btn-sm btn-primary" href="{{ route('admin.users.edit-amount', ['userId' => $user->UserId, 'userName' => $user->BusinessName, 'trType' => 1]) }}">Update PayOut Amounts</a></td>
                                <td><a class="btn btn-sm btn-primary" href="{{ route('admin.users.edit-amount', ['userId' => $user->UserId, 'userName' => $user->BusinessName, 'trType' => 2]) }}">Update PayIN Amount</a></td>
                                <td><a class="btn btn-sm btn-primary" href="{{ route('admin.users.edit-amount', ['userId' => $user->UserId, 'userName' => $user->BusinessName, 'trType' => 3]) }}">Update hold Amount</a></td>
                                <td>
                                    <form method="POST" action="{{ route('admin.users.settlement', $user->UserId) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Settlement</button>
                                    </form>
                                </td>
                            </tr>
                            <tr id="childRow{{ $user->UserId }}" style="display:none">
                                <td colspan="16">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0 ChildGrid">
                                            <thead>
                                                <tr>
                                                    <th>Business Type</th><th>Business Category</th><th>Business Sub Category</th>
                                                    <th>Mobile</th><th>EmailId</th><th>SID</th>
                                                    <th>Payout Amount</th><th>PayIN Amount</th><th>Website URL</th>
                                                    <th>Bank Name</th><th>Account Holder Name</th><th>Account Number</th><th>IFSC Code</th>
                                                    <th>PayIn Callback URL</th><th>PayOUT Callback URL</th><th>Hold Amount</th>
                                                    <th>PayIn Tax</th><th>PayOut Tax</th><th>Client ID</th><th>Client Secret</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($user->childRows as $c)
                                                    <tr>
                                                        <td>{{ $c->Business_Type }}</td><td>{{ $c->Business_Category }}</td><td>{{ $c->Business_Sub_Category }}</td>
                                                        <td>{{ $c->Mobile }}</td><td>{{ $c->EmailId }}</td><td>{{ $c->SID }}</td>
                                                        <td>{{ $c->Available_Amount }}</td><td>{{ $c->Collection_Amount }}</td><td>{{ $c->WebAppURL }}</td>
                                                        <td>{{ $c->BankName }}</td><td>{{ $c->AccountHolderName }}</td><td>{{ $c->Account }}</td><td>{{ $c->ifsc }}</td>
                                                        <td>{{ $c->Callback_URL }}</td><td>{{ $c->Payout_Url }}</td><td>{{ $c->Hold_Amt }}</td>
                                                        <td>{{ $c->Tax }}</td><td>{{ $c->Pay_Tax }}</td><td>{{ $c->ClientID }}</td><td>{{ $c->ClientSecret }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="20" class="text-center">No data</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="16" class="text-center">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
