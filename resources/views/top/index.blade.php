@extends('_layout.page')

@section('main')
    <div class="container">

        <!-- Main component for a primary marketing message or call to action -->
        <div class="jumbotron">
            <h1>PHP Qiita-ism</h1>
            <p>Mini Qiita clone for small team written by PHP and Laravel</p>
        </div>

    </div> <!-- /container -->

    <div class="container">

        <!-- Main component for a primary marketing message or call to action -->
        <div class="row">
            <h2>Login / SignUp</h2>
            <div class="col-sm-offset-1 col-sm-5">
                <form>
                    Google アカウントでログイン
                </form>
            </div>
        </div>
    </div> <!-- /container -->

@endsection


@section('js')
    <script type="text/javascript">
        $('#authSubmit').click(function (event) {
            event.target.innerHTML = 'ログイン中';
            return false;
        });
    </script>
@endsection