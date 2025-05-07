-- Add is_read column to request_messages table
ALTER TABLE request_messages ADD COLUMN is_read BOOLEAN NOT NULL DEFAULT FALSE;