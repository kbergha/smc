/* === Users === */
CREATE TABLE IF NOT EXISTS users(
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  username      TEXT UNIQUE NOT NULL,
  password      TEXT NOT NULL,
  status        INTEGER DEFAULT NULL,
  created       TEXT NOT NULL DEFAULT CURRENT_DATE,
  last_login    TEXT DEFAULT NULL
) STRICT;

/* admin / password / require reset */
INSERT INTO users (username, password, status)
VALUES ('admin', '$2y$12$NDaH6etcZwSuZtcxIkD0EumdecXMRMoWnX.uvePUleM7FNruCD4LO', 2)
ON CONFLICT(username) DO NOTHING;

/* === Pages === */
CREATE TABLE IF NOT EXISTS pages(
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  title       TEXT NOT NULL,
  slug        TEXT UNIQUE NOT NULL,
  user_id     INTEGER,
  FOREIGN KEY(user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) STRICT;

/* === Migrations === */
CREATE TABLE IF NOT EXISTS migrations(
  id                INTEGER PRIMARY KEY AUTOINCREMENT,
  migration_name    TEXT UNIQUE NOT NULL,
  applied_at        TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
) STRICT;
