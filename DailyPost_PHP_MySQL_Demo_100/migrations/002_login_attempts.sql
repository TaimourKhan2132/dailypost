-- ---------------------------------------------------------------
-- DailyPost migration 002
-- Records failed admin logins so we can lock out password guessing.
--
-- Without this, someone can try passwords against admin/auth.php as
-- fast as the server will answer - thousands per minute. With it,
-- five wrong guesses buys a fifteen minute wait.
--
-- Only FAILED attempts are stored, and the address is hashed the
-- same way as submission_log.
-- ---------------------------------------------------------------

CREATE TABLE IF NOT EXISTS login_attempts (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  ip_hash     CHAR(64) NOT NULL,
  attempted_at DATETIME NOT NULL,
  INDEX (ip_hash, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
