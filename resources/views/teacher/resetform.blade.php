@extends('layout')

@section('content')
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0f2f5;
    margin: 0;
    padding: 0;
  }

  .container {
    max-width: 400px;
    margin: 60px auto;
    background: #fff;
    padding: 40px 30px;
    border-radius: 10px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
  }

  h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #333;
  }

  label {
    font-size: 14px;
    font-weight: 600;
    display: block;
    margin-bottom: 8px;
    color: #333;
  }

  input[type="email"],
  input[type="password"] {
    width: 100%;
    padding: 12px 14px;
    font-size: 15px;
    margin-bottom: 20px;
    border: 1.5px solid #ccc;
    border-radius: 6px;
    background-color: #f9f9f9;
    transition: border-color 0.3s;
  }

  input:focus {
    border-color: #1877f2;
    outline: none;
    background-color: #fff;
  }

  .btn-submit {
    width: 100%;
    padding: 12px;
    background-color: #1877f2;
    color: white;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.25s;
  }

  .btn-submit:hover {
    background-color: #145cc0;
  }

  .error-box {
    background: #ffe5e5;
    border-left: 4px solid #f02849;
    padding: 12px 16px;
    margin-bottom: 20px;
    border-radius: 6px;
    color: #d1001f;
    font-size: 14px;
  }
</style>

<div class="container">
  <h2>Set a New Password</h2>

  @if ($errors->any())
    <div class="error-box">
      @foreach ($errors->all() as $error)
        <p>{{ $error }}</p>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('password.update') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="{{ old('email') }}" required>

    <label for="password">New Password</label>
    <input id="password" type="password" name="password" required>

    <label for="password_confirmation">Confirm Password</label>
    <input id="password_confirmation" type="password" name="password_confirmation" required>

    <button type="submit" class="btn-submit">Reset Password</button>
  </form>
</div>
@endsection
