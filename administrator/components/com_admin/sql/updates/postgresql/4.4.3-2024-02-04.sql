ALTER TABLE "#__assets" ALTER COLUMN "name" TYPE character varying(180);
ALTER TABLE "#__assets" ALTER COLUMN "title" TYPE character varying(255);
ALTER TABLE "#__categories" ALTER COLUMN "extension" TYPE character varying(100);
ALTER TABLE "#__workflows" ALTER COLUMN "extension" TYPE character varying(160);
ALTER TABLE "#__workflow_associations" ALTER COLUMN "extension" TYPE character varying(160);
