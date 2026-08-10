@extends('layout')

@section('content')
<style>
  /* Reset & base */
  * {
    box-sizing: border-box;
  }
  body, html {
    height: 100%;
    margin: 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
      Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
    background: #f0f2f5;
  }

  .container {
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
  }

  .reset-card {
    background: white;
    padding: 40px 35px;
    border-radius: 12px;
    box-shadow: 0 12px 24px rgba(0,0,0,0.15);
    width: 360px;
    max-width: 100%;
    text-align: center;
  }

  .reset-card h2 {
    margin-bottom: 25px;
    font-weight: 700;
    font-size: 28px;
    color: #050505;
  }

  .input-group {
    margin-bottom: 25px;
    text-align: left;
  }

  label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
  }

  input[type="email"] {
    width: 100%;
    padding: 12px 15px;
    font-size: 16px;
    border: 1.8px solid #ccd0d5;
    border-radius: 8px;
    transition: border-color 0.3s ease;
  }

  input[type="email"]:focus {
    outline: none;
    border-color: #1877f2;
    box-shadow: 0 0 5px rgba(24,119,242,0.5);
    background: #fff;
  }

  .btn-submit {
    background-color: #1877f2;
    color: white;
    font-weight: 700;
    border: none;
    width: 100%;
    padding: 14px;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.25s ease;
  }

  .btn-submit:hover {
    background-color: #165ec9;
  }

  .message {
    margin-bottom: 20px;
    font-weight: 600;
    color: #4BB543; /* success green */
    font-size: 15px;
  }

  .error {
    color: #f02849;
    font-weight: 600;
    font-size: 13px;
    margin-top: -15px;
    margin-bottom: 15px;
  }
</style>

<div class="container">
  <div class="reset-card">
    <h2>Reset Your Password</h2>

    @if (session('status'))
      <div class="message">
        {{ session('status') }}
      </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
      @csrf
      <div class="input-group">
        <label for="email">Email Address</label>
        <input 
          id="email" 
          type="email" 
          name="email" 
          placeholder="you@example.com" 
          value="{{ old('email') }}" 
          required 
          autofocus
        >
        @error('email')
          <div class="error">{{ $message }}</div>
        @enderror
      </div>

      <button type="submit" class="btn-submit">Send Password Reset Link</button>
    </form>
  </div>
</div>
@endsection

