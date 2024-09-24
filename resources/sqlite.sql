-- #!sqlite

-- # { players
-- #  { initialize
CREATE TABLE IF NOT EXISTS martiantools_players (
    uuid VARCHAR(36) PRIMARY KEY,
    username VARCHAR(16)
);
-- #  }

-- #  { select
SELECT *
FROM martiantools_players;
-- #  }

-- #  { create
-- #      :uuid string
-- #      :username string
INSERT OR REPLACE INTO martiantools_players(uuid, username)
VALUES (:uuid, :username);
-- #  }

-- #  { update
-- #      :uuid string
-- #      :username string
UPDATE martiantools_players
SET username = :username
WHERE uuid = :uuid;
-- #  }

-- #  { delete
-- #      :uuid string
DELETE FROM martiantools_players
WHERE uuid = :uuid;
-- #  }
-- # }

-- # { warnings
-- #  { initialize
CREATE TABLE IF NOT EXISTS martiantools_warnings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid VARCHAR(36),
    reason TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uuid) REFERENCES martiantools_players(uuid) ON DELETE CASCADE
);
-- # }

-- # { select
SELECT * FROM martiantools_warnings;
-- # }

-- # { create
-- #      :uuid string
-- #      :reason string
INSERT INTO martiantools_warnings (uuid, reason) 
VALUES (:uuid, :reason);
-- # }

-- # { update
-- #      :id integer
-- #      :reason string
UPDATE martiantools_warnings
SET reason = :reason, timestamp = CURRENT_TIMESTAMP
WHERE id = :id;
-- # }

-- # { delete
-- #      :id integer
DELETE FROM martiantools_warnings WHERE id = :id;
-- #  }
-- # }

-- # { mutes
-- #  { initialize
CREATE TABLE IF NOT EXISTS martiantools_mutes (
    mute_id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid VARCHAR(36),
    reason TEXT,
    staff VARCHAR(16),
    duration INTEGER,
    muted_until DATETIME,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uuid) REFERENCES martiantools_players(uuid) ON DELETE CASCADE
);
-- # }

-- # { select
SELECT * FROM martiantools_mutes WHERE uuid = :uuid;
-- # }

-- # { add 
-- #   :uuid string
-- #   :reason string
-- #   :staff string
-- #   :duration int
-- #   :muted_until datetime
INSERT INTO martiantools_mutes(uuid, reason, staff, duration, muted_until)
VALUES (:uuid, :reason, :staff, :duration, :muted_until);
-- # }

-- # { update 
-- #   :mute_id integer
-- #   :reason string
-- #   :duration int
-- #   :muted_until datetime
UPDATE martiantools_mutes
SET reason = :reason, duration = :duration, muted_until = :muted_until
WHERE mute_id = :mute_id;
-- #  }
-- # }

-- # { reports 
-- #  { initialize
CREATE TABLE IF NOT EXISTS martiantools_reports (
    report_id INTEGER PRIMARY KEY AUTOINCREMENT,
    reporter_uuid VARCHAR(36),
    reported_uuid VARCHAR(36),
    reason TEXT,
    staff_assigned VARCHAR(16),
    status TEXT DEFAULT 'unresolved',
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_uuid) REFERENCES martiantools_players(uuid) ON DELETE CASCADE,
    FOREIGN KEY (reported_uuid) REFERENCES martiantools_players(uuid) ON DELETE CASCADE
);
-- # }

-- # { select 
SELECT * FROM martiantools_reports WHERE reporter_uuid = :uuid;
-- # }

-- # { add 
-- #   :reporter_uuid string
-- #   :reported_uuid string
-- #   :reason string
INSERT INTO martiantools_reports(reporter_uuid, reported_uuid, reason)
VALUES (:reporter_uuid, :reported_uuid, :reason);
-- # }

-- # { update 
-- #   :report_id integer
-- #   :status string
UPDATE martiantools_reports
SET status = :status
WHERE report_id = :report_id;
-- #  }
-- # }

-- # { kicks
-- #  { initialize
CREATE TABLE IF NOT EXISTS martiantools_kicks (
    kick_id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid VARCHAR(36),
    reason TEXT,
    staff VARCHAR(16),
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uuid) REFERENCES martiantools_players(uuid) ON DELETE CASCADE
);
-- # }

-- # { add kick
-- #      :uuid string
-- #      :reason string
-- #      :staff string
INSERT INTO martiantools_kicks(uuid, reason, staff)
VALUES (:uuid, :reason, :staff);
-- # }

-- # { update
-- #      :kick_id integer
-- #      :staff string
-- #      :reason string
UPDATE martiantools_kicks
SET reason = :reason, staff = :staff
WHERE kick_id = :kick_id;
-- #  }
-- # }
