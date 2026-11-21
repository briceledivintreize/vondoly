-- =====================================================================
--  Socle « Plume » – schéma de la base SQLite
--  Un fichier = un site d'auteur. Aucune valeur propre à l'auteur ici.
-- =====================================================================
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS settings (
  key   TEXT PRIMARY KEY,
  value TEXT
);

-- Textes éditables du site (accroches, libellés, boutons…), par langue
CREATE TABLE IF NOT EXISTS texts (
  key   TEXT NOT NULL,
  lang  TEXT NOT NULL DEFAULT 'fr',
  value TEXT,
  PRIMARY KEY (key, lang)
);

-- Traductions des contenus (livres, actualités…) pour les langues additionnelles
CREATE TABLE IF NOT EXISTS translations (
  entity    TEXT NOT NULL,
  entity_id INTEGER NOT NULL,
  field     TEXT NOT NULL,
  lang      TEXT NOT NULL,
  value     TEXT,
  PRIMARY KEY (entity, entity_id, field, lang)
);

-- Images traitées (recadrées, compressées, déclinées en AVIF/WebP/JPEG)
CREATE TABLE IF NOT EXISTS media (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  base       TEXT NOT NULL,          -- chemin relatif sans suffixe : uploads/2026/abcd
  widths     TEXT NOT NULL,          -- JSON [400,800,…]
  formats    TEXT NOT NULL,          -- JSON ["avif","webp","jpg"]
  width      INTEGER, height INTEGER,-- dimensions de la plus grande déclinaison
  orig_width INTEGER, orig_height INTEGER,
  ratio      TEXT,                   -- '4:5', '3:2', null = libre
  alt        TEXT,
  color      TEXT,                   -- couleur dominante (placeholder)
  created_at TEXT DEFAULT (datetime('now'))
);

-- Emplacements photo du site (section 3.5) : portraits, parcours, maison…
CREATE TABLE IF NOT EXISTS photos (
  id       INTEGER PRIMARY KEY AUTOINCREMENT,
  slot     TEXT NOT NULL,
  media_id INTEGER REFERENCES media(id) ON DELETE SET NULL,
  caption  TEXT, year TEXT,
  visible  INTEGER DEFAULT 1,
  sort     INTEGER DEFAULT 0,
  note     TEXT
);

