@extends('admin.master')
@section('title', ' - Send Sms')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .select2-selection {
            /* height: 34px !important; */
        }

        .select2-selection ul li{
            /* height: 23px; */
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #818181;
        }
    </style>
@endsection
@section('content')
    <main class="app-content">
        <div class="app-title">
            <div class="div">
                <h1><i class="fa fa-laptop"></i> Sms Send</h1>
                <p>Send SMS Form</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item"><a href="#">Sms Send</a></li>
            </ul>
        </div>

        <!-- Navs-->
        <div class="tile mb-4">
            <div class="row">
                <div class="col-lg-12">
                    <div class="page-header">
                        <h2 class="mb-3 line-head" id="navs">Send SMS</h2>
                    </div>
                </div>
            </div>

            <div class="alert-message"></div>

            <form action="{{ route('all-sms-sending') }}" method="post">
            @csrf


                <div class="row">

                    <div class="col-12 form-group">
                        <label for="" style="font-weight: bolder ; font-size: 20px">Message : </label><br>
                        <textarea name="message" class="form-control-text" id="editor" cols="30" rows="3" placeholder="Type Message"></textarea>
                    </div>


                    <div class="col-12 form-group">
                        <label for="" style="font-weight: bolder ; font-size: 20px">Selected Numbers</label>
                        <select name="numbers[]" class="form-control select2" id="" multiple>
                            @foreach ($numbers as $number)
                                <option value="{{ $number }}" selected>{{ $number }}</option>
                            @endforeach
                        </select>
                    </div>




                    <div class="col-12 text-right">
                        <button type="submit" class="btn btn-info ">Send SMS</button>
                    </div>
                </div>
            </form>
        </div>
    </main>
@endsection

@section('footer-script')
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- <script src="{{ asset('assets/custom_js/send-custom-sms.js') }}?{{ fdate(now(), 'Y-m-d h:m') }}"></script> --}}
    <script>
        ClassicEditor
            .create(document.querySelector('#editor'))
            .catch(error => {
                console.error(error);
            });
    </script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });

    </script>
@endsection
