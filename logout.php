<?php
session_start();
unset($_SESSION['roulette_user_name']);
unset($_SESSION['roulette_user_id']);
unset($_SESSION['roulette_user_ip']);
unset($_SESSION['roulette_refresh_token']);
?>
<script>window.location.href="./loginpage.php";</script>