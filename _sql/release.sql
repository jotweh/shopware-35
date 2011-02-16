-- Release.sql for Shopware 3.5.4
-- STORED PROCEDURES
drop procedure if exists AddColumnUnlessExists;
DELIMITER $$
create procedure AddColumnUnlessExists(
	IN dbName tinytext,
	IN tableName tinytext,
	IN fieldName tinytext,
	IN fieldDef text)
begin
	IF NOT EXISTS (
		SELECT * FROM information_schema.COLUMNS
		WHERE column_name=fieldName
		and table_name=tableName
		and table_schema=dbName
		)
	THEN
		set @ddl=CONCAT('ALTER TABLE ',dbName,'.',tableName,
			' ADD COLUMN ',fieldName,' ',fieldDef);
		prepare stmt from @ddl;
		execute stmt;
	END IF;
end;
$$
DELIMITER ;

-- Notices
-- Every sql changeset needs a corresponding ticket-id, author and change-date
-- Example: #T3333 / st.hamann / 12.03.2011

-- New structures

-- Changes on structure
-- Sample: call AddColumnUnlessExists(Database(), 'cb_events', 'event_test2', 'varchar(32) null AFTER `cb_datetime`');

-- Cleanup 
drop procedure AddColumnUnlessExists;