CREATE TABLE IF NOT EXISTS genres (
  id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, slug TEXT, sort INTEGER DEFAULT 0
);
CREATE TABLE IF NOT EXISTS publishers (
  id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, slug TEXT, city TEXT, url TEXT,
  sort INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS books (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  slug           TEXT UNIQUE,
  title          TEXT NOT NULL,
  subtitle       TEXT,
  genre_id       INTEGER REFERENCES genres(id) ON DELETE SET NULL,
  genre_label    TEXT,               -- libellé exact si différent du genre de filtre
  year           INTEGER,
  edition_num    INTEGER DEFAULT 1,
  publisher_id   INTEGER REFERENCES publishers(id) ON DELETE SET NULL,
  place          TEXT,
  collection     TEXT,
  preface        TEXT,
  postface       TEXT,
  pages          TEXT,
  isbn           TEXT,
  summary        TEXT,
  publisher_link TEXT,
  special_note   TEXT,               -- « ouvrage collectif »…
  is_collective  INTEGER DEFAULT 0,
  other_edition_id INTEGER,
  cover_id       INTEGER REFERENCES media(id) ON DELETE SET NULL,
  back_id        INTEGER REFERENCES media(id) ON DELETE SET NULL,
  cover_alt      TEXT,
  featured       INTEGER DEFAULT 0,
  visible        INTEGER DEFAULT 1,
  sort           INTEGER DEFAULT 0,
  note           TEXT,               -- note interne (jamais publiée)
  created_at     TEXT DEFAULT (datetime('now')),
  updated_at     TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS timeline (
  id INTEGER PRIMARY KEY AUTOINCREMENT, year_label TEXT NOT NULL, title TEXT, text TEXT,
  visible INTEGER DEFAULT 1, sort INTEGER DEFAULT 0, note TEXT
);

CREATE TABLE IF NOT EXISTS associations (
  id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, role TEXT, since TEXT, text TEXT, url TEXT,
  visible INTEGER DEFAULT 1, sort INTEGER DEFAULT 0, note TEXT
);

CREATE TABLE IF NOT EXISTS distinctions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  scope TEXT DEFAULT 'personal',      -- personal | house
  status TEXT,                        -- Lauréat, Finaliste, Nommé, Mention…
  label TEXT NOT NULL,                -- intitulé exact
  year TEXT, source TEXT, source_url TEXT,
  image_id INTEGER REFERENCES media(id) ON DELETE SET NULL,
  caption TEXT,
  visible INTEGER DEFAULT 1, sort INTEGER DEFAULT 0, note TEXT
);

CREATE TABLE IF NOT EXISTS press (
  id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, media_name TEXT, date_label TEXT,
  date_sort TEXT, excerpt TEXT, url TEXT,
  image_id INTEGER REFERENCES media(id) ON DELETE SET NULL,
  visible INTEGER DEFAULT 1, sort INTEGER DEFAULT 0, note TEXT
);

CREATE TABLE IF NOT EXISTS testimonials (
  id INTEGER PRIMARY KEY AUTOINCREMENT, quote TEXT NOT NULL, name TEXT, role TEXT, book TEXT,
  visible INTEGER DEFAULT 1, sort INTEGER DEFAULT 0, note TEXT
);

CREATE TABLE IF NOT EXISTS embeds (
  id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, url TEXT NOT NULL, date_label TEXT,
  description TEXT, visible INTEGER DEFAULT 1, sort INTEGER DEFAULT 0, note TEXT
);

CREATE TABLE IF NOT EXISTS news (
  id INTEGER PRIMARY KEY AUTOINCREMENT, slug TEXT UNIQUE, type TEXT DEFAULT 'parution',
  title TEXT NOT NULL, date TEXT, end_date TEXT, place TEXT, excerpt TEXT, body TEXT, link TEXT,
  image_id INTEGER REFERENCES media(id) ON DELETE SET NULL,
  is_appearance INTEGER DEFAULT 0,    -- apparaît dans « Prochaine apparition » si date future
  visible INTEGER DEFAULT 1, sort INTEGER DEFAULT 0, note TEXT,
  created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS house_collections (
  id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, text TEXT,
  visible INTEGER DEFAULT 1, sort INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS house_figures (
  id INTEGER PRIMARY KEY AUTOINCREMENT, value TEXT NOT NULL, label TEXT NOT NULL,
  visible INTEGER DEFAULT 1, sort INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS social_links (
  id INTEGER PRIMARY KEY AUTOINCREMENT, network TEXT NOT NULL, label TEXT, url TEXT,
  in_footer INTEGER DEFAULT 1, visible INTEGER DEFAULT 1, sort INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT UNIQUE NOT NULL, name TEXT,
  password_hash TEXT NOT NULL, role TEXT DEFAULT 'editor',   -- admin | editor
  active INTEGER DEFAULT 1, last_login TEXT, created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS messages (
  id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, phone TEXT, subject TEXT, body TEXT,
  is_read INTEGER DEFAULT 0, mailed INTEGER DEFAULT 0, created_at TEXT DEFAULT (datetime('now'))
);

-- Statistiques sans cookie (aucune donnée personnelle stockée)
CREATE TABLE IF NOT EXISTS stats_views (
  day TEXT NOT NULL, path TEXT NOT NULL, views INTEGER DEFAULT 0, PRIMARY KEY (day, path)
);
CREATE TABLE IF NOT EXISTS stats_visitors (
  day TEXT NOT NULL, hash TEXT NOT NULL, country TEXT, PRIMARY KEY (day, hash)
);
CREATE TABLE IF NOT EXISTS stats_events (
  day TEXT NOT NULL, event TEXT NOT NULL, ref TEXT NOT NULL DEFAULT '', count INTEGER DEFAULT 0,
  PRIMARY KEY (day, event, ref)
);

CREATE TABLE IF NOT EXISTS throttle (
  bucket TEXT NOT NULL, ts INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS throttle_idx ON throttle(bucket, ts);

CREATE TABLE IF NOT EXISTS audit (
  id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, action TEXT, target TEXT,
  created_at TEXT DEFAULT (datetime('now'))
);
