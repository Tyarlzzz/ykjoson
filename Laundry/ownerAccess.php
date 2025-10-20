<?php
session_start();

// If already logged in, redirect to inventory page
if (isset($_SESSION['owner_logged_in']) && $_SESSION['owner_logged_in'] === true) {
  header('Location: expenses.php');
  exit;
}

$stored_hash = '$2y$10$0fOdy1oX7pZxiczZgZI/vuKOOj4Taj4kZZXIwNOQAr26jyIcp1wNa'; // owner123

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $password = trim($_POST['password']);

  // Verify the password against the stored hash
  if (password_verify($password, $stored_hash)) {
    $_SESSION['owner_logged_in'] = true;
    header('Location: expenses.php');
    exit;
  } else {
    $error = 'Invalid password. Please try again.';
  }
}
?>

<?php require '../layout/header.php'; ?>

<main class="flex flex-1 items-center justify-center min-h-screen bg-gray-100">
  <div class="w-full max-w-md p-8 bg-white rounded-xl shadow-lg">
    <div class="text-center mb-8">
      <h2 class="text-2xl font-bold">Owner Access Only</h2>
    </div>
    <?php if ($error): ?>
      <div class="mb-4 text-center text-red-600 font-semibold">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <form method="POST" autocomplete="off">
      <div class="space-y-3">
        <label for="password" class="block text-sm font-medium text-gray-700 ms-1 mb-3">
          PASSWORD
        </label>
        <div class="relative">
          <input type="password" id="password" name="password"
            class="w-full px-4 py-2 mb-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
            placeholder="Enter password" required>
          <button type="button" id="togglePassword"
            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
            <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg id="eyeSlashIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            </svg>
          </button>
        </div>
        <a href="#" class="text-sm ms-1 mt-3">
          Forgot password?
        </a>
      </div>

      <div class="flex justify-end mt-6">
        <button type="submit" class="bg-blue-500 text-white font-semibold py-2 px-6 rounded-lg">
          Confirm
        </button>
      </div>

    </form>
  </div>
</main>

<script>

  // Toggle password visibility
  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  const eyeIcon = document.getElementById('eyeIcon');
  const eyeSlashIcon = document.getElementById('eyeSlashIcon');

  togglePassword.addEventListener('click', function () {
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    eyeIcon.classList.toggle('hidden');
    eyeSlashIcon.classList.toggle('hidden');
  });

  // Handle forgot password
  const forgotPassword = document.getElementById('forgotPassword');
  forgotPassword.addEventListener('click', function () {
    fetch('../auth/send.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      }
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Forgotten your password? It was sent to your email');
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        alert('Forgotten your password? It was sent to your email');
      });
  });

</script>

<?php
require '../layout/footer.php';
