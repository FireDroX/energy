<?php 

function createAlert($message, $type = 'success') {
  $id = 'alert_' . uniqid();

  return "
  <style>
    .custom-alert {
      position: absolute;
      top: 5rem;
      right: 1.5rem;
      display: inline-block;
      width: fit-content;
      max-width: 400px;
      padding: 12px 18px;
      border-radius: 8px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
      z-index: 9999;
      opacity: 1;
      transition: opacity 0.5s ease, transform 0.5s ease;
      transform: translateY(0);
      font-family: sans-serif;
    }

    .custom-alert.alert-success {
      background: #d1e7dd;
      color: #0f5132;
      border: 1px solid #badbcc;
    }

    .custom-alert.alert-danger {
      background: #f8d7da;
      color: #842029;
      border: 1px solid #f5c2c7;
    }

    .custom-alert.alert-warning {
      background: #fff3cd;
      color: #664d03;
      border: 1px solid #ffecb5;
    }

    .custom-alert.alert-info {
      background: #cff4fc;
      color: #055160;
      border: 1px solid #b6effb;
    }
  </style>

  <div id=\"$id\" class=\"custom-alert alert alert-$type\" role=\"alert\">
    $message
  </div>

  <script>
    setTimeout(function() {
      var el = document.getElementById('$id');
      if (el) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(-10px)';
        setTimeout(() => el.remove(), 500);
      }
    }, 2000);
  </script>
  ";
}
?>