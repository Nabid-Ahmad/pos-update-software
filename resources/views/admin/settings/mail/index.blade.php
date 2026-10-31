@extends('admin.master')
@section('title', ' - Mail Settings')

@section('content')
<main class="app-content">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa fa-envelope"></i> SMTP Mail Settings</h5>
                </div>
                <div class="card-body">
                    @include('partials._alert_message')

                    <form action="{{ url('mail-settings') }}" method="POST">
                        @csrf

                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Mail Mailer <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" name="mail_mailer" class="form-control" value="{{ old('mail_mailer', $setting->mail_mailer ?? 'smtp') }}" placeholder="smtp" required>
                                <small class="text-muted">Usually 'smtp'</small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Mail Host <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" name="mail_host" class="form-control" value="{{ old('mail_host', $setting->mail_host ?? '') }}" placeholder="smtp.gmail.com" required>
                                <small class="text-muted">For Gmail: smtp.gmail.com</small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Mail Port <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" name="mail_port" class="form-control" value="{{ old('mail_port', $setting->mail_port ?? '') }}" placeholder="587" required>
                                <small class="text-muted">Usually 465 or 587</small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Mail Username <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" name="mail_username" class="form-control" value="{{ old('mail_username', $setting->mail_username ?? '') }}" placeholder="your-email@example.com" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Mail Password <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="password" name="mail_password" class="form-control" value="{{ old('mail_password', $setting->mail_password ?? '') }}" placeholder="Your App Password" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Mail Encryption</label>
                            <div class="col-sm-8">
                                <select name="mail_encryption" class="form-control">
                                    <option value="">None</option>
                                    <option value="tls" {{ old('mail_encryption', $setting->mail_encryption ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ old('mail_encryption', $setting->mail_encryption ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Mail From Address <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="email" name="mail_from_address" class="form-control" value="{{ old('mail_from_address', $setting->mail_from_address ?? '') }}" placeholder="no-reply@example.com" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">Mail From Name <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" name="mail_from_name" class="form-control" value="{{ old('mail_from_name', $setting->mail_from_name ?? '') }}" placeholder="Your Company Name" required>
                            </div>
                        </div>

                        <div class="form-group row mt-4">
                            <div class="col-sm-12 text-right">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Settings</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
