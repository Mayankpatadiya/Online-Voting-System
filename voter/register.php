<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Voter Registration</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link rel="stylesheet" href="../assets/css/style.css">

  <style>
    body {
      background: linear-gradient(135deg, #e0f7fa, #f0f4f8);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }
   
  </style>
</head>
<body>

  <div class="card shadow-lg animate__animated animate__fadeInDown">
    <div class="card-header">
      <i class="fas fa-vote-yea fa-2x mb-2"></i>
      <h2>Voter Registration</h2>
      <small>Register securely to vote</small>
    </div>
    <div class="card-body p-4">
      <form id="registerForm">
      <div class="text-center mt-3" id="message"></div>

        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" name="name" required placeholder="John Doe">
        </div>
       
        <div class="mb-3">
          <label class="form-label">Date of Birth</label>
          <input type="date" class="form-control" name="dob" required required max="2009-10-03">
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" required placeholder="you@example.com">
        </div>
        <div class="mb-3 position-relative">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" name="password" id="password" required minlength="6">
          <i class="fa fa-eye toggle-password" onclick="togglePassword('password')"></i>
        </div>
        <div class="mb-3 position-relative">
          <label class="form-label">Confirm Password</label>
          <input type="password" class="form-control" name="confirmPassword" id="confirmPassword" required minlength="6">
          <i class="fa fa-eye toggle-password" onclick="togglePassword('confirmPassword')"></i>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
      </form>
    </div>
    <div class="card-footer text-center bg-light py-3">
      Already registered? <a href="login.php" class="text-primary text-decoration-underline">Login here</a>
    </div>
  </div>

  <!-- JS Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function togglePassword(id) {
      const input = document.getElementById(id);
      input.type = input.type === "password" ? "text" : "password";
    }

   $('#registerForm').submit(function(e) {
  e.preventDefault();
  $('#message').removeClass().text('');

  const dob = new Date($('input[name="dob"]').val());
  const today = new Date();
  const ageDiff = today.getFullYear() - dob.getFullYear();
  const monthDiff = today.getMonth() - dob.getMonth();
  const dayDiff = today.getDate() - dob.getDate();

  let age = ageDiff;
  if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
    age--;
  }

  if (isNaN(dob.getTime())) {
    $('#message').addClass('text-danger animate__animated animate__shakeX').text("Please enter a valid date of birth.");
    return;
  }

  if (age < 16) {
    $('#message').addClass('text-danger animate__animated animate__shakeX').text("You must be at least 16 years old to register.");
    return;
  }

  const formData = $(this).serialize();
  const password = $('#password').val();
  const confirmPassword = $('#confirmPassword').val();

  if (password !== confirmPassword) {
    $('#message').addClass('text-danger animate__animated animate__shakeX').text("Passwords do not match.");
    return;
  }

  $('button[type="submit"]').prop('disabled', true).text('Registering...');

  $.ajax({
    url: 'register-process.php',
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        $('#message').removeClass().addClass('text-success animate__animated animate__fadeIn').html(
          response.message + '<br><strong>Please save your Voter ID for login.</strong>'
        );
        setTimeout(() => {
          window.location.href = response.redirect;
        }, 5000);
      } else {
        $('#message').removeClass().addClass('text-danger animate__animated animate__shakeX').text(response.message);
        $('button[type="submit"]').prop('disabled', false).text('Register');
      }
    }
  });
});

  </script>
</body>
</html>
