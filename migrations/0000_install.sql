/* === Users === */
CREATE TABLE IF NOT EXISTS users(
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  username    TEXT UNIQUE NOT NULL,
  password    TEXT NOT NULL,
  created     TEXT NOT NULL DEFAULT CURRENT_DATE,
  last_login  TEXT DEFAULT NULL
) STRICT;

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
