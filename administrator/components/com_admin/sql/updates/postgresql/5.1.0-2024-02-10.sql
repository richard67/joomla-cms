ALTER TABLE "#__guidedtour_steps" ADD COLUMN "params" text /** CAN FAIL **/;
UPDATE "#__guidedtour_steps" SET "params" = '{}' WHERE "params" IS NULL;
ALTER TABLE "#__guidedtour_steps" ALTER COLUMN "params" SET NOT NULL;
