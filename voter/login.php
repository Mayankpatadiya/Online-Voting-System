<?php
session_start();
if (isset($_SESSION['voter_id'])) {
    header("Location: ../index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Online Voting System - Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">

<div class="container">
  <div class="row justify-content-center align-items-center vh-100">
    <div class="col-md-5">
      <div class="card shadow">
        <div class="card-header text-center bg-primary text-white">
          <h4>Online Voting System Login</h4>
        </div>
        <div class="card-body">
          <form id="loginForm" action="">
            <div class="mb-3">
              <label for="username" class="form-label">username</label>
              <input type="text" class="form-control" id="username" name="username" required autofocus>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="rememberMe">
              <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>
            <div id="loginMsg" class="mb-3 text-danger"></div>
            <button type="submit" class="btn btn-primary w-100">Sign In</button>
          </form>
        </div>
        <div class="card-footer text-center">
          <a href="#">Forgot password?</a> | <a href="register.php">Register</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS (for modal, etc.) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function(){
  $('#loginForm').on('submit', function(e){
    e.preventDefault();
    $('#loginMsg').text('');
    $.ajax({
      url: 'login-process.php',
      type: 'POST',
      data: {
        voterId: $('#username').val().trim(), // Changed
        password: $('#password').val()
      },
      dataType: 'json',
      success: function(response){
        if(response.success){
          window.location.href = '../index.php';
        } else {
          $('#loginMsg').text(response.message);
        }
      },
      // error: function(xhr){
      //   $('#loginMsg').text('Server error. Please try again.');
      //   console.log(xhr.responseText); // Debug
      // }
    });
  });
});
</script>

</body>
</html>
