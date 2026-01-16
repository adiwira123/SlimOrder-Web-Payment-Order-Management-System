<?php
function isLogin() {
    return isset($_SESSION['user']);
}
