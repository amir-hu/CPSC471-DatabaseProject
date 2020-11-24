USE ChildDaycare;

-- This is just an example. This will be called in the 'read.php' file.

/*
 * Table: DAYCARE
 * Description: Selects all attributes from the table.
 * Input: the name of the daycare.
 */
DROP PROCEDURE IF EXISTS SelectDaycare;
CREATE PROCEDURE SelectDaycare (
      IN name VARCHAR(100)
    , IN address VARCHAR(100)
    )

BEGIN
    SELECT
         DaycareName
       , DaycareAddress
       , TotalNumOfCaretakers
    FROM DAYCARE
    WHERE DaycareName = name;
END;


