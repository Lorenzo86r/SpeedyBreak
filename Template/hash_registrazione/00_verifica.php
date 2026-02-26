<?php
// 1. The password the user is trying to log in with
$loginAttempt = "MySecretPassword123";

// 2. Fetch the stored hash from the database based on the username
// (Pseudo-code: SELECT password_hash FROM users WHERE username = 'alice')
// Let's pretend this is the string we retrieved from the classDBBiso in step 1:
$storedHashFromDB = '$2y$10$Qj/t.y.z.x.w.v.u.t.s.r.q.p.o.n.m.l.k.j.i.h.g.f.e.d.c.b.a'; 
// (Note: In a real lesson, copy-paste the output from register.php here to test)

// 3. Verify the password
if (password_verify($loginAttempt, $storedHashFromDB)) {
    echo "<h3>Login Successful!</h3>";
    echo "The password matches.";
} else {
    echo "<h3>Login Failed.</h3>";
    echo "Wrong password.";
}
?>