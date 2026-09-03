@extends('layouts.admin')

@section('title', 'Update User')

{{--
    Port of admin/EditUser.aspx(.cs). See EditUserController's docblock for
    the two source bugs preserved here on purpose (City/Address swapped on
    load, Password field pre-filled from Available_Amount) - both are
    reproduced below via the old('field', $user->Column) bindings, not fixed.
--}}

@push('head')
<script>
    function AllPassword() {
        var Validatectrl = validatePassword();
        if (Validatectrl != '') { alert(Validatectrl); return false; }
        return true;

        function validatePassword() {
            var v = document.getElementById('txt_Password_super').value;
            if (v == '') return 'Please Enter Password\n';
            return '';
        }
    }

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
        // validateGender() intentionally not called here - commented out
        // in the source's own Allvalidate() too.
        Validatectrl += validateCity();
        Validatectrl += validateState();
        Validatectrl += validatePincode();
        // validatePassword() intentionally not called here either - same
        // in the source, since this field is prefilled with Available_Amount,
        // not a real password (see EditUserController docblock).
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
        if (v == '') return 'Please Enter Name\n';
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
    function validateGender() {
        var radio = document.querySelectorAll('#rb_Gender input');
        for (var i = 0; i < radio.length; i++) {
            if (radio[i].checked) return '';
        }
        return 'Please select an Gender \n';
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
    // Dead code in the source (never called - see Allvalidate() above),
    // preserved for fidelity only.
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
    <div class="card np-card">
        <div class="card-body">
            <form id="edit-user-form" method="POST" action="{{ route('admin.users.edit.update', $userId) }}" onsubmit="return Allvalidate();" autocomplete="off">
                @csrf
                <div class="row">
                    <label class="col-sm-2 col-form-label">Name:</label>
                    <div class="col-sm-4"><input type="text" id="txt_userName" name="user_name" value="{{ old('user_name', $user->Name) }}" class="form-control" autocomplete="off" maxlength="200"></div>
                    <label class="col-sm-2 col-form-label">BusinessName:</label>
                    <div class="col-sm-4"><input type="text" id="txt_BusinessName" name="business_name" value="{{ old('business_name', $user->BusinessName) }}" class="form-control" autocomplete="off" maxlength="200"></div>
                </div>
                <div class="row mt-2">
                    <label class="col-sm-2 col-form-label">Business Type:</label>
                    <div class="col-sm-4"><input type="text" id="txt_business_type" name="business_type" value="{{ old('business_type', $user->Business_Type) }}" class="form-control" autocomplete="off" maxlength="10"></div>
                    <label class="col-sm-2 col-form-label">Business Category:</label>
                    <div class="col-sm-4"><input type="text" id="txt_business_category" name="business_category" value="{{ old('business_category', $user->Business_Category) }}" class="form-control" autocomplete="off" maxlength="2000"></div>
                </div>
                <div class="row mt-2">
                    <label class="col-sm-2 col-form-label">Business Sub Category:</label>
                    <div class="col-sm-4"><input type="text" id="txt_business_sub_category" name="business_sub_category" value="{{ old('business_sub_category', $user->Business_Sub_Category) }}" class="form-control" autocomplete="off" maxlength="2000"></div>
                    <label class="col-sm-2 col-form-label">WebSite URL:</label>
                    <div class="col-sm-4"><input type="text" id="txt_webappURL" name="webapp_url" value="{{ old('webapp_url', $user->WebAppURL) }}" class="form-control" autocomplete="off" maxlength="2000"></div>
                </div>
                <div class="row mt-2">
                    <label class="col-sm-2 col-form-label">Date Of Birth:</label>
                    <div class="col-sm-4"><input type="date" id="txt_Dob" name="dob" value="{{ old('dob', $user->Dob) }}" class="form-control" autocomplete="off"></div>
                    <label class="col-sm-2 col-form-label">PAN:</label>
                    <div class="col-sm-4"><input type="text" id="txt_PAN" name="pan" value="{{ old('pan', $user->PAN) }}" class="form-control" autocomplete="off" maxlength="10"></div>
                </div>
                <div class="row mt-2">
                    <label class="col-sm-2 col-form-label">State:</label>
                    <div class="col-sm-4"><input type="text" id="txt_State" name="state" value="{{ old('state', $user->State) }}" class="form-control" autocomplete="off" maxlength="100"></div>
                    <label class="col-sm-2 col-form-label">City:</label>
                    {{-- Source bug, preserved: pre-filled from the Address column, not City. See EditUserController docblock. --}}
                    <div class="col-sm-4"><input type="text" id="txt_City" name="city" value="{{ old('city', $user->Address) }}" class="form-control" autocomplete="off" maxlength="100"></div>
                </div>
                <div class="row mt-2">
                    <label class="col-sm-2 col-form-label">Mobile:</label>
                    <div class="col-sm-4"><input type="text" id="txt_mobile" name="mobile" value="{{ old('mobile', $user->Mobile) }}" class="form-control" autocomplete="off" maxlength="15"></div>
                    <label class="col-sm-2 col-form-label">Email:</label>
                    <div class="col-sm-4"><input type="text" id="txt_Email" name="email" value="{{ old('email', $user->EmailId) }}" class="form-control" autocomplete="off" maxlength="100"></div>
                </div>
                <div class="row mt-2">
                    <label class="col-sm-2 col-form-label">Pincode:</label>
                    <div class="col-sm-4"><input type="text" id="txt_Pincode" name="pincode" value="{{ old('pincode', $user->Pincode) }}" class="form-control" autocomplete="off" maxlength="6"></div>
                    <label class="col-sm-2 col-form-label">Password:</label>
                    <div class="col-sm-4">
                        {{-- Source bug, preserved: pre-filled from Available_Amount, not the real password. See EditUserController docblock. --}}
                        <input type="password" id="txt_Password" name="password" value="" class="form-control" autocomplete="off" maxlength="20" placeholder="*****">
                        @if ($revealedPassword !== null)
                            <span class="fw-bold text-danger">{{ $revealedPassword }}</span>
                        @endif
                        @if ($showPasswordButton)
                            <div class="d-sm-flex align-items-center justify-content-between mb-2 mt-2">
                                <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalPopUpIP"><i class="ri-add-line"></i> View Password</a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="row mt-2">
                    <label class="col-sm-2 col-form-label">Gender:</label>
                    <div class="col-sm-4" id="rb_Gender">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="gender_m" value="M" {{ old('gender', $user->Gender) === 'M' ? 'checked' : '' }}>
                            <label class="form-check-label" for="gender_m">Male</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="gender_f" value="F" {{ old('gender', $user->Gender) === 'F' ? 'checked' : '' }}>
                            <label class="form-check-label" for="gender_f">Female</label>
                        </div>
                    </div>
                    <label class="col-sm-2 col-form-label">Status:</label>
                    <div class="col-sm-4">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="status_active" value="1" {{ (string) old('status', $user->Status) === '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="status_active">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="status_inactive" value="0" {{ (string) old('status', $user->Status) === '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="status_inactive">InActive</label>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <label class="col-sm-2 col-form-label">Address:</label>
                    {{-- Source bug, preserved: pre-filled from the City column, not Address. See EditUserController docblock. --}}
                    <div class="col-sm-4"><textarea id="txt_Address" name="address" class="form-control" autocomplete="off" maxlength="200">{{ old('address', $user->City) }}</textarea></div>
                    <label class="col-sm-1 col-form-label">SID:</label>
                    <div class="col-sm-3"><input type="text" id="txt_SID" name="sid" value="{{ old('sid', $user->SID) }}" class="form-control" maxlength="20"></div>
                </div>

                <div class="row mt-2">
                    <label class="col-sm-2 col-form-label">Bank Name:</label>
                    <div class="col-sm-4"><input type="text" id="txt_bank_name" name="bank_name" value="{{ old('bank_name', $user->BankName) }}" class="form-control" autocomplete="off" maxlength="500"></div>
                    <label class="col-sm-2 col-form-label">Account Holder Name:</label>
                    <div class="col-sm-4"><input type="text" id="txt_account_holder_name" name="account_holder_name" value="{{ old('account_holder_name', $user->AccountHolderName) }}" class="form-control" autocomplete="off" maxlength="500"></div>
                </div>
                <div class="row mt-2">
                    <label class="col-sm-2 col-form-label">Account Number:</label>
                    <div class="col-sm-4"><input type="text" id="txt_account_no" name="account_no" value="{{ old('account_no', $user->Account) }}" class="form-control" autocomplete="off" maxlength="50"></div>
                    <label class="col-sm-2 col-form-label">IFSC Code:</label>
                    <div class="col-sm-4"><input type="text" id="txt_ifsc" name="ifsc" value="{{ old('ifsc', $user->ifsc) }}" class="form-control" autocomplete="off" maxlength="15"></div>
                </div>

                <div class="row mt-2">
                    <label class="col-sm-2 col-form-label">Payout Callback:</label>
                    <div class="col-sm-4"><input type="text" id="txt_payout_callback" name="payout_callback" value="{{ old('payout_callback', $user->Payout_Url) }}" class="form-control" maxlength="500"></div>
                    <label class="col-sm-2 col-form-label">Payin Callback:</label>
                    <div class="col-sm-4"><input type="text" id="txt_payin_callback" name="payin_callback" value="{{ old('payin_callback', $user->Callback_URL) }}" class="form-control" maxlength="500"></div>
                </div>

                <div class="row mt-2 align-items-center">
                    <div class="form-check col-lg-4 col-md-6 col-sm-6">
                        <input class="form-check-input" type="checkbox" id="ck_payout_flag_a" name="payout_flag_a" value="1" {{ old('payout_flag_a', (int) $user->payout_flag === 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="ck_payout_flag_a">Payout Activated</label>
                    </div>
                    <div class="form-check col-lg-4 col-md-6 col-sm-6">
                        <input class="form-check-input" type="checkbox" id="ck_rl_time_st_on_a" name="rl_time_st_on_a" value="1" {{ old('rl_time_st_on_a', (int) $user->RTSettlementOn === 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="ck_rl_time_st_on_a">Realtime Settlement ON</label>
                    </div>
                    <label class="col-sm-1 col-form-label">Failed Count:</label>
                    <div class="col-sm-3"><input type="text" id="txt_Failed_Count" name="failed_count" value="{{ old('failed_count', $user->FailedCount) }}" class="form-control {{ (int) $user->FailedCount > 10 ? 'text-danger' : '' }}"></div>
                </div>

                <div class="row mt-2 align-items-center">
                    <label class="col-sm-1 col-form-label">PayIN Channel:</label>
                    <div class="col-sm-2">
                        <select id="pay_in_channel" name="pay_in_channel" class="form-select">
                            <option value="Coinnected" {{ old('pay_in_channel', $user->Payin_Chnl) === 'Coinnected' ? 'selected' : '' }}>Coinnected</option>
                            <option value="Paytara" {{ old('pay_in_channel', $user->Payin_Chnl) === 'Paytara' ? 'selected' : '' }}>Paytara</option>
                        </select>
                    </div>
                    <label class="col-sm-1 col-form-label">PayOUT Channel:</label>
                    <div class="col-sm-2">
                        <select id="pay_out_channel" name="pay_out_channel" class="form-select">
                            <option value="2" {{ (string) old('pay_out_channel', $user->PayoutServiceID) === '2' ? 'selected' : '' }}>RazorPay</option>
                            <option value="12" {{ (string) old('pay_out_channel', $user->PayoutServiceID) === '12' ? 'selected' : '' }}>MizorPay</option>
                            <option value="13" {{ (string) old('pay_out_channel', $user->PayoutServiceID) === '13' ? 'selected' : '' }}>ICASH</option>
                        </select>
                    </div>
                    <label class="col-sm-1 col-form-label">Payin Tax:</label>
                    <div class="col-sm-2"><input type="text" id="txt_payin_tax" name="payin_tax" value="{{ old('payin_tax', $user->Tax) }}" class="form-control"></div>
                    <label class="col-sm-1 col-form-label">Payout Tax:</label>
                    <div class="col-sm-2"><input type="text" id="txt_payout_tax" name="payout_tax" value="{{ old('payout_tax', $user->Pay_Tax) }}" class="form-control"></div>
                </div>

                <div class="row mt-2">
                    <label class="col-sm-1 col-form-label">Attached Agent:</label>
                    <div class="col-sm-5">
                        <select id="dp_agent" name="agent_id" class="form-select">
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->ID }}" {{ (string) old('agent_id', $user->AgentID) === (string) $agent->ID ? 'selected' : '' }}>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="col-sm-1 col-form-label">Jio Working Key:</label>
                    <div class="col-sm-5"><input type="text" id="Jio_workingkey" name="working_key" value="{{ old('working_key') }}" class="form-control" maxlength="200"></div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 text-center">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('admin.users') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- "View Password" modal - port of EditUser.aspx's modalPopUpIP. Its
         GetPassword button uses formaction/formnovalidate to submit to a
         separate route while staying inside the one big form above, the
         same way the source's single <form runat="server"> handles both
         btnSubmit and btn_get_pasword via server-side postback routing. --}}
    <div id="modalPopUpIP" class="modal fade" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Put Admin Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <label class="col-sm-2 col-form-label">Admin Password</label>
                        <div class="col-sm-8">
                            <input type="password" id="txt_Password_super" name="admin_password" class="form-control" form="edit-user-form">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-sm-12 text-center">
                            <button type="submit" form="edit-user-form" formaction="{{ route('admin.users.edit.reveal-password', $userId) }}" formnovalidate onclick="return AllPassword();" class="btn btn-primary">GetPassword</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
