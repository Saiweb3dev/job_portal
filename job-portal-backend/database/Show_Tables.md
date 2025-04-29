# Problem

When working with MySQL databases, sometimes you need to quickly view data from all tables in a database without writing separate SELECT statements for each table.

## Solution

The solution involves creating a stored procedure that loops through all tables in a database and displays their data.

### Steps

#### Step 1: Create a Stored Procedure

```sql
DELIMITER //
CREATE PROCEDURE show_all_tables()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE tbl VARCHAR(100);
    DECLARE cur CURSOR FOR
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'job_portal'; -- Replace 'job_portal' with your database name
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO tbl;
        IF done THEN
            LEAVE read_loop;
        END IF;

        SET @sql = CONCAT('SELECT "', tbl, '" AS table_name FROM ', tbl, ' LIMIT 1');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;

        SET @sql = CONCAT('SELECT * FROM ', tbl);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END LOOP;

    CLOSE cur;
END //
DELIMITER ;
```

#### Step 2: Call the Stored Procedure

```sql
CALL show_all_tables();
```

#### Step 3: Clean Up

After viewing the data, you should drop the procedure:

```sql
DROP PROCEDURE show_all_tables;
```

## How It Works

1. The procedure creates a cursor that loops through all tables in the specified database.
2. For each table:
    - It first displays the table name in a separate result set
    - Then it shows all data from that table
3. This approach avoids issues with different table structures that would occur when using UNION ALL.
